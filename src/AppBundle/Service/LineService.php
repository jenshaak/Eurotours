<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:46
 */

namespace AppBundle\Service;


use AppBundle\Entity\Fare;
use AppBundle\Entity\Line;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\ScheduleLineStation;
use AppBundle\Entity\Station;
use AppBundle\Entity\Tariff;
use AppBundle\Entity\User;
use AppBundle\Repository\LineRepository;
use AppBundle\VO\DuplicatorVO;
use AppBundle\VO\LineFilter;
use AppBundle\VO\LinePeriod;
use Symfony\Component\HttpFoundation\Request;

class LineService
{
	const PARAM_WEIGHT_LINE_STATION = "weightLineStation";
	const PARAM_SCHEDULE_TIME = "scheduleTime";
	const PARAM_CODE = "code";
	const PARAM_CARRIER = "carrier";
	const PARAM_WEEK_DAYS = "weekDays";
	const PARAM_TARIFF = "tariff";
	const PARAM_INCLUDE_DAYS = "includeDays";
	const PARAM_EXCLUDE_DAYS = "excludeDays";
	const PARAM_OWN_SEATS = "ownSeats";
	const PARAM_PAY_ONLINE = "payOnline";
	const PARAM_OPEN_POSSIBLE = "openPossible";
	const PARAM_ALLOW_ORDER_DAYS = "allowOrderDays";
	const PARAM_WEIGHT_SCHEDULE = "weightSchedule";
	const PARAM_ADMIN_FREE_POSSIBLE = "adminFreePossible";
	const PARAM_BACK_WAY_ONLY_AS_RETURN_PAY_POSSIBLE = "backWayOnlyAsReturnPayPossible";
	const PARAM_SEATS_WITHOUT_NUMBERS_THERE = "seatsWithoutNumbersThere";
	const PARAM_SEATS_WITHOUT_NUMBERS_BACK = "seatsWithoutNumbersBack";
	const PARAM_GENERATE_INTERNAL_TICKET = "generateInternalTicket";
	const PARAM_PRIVATE_NOTE = "privateNote";
	const PARAM_PUBLIC_NOTE = "publicNote";
	const PARAM_ALLOW_ORDER_HOURS_THERE = "allowOrderHoursThere";
	const PARAM_ALLOW_ORDER_HOURS_BACK = "allowOrderHoursBack";
	const PARAM_LINE_PERIOD_DAYS = "linePeriodDays";
	const PARAM_LINE_PERIOD_DATE_BEGIN = "linePeriodDateBegin";
	const PARAM_SEATS_WITHOUT_NUMBERS_SCHEDULE = "seatsWithoutNumbersSchedule";
	const PARAM_PAY_ONLINE_DISABLED_SCHEDULE = "payOnlineDisabledSchedule";

	/**
	 * @var LineRepository
	 */
	private $lineRepository;
	/**
	 * @var ScheduleLineStationService
	 */
	private $scheduleLineStationService;
	/**
	 * @var LineStationService
	 */
	private $lineStationService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var ScheduleService
	 */
	private $scheduleService;
	/**
	 * @var TariffService
	 */
	private $tariffService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;

	public function __construct(LineRepository $lineRepository,
	                            ScheduleLineStationService $scheduleLineStationService,
	                            LineStationService $lineStationService,
	                            CarrierService $carrierService,
	                            ScheduleService $scheduleService,
	                            TariffService $tariffService,
	                            LanguageService $languageService,
	                            DateFormatService $dateFormatService)
	{
		$this->lineRepository = $lineRepository;
		$this->scheduleLineStationService = $scheduleLineStationService;
		$this->lineStationService = $lineStationService;
		$this->carrierService = $carrierService;
		$this->scheduleService = $scheduleService;
		$this->tariffService = $tariffService;
		$this->languageService = $languageService;
		$this->dateFormatService = $dateFormatService;
	}

	/**
	 * @param int $id
	 * @return null|Line
	 */
	public function getLine($id)
	{
		return $this->lineRepository->find($id);
	}

	/**
	 * @param string $code
	 * @return null|Line
	 */
	public function getLineByCode($code)
	{
		return $this->lineRepository->findOneBy([ "code" => $code ]);
	}

	/**
	 * @param Line $line
	 */
	public function saveLine(Line $line)
	{
		$this->lineRepository->save($line);
		$line->getActiveLineStations()->map(function (LineStation $lineStation) {
			$this->lineStationService->saveLineStation($lineStation);
		});
		$line->getSchedules()->map(function (Schedule $schedule) {
			$this->scheduleService->saveSchedule($schedule);
		});
	}

	/**
	 * @return Line[]
	 */
	public function getAllLines()
	{
		return $this->lineRepository->findBy([ "deleted" => false ]);
	}

