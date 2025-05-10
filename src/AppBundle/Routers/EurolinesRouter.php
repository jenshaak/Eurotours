<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 07.11.17
 * Time: 15:27
 */

namespace AppBundle\Routers;


use AppBundle\Connectors\EurolinesConnector;
use AppBundle\Connectors\EurolinesConnectorOLD;
use AppBundle\Entity\ExternalCityEurolines;
use AppBundle\Entity\ExternalStationEurolines;
use AppBundle\Entity\ExternalTariffEurolines;
use AppBundle\Entity\ExternalTicketEurolines;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalStationService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\ExternalTicketService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @deprecated
 */
class EurolinesRouter
{
	const TARIFF_ECONOMY = "economy";
	const TARIFF_PROMO = "promo";
	const TARIFF_SMALL_CHILD = "small_child";
	const TARIFF_BIGGER_CHILD = "bigger_child";
	const TARIFF_JUNIOR = "junior";
	const TARIFF_SENIOR = "senior";
	const TARIFF_ISIC = "isic";

	/**
	 * @var EurolinesConnector
	 */
	private $connector;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var ExternalStationService
	 */
	private $externalStationService;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;
	/**
	 * @var ExternalTicketService
	 */
	private $externalTicketService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;

	public function __construct(LanguageService $languageService,
	                            CarrierService $carrierService,
	                            RouteService $routeService,
	                            ExternalStationService $externalStationService,
	                            ExternalTariffService $externalTariffService,
	                            ExternalTicketService $externalTicketService,
	                            CurrencyService $currencyService,
	                            CityService $cityService,
	                            ExternalCityService $externalCityService,
	                            EurolinesConnector $connector)
	{
		$this->connector = $connector;
		$this->languageService = $languageService;
		$this->carrierService = $carrierService;
		$this->routeService = $routeService;
		$this->externalStationService = $externalStationService;
		$this->externalTariffService = $externalTariffService;
		$this->externalTicketService = $externalTicketService;
		$this->currencyService = $currencyService;
		$this->cityService = $cityService;
		$this->externalCityService = $externalCityService;
	}

	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();

		$language = $this->languageService->getEnglish();

