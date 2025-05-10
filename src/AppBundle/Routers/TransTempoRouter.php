<?php


namespace AppBundle\Routers;


use AppBundle\Connectors\TransTempoConnector;
use AppBundle\Entity\BookTransTempo;
use AppBundle\Entity\ExternalCityTransTempo;
use AppBundle\Entity\ExternalTariffRegabus;
use AppBundle\Entity\ExternalTicketTransTempo;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Entity\SeatsPlan;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\GotenbergService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\UploadService;
use AppBundle\VO\ExternalRouteEcolines;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\ExternalRouteTransTempo;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use AppBundle\VO\SelectSeatVO;
use Doctrine\Common\Collections\ArrayCollection;

class TransTempoRouter
{
	/**
	 * @var TransTempoConnector
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
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;
	/**
	 * @var GotenbergService
	 */
	private $gotenbergService;
	/**
	 * @var UploadService
	 */
	private $uploadService;

	public function __construct(TransTempoConnector $connector,
	                            CityService $cityService,
	                            LanguageService $languageService,
	                            ExternalCityService $externalCityService,
	                            RouteService $routeService,
	                            CarrierService $carrierService,
	                            CurrencyService $currencyService,
	                            ExternalTariffService $externalTariffService,
	                            GotenbergService $gotenbergService,
	                            UploadService $uploadService)
	{
		$this->connector = $connector;
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->externalCityService = $externalCityService;
		$this->routeService = $routeService;
		$this->carrierService = $carrierService;
		$this->currencyService = $currencyService;
		$this->externalTariffService = $externalTariffService;
		$this->gotenbergService = $gotenbergService;
		$this->uploadService = $uploadService;
	}

	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();

		$language = $this->languageService->getEnglish();

		$cities = $this->connector->getCities();

