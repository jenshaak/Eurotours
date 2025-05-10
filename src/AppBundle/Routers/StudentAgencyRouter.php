<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 11:04
 */

namespace AppBundle\Routers;


use AppBundle\Connectors\StudentAgencyConnector;
use AppBundle\Entity\BookStudentAgency;
use AppBundle\Entity\ExternalCityStudentAgency;
use AppBundle\Entity\ExternalStationStudentAgency;
use AppBundle\Entity\ExternalTariffStudentAgency;
use AppBundle\Entity\ExternalTicketStudentAgency;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalStationService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\RouteTariffService;
use AppBundle\VO\ExternalRouter;
use AppBundle\Entity\Route;
use AppBundle\VO\OrderPersonRouteTariff;
use AppBundle\VO\RouteFilter;
use Doctrine\Common\Collections\ArrayCollection;

class StudentAgencyRouter
{
	/**
	 * @var StudentAgencyConnector
	 */
	private $connector;
	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var ExternalStationService
	 */
	private $externalStationService;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;
	/**
	 * @var RouteTariffService
	 */
	private $routeTariffService;

	public function __construct(StudentAgencyConnector $connector,
	                            ExternalCityService $externalCityService,
	                            LanguageService $languageService,
	                            CityService $cityService,
	                            ExternalStationService $externalStationService,
	                            RouteService $routeService,
	                            CarrierService $carrierService,
	                            ExternalTariffService $externalTariffService,
	                            RouteTariffService $routeTariffService)
	{
		$this->connector = $connector;
		$this->externalCityService = $externalCityService;
		$this->languageService = $languageService;
		$this->cityService = $cityService;
		$this->externalStationService = $externalStationService;
		$this->routeService = $routeService;
		$this->carrierService = $carrierService;
		$this->externalTariffService = $externalTariffService;
		$this->routeTariffService = $routeTariffService;
	}

	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();

