<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-04-05
 * Time: 10:33
 */

namespace AppBundle\Routers;

use AppBundle\Connectors\SvitgoConnector;
use AppBundle\Entity\BookNikolo;
use AppBundle\Entity\BookRegabus;
use AppBundle\Entity\ExternalCity;
use AppBundle\Entity\ExternalCityNikolo;
use AppBundle\Entity\ExternalStation;
use AppBundle\Entity\ExternalStationNikolo;
use AppBundle\Entity\ExternalTariff;
use AppBundle\Entity\ExternalTariffNikolo;
use AppBundle\Entity\ExternalTariffRegabus;
use AppBundle\Entity\ExternalTicketNikolo;
use AppBundle\Entity\ExternalTicketRegabus;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Entity\SeatsPlan;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\DateTimeService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalStationService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\RouteTariffService;
use AppBundle\VO\ExternalRouteNikolo;
use AppBundle\VO\ExternalRouteSvitgo;
use AppBundle\VO\NikoloSellTicket;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use AppBundle\VO\SelectSeatVO;
use AppBundle\VO\SvitgoSellTicket;
use Doctrine\Common\Collections\ArrayCollection;

class SvitgoRouter
{
	const TARIFF_REGULAR = "REGULAR";

	/**
	 * @var SvitgoConnector
	 */
	private $connector;
	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;
	/**
	 * @var ExternalStationService
	 */
	private $externalStationService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
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
	 * @var DateTimeService
	 */
	private $dateTimeService;

	public function __construct(ExternalCityService $externalCityService,
	                            ExternalStationService $externalStationService,
	                            CityService $cityService,
	                            LanguageService $languageService,
	                            CarrierService $carrierService,
	                            CurrencyService $currencyService,
	                            RouteService $routeService,
	                            ExternalTariffService $externalTariffService,
	                            RouteTariffService $routeTariffService,
	                            DateTimeService $dateTimeService)
	{
		$this->externalCityService = $externalCityService;
		$this->externalStationService = $externalStationService;
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->carrierService = $carrierService;
		$this->currencyService = $currencyService;
		$this->routeService = $routeService;
		$this->externalTariffService = $externalTariffService;
		$this->routeTariffService = $routeTariffService;
		$this->dateTimeService = $dateTimeService;
	}

	/**
	 * @param SvitgoConnector $connector
	 */
	public function setConnector($connector)
	{
		$this->connector = $connector;
	}

	/**
	 * @return null|string
	 */
	protected function getExternalRouterClass()
	{
		return null;
	}

	/**
	 * @return null|string
	 */
	protected function getCarriedCode()
	{
		return null;
	}

	/**
	 * @return ExternalCityNikolo
	 */
	protected function createExternalCity()
	{
		return null;
	}

	/**
	 * @return ExternalStationNikolo
	 */
	protected function createExternalStation()
	{
		return null;
	}

	/**
	 * @return ExternalRouteNikolo
	 */
	protected function createExternalRoute()
	{
		return null;
	}

	/**
	 * @return ExternalTariffNikolo
	 */
	protected function createExternalTariff()
	{
		return null;
	}

	/**
	 * @return NikoloSellTicket
	 */
	protected function createSellTicket()
	{
		return null;
	}

