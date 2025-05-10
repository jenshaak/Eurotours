<?php

namespace AppBundle\Routers;

use AppBundle\Connectors\LikeBusConnector;
use AppBundle\Entity\BookLikeBus;
use AppBundle\Entity\ExternalCityLikeBus;
use AppBundle\Entity\ExternalStationLikeBus;
use AppBundle\Entity\ExternalTariffLikeBus;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Order;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\SearchExternal;
use AppBundle\Exceptions\LikeBusConnectorException;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalStationService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\RouteService;
use AppBundle\Service\RouteTariffService;
use AppBundle\VO\ExternalRouteLikeBus;
use AppBundle\VO\ExternalRouter;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;

class LikeBusRouter
{
	public const CARRIER_CODE = 'LikeBusAPI';

	private CityService $cityService;

	private LanguageService $languageService;

	private LikeBusConnector $connector;

	private ExternalCityService $externalCityService;

	private ExternalStationService $externalStationService;

	private RouteService $routeService;

	private CurrencyService $currencyService;

	private CarrierService $carrierService;

	private ExternalTariffService $externalTariffService;

	private RouteTariffService $routeTariffService;

	public function __construct(
		CityService $cityService,
		LanguageService $languageService,
		LikeBusConnector $connector,
		ExternalCityService $externalCityService,
		ExternalStationService $externalStationService,
		RouteService $routeService,
		CurrencyService $currencyService,
		CarrierService $carrierService,
		ExternalTariffService $externalTariffService,
		RouteTariffService $routeTariffService
	)
	{
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->connector = $connector;
		$this->externalCityService = $externalCityService;
		$this->externalStationService = $externalStationService;
		$this->routeService = $routeService;
		$this->currencyService = $currencyService;
		$this->carrierService = $carrierService;
		$this->externalTariffService = $externalTariffService;
		$this->routeTariffService = $routeTariffService;
	}

	/** @throws Exception */
	public function syncExternals()
	{
		$allCities = $this->cityService->findAllCities();
		$cities = $this->connector->getCities();
		$languages = $this->languageService->getAllLanguages();

		foreach ($cities as $c) {
			$externalCity = $this->externalCityService->getExternalCityByIdent($c['id'], ExternalRouter::LIKEBUS);
			if ($externalCity === null) {
				$externalCity = new ExternalCityLikeBus();
				$externalCity->setIdent($c['id']);
			}

			if (!$externalCity->isProcessed()) {
				foreach ($allCities as $city) {
					$found = false;

					foreach ($languages as $language) {
						if (!$language->getIdForLikeBus()) continue;

						if ($city->getName()->getString($language) == $c['loc'][$language->getIdForLikeBus()]['name']) {
							$externalCity->setCity($city);
							$found = true;
							break;
						}
					}

					if ($found) break;
				}
			}
			foreach ($languages as $language) {
				if (!$language->getIdForLikeBus()) continue;

				if (!empty($c['loc'][$language->getIdForLikeBus()]['name'])) {
					$externalCity->setName($language, $c['loc'][$language->getIdForLikeBus()]['name']);
				}
			}

			$this->externalCityService->saveExternalCity($externalCity);
		}

		$stations = $this->connector->getStations();

		foreach ($stations as $s) {
			$externalStation = $this->externalStationService->getExternalStationByIdent($s['id'], ExternalRouter::LIKEBUS);
			if ($externalStation === null) {
				$externalStation = new ExternalStationLikeBus();
				$externalStation->setIdent($s['id']);
				$externalStation->setExternalCity(
					$this->externalCityService->getExternalCityByIdent($s['city_id'], ExternalRouter::LIKEBUS)
				);
			}

			foreach ($languages as $language) {
				if (!$language->getIdForLikeBus()) continue;

				$externalStation->setName($language, $s['loc'][$language->getIdForLikeBus()]['name']);
			}

			$this->externalStationService->saveExternalStation($externalStation);
		}

		$tariffs = $this->connector->getTariffs();
		$language = $this->languageService->getUkrainian();

		foreach ($tariffs as $tariffData) {
			$externalTariff = $this->externalTariffService->getExternalTariffByIdent(
				$tariffData['id'], ExternalRouter::LIKEBUS
			);

			if ($externalTariff === null) {
				$externalTariff = new ExternalTariffLikeBus();
				$externalTariff->setIdent($tariffData['id']);
			}

			$externalTariff->setName($language, $tariffData['title']);

			$externalTariff->setPercent($tariffData['price']);
			$this->externalTariffService->saveExternalTariff($externalTariff);
		}
	}

