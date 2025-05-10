<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 31.10.17
 * Time: 19:15
 */

namespace AppBundle\Routers;


use AppBundle\Connectors\FlixbusConnector;
use AppBundle\Entity\ExternalCityFlixbus;
use AppBundle\Entity\ExternalStationFlixbus;
use AppBundle\Entity\ExternalTariffFlixbus;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\DateTimeService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalStationService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use Doctrine\Common\Collections\ArrayCollection;

class FlixbusRouter
{
	const TARIFF_ADULT = "ADULT";
	const TARIFF_CHILD = "CHILD";

	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var FlixbusConnector
	 */
	private $connector;
	/**
	 * @var ExternalStationService
	 */
	private $externalStationService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var DateTimeService
	 */
	private $dateTimeService;

	public function __construct(FlixbusConnector $connector,
	                            CityService $cityService,
	                            ExternalCityService $externalCityService,
	                            LanguageService $languageService,
	                            ExternalStationService $externalStationService,
	                            CarrierService $carrierService,
	                            RouteService $routeService,
	                            ExternalTariffService $externalTariffService,
	                            CurrencyService $currencyService,
	                            DateTimeService $dateTimeService)
	{
		$this->cityService = $cityService;
		$this->externalCityService = $externalCityService;
		$this->languageService = $languageService;
		$this->connector = $connector;
		$this->externalStationService = $externalStationService;
		$this->carrierService = $carrierService;
		$this->routeService = $routeService;
		$this->externalTariffService = $externalTariffService;
		$this->currencyService = $currencyService;
		$this->dateTimeService = $dateTimeService;
	}

	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();

		$language = $this->languageService->getEnglish();