		foreach ($this->languageService->getAllLanguages() as $language) {
			$cities = $this->connector->getCities($language->getId());
			foreach ($cities as $c) {
				$externalCity = $this->externalCityService->getExternalCityByIdent($c['id'], ExternalRouter::STUDENT_AGENCY);
				if ($externalCity === null) {
					$externalCity = new ExternalCityStudentAgency;
					$externalCity->setIdent($c['id']);
				}

				if (!$externalCity->isProcessed()) {
					foreach ($allCities as $city) {
						if ($city->getName()->getString($language) == str_replace(" (bus)", "", $c['name'])) {
							$externalCity->setCity($city);
						}
					}
				}

				$externalCity->setName($language, $c['name']);
				$this->externalCityService->saveExternalCity($externalCity);
			}

			$stations = $this->connector->getStations($language->getId());
			foreach ($stations as $s) {
				$externalStation = $this->externalStationService->getExternalStationByIdent($s['id'], ExternalRouter::STUDENT_AGENCY);
				if ($externalStation === null) {
					$externalStation = new ExternalStationStudentAgency();
					$externalStation->setIdent($s['id']);
					$externalStation->setExternalCity(
						$this->externalCityService->getExternalCityByIdent($s['cityId'], ExternalRouter::STUDENT_AGENCY)
					);
				}
				$externalStation->setName($language, $s['name']);
				$this->externalStationService->saveExternalStation($externalStation);
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

		$from = $routeFilter->getFromCity()->getExternalCity(ExternalRouter::STUDENT_AGENCY);
		$to = $routeFilter->getToCity()->getExternalCity(ExternalRouter::STUDENT_AGENCY);

		if ($from === null or $to === null) return [];

		//$tariffNames = [ "REGULAR", "CHILD", "YOUTH", "SENIOR", "ATTENDED_CHILD", "ISIC" ];
		$tariffNames = [ "REGULAR", "YOUTH", "SENIOR", "ISIC" ];

		foreach ($tariffNames as $tariffName) {
			$data = $this->connector->findSingleRoutes(
				$this->languageService->getCurrentLanguage()->getId(),
				$from->getIdent(),
				$to->getIdent(),
				$routeFilter->getDateDay(),
				$tariffName,
				$searchExternal->getCurrency()
			);

			if ($data === null) return [];

			foreach ($data['routes'] as $r) {
				$routeBuff = $this->createRoute($searchExternal, $r, LineStation::DIRECTION_THERE);
				$route = $routes->get($routeBuff->getExternalIdent());
				if ($route instanceof Route) {
					$this->createFareForRoute($route, $r, $tariffName);
				} else {
					$this->createFareForRoute($routeBuff, $r, $tariffName);
					$routes->set($routeBuff->getExternalIdent(), $routeBuff);
				}
			}

			$routes->map(function (Route $route) {
				$this->routeService->saveRoute($route);
			});
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			foreach ($tariffNames as $tariffName) {
				$data = $this->connector->findSingleRoutes(
					$this->languageService->getCurrentLanguage()->getId(),
					$to->getIdent(),
					$from->getIdent(),
					$routeFilter->getDateBack(),
					$tariffName,
					$searchExternal->getCurrency()
				);

				foreach ($data['routes'] as $r) {
					$routeBuff = $this->createRoute($searchExternal, $r, LineStation::DIRECTION_BACK);
					$route = $routes->get($routeBuff->getExternalIdent());
					if ($route instanceof Route) {
						$this->createFareForRoute($route, $r, $tariffName);
					} else {
						$this->createFareForRoute($routeBuff, $r, $tariffName);
						$routes->set($routeBuff->getExternalIdent(), $routeBuff);
					}
				}

				$routes->map(function (Route $route) {
					$this->routeService->saveRoute($route);
				});
			}
		}

		return $routes;
	}

	/**
	 * @param Order $order
	 * @param Route $route
	 * @param OrderPersonRouteTariff[] $orderPersonsRouteTariffs
	 * @return BookStudentAgency
	 * @throws \Exception
	 */
	public function bookRoute(Order $order, Route $route, $orderPersonsRouteTariffs)
	{
		$tariffsString = [];
		foreach ($orderPersonsRouteTariffs as $orderPersonRouteTariff) {
			$tariffsString[] = $orderPersonRouteTariff->getRouteTariff()->getExternalTariff()->getIdent();
		}

		return $this->connector->bookRoute(
			$order,
			$route,
			$order->getLanguage()->getId(),
			$route->getFromExternalStation()->getIdent(),
			$route->getToExternalStation()->getIdent(),
			$route->getDatetimeDeparture(),
			$order->getCurrency(),
			$tariffsString
		);
	}

	public function getStations()
	{
		return $this->connector->getStations("cs");
	}

	public function getSymbols()
	{
		return $this->connector->getSymbols("cs");
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @param array $routeData
	 * @param string $direction
	 * @return Route
	 */
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction)
	{
		$route = new Route;
		$route->setFromExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData['departureStationId'], ExternalRouter::STUDENT_AGENCY
		));
		$route->setToExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData['arrivalStationId'], ExternalRouter::STUDENT_AGENCY
		));
		$route->setFromCity($route->getFromExternalStation()->getExternalCity()->getCity());
		$route->setToCity($route->getToExternalStation()->getExternalCity()->getCity());
		$route->setPrice($routeData['price']);
		$route->setDatetimeDeparture($routeData['departureTime']);
		$route->setDatetimeArrival($routeData['arrivalTime']);
		$route->setCarrier($this->carrierService->getCarrierByCode("RegioJet"));
		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setCurrency($routeData['currency']);
		$route->setExternalIdent($routeData['uniqueIdent']);

		return $route;
	}

	/**
	 * @param Route $route
	 * @param array $routeData
	 * @param string $tariffName
	 */
	private function createFareForRoute(Route $route, $routeData, $tariffName)
	{
		$existsSamePrice = $route->getRouteTariffs()->exists(function ($key, RouteTariff $rt) use ($routeData) {
			return $rt->getPrice() === $routeData['price'];
		});

		if ($existsSamePrice) return;

		$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
			$tariffName, ExternalRouter::STUDENT_AGENCY
		);

		if ($externalTariff === null) {
			$externalTariff = new ExternalTariffStudentAgency;
			$externalTariff->setIdent($tariffName);
			$this->externalTariffService->saveExternalTariff($externalTariff);
		}

		$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
		$routeTariff->setPrice($routeData['price']);
		$routeTariff->setExternalBookingIdent($routeData['bookingCode']);
		$routeTariff->setPersonLimit($routeData['freeSeats']);
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketStudentAgency|null
	 */
	public function buyRoute(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		$externalTicket = ExternalTicketStudentAgency::create($route, $routeTariff, $orderPerson);
		/** @var BookStudentAgency $book */
		$book = $route->getBook();

		if ($book->getExternalTicket()) return null;

		$externalTicket->setTicketIdent($book->getAccountCode());

		$this->connector->buyRoute(
			$externalTicket,
			$book->getAccountCode()
		);

		$book->setExternalTicket($externalTicket);

		return $externalTicket;
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @return bool
	 */
	public function canBuyRoute(Route $route, RouteTariff $routeTariff)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("RegioJet")) return false;
		if ($routeTariff->getExternalTariff() === null) return false;

		return true;
	}

}