	/**
	 * @return ArrayCollection<Route>|array
	 */
	public function findRoutes(SearchExternal $searchExternal)
	{
		$routeFilter = $searchExternal->getSearch()->createRouteFilter();

		/** @var ArrayCollection<Route> $routes */
		$routes = new ArrayCollection();

		$from = $routeFilter->getFromCity()->getExternalCity(ExternalRouter::LIKEBUS);
		$to = $routeFilter->getToCity()->getExternalCity(ExternalRouter::LIKEBUS);

		if ($from === null or $to === null) return [];

		try {
			$externalRoutes = $this->connector->findRoutes(
				$from->getIdent(), $to->getIdent(), $routeFilter->getDateDay(),
			);
		} catch (LikeBusConnectorException $e) {
			return [];
		}

		foreach ($externalRoutes['list'] as $externalRoute) {
			try {
				$route = $this->createRoute(
					$searchExternal,
					$externalRoute,
					LineStation::DIRECTION_THERE,
					$from->getIdent(),
					$to->getIdent()
				);
			} catch (Exception $e) {
				continue;
			}

			$routes->add($route);
		}

		if ($searchExternal->getSearch()->getType() == RouteFilter::TYPE_RETURN) {
			try {
				$externalRoutes = $this->connector->findRoutes(
					$to->getIdent(), $from->getIdent(), $routeFilter->getDateBack(),
				);
			} catch (LikeBusConnectorException $e) {
				return [];
			}

			foreach ($externalRoutes['list'] as $externalRoute) {
				try {
					$route = $this->createRoute(
						$searchExternal,
						$externalRoute,
						LineStation::DIRECTION_BACK,
						$to->getIdent(),
						$from->getIdent()
					);
				} catch (Exception $e) {
					continue;
				}
				$routes->add($route);
			}
		}

		return $routes;
	}