	/**
	 * @param LineFilter $filter
	 * @return Line[]
	 */
	public function findLines(LineFilter $filter)
	{
		return $this->lineRepository->findLines($filter);
	}

	/**
	 * @param Line $line
	 * @param Station $station
	 */
	public function addStationToLine(Line $line, Station $station)
	{
		$lastLineStation = $line->getLastLineStation();
		$weight = $lastLineStation ? ($lastLineStation->getWeight() + 1) : 0;

		$lineStation = new LineStation;
		$lineStation->setLine($line);
		$lineStation->setCity($station->getCity());
		$lineStation->setStation($station);

		$there = clone $lineStation;
		$there->setDirection(LineStation::DIRECTION_THERE);
		$there->setWeight($weight);
		$line->addLineStation($there);

		$back = clone $lineStation;
		$back->setDirection(LineStation::DIRECTION_BACK);
		$back->setWeight(0);
		$line->getActiveLineStations(LineStation::DIRECTION_BACK)->map(function (LineStation $lineStation) {
			$lineStation->setWeight($lineStation->getWeight() + 1);
		});
		$line->addLineStation($back);
	}

	/**
	 * @param Line $line
	 * @param Request $request
	 */
	public function updateLineByRequest(Line $line, Request $request)
	{
		if ($request->request->has(self::PARAM_WEIGHT_LINE_STATION)) {
			foreach ($request->request->get(self::PARAM_WEIGHT_LINE_STATION) as $lineStationId => $weight) {
				$lineStation = $line->getLineStationById($lineStationId);
				if ($lineStation) {
					$lineStation->setWeight($weight);
					$this->lineStationService->saveLineStation($lineStation);
				}
			}
		}

		if ($request->request->has(self::PARAM_WEIGHT_SCHEDULE)) {
			foreach ($request->request->get(self::PARAM_WEIGHT_SCHEDULE) as $scheduleId => $weight) {
				$schedule = $line->getScheduleById($scheduleId);
				if ($schedule) {
					$schedule->setWeight($weight);
				}
			}
		}

		if ($request->request->has(self::PARAM_SCHEDULE_TIME)) {
			foreach ($request->request->get(self::PARAM_SCHEDULE_TIME) as $scheduleId => $array) {
				$schedule = $line->getScheduleById($scheduleId);
				foreach ($array as $lineStationId => $times) {
					$lineStation = $line->getLineStationById($lineStationId);
					$scheduleLineStation = $lineStation->getScheduleLineStationBySchedule($schedule);
					if ($times['departureTime'] or $times['arrivalTime']) {
						if ($scheduleLineStation === null) {
							$scheduleLineStation = new ScheduleLineStation;
							$scheduleLineStation->setSchedule($schedule);
							$scheduleLineStation->setLineStation($lineStation);
							$lineStation->addScheduleLineStation($scheduleLineStation);
						}
						$scheduleTime = clone $scheduleLineStation->getTime();
						$scheduleTime->setDepartureTime($times['departureTime']);
						$scheduleTime->setDepartureDayModify(isset($times['departureDayModify']) ? $times['departureDayModify'] : 0);
						$scheduleTime->setArrivalTime($times['arrivalTime']);
						$scheduleTime->setArrivalDayModify(isset($times['arrivalDayModify']) ? $times['arrivalDayModify'] : 0);
						$scheduleLineStation->setPlatform($times['platform'] ?: null);
						$scheduleLineStation->setTime($scheduleTime);
						$scheduleLineStation->setDeleted(false);
						$this->scheduleLineStationService->saveScheduleLineStation($scheduleLineStation);
					} elseif ($scheduleLineStation) {
						$scheduleLineStation->setDeleted(true);
						$this->scheduleLineStationService->saveScheduleLineStation($scheduleLineStation);
					}
				}
			}
		}

		if ($request->request->has(self::PARAM_WEEK_DAYS)) {
			foreach ($request->request->get(self::PARAM_WEEK_DAYS) as $scheduleId => $weekDays) {
				$schedule = $line->getScheduleById($scheduleId);
				$schedule->getWeekDays()->clear();
				foreach ($weekDays as $day) {
					$schedule->getWeekDays()->add((int) $day);
				}
				$schedule->setWeekDays(clone $schedule->getWeekDays());
			}
		}

		if ($request->request->has(self::PARAM_OWN_SEATS)) {
			foreach ($request->request->get(self::PARAM_OWN_SEATS) as $scheduleId => $ownSeats) {
				$schedule = $line->getScheduleById($scheduleId);
				$schedule->setOwnSeats($ownSeats > 0 ? $ownSeats : null);
			}
		}

		if ($request->request->has(self::PARAM_ALLOW_ORDER_HOURS_THERE)) {
			$allowOrderDaysThere = $request->request->get(self::PARAM_ALLOW_ORDER_HOURS_THERE);
			$line->setAllowOrderHoursThere($allowOrderDaysThere === "" ? null : $allowOrderDaysThere);
		}

		if ($request->request->has(self::PARAM_ALLOW_ORDER_HOURS_BACK)) {
			$allowOrderDaysBack = $request->request->get(self::PARAM_ALLOW_ORDER_HOURS_BACK);
			$line->setAllowOrderHoursBack($allowOrderDaysBack === "" ? null : $allowOrderDaysBack);
		}

		if ($request->request->has(self::PARAM_CODE)) {
			$line->setCode($request->request->get(self::PARAM_CODE));
		}

		if ($request->request->has(self::PARAM_LINE_PERIOD_DAYS) and $request->request->has(self::PARAM_LINE_PERIOD_DATE_BEGIN)) {
			$linePeriod = new LinePeriod;
			$linePeriod->setDays($request->request->getInt(self::PARAM_LINE_PERIOD_DAYS));
			$linePeriod->setDateBegin($this->dateFormatService->dateParse($request->request->get(self::PARAM_LINE_PERIOD_DATE_BEGIN)));
			$line->setLinePeriod($linePeriod);
		} else {
			$line->setLinePeriod(null);
		}

		if ($request->request->has(self::PARAM_CARRIER)) {
			$line->setCarrier($this->carrierService->getCarrier($request->request->get(self::PARAM_CARRIER)));
		}

		if ($request->request->has(self::PARAM_PAY_ONLINE)) {
			$line->setPayOnline($request->request->get(self::PARAM_PAY_ONLINE) == 1);
		}

		if ($request->request->has(self::PARAM_GENERATE_INTERNAL_TICKET)) {
			$line->setGenerateInternalTicket($request->request->get(self::PARAM_GENERATE_INTERNAL_TICKET) == 1);
		}

		if ($request->request->has(self::PARAM_OPEN_POSSIBLE)) {
			$line->setOpenPossible($request->request->get(self::PARAM_OPEN_POSSIBLE) == 1);
		}

		if ($request->request->has(self::PARAM_ADMIN_FREE_POSSIBLE)) {
			$line->setAdminFreePossible($request->request->get(self::PARAM_ADMIN_FREE_POSSIBLE) == 1);
		}

		if ($request->request->has(self::PARAM_BACK_WAY_ONLY_AS_RETURN_PAY_POSSIBLE)) {
			$line->setBackWayOnlyAsReturnPayPossible($request->request->get(self::PARAM_BACK_WAY_ONLY_AS_RETURN_PAY_POSSIBLE) == 1);
		}

		if ($request->request->has(self::PARAM_SEATS_WITHOUT_NUMBERS_THERE)) {
			$line->setSeatsWithoutNumbersThere($request->request->get(self::PARAM_SEATS_WITHOUT_NUMBERS_THERE) == 1);
		}

		if ($request->request->has(self::PARAM_SEATS_WITHOUT_NUMBERS_BACK)) {
			$line->setSeatsWithoutNumbersBack($request->request->get(self::PARAM_SEATS_WITHOUT_NUMBERS_BACK) == 1);
		}

		if ($request->request->has(self::PARAM_TARIFF)) {
			foreach ($request->request->get(self::PARAM_TARIFF) as $scheduleId => $tariffIds) {
				$schedule = $line->getScheduleById($scheduleId);
				if (!is_array($tariffIds)) $tariffIds = [];
				foreach ($tariffIds as $tariffId) {
					$tariff = $this->tariffService->getTariff($tariffId);
					if (!$schedule->getTariffs()->contains($tariff)) {
						$schedule->getTariffs()->add($tariff);
					}
				}
				$schedule->setTariffs($schedule->getTariffs()->filter(function (Tariff $tariff) use ($tariffIds) {
					return in_array($tariff->getId(), $tariffIds);
				}));
			}
		}

		if ($request->request->has(self::PARAM_SEATS_WITHOUT_NUMBERS_SCHEDULE)) {
			foreach ($request->request->get(self::PARAM_SEATS_WITHOUT_NUMBERS_SCHEDULE) as $scheduleId => $seatsWithoutNumber) {
				$schedule = $line->getScheduleById($scheduleId);
				$schedule->setSeatsWithoutNumbers($seatsWithoutNumber === "1");
			}
		}

		if ($request->request->has(self::PARAM_PAY_ONLINE_DISABLED_SCHEDULE)) {
			foreach ($request->request->get(self::PARAM_PAY_ONLINE_DISABLED_SCHEDULE) as $scheduleId => $payOnlineDisabled) {
				$schedule = $line->getScheduleById($scheduleId);
				$schedule->setPayOnlineDisabled($payOnlineDisabled === "1");
			}
		}

		if ($request->request->has(self::PARAM_INCLUDE_DAYS)) {
			foreach ($request->request->get(self::PARAM_INCLUDE_DAYS) as $scheduleId => $days) {
				$schedule = $line->getScheduleById($scheduleId);
				$schedule->getIncludeDays()->clear();
				$schedule->getIncludeDays()->setAll(false);
				if ($days == "all") {
					$schedule->getIncludeDays()->setAll(true);
				} elseif (count(explode(",", $days)) > 0) {
					foreach (explode(",", $days) as $d) {
						if ($d) $schedule->getIncludeDays()->addFromString($d);
					}
				}
				$schedule->setIncludeDays(clone $schedule->getIncludeDays());
			}
		}

		if ($request->request->has(self::PARAM_EXCLUDE_DAYS)) {
			foreach ($request->request->get(self::PARAM_EXCLUDE_DAYS) as $scheduleId => $days) {
				$schedule = $line->getScheduleById($scheduleId);
				$schedule->getExcludeDays()->clear();
				$schedule->getExcludeDays()->setAll(false);
				if ($days == "all") {
					$schedule->getExcludeDays()->setAll(true);
				} elseif (count(explode(",", $days)) > 0) {
					foreach (explode(",", $days) as $d) {
						if ($d) $schedule->getExcludeDays()->addFromString($d);
					}
				}
				$schedule->setExcludeDays(clone $schedule->getExcludeDays());
			}
		}

		if ($request->request->has(self::PARAM_PRIVATE_NOTE)) {
			foreach ($request->request->get(self::PARAM_PRIVATE_NOTE) as $lng => $note) {
				$line->setPrivateNote($this->languageService->getLanguage($lng), $note);
			}
		}

		if ($request->request->has(self::PARAM_PUBLIC_NOTE)) {
			foreach ($request->request->get(self::PARAM_PUBLIC_NOTE) as $lng => $note) {
				$line->setPublicNote($this->languageService->getLanguage($lng), $note);
			}
		}
	}

