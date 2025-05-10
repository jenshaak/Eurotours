<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-04-05
 * Time: 11:27
 */

namespace AppBundle\Routers;


use AppBundle\Connectors\RegabusConnector;
use AppBundle\Entity\BookRegabus;
use AppBundle\Entity\BookStudentAgency;
use AppBundle\Entity\ExternalCityRegabus;
use AppBundle\Entity\ExternalStationRegabus;
use AppBundle\Entity\ExternalTariffRegabus;
use AppBundle\Entity\ExternalTicketRegabus;
use AppBundle\Entity\ExternalTicketStudentAgency;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Entity\SeatsPlan;
use AppBundle\Exceptions\RegabusConnectorException;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalStationService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\ExternalRouteRegabus;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RegabusBookRoute;
use AppBundle\VO\RegabusBookRoutePerson;
use AppBundle\VO\RouteFilter;
use AppBundle\VO\SelectSeatVO;
use Doctrine\Common\Collections\ArrayCollection;


/*
 * @deprecated
 * Nahradil blabla
 */
class RegabusRouter
{
	/**
	 * @var RegabusConnector
	 */
	private $connector;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var ExternalStationService
	 */
	private $externalStationService;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;

	public function __construct(RegabusConnector $connector,
	                            CityService $cityService,
	                            LanguageService $languageService,
	                            ExternalCityService $externalCityService,
	                            CarrierService $carrierService,
	                            CurrencyService $currencyService,
	                            ExternalStationService $externalStationService,
	                            RouteService $routeService,
	                            ExternalTariffService $externalTariffService)
	{
		$this->connector = $connector;
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->externalCityService = $externalCityService;
		$this->carrierService = $carrierService;
		$this->currencyService = $currencyService;
		$this->externalStationService = $externalStationService;
		$this->routeService = $routeService;
		$this->externalTariffService = $externalTariffService;
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @return array
	 * @throws \ErrorException
	 */
	public function findRoutes(SearchExternal $searchExternal)
	{
		$routeFilter = $searchExternal->getSearch()->createRouteFilter();

		/** @var Route[]|ArrayCollection $routes */
		$routes = new ArrayCollection;

		$fromList = $routeFilter->getFromCity()->getExternalCitiesByType(ExternalRouter::REGABUS);
		$toList = $routeFilter->getToCity()->getExternalCitiesByType(ExternalRouter::REGABUS);

		if ($fromList->isEmpty() or $toList->isEmpty() === null) return [];

		/** @var ExternalCityRegabus $fromExternalCity */
		/** @var ExternalCityRegabus $toExternalCity */
		foreach ($fromList->toArray() as $fromExternalCity) {
			foreach ($toList->toArray() as $toExternalCity) {
				try {
					$data = $this->connector->findRoute(
						$searchExternal->getCurrency(),
						$fromExternalCity->getIdent(),
						$toExternalCity->getIdent(),
						$routeFilter->getDateDay()
					);
				} catch (RegabusConnectorException $e) {
					continue;
				}

				foreach ($data as $d) {
					$route = $this->createRoute($searchExternal, $d, LineStation::DIRECTION_THERE, $fromExternalCity, $toExternalCity);
					if ($route) {
						$routes->add($route);
						$this->routeService->saveRoute($route);
					}
				}
			}
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			foreach ($fromList->toArray() as $fromExternalCity) {
				foreach ($toList->toArray() as $toExternalCity) {
					try {
						$data = $this->connector->findRoute(
							$searchExternal->getCurrency(),
							$toExternalCity->getIdent(),
							$fromExternalCity->getIdent(),
							$routeFilter->getDateBack()
						);
					} catch (RegabusConnectorException $e) {
						continue;
					}

					foreach ($data as $d) {
						$route = $this->createRoute($searchExternal, $d, LineStation::DIRECTION_BACK, $fromExternalCity, $toExternalCity);
						if ($route) {
							$routes->add($route);
							$this->routeService->saveRoute($route);
						}
					}
				}
			}
		}

		return $routes;
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @param object $routeData
	 * @param string $direction
	 * @return Route|null
	 * @throws \Exception
	 */
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction, ExternalCityRegabus $fromExternalCity, ExternalCityRegabus $toExternalCity)
	{
		$route = new Route;

		list($fromStationId, $toStationId) = explode(":", $routeData->stId);

		$route->setFromExternalStation($this->externalStationService->getExternalStationByIdent(
			$fromStationId, ExternalRouter::REGABUS
		));
		$route->setToExternalStation($this->externalStationService->getExternalStationByIdent(
			$toStationId, ExternalRouter::REGABUS
		));

		$route->setFromExternalCity($fromExternalCity);
		$route->setToExternalCity($toExternalCity);
		$route->setFromCity($fromExternalCity->getCity());
		$route->setToCity($toExternalCity->getCity());

		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->tripId);
		$route->setIsDirect(true);

		$route->setDatetimeDeparture(new \DateTime($routeData->dtDep));
		$route->setDatetimeArrival(new \DateTime($routeData->dtArr));
		$route->setCarrier($this->carrierService->getCarrierByCode("BlaBla"));
		$route->setCarrierTitle($routeData->carrier);
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create(ceil($routeData->price), $routeData->currency), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());
		$route->setCurrency($priceCurrency->getCurrency());

