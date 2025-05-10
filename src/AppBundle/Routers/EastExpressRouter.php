<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 07.09.17
 * Time: 16:13
 */

namespace AppBundle\Routers;


use AppBundle\Connectors\EastExpressConnector;
use AppBundle\Entity\ExternalCityEastExpress;
use AppBundle\Entity\ExternalTariffEastExpress;
use AppBundle\Entity\ExternalTicketEastExpress;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\RouteTariffService;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use Doctrine\Common\Collections\ArrayCollection;

class EastExpressRouter
{
	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var EastExpressConnector
	 */
	private $connector;
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
	 * @var RouteTariffService
	 */
	private $routeTariffService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;

	public function __construct(EastExpressConnector $connector,
	                            ExternalCityService $externalCityService,
	                            CityService $cityService,
	                            LanguageService $languageService,
	                            CarrierService $carrierService,
	                            RouteService $routeService,
	                            ExternalTariffService $externalTariffService,
	                            RouteTariffService $routeTariffService,
	                            CurrencyService $currencyService)
	{
		$this->externalCityService = $externalCityService;
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->connector = $connector;
		$this->carrierService = $carrierService;
		$this->routeService = $routeService;
		$this->externalTariffService = $externalTariffService;
		$this->routeTariffService = $routeTariffService;
		$this->currencyService = $currencyService;
	}

	public function syncExternals()
	{
		$czechLanguage = $this->languageService->getCzech();
		$allCities = $this->cityService->findAllCities();
		$cities = $this->connector->getCities();

		foreach ($cities as $cityId => $cityName) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($cityId, ExternalRouter::EAST_EXPRESS);
			if ($externalCity === null) {
				$externalCity = new ExternalCityEastExpress();
				$externalCity->setIdent($cityId);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					if ($city->getName()->getString($czechLanguage) == trim($cityName)) {
						$externalCity->setCity($city);
					}
				}
			}

			$externalCity->setName($czechLanguage, trim($cityName));
			$this->externalCityService->saveExternalCity($externalCity);
		}

		$tariffs = $this->connector->getTariffs();
		foreach ($tariffs as $tariffId => $tariffName) {
			$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
				$tariffId, ExternalRouter::EAST_EXPRESS
			);

			if ($externalTariff === null) {
				$externalTariff = new ExternalTariffEastExpress();
				$externalTariff->setIdent($tariffId);
				$externalTariff->setName($czechLanguage, $tariffName);
				if (preg_match("~(?P<percent>[0-9]+)%~", $tariffName, $buff)) {
					$externalTariff->setPercent($buff['percent']);
				}
				$this->externalTariffService->saveExternalTariff($externalTariff);
			}
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

		$from = $routeFilter->getFromCity()->getExternalCity(ExternalRouter::EAST_EXPRESS);
		$to = $routeFilter->getToCity()->getExternalCity(ExternalRouter::EAST_EXPRESS);

		if ($from === null or $to === null) return [];

		$data = $this->connector->findRoute(
			$from->getIdent(),
			$to->getIdent(),
			$routeFilter->getDateDay()
		);

		foreach ($data as $r) {
			if (count($r->Seats) <= 1) continue;
			$routes->add($this->createRoute($searchExternal, $r, LineStation::DIRECTION_THERE, $from->getIdent(), $to->getIdent()));
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			$data = $this->connector->findRoute(
				$to->getIdent(),
				$from->getIdent(),
				$routeFilter->getDateBack()
			);

			foreach ($data as $r) {
				if (count($r->Seats) <= 1) continue;
				$routes->add($this->createRoute($searchExternal, $r, LineStation::DIRECTION_BACK, $to->getIdent(), $from->getIdent()));
			}
		}

		return $routes;
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @param object $routeData
	 * @param string $direction
	 * @param string $from
	 * @param string $to
	 * @return Route
	 */
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction, $from, $to)
	{
		$route = new Route;
		$route->setFromExternalCity($this->externalCityService->getExternalCityByIdent(
			$from, ExternalRouter::EAST_EXPRESS
		));
		$route->setToExternalCity($this->externalCityService->getExternalCityByIdent(
			$to, ExternalRouter::EAST_EXPRESS
		));

		$route->setFromCity($route->getFromExternalCity()->getCity());
		$route->setToCity($route->getToExternalCity()->getCity());
		$route->setDatetimeDeparture(new \DateTime($routeData->DepartureDate . " " . $routeData->DepartureTime));
		$route->setDatetimeArrival(new \DateTime($routeData->ArrivalDate . " " . $routeData->ArrivalTime));
		$route->setCarrier($this->carrierService->getCarrierByCode("EE"));
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->PriceAmount, $routeData->Currency), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());
		$route->setCurrency($priceCurrency->getCurrency());
		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->LineId);
		$this->routeService->saveRoute($route);

		$this->processFares($route);

		return $route;
	}

	/**
	 * @param Route $route
	 */
	private function processFares(Route $route)
	{
		/** @var ExternalTariffEastExpress[] $tariffs */
		$tariffs = $this->externalTariffService->findExternalTariffsForType(ExternalRouter::EAST_EXPRESS);
		foreach ($tariffs as $externalTariff) {
			$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
			$routeTariff->setPrice($route->getPrice() - (($route->getPrice() / 100) * $externalTariff->getPercent()));
			$this->routeTariffService->saveRouteTariff($routeTariff);
		}
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @return bool
	 */
	public function canBuyRoute(Route $route, RouteTariff $routeTariff)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("EE")) return false;
		if ($routeTariff->getExternalTariff() === null) return false;

		return true;
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketEastExpress
	 */
	public function buyRoute(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		$htmlBody = $this->connector->buyRoute(
			$route->getExternalIdent(),
			$route->getDatetimeDeparture(),
			$orderPerson->getName(),
			$orderPerson->getPhone(),
			$route->getFromExternalCity()->getIdent(),
			$route->getToExternalCity()->getIdent(),
			$routeTariff->getExternalTariff()->getIdent()
		);

		$externalTicket = ExternalTicketEastExpress::create($route, $routeTariff, $orderPerson);
		$externalTicket->setHtmlBody($htmlBody);

		return $externalTicket;
	}


}
