<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.17
 * Time: 17:18
 */

namespace AppBundle\Routers;


use AppBundle\Connectors\EcolinesConnector;
use AppBundle\Entity\BookEcolines;
use AppBundle\Entity\ExternalCityEcolines;
use AppBundle\Entity\ExternalTariffEcolines;
use AppBundle\Entity\ExternalTicketEcolines;
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
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\RouteTariffService;
use AppBundle\VO\EcolinesPersonBookingVO;
use AppBundle\VO\ExternalRouteEcolines;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use AppBundle\VO\SelectSeatVO;
use Doctrine\Common\Collections\ArrayCollection;

class EcolinesRouter
{
	/**
	 * @var EcolinesConnector
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
	/**
	 * @var DateTimeService
	 */
	private $dateTimeService;

	public function __construct(EcolinesConnector $connector,
	                            CityService $cityService,
	                            LanguageService $languageService,
	                            ExternalCityService $externalCityService,
	                            CarrierService $carrierService,
	                            RouteService $routeService,
	                            ExternalTariffService $externalTariffService,
	                            RouteTariffService $routeTariffService,
	                            CurrencyService $currencyService,
	                            DateTimeService $dateTimeService)
	{
		$this->connector = $connector;
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->externalCityService = $externalCityService;
		$this->carrierService = $carrierService;
		$this->routeService = $routeService;
		$this->externalTariffService = $externalTariffService;
		$this->routeTariffService = $routeTariffService;
		$this->currencyService = $currencyService;
		$this->dateTimeService = $dateTimeService;
	}

	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();

		$language = $this->languageService->getEnglish();