		$tripData = $this->connector->getTrip($searchExternal->getCurrency(), $route->getExternalIdent());
		if ($tripData === null) return null;
		$tripData = $tripData[0];

		$this->routeService->saveRoute($route);

		$discountPrice = null;
		if (isset($tripData->autoLgot)) {
			$discountPrice = ceil($tripData->autoLgot->price);
			$this->createFareForRoute($route, (object) [
				"name" => $tripData->autoLgot->name,
				"code" => $tripData->autoLgot->code,
				"price" => $discountPrice
			]);

			$priceCurrency = $this->currencyService->currencyConvert(
				PriceCurrency::create($discountPrice, $routeData->currency), $searchExternal->getCurrency()
			);
			$route->setPrice($priceCurrency->getPrice());
			$route->setCurrency($priceCurrency->getCurrency());
		} else {
			$this->createFareForRoute($route, (object) [
				"name" => "Standard",
				"code" => "standard",
				"price" => ceil($routeData->price)
			]);
		}

		if (isset($tripData->lgotes)) {
			array_map(function ($tariffData) use ($route, $discountPrice) {
				if ($discountPrice !== null and $tariffData->price > $discountPrice) return;
				$this->createFareForRoute($route, $tariffData);
			}, $tripData->lgotes);
		}

		return $route;
	}

	/**
	 * @param Route $route
	 * @param string $tariffName
	 */
	private function createFareForRoute(Route $route, $tariffData)
	{
		$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
			$tariffData->code, ExternalRouter::REGABUS
		);

		$language = $this->languageService->getEnglish();

		if ($externalTariff === null) {
			$externalTariff = new ExternalTariffRegabus;
			$externalTariff->setIdent($tariffData->code);
			$externalTariff->setName($language, $tariffData->name);
			$this->externalTariffService->saveExternalTariff($externalTariff);
		}

		$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
		$routeTariff->setPrice(ceil($tariffData->price));
		$routeTariff->setExternalBookingIdent($tariffData->code);
	}

	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();

		$language = $this->languageService->getEnglish();

		$cities = $this->connector->getCities();
		foreach ($cities as $c) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($c->id, ExternalRouter::REGABUS);
			if ($externalCity === null) {
				$externalCity = new ExternalCityRegabus;
				$externalCity->setIdent($c->id);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					if ($city->getName()->getString($language) == $c->nameEn || (mb_strtolower($city->getNextVariations()) == mb_strtolower($c->nameEn))) {
						$externalCity->setCity($city);
					}
				}
			}

			$externalCity->setName($language, $c->nameEn);
			$this->externalCityService->saveExternalCity($externalCity);
		}

		$stations = $this->connector->getStations();
		foreach ($stations as $s) {
			$externalStation = $this->externalStationService->getExternalStationByIdent($s->id, ExternalRouter::REGABUS);
			if ($externalStation === null) {
				$externalStation = new ExternalStationRegabus;
				$externalStation->setIdent($s->id);
				$externalStation->setExternalCity(
					$this->externalCityService->getExternalCityByIdent($s->city, ExternalRouter::REGABUS)
				);
			}

			$externalStation->setName($language, $s->name);
			$this->externalStationService->saveExternalStation($externalStation);
		}
	}

	/**
	 * @param Route $route
	 * @return BookRegabus[]
	 */
	public function bookRoute(Route $route)
	{
		/** @var ExternalRouteRegabus $externalObject */
		$externalObject = $route->getExternalObject();

		/** @var Order $order */
		$order = $route->getBooks()->map(function (BookRegabus $book) {
			return $book->getOrder();
		})->first();

		$bookRoute = new RegabusBookRoute;
		$bookRoute->setBuyId($this->connector->buyInit());
		$bookRoute->setId($route->getExternalIdent());

		$order->getOrderPersonRouteTariffs()->map(function (OrderPersonRouteTariff $orderPersonRouteTariff) use ($externalObject, $order, $bookRoute) {
			$tariffIdent = $orderPersonRouteTariff->getRouteTariff()->getExternalTariff()->getIdent();
			$tariffIdent = str_replace("standard", "", $tariffIdent);

			$orderPerson = $orderPersonRouteTariff->getOrderPerson();
			list($firstName, $lastName) = explode(" ", $orderPerson->getName());

			/** @var BookRegabus $book */
			$book = $orderPersonRouteTariff->getBook();

			$bookRoute->addPerson($person = new RegabusBookRoutePerson);
			$person->setRaceId($externalObject->getRaceId());
			$person->setFirstName($firstName);
			$person->setLastName($lastName);
			$person->setPhone($orderPerson->getPhone());
			$person->setEmail($order->getEmail());
			$person->setBaggage(0);
			$person->setTariffIdent($tariffIdent);
			$person->setSeatNumber($book->getSeatNumber());
		});

		$route->getBooks()->map(function (BookRegabus $book) use ($bookRoute) {
			$book->setBuyId($bookRoute->getBuyId());
		});

		return $this->connector->bookRoute($route, $bookRoute);
	}

	public function loadExternalObjectForRoute(Route $route)
	{
		$externalObject = $route->getExternalObject();
		if ($externalObject instanceof ExternalRouteRegabus) {
			if ($externalObject->getDatetimeUpdated() > new \DateTime("-1 minute")) {
				return;
			}
		}

		$tripData = $this->connector->getTrip($route->getCurrency(), $route->getExternalIdent());
		$externalObject = new ExternalRouteRegabus;
		$externalObject->setDatetimeUpdated(new \DateTime);
		$externalObject->setSeats($tripData[0]->busTempl->matrix);
		$externalObject->setRaceId($tripData[0]->raceId);

		$route->setExternalObject($externalObject);
	}

	public function createSelectSeats(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$fakeSeatsPlan = new SeatsPlan;

		/** @var ExternalRouteRegabus $externalObject */
		$externalObject = $orderPersonRouteTariff->getRoute()->getExternalObject();
		foreach ($externalObject->getSeats() as $floor => $floorData) {
			foreach ($floorData as $column => $columnData) {
				foreach ($columnData as $row => $rowData) {
					if (isset($rowData->svc) and $rowData->svc === "none" and $column === 2) continue;
					$seat = $fakeSeatsPlan->createSeatForPosition(4 - $column, $row, $floor);
					$seat->setNumber(isset($rowData->n) ? $rowData->n : null);
					$seat->setAvailable(isset($rowData->free) and $rowData->free);
				}
			}
		}

		return SelectSeatVO::create($fakeSeatsPlan, $orderPersonRouteTariff);
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketRegabus[]
	 */
	public function buyRoute(Route $route)
	{
		return $this->connector->buyRoute($route);
	}

	public function canPayOnline(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("BlaBla")) return false;

		return true;
	}

	public function canBuyRoute(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("BlaBla")) return false;

		return true;
	}
}