	/** @throws Exception */
	private function createRoute(
		SearchExternal $searchExternal,
		array $routeData,
		string $direction,
		string $fromCity,
		string $toCity
	): Route
	{
		$route = new Route();

		$route->setFromExternalCity($this->externalCityService->getExternalCityByIdent(
			$fromCity, ExternalRouter::LIKEBUS
		));
		$route->setToExternalCity($this->externalCityService->getExternalCityByIdent(
			$toCity, ExternalRouter::LIKEBUS
		));
		$route->setFromExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData['departure'][0]['station_id'], ExternalRouter::LIKEBUS
		));
		$route->setToExternalStation($this->externalStationService->getExternalStationByIdent(
			$routeData['arrival'][0]['station_id'], ExternalRouter::LIKEBUS
		));

		$route->setFromCity($route->getFromExternalCity()->getCity());
		$route->setToCity($route->getToExternalCity()->getCity());

		if ($route->getFromExternalStation()) {
			$route->setFromStation($route->getFromExternalStation()->getStation());
		}

		if ($route->getToExternalStation()) {
			$route->setToStation($route->getToExternalStation()->getStation());
		}

		$route->setSearchExternal($searchExternal);
		$route->setSearch($searchExternal->getSearch());
		$route->setDirection($direction);
		$route->setExternalIdent($routeData['id']);
		$route->setIsDirect(!$routeData['transfer']);

		$route->setDatetimeDeparture(new DateTime($routeData['departure'][0]['time_out']));
		$route->setDatetimeArrival(new DateTime($routeData['arrival'][0]['time_in']));
		$route->setCarrier($this->carrierService->getCarrierByCode(LikeBusRouter::CARRIER_CODE));

		$priceCurrency = $this->currencyService->currencyConvert(
			PriceCurrency::create($routeData['price'], $routeData['currency']), $searchExternal->getCurrency()
		);
		$route->setPrice($priceCurrency->getPrice());
		$route->setCurrency($priceCurrency->getCurrency());

		$externalObject = new ExternalRouteLikeBus();
		$externalObject->setPriceList($routeData['priceList']);

		$route->setExternalObject($externalObject);

		$this->routeService->saveRoute($route);

		$this->processFares($route);

		return $route;
	}

	private function processFares(Route $route): void
	{
		/** @var ExternalTariffLikeBus[] $tariffs */
		try {
			$tariffs = $this->externalTariffService->findExternalTariffsForType(ExternalRouter::LIKEBUS);
		} catch (Exception $e) {
			return;
		}

		foreach ($tariffs as $externalTariff) {
			$routeTariff = RouteTariff::createForExternalTariff($route, $externalTariff, $route->getCurrency());
			$routeTariff->setPrice((($route->getPrice() / 100) * $externalTariff->getPercent()));
			$routeTariff->setExternalBookingIdent($externalTariff->getIdent());
			$this->routeTariffService->saveRouteTariff($routeTariff);
		}
	}

	public function canBuyRoute(Route $route): bool
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode(LikeBusRouter::CARRIER_CODE)) return false;

		return true;
	}

	/**
	 * @return BookLikeBus[]
	 * @throws LikeBusConnectorException
	 */
	public function bookRoute(Order $order, Route $route): array
	{
		/** @var ExternalRouteLikeBus $externalObjectLikebus */
		$externalObjectLikebus = $route->getExternalObject();
		$routePriceList = $externalObjectLikebus->getPriceList();

		if (!in_array($route->getCurrency(), array_keys($routePriceList))) {
			$currency = array_keys($routePriceList)[0];

			$externalObjectLikebus = new ExternalRouteLikeBus();
			$externalObjectLikebus->setPriceList($routePriceList);
			$externalObjectLikebus->setChangedToCurrency($currency);
			$route->setExternalObject($externalObjectLikebus);

			$this->routeService->saveRoute($route);
		} else {
			$currency = $route->getCurrency();
		}

		$createOrderResponse = $this->connector->createOrder($order, $route, $currency);
		$externalOrderId = array_keys($createOrderResponse)[0] ?? null;

		$books = [];

		foreach ($route->getOrderPersonRouteTariffs() as $orderPersonRouteTariff) {
			$likebusBook = new BookLikeBus();
			$likebusBook->setExternalOrderId($externalOrderId);
			$likebusBook->setOrder($order);
			$likebusBook->setOrderPersonRouteTariff($orderPersonRouteTariff);
			$orderPersonRouteTariff->setBook($likebusBook);

			$books[] = $likebusBook;
		}

		return $books;
	}

	/** @throws Exception */
	public function buyRoute(Route $route): void
	{
		/** @var BookLikeBus[] $books */
		$books = $route->getBooks()->getValues();

		if (!empty($books)) {
			$book = $books[0];
			$order = $books[0]->getOrder();
		} else {
			return;
		}

		/** @var ExternalRouteLikeBus $externalObjectLikeBus */
		$externalObjectLikeBus = $route->getExternalObject();

		if ($externalObjectLikeBus->getChangedToCurrency() === null) {
			$price = $order->getPrice();
		} else {
			$price = $externalObjectLikeBus->getPriceList()[$externalObjectLikeBus->getChangedToCurrency()];
		}

		$confirmOrderResponse = $this->connector->confirmOrder($book->getExternalOrderId(), $price);

		if (!$confirmOrderResponse['status']) return;
	}

	public function canPayOnline(Route $route): bool
	{
		if ($route->getCarrier() !== $this->carrierService->getCarrierByCode(LikeBusRouter::CARRIER_CODE)) return false;

		return true;
	}
}
