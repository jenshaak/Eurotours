<?php


namespace AppBundle\Routers;


use AppBundle\Connectors\BusSystemConnector;
use AppBundle\Entity\ExternalCityInfobus;
use AppBundle\Entity\ExternalCityNikolo;
use AppBundle\Entity\ExternalStationInfobus;
use AppBundle\Entity\ExternalStationNikolo;
use AppBundle\Entity\ExternalTariffInfobus;
use AppBundle\Entity\ExternalTariffNikolo;
use AppBundle\Entity\ExternalTicketBusSystem;
use AppBundle\Entity\ExternalTicketNikolo;
use AppBundle\Entity\LineStation;
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
use AppBundle\Service\UploadService;
use AppBundle\VO\BookBusSystemInterface;
use AppBundle\VO\ExternalRouteBusSystem;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use AppBundle\VO\SelectSeatVO;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

class BusSystemRouter
{
	const TARIFF_REGULAR = "REGULAR";

	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var BusSystemConnector
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
	 * @var CarrierService
	 */
	protected $carrierService;
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
	/**
	 * @var UploadService
	 */
	private $uploadService;

	public function __construct(CityService $cityService,
								LanguageService $languageService,
								ExternalCityService $externalCityService,
								ExternalStationService $externalStationService,
								CarrierService $carrierService,
								CurrencyService $currencyService,
								RouteService $routeService,
								ExternalTariffService $externalTariffService,
								RouteTariffService $routeTariffService,
								DateTimeService $dateTimeService,
								UploadService $uploadService)
	{
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->externalCityService = $externalCityService;
		$this->externalStationService = $externalStationService;
		$this->carrierService = $carrierService;
		$this->currencyService = $currencyService;
		$this->routeService = $routeService;
		$this->externalTariffService = $externalTariffService;
		$this->routeTariffService = $routeTariffService;
		$this->dateTimeService = $dateTimeService;
		$this->uploadService = $uploadService;
	}

	/**
	 * @param BusSystemConnector $busSystemConnector
	 */
	public function setConnector(BusSystemConnector $busSystemConnector)
	{
		$this->connector = $busSystemConnector;
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
	 * @return ExternalCityNikolo|ExternalCityInfobus
	 */
	protected function createExternalCity()
	{
		return null;
	}

	/**
	 * @return ExternalStationNikolo|ExternalStationInfobus
	 */
	protected function createExternalStation()
	{
		return null;
	}

	/**
	 * @return ExternalTariffNikolo|ExternalTariffInfobus
	 */
	protected function createExternalTariff()
	{
		return null;
	}

	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();
		$language = $this->languageService->getCzech();

		$cities = $this->connector->getPoints($language);
		foreach ($cities as $c) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($c['id'], $this->getExternalRouterClass());
			if ($externalCity === null) {
				$externalCity = $this->createExternalCity();
				$externalCity->setIdent($c['id']);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					if ($city->getName()->getString($language) == $c['name']) {
						$externalCity->setCity($city);
					}
				}
			}

			$externalCity->setName($language, $c['name']);
			$this->externalCityService->saveExternalCity($externalCity);
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

		$from = $routeFilter->getFromCity()->getExternalCity($this->getExternalRouterClass());
		$to = $routeFilter->getToCity()->getExternalCity($this->getExternalRouterClass());

		if ($from === null or $to === null) return [];

		$data = $this->connector->findRoute(
			$from->getIdent(),
			$to->getIdent(),
			$routeFilter->getDateDay(),
			$searchExternal->getCurrency()
		);

		foreach ($data as $r) {
			$routes->add($this->createRoute($searchExternal, $r, LineStation::DIRECTION_THERE));
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			$data = $this->connector->findRoute(
				$to->getIdent(),
				$from->getIdent(),
				$routeFilter->getDateBack(),
				$searchExternal->getCurrency()
			);

			foreach ($data as $r) {
				$routes->add($this->createRoute($searchExternal, $r, LineStation::DIRECTION_BACK));
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
		$route->setFromExternalCity($this->externalCityService->getExternalCityByIdent(
			$routeData->pointFromId, $this->getExternalRouterClass()
		));
		$route->setToExternalCity($this->externalCityService->getExternalCityByIdent(
			$routeData->pointToId, $this->getExternalRouterClass()
		));

		$departureStation = $this->externalStationService->getExternalStationByIdent(
			md5($routeData->departureStation), $this->getExternalRouterClass()
		);
		if ($departureStation === null) {
			$departureStation = $this->createExternalStation();
			$departureStation->setName($this->languageService->getEnglish(), $routeData->departureStation);
			$departureStation->setIdent(md5($routeData->departureStation));
			$departureStation->setExternalCity($route->getFromExternalCity());
			$this->externalStationService->saveExternalStation($departureStation);
		}

		$arrivalStation = $this->externalStationService->getExternalStationByIdent(
			md5($routeData->arrivalStation), $this->getExternalRouterClass()
		);
		if ($arrivalStation === null) {
			$arrivalStation = $this->createExternalStation();
			$arrivalStation->setName($this->languageService->getEnglish(), $routeData->arrivalStation);
			$arrivalStation->setIdent(md5($routeData->arrivalStation));
			$arrivalStation->setExternalCity($route->getToExternalCity());
			$this->externalStationService->saveExternalStation($arrivalStation);
		}

		$route->setFromExternalStation($departureStation);
		$route->setToExternalStation($arrivalStation);
		$route->setFromCity($route->getFromExternalCity()->getCity());
		$route->setToCity($route->getToExternalCity()->getCity());
		$route->setCarrierTitle($routeData->carrierTitle);

		$route->setDatetimeDeparture($routeData->departureTime);
		$route->setDatetimeArrival($routeData->arrivalTime);
		$route->setCarrier($this->carrierService->getCarrierByCode($this->getCarriedCode()));
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->price, $routeData->currency), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());