		$cities = $this->connector->getCities();
		foreach ($cities as $c) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($c->id, ExternalRouter::EUROLINES);
			if ($externalCity === null) {
				$externalCity = new ExternalCityEurolines;
				$externalCity->setIdent($c->id);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					foreach ($c->names as $name) {
						if ($city->getName()->getString($language) == $name) {
							$externalCity->setCity($city);
						}
					}
				}
			}

			$externalCity->setName($language, $externalCity->getCity() ? $externalCity->getCity()->getName()->getString($language) : $c->names[0]);
			$this->externalCityService->saveExternalCity($externalCity);
		}
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @return \AppBundle\Entity\Route[]|array|ArrayCollection
	 */
	public function findRoutes(SearchExternal $searchExternal)
	{
		$englishLanguage = $this->languageService->getEnglish();
		$routeFilter = $searchExternal->getSearch()->createRouteFilter();

		/** @var Route[]|ArrayCollection $routes */
		$routes = new ArrayCollection;

		$from = $routeFilter->getFromCity()->getExternalCity(ExternalRouter::EUROLINES);
		$to = $routeFilter->getToCity()->getExternalCity(ExternalRouter::EUROLINES);

		if ($from === null or $to === null) return [];

		$data = $this->connector->findRoute(
			$from->getIdent(),
			$to->getIdent(),
			$routeFilter->getDateDay()
		);

		foreach ($data as $r) {
			$route = $this->createRoute($searchExternal, $r, LineStation::DIRECTION_THERE);
			$this->routeService->saveRoute($route);
			$routes->add($route);
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			$data = $this->connector->findRoute(
				$to->getIdent(),
				$from->getIdent(),
				$routeFilter->getDateBack()
			);

			foreach ($data as $r) {
				$route = $this->createRoute($searchExternal, $r, LineStation::DIRECTION_BACK);
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
	 * @return Route
	 */
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction)
	{
		$route = new Route;

		$departureStop = md5($routeData->departure->busStop);
		$fromStation = $this->externalStationService->getExternalStationByIdent($departureStop, ExternalRouter::EUROLINES);
		if ($fromStation === null) {
			$fromStation = new ExternalStationEurolines;
			$fromStation->setIdent($departureStop);
			$fromStation->setName($this->languageService->getCzech(), $routeData->departure->busStop);
			$this->externalStationService->saveExternalStation($fromStation);
		}
		$route->setFromExternalStation($fromStation);

		$arrivalStop = md5($routeData->arrival->busStop);
		$toStation = $this->externalStationService->getExternalStationByIdent($arrivalStop, ExternalRouter::EUROLINES);
		if ($toStation === null) {
			$toStation = new ExternalStationEurolines;
			$toStation->setIdent($arrivalStop);
			$toStation->setName($this->languageService->getEnglish(), $routeData->arrival->busStop);
			$this->externalStationService->saveExternalStation($toStation);
		}
		$route->setToExternalStation($toStation);

		if ($direction === LineStation::DIRECTION_THERE) {
			$route->setFromCity($searchExternal->getSearch()->getFromCity());
			$route->setToCity($searchExternal->getSearch()->getToCity());
		} else {
			$route->setToCity($searchExternal->getSearch()->getFromCity());
			$route->setFromCity($searchExternal->getSearch()->getToCity());
		}
		$route->setDatetimeDeparture(new \DateTime($routeData->departure->time));
		$route->setDatetimeArrival(new \DateTime($routeData->arrival->time));
		$route->setCarrier($this->carrierService->getCarrierByCode("E"));
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->bestPrice, PriceCurrency::CZK), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());
		$route->setCurrency($priceCurrency->getCurrency());
		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->id);
		$this->routeService->saveRoute($route);

		foreach ($routeData->prices as $priceData) {
			if ($priceData->price == 0) continue;
			if ($priceData->isPromo) continue; # u proma se meni cena, neumime zajistit stejnou cenu pred / pri nakupu
			$this->createFareForRoute($route, $priceData);
		}

		return $route;
	}

	/**
	 * @param Route $route
	 * @param \stdClass $routeData
	 */
	private function createFareForRoute(Route $route, $routeData)
	{
		$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
			$routeData->priceTypeId, ExternalRouter::EUROLINES
		);

		if ($externalTariff === null) {
			$externalTariff = new ExternalTariffEurolines;
			$externalTariff->setIdent($routeData->priceTypeId);
			$externalTariff->setName($this->languageService->getCzech(), $routeData->priceTypeName);
			$this->externalTariffService->saveExternalTariff($externalTariff);
		}

		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->price, PriceCurrency::CZK), $route->getCurrency()
		);

		$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $priceCurrency->getCurrency());
		$routeTariff->setPrice($priceCurrency->getPrice());
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketEurolines
	 */
	public function buyRoute(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		list($firstName, $lastName) = explode(" ", $orderPerson->getName());
		$externalTicket = ExternalTicketEurolines::create($route, $routeTariff, $orderPerson);

		/** @var ExternalTariffEurolines $externalTariff */
		$externalTariff = $routeTariff->getExternalTariff();

		$this->connector->buyRoute(
			$route->getFromCity()->getName()->getString($this->languageService->getEnglish()),
			$route->getToCity()->getName()->getString($this->languageService->getEnglish()),
			$firstName,
			$lastName,
			$route->getDatetimeDeparture(),
			$routeTariff->getExternalTariff()->getIdent() === self::TARIFF_PROMO,
			$externalTicket,
			$externalTariff
		);

		return $externalTicket;
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @return bool
	 */
	public function canBuyRoute(Route $route, RouteTariff $routeTariff)
	{
		return false; # nakup mame zatim vypnuty, musi se predelat

		if ($route->getCarrier() != $this->carrierService->getCarrierByCode("E")) return false;
		if ($routeTariff->getExternalTariff() === null) return false;

		return true;
	}
}
