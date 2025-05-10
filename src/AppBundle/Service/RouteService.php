<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.05.17
 * Time: 10:45
 */

namespace AppBundle\Service;


use AppBundle\Entity\Line;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Route;
use AppBundle\Entity\ScheduleLineStation;
use AppBundle\Entity\Search;
use AppBundle\Entity\SearchExternal;
use AppBundle\Entity\Tariff;
use AppBundle\Repository\RouteRepository;
use AppBundle\VO\RouteFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

class RouteService
{
	const PARAM_SEARCH_FROM = "searchFrom";
	const PARAM_SEARCH_TO = "searchTo";
	const PARAM_SEARCH_DAY = "searchDay";
	const PARAM_SEARCH_BACK = "searchBack";
	const PARAM_SEARCH_TYPE = "type";
	const PARAM_ONLY_OPEN = "onlyOpen";
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var RouteRepository
	 */
	private $routeRepository;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var UserService
	 */
	private $userService;

	public function __construct(CityService $cityService,
	                            LineService $lineService,
	                            DateFormatService $dateFormatService,
	                            RouteRepository $routeRepository,
	                            CurrencyService $currencyService,
	                            UserService $userService)
	{
		$this->cityService = $cityService;
		$this->lineService = $lineService;
		$this->dateFormatService = $dateFormatService;
		$this->routeRepository = $routeRepository;
		$this->currencyService = $currencyService;
		$this->userService = $userService;
	}

	/**
	 * @param Request $request
	 * @return RouteFilter
	 */
	public function createRouteFilterFromRequest(Request $request)
	{
		$filter = new RouteFilter;

		if ($request->request->has(self::PARAM_SEARCH_FROM)) {
			$filter->setFromCity($this->cityService->getCity($request->request->get(self::PARAM_SEARCH_FROM)));
		}

		if ($request->request->has(self::PARAM_SEARCH_TO)) {
			$filter->setToCity($this->cityService->getCity($request->request->get(self::PARAM_SEARCH_TO)));
		}

		if ($request->request->has(self::PARAM_SEARCH_DAY)) {
			$filter->setDateDay($this->dateFormatService->dateParse($request->request->get(self::PARAM_SEARCH_DAY)));
		}

		if ($request->request->has(self::PARAM_SEARCH_BACK)) {
			$filter->setDateBack($this->dateFormatService->dateParse($request->request->get(self::PARAM_SEARCH_BACK)));
		}

		if ($request->request->has(self::PARAM_SEARCH_TYPE)) {
			$filter->setType($request->request->get(self::PARAM_SEARCH_TYPE));
		}

		if ($request->request->has(self::PARAM_SEARCH_TYPE) and $request->request->get(self::PARAM_ONLY_OPEN) == 1) {
			$filter->setType(RouteFilter::TYPE_RETURN_OPEN);
		}

		return $filter;
	}

	/**
	 * @param Search $search
	 * @return \AppBundle\Entity\Route[]|ArrayCollection
	 */
	public function findRoutes(Search $search, $missed = false)
	{
		$filter = $search->createRouteFilter();

		/** @var Route[]|ArrayCollection $routes */
		$routes = new ArrayCollection;

		if ($search->getType() == RouteFilter::TYPE_RETURN) {
			$directions = [ LineStation::DIRECTION_THERE, LineStation::DIRECTION_BACK ];
		} else {
			$directions = [ LineStation::DIRECTION_THERE ];
		}

		foreach ($directions as $direction) {
			if ($direction === LineStation::DIRECTION_THERE) {
				$lines = $this->lineService->findLines($filter->createLineFilterThere());
			} else {
				$lines = $this->lineService->findLines($filter->createLineFilterBack());
			}

			foreach ($lines as $line) {
				if ($line->isHidden()) continue;
				if ($this->userService->isSeller() and !$line->getSellers()->contains($this->userService->getCurrentUser())) continue;
				$this->getSchedulesForLine($filter, $line, $direction)->map(function (Schedule $schedule) use ($routes, $search, $direction, $missed) {
					foreach ($this->createRoutesForSchedule($search, $schedule, $direction, $missed) as $route) {
						$routes->add($route);
					}
				});
			}
		}

		$routes = $routes->toArray();
		usort($routes, function (Route $a, Route $b) {
			return (int) $a->getDatetimeDeparture()->format("YmdHi") > (int) $b->getDatetimeDeparture()->format("YmdHi") ? +1 : -1;
		});

		return new ArrayCollection($routes);
	}