	/**
	 * @param Line $oldLine
	 * @return Line
	 */
	public function duplicateLine(Line $oldLine)
	{
		$duplicator = new DuplicatorVO;
		/** @var Line $line */
		$line = $duplicator->duplicate($oldLine);
		$line->setCode($line->getCode() . "_duplicate");

		$line->setSchedules($duplicator->processArrayCollection($line->getSchedules()));
		$line->getSchedules()->map(function (Schedule $schedule) use ($duplicator) {
			$schedule->setLine($duplicator->duplicate($schedule->getLine()));
			$schedule->setTariffs($duplicator->processArrayCollection($schedule->getTariffs()));
			$schedule->setScheduleLineStations($duplicator->processArrayCollection($schedule->getScheduleLineStations()));
			$schedule->getScheduleLineStations()->map(function (ScheduleLineStation $scheduleLineStation) use ($duplicator) {
				$scheduleLineStation->setLineStation($duplicator->duplicate($scheduleLineStation->getLineStation()));
				$scheduleLineStation->setSchedule($duplicator->duplicate($scheduleLineStation->getSchedule()));
			});
		});

		$line->setLineStations($duplicator->processArrayCollection($line->getLineStations()));
		$line->getLineStations()->map(function (LineStation $lineStation) use ($duplicator) {
			$lineStation->setLine($duplicator->duplicate($lineStation->getLine()));
			$lineStation->setScheduleLineStations($duplicator->processArrayCollection($lineStation->getScheduleLineStations()));
		});

		$line->setTariffs($duplicator->processArrayCollection($line->getTariffs()));
		$line->getTariffs()->map(function (Tariff $tariff) use ($duplicator) {
			$tariff->setLine($duplicator->duplicate($tariff->getLine()));
			$tariff->setFares($duplicator->processArrayCollection($tariff->getFares()));
			$tariff->getFares()->map(function (Fare $fare) use ($duplicator) {
				$fare->setFromLineStation($duplicator->duplicate($fare->getFromLineStation()));
				$fare->setToLineStation($duplicator->duplicate($fare->getToLineStation()));
				$fare->setTariff($duplicator->duplicate($fare->getTariff()));
			});
			$tariff->setOtherCurrencyTariff($duplicator->duplicate($tariff->getOtherCurrencyTariff()));
			$tariff->setTemporaryFromTariff($duplicator->duplicate($tariff->getTemporaryFromTariff()));
			$tariff->setPercentFromTariff($duplicator->duplicate($tariff->getPercentFromTariff()));
		});

		return $line;
	}

	public function setSellerForLine(Line $line, User $seller, $activated)
	{
		$sellers = $line->getSellers();
		$contains = $sellers->contains($seller);

		if (!$contains and $activated) {
			$sellers->add($seller);
		} elseif ($contains and !$activated) {
			$sellers->removeElement($seller);
		}
	}

}