	protected function canUseExternalTariff(ExternalTariff $externalTariff)
	{
		return true;
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketNikolo
	 */
	public function createExternalTicket(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		return null;
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return bool
	 */
	public function canBuyOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		return false;
	}

	/**
	 * @throws \ErrorException
	 * @throws \Exception
	 */
	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();
		$language = $this->languageService->getEnglish();

		$stations = $this->getAllStations();

		foreach ($stations as $s) {
			$externalCity = $this->externalCityService->getExternalCityByIdent(md5($s->cityName), $this->getExternalRouterClass());
			if ($externalCity === null) {
				$externalCity = $this->createExternalCity();
				$externalCity->setIdent(md5($s->cityName));
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					if ($city->getName()->getString($language) == str_replace(" *", "", $s->cityName)) {
						$externalCity->setCity($city);
					}
				}
			}

			$externalCity->setName($language, $s->cityName);
			$this->externalCityService->saveExternalCity($externalCity);

			$externalStation = $this->externalStationService->getExternalStationByIdent($s->stationId, $this->getExternalRouterClass());
			if ($externalStation === null) {
				$externalStation = $this->createExternalStation();
				$externalStation->setIdent($s->stationId);
				$externalStation->setExternalCity($externalCity);
			}
			$externalStation->setName($language, $s->stationName);
			$this->externalStationService->saveExternalStation($externalStation);
		}
	}

	/**
	 * @throws \ErrorException
	 */
	public function getAllStations()
	{
		$stations = [];
		foreach ($this->connector->getAllRoutes() as $route) {
			foreach ($this->connector->getAllRouteStations($route->route_name_id) as $s) {

				$stations[$s->route_city_id] = (object) [
					"stationId" => $s->route_city_id,
					"cityName" => trim($s->city_i18n),
					"stationName" => trim($s->name_i18n)
				];
			}
		}

		return array_values($stations);
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

		$fromList = $routeFilter->getFromCity()->getExternalCitiesByType($this->getExternalRouterClass());
		$toList = $routeFilter->getToCity()->getExternalCitiesByType($this->getExternalRouterClass());

		if ($fromList->isEmpty() or $toList->isEmpty() === null) return [];

		$fromExternalStations = new ArrayCollection;
		$toExternalStations = new ArrayCollection;

		$fromList->map(function (ExternalCity $externalCity) use ($fromExternalStations) {
			$externalCity->getExternalStations()->map(function (ExternalStation $externalStation) use ($fromExternalStations) {
				$fromExternalStations->add($externalStation);
			});
		});

		$toList->map(function (ExternalCity $externalCity) use ($toExternalStations) {
			$externalCity->getExternalStations()->map(function (ExternalStation $externalStation) use ($toExternalStations) {
				$toExternalStations->add($externalStation);
			});
		});

		/** @var ExternalStation $fromExternalStation */
		/** @var ExternalStation $toExternalStation */
		foreach ($fromExternalStations->toArray() as $fromExternalStation) {
			foreach ($toExternalStations->toArray() as $toExternalStation) {
				$data = $this->connector->findRoute(
					$fromExternalStation->getIdent(),
					$toExternalStation->getIdent(),
					$routeFilter->getDateDay()
				);

				foreach ($data as $d) {
					$route = $this->createRoute($searchExternal, $d, LineStation::DIRECTION_THERE);
					if ($route) {
						$routes->add($route);
					}
				}
			}
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			foreach ($fromExternalStations->toArray() as $fromExternalStation) {
				foreach ($toExternalStations->toArray() as $toExternalStation) {
					$data = $this->connector->findRoute(
						$toExternalStation->getIdent(),
						$fromExternalStation->getIdent(),
						$routeFilter->getDateBack()
					);

					foreach ($data as $d) {
						$route = $this->createRoute($searchExternal, $d, LineStation::DIRECTION_BACK);
						if ($route) {
							$routes->add($route);
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
	 * @throws \ErrorException
	 */
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction)
	{
		if ($routeData->free_plases < 1) {
			return null;
		}

		$route = new Route;
		$route->setFromExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData->from->{'0'},
			$this->getExternalRouterClass()
		));

		$route->setToExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData->to->{'0'},
			$this->getExternalRouterClass()
		));

		$route->setFromExternalCity($route->getFromExternalStation()->getExternalCity());
		$route->setToExternalCity($route->getToExternalStation()->getExternalCity());

		$route->setFromCity($route->getFromExternalCity()->getCity());
		$route->setToCity($route->getToExternalCity()->getCity());

		$route->setDatetimeDeparture(new \DateTime($routeData->from->date . " " . $routeData->from->hours));
		$route->setDatetimeArrival(new \DateTime($routeData->to->end_date . " " . $routeData->to->time));
		$route->setCarrier($this->carrierService->getCarrierByCode($this->getCarriedCode()));

		if (empty($routeData->route_price)) return null;

		foreach ($routeData->route_price as $routePrice) {
			if ($routePrice->valute === PriceCurrency::CZK) {
				$priceCurrency = $this->currencyService->currencyConvert(
					PriceCurrency::create($routePrice->price, $routePrice->valute), $searchExternal->getCurrency()
				);
				$route->setPrice($priceCurrency->getPrice());
				$route->setCurrency($priceCurrency->getCurrency());
			}
		}

		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->route_name);

		$externalObject = $this->createExternalRoute();
		$externalObject->setFerrymanId($routeData->from->ferryman_id);
		$externalObject->setRouteNameId($routeData->from->route_name_id);
		$externalObject->setBusesId($routeData->from->buses_id);
		$externalObject->setSeats(array_unique(array_values((array) $routeData->all_free_plases)));
		$route->setExternalObject($externalObject);

		$this->routeService->saveRoute($route);

		$this->processFares($route, $routeData);

		return $route;
	}

	/**
	 * @param Route $route
	 * @throws \ErrorException
	 */
	private function processFares(Route $route, $routeData)
	{
		$discounts = $this->connector->getAllDiscounts();

		foreach ($discounts as $discountData) {
			$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
				$discountData->main_id, $this->getExternalRouterClass()
			);

			if ($externalTariff === null) {
				$externalTariff = $this->createExternalTariff();
				$externalTariff->setIdent($discountData->main_id);
				$externalTariff->setName($this->languageService->getEnglish(), $discountData->name_i18n);
				$externalTariff->setPercent($discountData->value);
				$this->externalTariffService->saveExternalTariff($externalTariff);
			}

			if ($this->canUseExternalTariff($externalTariff)) {
				$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
				$priceCurrency = $this->currencyService->currencyConvert(
					PriceCurrency::create(
						$route->getPrice() - ($route->getPrice() * ($discountData->value/100)),
						$route->getCurrency()
					), $route->getCurrency()
				);
				$routeTariff->setPrice($priceCurrency->getPrice());
				$this->routeTariffService->saveRouteTariff($routeTariff);
			}
		}
	}

	public function canPayOnline(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode($this->getCarriedCode())) return false;

		$datetime = (new \DateTime)->modify("+24 hours");
		if ($route->getDatetimeDeparture() > $datetime) return true;

		$datetime = (new \DateTime)->modify("+4 hours");
		if ($route->getDatetimeDeparture() > $datetime) {
			if ($this->dateTimeService->isWorkingTime($route->getDatetimeDeparture())) {
				return true;
			}
		}

		return false;
	}

	public function canBuyRoute(Route $route, RouteTariff $routeTariff)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode($this->getCarriedCode())) return false;
		if ($routeTariff->getExternalTariff() === null) return false;

		if ($this->canPayOnline($route)) {
			return true;
		}

		return true;
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return # TODO
	 */
	public function buyRoute(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{

	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return BookNikolo|BookRegabus
	 * @throws \AppBundle\Exceptions\SvitgoBookTicketException
	 * @throws \ErrorException
	 */
	public function bookOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		/** @var ExternalRouteSvitgo $externalRoute */
		$externalRoute = $orderPersonRouteTariff->getRoute()->getExternalObject();
		/** @var ExternalTariffNikolo|ExternalTariffRegabus $externalTariff */
		$externalTariff = $orderPersonRouteTariff->getRouteTariff()->getExternalTariff();
		/** @var BookNikolo|BookRegabus $book */
		$book = $orderPersonRouteTariff->getBook();
		$orderPerson = $orderPersonRouteTariff->getOrderPerson();
		list($firstName, $lastName) = explode(" ", $orderPerson->getName());

		$sellTicket = $this->createSellTicket()
			->setBusesId($externalRoute->getBusesId())
			->setRouteNameId($externalRoute->getRouteNameId())
			->setFerrymanId($externalRoute->getFerrymanId())
			->setFrom($orderPersonRouteTariff->getRoute()->getFromExternalStation()->getIdent())
			->setTo($orderPersonRouteTariff->getRoute()->getToExternalStation()->getIdent())
			->setName($firstName)
			->setSurname($lastName)
			->setTel($orderPerson->getPhone())
			->setUserbd($orderPerson->getDateBirth())
			->setDiscount($externalTariff->getIdent())
			->setSellOrOrder(SvitgoSellTicket::ORDER)
			->setRouteDate($orderPersonRouteTariff->getRoute()->getDatetimeDeparture())
			->setRice($this->currencyService->currencyConvert($orderPersonRouteTariff->getRouteTariff()->getPriceCurrency(), PriceCurrency::CZK)->getPrice())
			->setSeat($book->getSeatNumber());

		$response = $this->connector->bookTicket($sellTicket);
		$book->setTicketIdentifier($response->ticket_id);
		return $book;
	}

	public function createSelectSeatsForSvitgo(ExternalRouteSvitgo $externalRoute, OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$maxSeatNumber = max($externalRoute->getSeats());
		$availableSeats = $externalRoute->getSeats();
		$currentSeatNumber = 1;
		$fakeSeatsPlan = new SeatsPlan;
		foreach (range(0, 50) as $y) {
			foreach (range(0, 4) as $x) {
				if ($x === 2) continue;
				if ($currentSeatNumber <= $maxSeatNumber) {
					$seat = $fakeSeatsPlan->createSeatForPosition($x, $y);
					$seat->setNumber($currentSeatNumber);
					$seat->setAvailable(in_array($currentSeatNumber, $availableSeats));
					$currentSeatNumber++;
				}
			}
		}
		return SelectSeatVO::create($fakeSeatsPlan, $orderPersonRouteTariff);
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return ExternalTicketNikolo|ExternalTicketRegabus
	 * @throws \AppBundle\Exceptions\SvitgoBuyBookTicketException
	 * @throws \ErrorException
	 */
	public function buyOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		/** @var BookNikolo|BookRegabus $book */
		$book = $orderPersonRouteTariff->getBook();
		$htmlBody = $this->connector->buyBook($book->getTicketIdentifier());

		$externalTicket = $this->createExternalTicket(
			$orderPersonRouteTariff->getRoute(),
			$orderPersonRouteTariff->getRouteTariff(),
			$orderPersonRouteTariff->getOrderPerson()
		);
		$externalTicket->setTicketIdent($book->getTicketIdentifier());
		$externalTicket->setHtmlBody($htmlBody);

		return $externalTicket;
	}
}