		if ($routeData->maxPrice > $routeData->price) {
			$maxPriceCurrency = $this->currencyService->currencyConvert(
				PriceCurrency::create($routeData->maxPrice, $routeData->currency), $searchExternal->getCurrency()
			);
			$route->setMaxPrice($maxPriceCurrency->getPrice());
		}

		$route->setCurrency($priceCurrency->getCurrency());
		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->id);

		$externalRoute = new ExternalRouteBusSystem;
		$externalRoute->setSeats($routeData->freeSeats);
		$externalRoute->setIntervalId($routeData->intervalId);
		$externalRoute->setDateOfBirthRequired($routeData->dateBirthRequired);
		$externalRoute->setDocumentNumberRequired($routeData->documentRequired);
		$route->setExternalObject($externalRoute);

		$this->routeService->saveRoute($route);

		$this->processFares($route, $routeData);

		return $route;
	}

	/**
	 * @param Route $route
	 */
	private function processFares(Route $route, $routeData)
	{
		$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
			self::TARIFF_REGULAR, $this->getExternalRouterClass()
		);

		if ($externalTariff === null) {
			$externalTariff = $this->createExternalTariff();
			$externalTariff->setIdent(self::TARIFF_REGULAR);
			$externalTariff->setName($this->languageService->getEnglish(), self::TARIFF_REGULAR);
			$this->externalTariffService->saveExternalTariff($externalTariff);
		}

		$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->price, $routeData->currency), $route->getCurrency()
		);
		$routeTariff->setPrice($priceCurrency->getPrice());

		if ($routeData->maxPrice > $routeData->price) {
			$maxPriceCurrency = $this->currencyService->currencyConvert(
				PriceCurrency::create($routeData->maxPrice, $routeData->currency), $route->getCurrency()
			);
			$routeTariff->setMaxPrice($maxPriceCurrency->getPrice());
		}

		$this->routeTariffService->saveRouteTariff($routeTariff);

		foreach ($routeData->discounts as $discountData) {
			$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
				md5($discountData->name), $this->getExternalRouterClass()
			);

			if ($externalTariff === null) {
				$externalTariff = $this->createExternalTariff();
				$externalTariff->setIdent(md5($discountData->name));
				$externalTariff->setName($this->languageService->getEnglish(), $discountData->name);
				$this->externalTariffService->saveExternalTariff($externalTariff);
			}

			$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
			$routeTariff->setExternalBookingIdent($discountData->id);
			$priceCurrency = $this->currencyService->currencyConvert(
				PriceCurrency::create($discountData->price, $routeData->currency), $route->getCurrency()
			);
			$routeTariff->setPrice($priceCurrency->getPrice());
			$this->routeTariffService->saveRouteTariff($routeTariff);
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

	public function createSelectSeats(ExternalRouteBusSystem $externalRoute, OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$seatsPlan = new SeatsPlan;
		$maxFreeSeat = max($externalRoute->getSeats());
		$maxSeatNumber = $maxFreeSeat;
		$currentSeatNumber = 1;
		foreach (range(0, 50) as $y) {
			foreach (range(0, 4) as $x) {
				if ($x === 2) continue;
				if ($currentSeatNumber <= $maxSeatNumber) {
					$seat = $seatsPlan->createSeatForPosition($x, $y);
					$seat->setNumber($currentSeatNumber);
					$seat->setAvailable(in_array($currentSeatNumber, $externalRoute->getSeats()));
					$currentSeatNumber++;
				}
			}
		}

		return SelectSeatVO::create($seatsPlan, $orderPersonRouteTariff);
	}

	/**
	 * @param Route $route
	 * @return object
	 * @throws Exception
	 */
	public function bookRoute(Route $route): object
	{
		/** @var BookBusSystemInterface[] $books */
		$books = $route->getBooks()->getValues();
		foreach ($books as $book) {
			$order = $book->getOrder();
		}

		return $this->connector->bookRoute($order, $route, $books);
	}

	/**
	 * @param Route $route
	 * @return ExternalTicketNikolo[]
	 * @throws \Exception
	 */
	public function buyRoute(Route $route, bool $withBooking = true)
	{
		$tickets = [];

		/** @var BookBusSystemInterface[] $books */
		$books = $route->getBooks()->getValues();
		foreach ($books as $book) {
			$order = $book->getOrder();
		}

		if ($withBooking) {
			$response = $this->connector->bookRoute($order, $route, $books);
			$response = $this->connector->buyTicket($response->orderId);
		} else {
			if (!isset($books[0]) || $books[0]->getBookingId() === null) return [];

			$response = $this->connector->buyTicket($books[0]->getBookingId());
		}

		$ticketContent = file_get_contents($response->ticketLink);
		$file = $this->uploadService->createFile("pdf");
		file_put_contents($this->uploadService->getWebDir() . $file, $ticketContent);

		foreach ($route->getOrderPersonRouteTariffs() as $orderPersonRouteTariff) {
			$book = $orderPersonRouteTariff->getBook();
			$externalTicket = ExternalTicketBusSystem::create(
				$orderPersonRouteTariff->getRoute(),
				$orderPersonRouteTariff->getRouteTariff(),
				$orderPersonRouteTariff->getOrderPerson()
			);
			$book->setExternalTicket($externalTicket);
			$externalTicket->setPdfBody($ticketContent);
			$externalTicket->setFile($file);
			$externalTicket->setOrderIdent($response->orderId);
			$externalTicket->setContentType("application/pdf");
			$tickets[] = $externalTicket;
		}

		return $tickets;
	}
}