	private function createRoutesForScheduleAndStations(Schedule $schedule,
														Search $search,
														string $direction,
														ScheduleLineStation $fromScheduleLineStation,
														ScheduleLineStation $toScheduleLineStation,
														\DateTime $dateDay,
														bool $missed): ?ArrayCollection
	{
		$routes = new ArrayCollection;

		$fromCity = $fromScheduleLineStation->getLineStation()->getCity();
		$toCity = $toScheduleLineStation->getLineStation()->getCity();

		$route = new Route;
		$route->setSchedule($schedule);

		$route->setFromScheduleLineStation($fromScheduleLineStation);
		$route->setScheduleTimeDeparture($fromScheduleLineStation->getTime());
		$route->setFromStation($fromScheduleLineStation->getLineStation()->getStation());
		$route->setDatetimeDeparture(new \DateTime(
			$dateDay->format("Y-m-d") . " " . $fromScheduleLineStation->getTime()->getDepartureTime()
		));

		$route->setToScheduleLineStation($toScheduleLineStation);
		$route->setScheduleTimeArrival($toScheduleLineStation->getTime());
		$route->setToStation($toScheduleLineStation->getLineStation()->getStation());
		if ($toScheduleLineStation->getTime()->getArrivalTime()) {
			$route->setDatetimeArrival(new \DateTime(
				$dateDay->format("Y-m-d") . " " . $toScheduleLineStation->getTime()->getArrivalTime()
			));
			if ($toScheduleLineStation->getTime()->getArrivalDayModify()) {
				$route->getDatetimeArrival()->modify($toScheduleLineStation->getTime()->getArrivalDayModify() - $fromScheduleLineStation->getTime()->getDepartureDayModify() . " days");
			}
		} else {
			$route->setDatetimeArrival(new \DateTime(
				$dateDay->format("Y-m-d") . " " . $toScheduleLineStation->getTime()->getDepartureTime()
			));
			if ($toScheduleLineStation->getTime()->getDepartureDayModify()) {
				$route->getDatetimeArrival()->modify($toScheduleLineStation->getTime()->getDepartureDayModify() - $fromScheduleLineStation->getTime()->getDepartureDayModify() . " days");
			}
		}

		$route->setFromCity($fromCity);
		$route->setToCity($toCity);
		$route->setLine($schedule->getLine());
		$route->setCarrier($schedule->getLine()->getCarrier());
		$route->setSearch($search);
		$route->setDirection($direction);

		$linePeriod = $schedule->getLine()->getLinePeriod();
		if ($linePeriod and !$linePeriod->isValidForDate($dateDay)) return $routes;
		unset($linePeriod);

		if ($schedule->getActiveTariffs()->isEmpty()) return $routes;

		if (!$missed and $route->getDatetimeDeparture() <= new \DateTime) {
			return $routes;
		} elseif ($missed and $route->getDatetimeDeparture() <= new \DateTime("-1 hour")) {
			return $routes;
		}

		$schedule->getActiveTariffs()->map(function (Tariff $tariff) use ($route, $fromScheduleLineStation, $toScheduleLineStation, $search, $dateDay, $schedule) {
			if ($tariff->getType() !== Tariff::TYPE_PRICES) return;

			$originalTariff = $tariff;
			$temporaryTariff = $tariff->getTemporaryTariffForDate($dateDay, $schedule->getDirection());
			if ($temporaryTariff) {
				$tariff = $temporaryTariff;
			}

			if ($tariff->getAllowDays() !== null) {
				$days = ceil(($route->getDatetimeDeparture()->getTimestamp() - time()) / 60 / 60 / 24);
				if ($days <= $tariff->getAllowDays()) return null;
			}

			if ($tariff->getExcludeDays()->isDateTime($route->getDatetimeDeparture())) {
				return;
			}

			$fare = $tariff->getFareForLineStations(
				$fromScheduleLineStation->getLineStation(),
				$toScheduleLineStation->getLineStation()
			);

			if ($fare === null) return null;
			if ($fare->isNotAvailable()) return null;

			if ($tariff->getOtherCurrencyTariffWithTemporary() !== null and $tariff->getCurrency() !== $this->currencyService->getCurrentCurrency()) {
				$otherFare = $tariff->getFareForLineStations(
					$fromScheduleLineStation->getLineStation(),
					$toScheduleLineStation->getLineStation()
				);
				if ($tariff->getOtherCurrencyTariffWithTemporary()->getCurrency() === $this->currencyService->getCurrentCurrency()) return null;
				if ($otherFare->isNotAvailable()) return null;
			}

			$routeTariff = RouteTariff::createForTariff($route, $originalTariff, $fare);
			if ($search->getType() == RouteFilter::TYPE_RETURN_OPEN) {
				$priceCurrency = $this->currencyService->currencyConvert(
					$fare->getPriceReturnCurrency(), $this->currencyService->getCurrentCurrency()
				);
				$route->setIsOpen(true);
			} else {
				$priceCurrency = $this->currencyService->currencyConvert(
					$fare->getPriceCurrency(), $this->currencyService->getCurrentCurrency()
				);
			}

			$routeTariff->setPrice($priceCurrency->getPrice() > 0 ? $priceCurrency->getPrice() : null);
			$routeTariff->setCurrency($priceCurrency->getCurrency());

			if ($temporaryTariff) {
				$routeTariff = RouteTariff::createForTariff($route, $tariff, $fare);

				$routeTariff->setPrice($priceCurrency->getPrice() > 0 ? $priceCurrency->getPrice() : null);
				$routeTariff->setCurrency($priceCurrency->getCurrency());
			}
		});

		$temporaryExcludeTariffsIds = new ArrayCollection;

		$schedule->getActiveTariffs()->map(function (Tariff $tariff) use ($route, $dateDay, $temporaryExcludeTariffsIds, $schedule) {
			if ($tariff->getType() !== Tariff::TYPE_PERCENT) return;

			$route->getRouteTariffs()->map(function (RouteTariff $rt) use ($tariff, $route, $dateDay, $temporaryExcludeTariffsIds, $schedule) {
				if ($rt->getTariff() === $tariff->getPercentFromTariff()) {
					$temporaryTariff = $rt->getTariff()->getTemporaryTariffForDate($dateDay, $schedule->getDirection());
					if ($temporaryTariff and $route->getRouteTariffForTariff($temporaryTariff)) {
						(new ArrayCollection($temporaryTariff->getTemporaryExcludeTariffs($dateDay, $schedule->getDirection())))->map(function ($id) use ($temporaryExcludeTariffsIds) {
							$temporaryExcludeTariffsIds->add($id);
						});
						$rt = $route->getRouteTariffForTariff($temporaryTariff);
					}
					$routeTariff = RouteTariff::createForTariff($route, $tariff);
					$priceCurrency = $this->currencyService->currencyConvert(
						$rt->getPriceCurrency(), $this->currencyService->getCurrentCurrency()
					);
					$routeTariff->setPrice(($priceCurrency->getPrice() / 100) * (100 - $tariff->getPercentForDay($dateDay, $route->getSchedule()->getDirection())));
					$routeTariff->setCurrency($priceCurrency->getCurrency());
				}
			});
		});

		$route->setRouteTariffs($route->getRouteTariffs()->filter(function (RouteTariff $routeTariff) use ($dateDay) {
			$tariff = $routeTariff->getTariff();
			return $tariff->getTemporaryFromTariff() === null or $tariff->isTemporaryTariffForDate($dateDay, $routeTariff->getFare()->getFromLineStation()->getDirection());
		}));

		$route->setRouteTariffs($route->getRouteTariffs()->filter(function (RouteTariff $routeTariff) use ($dateDay, $route, $temporaryExcludeTariffsIds) {
			$tariff = $routeTariff->getTariff();
			if ($temporaryExcludeTariffsIds->contains($tariff->getId())) return false;
			if ($tariff->getTemporaryFromTariff() === null and $tariff->getTemporaryTariffs()->isEmpty()) return true;
			$temporaryTariff = $tariff->getTemporaryTariffForDate($dateDay, $routeTariff->getFare()->getFromLineStation()->getDirection());
			if ($temporaryTariff === null or !$route->getTariffs()->contains($temporaryTariff)) return true;
			return $tariff->isTemporaryTariffForDate($dateDay, $routeTariff->getFare()->getFromLineStation()->getDirection());
		}));

		$route->getRouteTariffs()->map(function (RouteTariff $routeTariff) use ($route) {
			if ($routeTariff->getPrice() === null or $route->getPrice() < $routeTariff->getPrice()) {
				$route->setPrice($routeTariff->getPrice());
				$route->setCurrency($routeTariff->getCurrency());
			}
		});

		if ($route->getRouteTariffs()->isEmpty()) return $routes;

		$this->saveRoute($route);
		$routes->add($route);

		return $routes;
	}