		$cities = $this->connector->getCities();
		foreach ($cities as $c) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($c->id, ExternalRouter::FLIXBUS);
			if ($externalCity === null) {
				$externalCity = new ExternalCityFlixbus;
				$externalCity->setIdent($c->id);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					if ($city->getName()->getString($language) == $c->name) {
						$externalCity->setCity($city);
					}
				}
			}

			$externalCity->setName($language, $c->name);
			$this->externalCityService->saveExternalCity($externalCity);
		}

		$stations = $this->connector->getStations();
		foreach ($stations as $s) {
			$externalStation = $this->externalStationService->getExternalStationByIdent($s->id, ExternalRouter::FLIXBUS);
			if ($externalStation === null) {
				$externalStation = new ExternalStationFlixbus;
				$externalStation->setIdent($s->id);
				$externalStation->setExternalCity(
					$this->externalCityService->getExternalCityByIdent($s->cityId, ExternalRouter::FLIXBUS)
				);
			}
			$externalStation->setName($language, $s->name);
			$this->externalStationService->saveExternalStation($externalStation);
		}
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @return \AppBundle\Entity\Route[]|array|ArrayCollection
	 */
	public function findRoutes(SearchExternal $searchExternal)
	{
		$routeFilter = $searchExternal->getSearch()->createRouteFilter();

		/** @var Route[]|ArrayCollection $routes */
		$routes = new ArrayCollection;

		$from = $routeFilter->getFromCity()->getExternalCity(ExternalRouter::FLIXBUS);
		$to = $routeFilter->getToCity()->getExternalCity(ExternalRouter::FLIXBUS);

		if ($from === null or $to === null) return [];

		$dataAdult = $this->connector->findRoutes(
			$from->getIdent(),
			$to->getIdent(),
			$routeFilter->getDateDay(),
			1, 0
		);

		$dataChildren = $this->connector->findRoutes(
			$from->getIdent(),
			$to->getIdent(),
			$routeFilter->getDateDay(),
			0, 1
		);

		foreach ($dataAdult as $key => $r) {
			$route = $this->createRoute($searchExternal, $r, LineStation::DIRECTION_THERE, $from->getIdent(), $to->getIdent());
			if ($route === null) continue;
			$this->createFareForRoute($searchExternal, $route, $r, self::TARIFF_ADULT);
			$this->createFareForRoute($searchExternal, $route, $dataChildren[$key], self::TARIFF_CHILD);
			$this->routeService->saveRoute($route);
			$routes->add($route);
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			$dataAdult = $this->connector->findRoutes(
				$to->getIdent(),
				$from->getIdent(),
				$routeFilter->getDateBack(),
				1, 0
			);

			$dataChildren = $this->connector->findRoutes(
				$to->getIdent(),
				$from->getIdent(),
				$routeFilter->getDateBack(),
				0, 1
			);

			foreach ($dataAdult as $key => $r) {
				$route = $this->createRoute($searchExternal, $r, LineStation::DIRECTION_BACK, $to->getIdent(), $from->getIdent());
				if ($route === null) continue;
				$this->createFareForRoute($searchExternal, $route, $r, self::TARIFF_ADULT);
				$this->createFareForRoute($searchExternal, $route, $dataChildren[$key], self::TARIFF_CHILD);
				$this->routeService->saveRoute($route);
				$routes->add($route);
			}
		}

		return $routes;
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @param object $routeData
	 * @param string $direction
	 * @param string $fromCity
	 * @param string $toCity
	 * @return Route|null
	 */
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction, $fromCity, $toCity)
	{
		$route = new Route;
		$route->setFromExternalCity($this->externalCityService->getExternalCityByIdent(
			$fromCity, ExternalRouter::FLIXBUS
		));
		$route->setToExternalCity($this->externalCityService->getExternalCityByIdent(
			$toCity, ExternalRouter::FLIXBUS
		));
		$route->setFromExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData->departure->city_id, ExternalRouter::FLIXBUS
		));
		$route->setToExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData->arrival->city_id, ExternalRouter::FLIXBUS
		));

		$route->setFromCity($route->getFromExternalCity()->getCity());
		$route->setToCity($route->getToExternalCity()->getCity());

		if ($route->getFromExternalStation()) {
			$route->setFromStation($route->getFromExternalStation()->getStation());
		}

		if ($route->getToExternalStation()) {
			$route->setToStation($route->getToExternalStation()->getStation());
		}

		$route->setDatetimeDeparture(new \DateTime($routeData->departure->date));
		$route->setDatetimeArrival(new \DateTime($routeData->arrival->date));
		$route->setCarrier($this->carrierService->getCarrierByCode("FB"));
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->price->total, PriceCurrency::CZK), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());
		$route->setCurrency($priceCurrency->getCurrency());
		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->uid);
		$route->setIsDirect(count($routeData->legs) === 1);

		if (!$this->filterRoute($route)) return null;

		$this->routeService->saveRoute($route);

		return $route;
	}

	private function filterRoute(Route $route): bool
	{
		// Nekdy je cena 0. Nevim proc, ale necham schovat.
		if ($route->getPrice() === 0) return false;

		// HACK: Neukazuju Flixbus spoje na Ukrajinu ktere maji prestup
		if ($route->getToCity()->getCountry()->getId() === 31) {
			return $route->getIsDirect();
		}

		return true;
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @param Route $route
	 * @param \stdClass $routeData
	 * @param string $tariffName
	 */
	private function createFareForRoute(SearchExternal $searchExternal, Route $route, $routeData, $tariffName)
	{
		$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
			$tariffName, ExternalRouter::FLIXBUS
		);

		if ($externalTariff === null) {
			$externalTariff = new ExternalTariffFlixbus;
			$externalTariff->setIdent($tariffName);
			$this->externalTariffService->saveExternalTariff($externalTariff);
		}

		$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, PriceCurrency::CZK);
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->price->total, PriceCurrency::CZK), $searchExternal->getCurrency()
		);
		$routeTariff->setPrice($priceCurrency->getPrice());
		$routeTariff->setCurrency($priceCurrency->getCurrency());
	}

	public function canPayOnline(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("FB")) return false;

		$datetime = (new \DateTime)->modify("+4 hours");
		if ($route->getDatetimeDeparture() > $datetime) {
			if ($this->dateTimeService->isWorkingTime(new \DateTime)) {
				return true;
			}
		}

		return false;
	}

	public function canBuyRoute(Route $route, RouteTariff $routeTariff)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("FB")) return false;
		if ($routeTariff->getExternalTariff() === null) return false;

		if ($this->canPayOnline($route)) {
			return true;
		}

		return false;
	}

}