		foreach ($cities as $c) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($c->city_id, ExternalRouter::TRANS_TEMPO);
			if ($externalCity === null) {
				$externalCity = new ExternalCityTransTempo();
				$externalCity->setIdent($c->city_id);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					if (mb_strtolower($city->getName()->getString($language)) == str_replace("_", " ", mb_strtolower($c->key))) {
						$externalCity->setCity($city);
					}
				}
			}

			$externalCity->setName($language, ucfirst(str_replace("_", " ", $c->key)));
			$this->externalCityService->saveExternalCity($externalCity);
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

		$externalObject = new ExternalRouteTransTempo();
		$externalObject->setDatetimeUpdated(new \DateTime);
		$externalObject->setSeats($this->connector->getSeats($route)->seats);
		$externalObject->setSeatsBooked($this->connector->getSeats($route)->seats_booked);

		$route->setExternalObject($externalObject);
		$this->routeService->saveRoute($route);
	}

	public function createSelectSeats(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$fakeSeatsPlan = new SeatsPlan;

		/** @var ExternalRouteTransTempo $externalObject */
		$externalObject = $orderPersonRouteTariff->getRoute()->getExternalObject();

		$orderRows = array_reduce($externalObject->getSeats(), function ($return, $seat) {
			if (!in_array($seat->left, $return)) $return[] = $seat->left;
			return $return;
		}, []);

		$orderCells = array_reduce($externalObject->getSeats(), function ($return, $seat) {
			if (!in_array($seat->top, $return)) $return[] = $seat->top;
			return $return;
		}, []);

		sort($orderRows);
		rsort($orderCells);

		foreach ($orderRows as $y => $left) {
			foreach ($orderCells as $x => $top) {
				foreach ($externalObject->getSeats() as $s) {
					if ($s->left === $left and $s->top === $top) {
						$seat = $fakeSeatsPlan->createSeatForPosition($x, $y);
						$seat->setNumber((int) $s->name);
						$seat->setAvailable(!in_array($s->seat_id, $externalObject->getSeatsBooked()));
						break;
					}
				}
			}
		}

		return SelectSeatVO::create($fakeSeatsPlan, $orderPersonRouteTariff);
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

		$fromList = $routeFilter->getFromCity()->getExternalCitiesByType(ExternalRouter::TRANS_TEMPO);
		$toList = $routeFilter->getToCity()->getExternalCitiesByType(ExternalRouter::TRANS_TEMPO);

		if ($fromList->isEmpty() or $toList->isEmpty() === null) return [];

		/** @var ExternalCityTransTempo $fromExternalCity */
		/** @var ExternalCityTransTempo $toExternalCity */
		foreach ($fromList->toArray() as $fromExternalCity) {
			foreach ($toList->toArray() as $toExternalCity) {
				$data = $this->connector->getBuses(
					$fromExternalCity->getIdent(),
					$toExternalCity->getIdent(),
					$routeFilter->getDateDay()
				);

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
					$data = $this->connector->getBuses(
						$toExternalCity->getIdent(),
						$fromExternalCity->getIdent(),
						$routeFilter->getDateBack()
					);

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
	private function createRoute(SearchExternal $searchExternal, $routeData, $direction, ExternalCityTransTempo $fromExternalCity, ExternalCityTransTempo $toExternalCity)
	{
		$route = new Route;

		$route->setFromExternalCity($fromExternalCity);
		$route->setToExternalCity($toExternalCity);

		$route->setFromCity($fromExternalCity->getCity());
		$route->setToCity($toExternalCity->getCity());

		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData->bus_id);
		$route->setIsDirect(true);

		$route->setDatetimeDeparture(new \DateTime($routeData->departure_datetime));
		$route->setDatetimeArrival(new \DateTime($routeData->arrival_datetime));
		$route->setCarrier($this->carrierService->getCarrierByCode("TTbus"));

		$route->setPrice(0);
		$route->setCurrency(PriceCurrency::CZK);
		$this->routeService->saveRoute($route);

		array_map(function ($tariffData) use ($route) {
			$this->createFareForRoute($route, $tariffData);
		}, $routeData->ticket_types);

		$price = max($route->getRouteTariffs()->map(function (RouteTariff $routeTariff) {
			return $routeTariff->getPrice();
		})->getValues());

		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create(ceil($price), PriceCurrency::CZK), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());
		$route->setCurrency($priceCurrency->getCurrency());

		$this->routeService->saveRoute($route);

		//$tripData = $this->connector->getTrip($route->getExternalIdent())[0];

		return $route;
	}

	/**
	 * @param Route $route
	 * @param string $tariffName
	 */
	private function createFareForRoute(Route $route, $tariffData)
	{
		$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
			$tariffData->code, ExternalRouter::TRANS_TEMPO
		);

		$language = $this->languageService->getRussian();

		if ($externalTariff === null) {
			$externalTariff = new ExternalTariffRegabus;
			$externalTariff->setIdent($tariffData->ticket_type_id);
			$externalTariff->setName($language, $tariffData->name);
			$this->externalTariffService->saveExternalTariff($externalTariff);
		}

		$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
		$routeTariff->setPrice(ceil($tariffData->price));
		$routeTariff->setExternalBookingIdent($tariffData->ticket_type_id);
	}

	public function canBuyRoute(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("TTbus")) return false;

		return true;
	}

	/**
	 * @param Route $route
	 * @return ExternalTicketTransTempo[]
	 * @throws \Exception
	 */
	public function buyRoute(Route $route)
	{
		/** @var BookTransTempo[] $books */
		$books = $route->getBooks()->getValues();
		foreach ($books as $book) {
			$order = $book->getOrder();
		}

		$response = $this->connector->bookRoute($order, $route, $books);
		$this->connector->confirmBooking($response->hash);

		$file = $this->uploadService->createFile("pdf");
		$html = $this->connector->getTicketHtml($response->booking_id);
		file_put_contents(
			$this->uploadService->getWebDir() . $file,
			$this->gotenbergService->generatePdfFromHtml($html)
		);

		$tickets = [];

		foreach ($route->getOrderPersonRouteTariffs() as $orderPersonRouteTariff) {
			/** @var BookTransTempo $book */
			$book = $orderPersonRouteTariff->getBook();
			$externalTicket = ExternalTicketTransTempo::create(
				$orderPersonRouteTariff->getRoute(),
				$orderPersonRouteTariff->getRouteTariff(),
				$orderPersonRouteTariff->getOrderPerson()
			);
			$book->setExternalTicket($externalTicket);

			$externalTicket->setBookingId($response->booking_id);
			$externalTicket->setHash($response->hash);
			$externalTicket->setHtmlBody($html);
			$externalTicket->setFile($file);
			$externalTicket->setContentType("application/pdf");

			$tickets[] = $externalTicket;
		}

		return $tickets;
	}

	public function canPayOnline(Route $route)
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode("TTbus")) return false;

		return true;
	}


}