	/**
	 * @param Search $search
	 * @param Schedule $schedule
	 * @param string $direction
	 * @param bool $missed
	 * @return Route[]
	 * @throws \Exception
	 */
	private function createRoutesForSchedule(Search $search, Schedule $schedule, $direction, $missed = false)
	{
		$routes = new ArrayCollection;

		$routeFilter = $search->createRouteFilter();
		if ($direction === LineStation::DIRECTION_BACK) {
			$dateDay = $routeFilter->getDateBack();
			$fromCity = $routeFilter->getToCity();
			$toCity = $routeFilter->getFromCity();
		} else {
			$dateDay = $routeFilter->getDateDay();
			$fromCity = $routeFilter->getFromCity();
			$toCity = $routeFilter->getToCity();
		}

		$fromScheduleLineStations = $schedule->getScheduleLineStationsForCity($fromCity);
		$toScheduleLineStations = $schedule->getScheduleLineStationsForCity($toCity);

		foreach ($fromScheduleLineStations as $fromScheduleLineStation) {
			foreach ($toScheduleLineStations as $toScheduleLineStation) {
				$this->createRoutesForScheduleAndStations(
					$schedule,
					$search,
					$direction,
					$fromScheduleLineStation,
					$toScheduleLineStation,
					$dateDay,
					$missed
				)->map(fn (Route $r) => $routes->add($r));
			}
		}

		return $routes->toArray();
	}