		$stops = $this->connector->getStops();
		foreach ($stops as $s) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($s->id, ExternalRouter::ECOLINES);
			if ($externalCity === null) {
				$externalCity = new ExternalCityEcolines();
				$externalCity->setIdent($s->id);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					if ($city->getName()->getString($language) == str_replace(" *", "", $s->title)) {
						$externalCity->setCity($city);
					}
				}
			}

			$externalCity->setName($language, $s->title);
			$this->externalCityService->saveExternalCity($externalCity);
		}

		/*$stations = $this->connector->getStations($language->getId());
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
		}*/

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

		$from = $routeFilter->getFromCity()->getExternalCity(ExternalRouter::ECOLINES);
		$to = $routeFilter->getToCity()->getExternalCity(ExternalRouter::ECOLINES);

		if ($from === null or $to === null) return [];

		$data = $this->connector->findRoute(
			$from->getIdent(),
			$to->getIdent(),
			$routeFilter->getDateDay()
		);

		foreach ($data as $r) {
			$routes->add($this->createRoute($searchExternal, $r, LineStation::DIRECTION_THERE));
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			$data = $this->connector->findRoute(
				$to->getIdent(),
				$from->getIdent(),
				$routeFilter->getDateBack()
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
	 * @throws \Exception
	 */
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction)
	{
		$route = new Route;
		$route->setFromExternalCity($this->externalCityService->getExternalCityByIdent(
			$routeData->outbound->origin, ExternalRouter::ECOLINES
		));
		$route->setToExternalCity($this->externalCityService->getExternalCityByIdent(
			$routeData->outbound->destination, ExternalRouter::ECOLINES
		));
		$route->setFromCity($route->getFromExternalCity()->getCity());
		$route->setToCity($route->getToExternalCity()->getCity());
		$route->setDatetimeDeparture(new \DateTime($routeData->outbound->departure));
		$route->setDatetimeArrival(new \DateTime($routeData->outbound->arrival));
		$route->setCarrier($this->carrierService->getCarrierByCode("Eco"));
		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData->fare/100, PriceCurrency::EUR), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());
		$route->setCurrency($priceCurrency->getCurrency());
		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->id);

		$this->routeService->saveRoute($route);

		$this->processFares($route);

		return $route;
	}

	/**
	 * @param Route $route
	 */
	private function processFares(Route $route)
	{
		foreach ($this->connector->getFares($route->getExternalIdent()) as $fareData) {
			$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
				$fareData->tariff, ExternalRouter::ECOLINES
			);

			if ($externalTariff === null) {
				$externalTariff = new ExternalTariffEcolines;
				$externalTariff->setIdent($fareData->tariff);
				$this->externalTariffService->saveExternalTariff($externalTariff);
			}

			$priceCurrency = $this->currencyService->currencyConvert(
				PriceCurrency::create($fareData->amount/100, PriceCurrency::EUR), $route->getCurrency()
			);

			$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $priceCurrency->getCurrency());
			$routeTariff->setPrice($priceCurrency->getPrice());
			$routeTariff->setPersonLimit($fareData->limit);
			$this->routeTariffService->saveRouteTariff($routeTariff);
		}
	}

	public function loadExternalObjectForRoute(Route $route)
	{
		$externalObject = $route->getExternalObject();
		if ($externalObject instanceof ExternalRouteEcolines) {
			if ($externalObject->getDatetimeUpdated() > new \DateTime("-1 minute")) {
				return;
			}
		}

		$externalObject = new ExternalRouteEcolines;
		$externalObject->setDatetimeUpdated(new \DateTime);
		array_map(function ($leg) use ($externalObject) {
			$externalObject->addLegAndSeats($leg, $this->connector->getSeats($leg->id));
		}, $this->connector->getLegs($route->getExternalIdent()));
		$route->setExternalObject($externalObject);
	}

	/**
	 * @param ExternalRouteEcolines $externalRoute
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return SelectSeatVO[]
	 */
	public function createSelectSeatsForEcolines(ExternalRouteEcolines $externalRoute, OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$selectSeats = new ArrayCollection;

		foreach ($externalRoute->getLegs() as $leg) {
			$fakeSeatsPlan = new SeatsPlan;
			foreach ($leg->seats as $s) {
				$column = ($s->column <= 2) ? ($s->column - 1) : $s->column;
				$seat = $fakeSeatsPlan->createSeatForPosition($column, $s->row);
				$seat->setAvailable(!$s->busy);
				$seat->setNumber($s->id);
			}
			$selectSeat = SelectSeatVO::create($fakeSeatsPlan, $orderPersonRouteTariff);
			$selectSeat->setBusIdent($leg->id);
			$selectSeats->add($selectSeat);
		}

		return $selectSeats->toArray();
	}

	public function canBuyRoute(Route $route, RouteTariff $routeTariff)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("Eco")) return false;
		if ($routeTariff->getExternalTariff() === null) return false;

		return true;
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return bool
	 */
	public function canBuyOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$book = $orderPersonRouteTariff->getBook();
		if ($book instanceof BookEcolines and $book->getBookingId() !== null) {
			return true;
		}

		return false;
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return BookEcolines
	 * @throws \ErrorException
	 */
	public function bookOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		foreach ($orderPersonRouteTariff->getRoute()->getOrderPersonRouteTariffs()->toArray() as $oprt) {
			/** @var OrderPersonRouteTariff $oprt */
			$book = $oprt->getBook();
			if ($book instanceof BookEcolines) {
				if ($book->getBookingId() !== null) {
					/** @var BookEcolines $currentBook */
					$currentBook = $orderPersonRouteTariff->getBook();
					$currentBook->setBookingId($book->getBookingId());
					return $currentBook;
				}
			}
		}

		$order = $orderPersonRouteTariff->getOrder();
		$route = $orderPersonRouteTariff->getRoute();

		$bookingId = $this->connector->booking(
			$route->getExternalIdent(),
			array_map(function (OrderPersonRouteTariff $orderPersonRouteTariff) use ($order) {

				$orderPerson = $orderPersonRouteTariff->getOrderPerson();
				list($firstName, $lastName) = explode(" ", $orderPerson->getName());
				/** @var BookEcolines $book */
				$book = $orderPersonRouteTariff->getBook();

				$booking = new EcolinesPersonBookingVO;
				$booking->setFirstName($firstName);
				$booking->setLastName($lastName);
				$booking->setEmail($order->getEmail());
				$booking->setPhone($orderPerson->getPhone());
				$booking->setSeats($book->getSeatNumbers());
				$booking->setTariff($orderPersonRouteTariff->getRouteTariff()->getExternalTariff()->getIdent());
				return $booking;
		}, $route->getOrderPersonRouteTariffs()->toArray()));

		/** @var BookEcolines $book */
		$book = $orderPersonRouteTariff->getBook();
		$book->setBookingId($bookingId);
		return $book;
	}

	public function buyOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		/** @var BookEcolines $book */
		$book = $orderPersonRouteTariff->getBook();
		$this->connector->confirmBooking($book->getBookingId());

		$tickets = $this->connector->bookingTickets($book->getBookingId());

		# dam nahoru tu jizdenku, ktera je s nejvetsi pravdepodobnosti podle jmena souhlasici
		usort($tickets, function ($first, $second) use ($orderPersonRouteTariff) {
			$orderPerson = $orderPersonRouteTariff->getOrderPerson();
			$percentFirst = levenshtein($orderPerson->getName(), $first->firstName . " " . $first->lastName);
			$percentSecond = levenshtein($orderPerson->getName(), $second->firstName . " " . $second->lastName);
			return $percentFirst < $percentSecond ? -1 : +1;
		});

		foreach ($tickets as $ticket) {
			$exists = $orderPersonRouteTariff->getOrder()->getOrderPersonRouteTariffs()->exists(function ($i, OrderPersonRouteTariff $orderPersonRouteTariff) use ($ticket) {
				/** @var BookEcolines $book */
				$book = $orderPersonRouteTariff->getBook();
				return $book instanceof BookEcolines and $book->getTicketId() === $ticket->id;
			});

			if (!$exists) {
				$book->setTicketId($ticket->id);
				break;
			}
		}

		$externalTicket = ExternalTicketEcolines::create(
			$orderPersonRouteTariff->getRoute(),
			$orderPersonRouteTariff->getRouteTariff(),
			$orderPersonRouteTariff->getOrderPerson()
		);
		$externalTicket->setTicketIdent($book->getTicketId());
		$externalTicket->setSeatNumbers($book->getSeatNumbers());
		$book->setExternalTicket($externalTicket);

 		return $externalTicket;
	}

}