	/**
	 * @param Route $route
	 */
	public function saveRoute(Route $route)
	{
		$this->routeRepository->save($route);
	}

	/**
	 * @param RouteFilter $routeFilter
	 * @param Line $line
	 * @param string $direction
	 * @return \AppBundle\Entity\Schedule[]|ArrayCollection
	 */
	public function getSchedulesForLine(RouteFilter $routeFilter, Line $line, $direction)
	{
		/** @var ArrayCollection|Schedule[] $schedules */
		$schedules = $line->getActiveSchedules()->filter(function (Schedule $schedule) use ($routeFilter, $direction, $line) {
			if ($direction == LineStation::DIRECTION_THERE) {
				$dateDay = clone $routeFilter->getDateDay();
				$fromScheduleLineStation = $schedule->getScheduleLineStationForCity($routeFilter->getFromCity());
				$toScheduleLineStation = $schedule->getScheduleLineStationForCity($routeFilter->getToCity());
			} elseif ($direction == LineStation::DIRECTION_BACK) {
				$dateDay = clone $routeFilter->getDateBack();
				$fromScheduleLineStation = $schedule->getScheduleLineStationForCity($routeFilter->getToCity());
				$toScheduleLineStation = $schedule->getScheduleLineStationForCity($routeFilter->getFromCity());

			}
			if ($fromScheduleLineStation === null or $toScheduleLineStation === null) return false;

			$dayModify = $fromScheduleLineStation->getTime()->getDepartureDayModify();
			$dateDay->modify("-{$dayModify} days");

			if ($fromScheduleLineStation->getLineStation()->getWeight() > $toScheduleLineStation->getLineStation()->getWeight()) {
				return false;
			}

			if ($routeFilter->getType() == RouteFilter::TYPE_RETURN_OPEN and !$line->isOpenPossible()) {
				return false;
			}

			if (!$schedule->getWeekDays()->contains($dateDay->format("N")-1)) {
				return false;
			}

			if (!$schedule->getIncludeDays()->isDateTime($dateDay)) {
				return false;
			}

			if ($schedule->getExcludeDays()->isDateTime($dateDay)) {
				return false;
			}

			return true;
		});

		return $schedules;
	}

	/**
	 * @param SearchExternal $searchExternal
	 * @return Route[]
	 */
	public function findRoutesBySearchExternal(SearchExternal $searchExternal)
	{
		return $this->routeRepository->findBy([ "searchExternal" => $searchExternal ]);
	}

	/**
	 * @param int $id
	 * @return null|Route
	 */
	public function getRoute($id)
	{
		return $this->routeRepository->find($id);
	}
}
