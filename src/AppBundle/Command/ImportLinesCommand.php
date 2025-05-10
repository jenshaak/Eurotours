<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 19:11
 */

namespace AppBundle\Command;


use AppBundle\Entity\Fare;
use AppBundle\Entity\Line;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\ScheduleLineStation;
use AppBundle\Entity\Tariff;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\FareService;
use AppBundle\Service\LineService;
use AppBundle\Service\ScheduleService;
use AppBundle\Service\StationService;
use AppBundle\Service\TariffService;
use AppBundle\VO\ScheduleTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportLinesCommand extends Command
{
	const TYPE_DEPARTURE = "departure";
	const TYPE_ARRIVAL = "arrival";

	/**
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var StationService
	 */
	private $stationService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var FareService
	 */
	private $fareService;
	/**
	 * @var TariffService
	 */
	private $tariffService;
	/**
	 * @var ScheduleService
	 */
	private $scheduleService;

	public function __construct(LineService $lineService,
	                            CityService $cityService,
	                            StationService $stationService,
	                            CarrierService $carrierService,
	                            FareService $fareService,
	                            TariffService $tariffService,
	                            ScheduleService $scheduleService)
	{
		$this->lineService = $lineService;
		$this->cityService = $cityService;
		$this->stationService = $stationService;
		$this->carrierService = $carrierService;
		$this->fareService = $fareService;
		$this->tariffService = $tariffService;
		$this->scheduleService = $scheduleService;

		parent::__construct();
	}

	protected function configure()
	{
		$this->setName("eurotours:import:lines");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		\dibi::connect([
			'driver'   => "mysqli",
			'host'     => "127.0.0.1",
			'username' => "root",
			'password' => "heslycko",
			"database" => "eurotours"
		]);

		$weekDays = [ "mon" => 0, "tue" => 1, "wed" => 2, "thu" => 3, "fri" => 4, "sat" => 5, "sun" => 6 ];

		$tables = \dibi::query("SHOW TABLES")->fetchPairs(null, "Tables_in_eurotours");
		foreach ($tables as $table) {
			if (preg_match("~^t(?P<direction>[0-9]+)_(?P<lineCode>.+)$~", $table, $buff)) {
				$lineCode = $buff['lineCode'];
				$direction = $buff['direction'] == 1 ? LineStation::DIRECTION_THERE : LineStation::DIRECTION_BACK;
				$times = \dibi::query("SELECT * FROM `{$table}` ORDER BY id ASC")->fetchAssoc("id");
				$line = $this->lineService->getLineByCode($lineCode);
				if ($line === null) {
					$line = new Line;
					$line->setCode($lineCode);
				}

				$informations = \dibi::query("SELECT * FROM informations WHERE line = '{$lineCode}'")->fetch();
				if (!$informations) {
					$output->writeln("Line " . $lineCode . " doesn't exist in 'informations'");
					continue;
				}
				$line->setCarrier($this->carrierService->getCarrier((int) $informations->trader));

				$order = 0;
				/** @var Schedule[] $schedules */
				$schedules = [];

				foreach ($times as $t) {
					if ($t->city) {
						$lineStation = new LineStation;
						$lineStation->setDirection($direction);
						$lineStation->setCity($this->cityService->getCity((int) $t->city));
						if ($lineStation->getCity() === null) continue;

						$lineStation->setStation($this->stationService->getStationByOldStation($lineStation->getCity(), (int) $t->station));
						if ($lineStation->getStation() === null) {
							var_dump($lineStation->getCity()->getId(), (int) $t->station);
							var_dump("-----");
							continue;
						}
						$lineStation->setWeight($order);

						$line->addLineStation($lineStation);
						$order++;

						foreach ($t as $key => $v) {
							if (preg_match("~time_~", $key)) {
								if (!isset($schedules[$key])) {
									$schedules[$key] = $schedule = new Schedule;
									$schedule->setDirection($direction);
								}
								$schedule = $schedules[$key];
								$schedule->setLine($line);

								$days = array_slice($times, -5, 1)[0][$key];
								if ($days == "all") {
									$schedule->setWeekDays(range(0, 6));
								} elseif (count(explode("+", $days)) > 0) {
									foreach (explode("+", $days) as $d) {
										if (isset($weekDays[$d])) {
											$schedule->addWeekDay($weekDays[$d]);
										}
									}
								}

								#var_dump($table);
								#var_dump(array_slice($times, -4, 1)[0][$key]);

								$days = array_slice($times, -4, 1)[0][$key];
								if ($days == "all") {
									$schedule->getIncludeDays()->setAll(true);
								} elseif ($days == "not" or $days == "" or $days == 0) {
									$schedule->getIncludeDays()->setAll(false);
								} elseif (count(explode("+", $days)) > 0) {
									foreach (explode("+", $days) as $d) {
										$schedule->getIncludeDays()->addFromString($d);
									}
								}

								$days = array_slice($times, -1, 1)[0][$key];
								if ($days == "all") {
									$schedule->getExcludeDays()->setAll(true);
								} elseif ($days == "not" or $days == "" or $days == 0) {
									$schedule->getExcludeDays()->setAll(false);
								} elseif (count(explode("+", $days)) > 0) {
									foreach (explode("+", $days) as $d) {
										$schedule->getExcludeDays()->addFromString($d);
									}
								}

								$scheduleLineStation = new ScheduleLineStation;
								$scheduleTime = new ScheduleTime;

								$explode = explode("+", $v);
								if (!isset($explode[1])) {
									if ($this->parseTime($scheduleTime, $explode[0], self::TYPE_DEPARTURE) === null) continue;
								} elseif (isset($explode[1])) {
									if ($this->parseTime($scheduleTime, $explode[0], self::TYPE_DEPARTURE) === null) continue;
									if ($this->parseTime($scheduleTime, $explode[1], self::TYPE_ARRIVAL) === null) continue;
									$scheduleTime->setDepartureDayModify($scheduleTime->getArrivalDayModify());
								}

								$schedule->setOldPriceTableNumber(array_slice($times, -3, 1)[0][$key]);
								$schedule->setOwnSeats(array_slice($times, -2, 1)[0][$key]);

								$scheduleLineStation->setTime($scheduleTime);
								$lineStation->addScheduleLineStation($scheduleLineStation);
								$schedule->addScheduleLineStation($scheduleLineStation);

								# zparsovat i dalsi radky - jezte zbyva kapacita
								#var_dump($times[count($times)-2]);
							}
						}
					}
				}

				$this->lineService->saveLine($line);
			}
		}
	}

	/**
	 * @param ScheduleTime $scheduleTime
	 * @param string $v
	 * @param string $type
	 * @return ScheduleTime|null
	 */
	private function parseTime(ScheduleTime $scheduleTime, $v, $type)
	{
		if (preg_match("~^([0-9]+):([0-9]+)$~", $v)) {
			if ($type == self::TYPE_DEPARTURE) {
				$scheduleTime->setDepartureTime($v);
			} else {
				$scheduleTime->setArrivalTime($v);
			}
		} elseif (preg_match("~^([0-9]+):([0-9]+)\*([0-9]+)$~", $v, $buff2)) {
			if ($type == self::TYPE_DEPARTURE) {
				$scheduleTime->setDepartureTime($buff2[1] . ":" . $buff2[2]);
				$scheduleTime->setDepartureDayModify($buff2[3]);
			} else {
				$scheduleTime->setArrivalTime($buff2[1] . ":" . $buff2[2]);
				$scheduleTime->setArrivalDayModify($buff2[3]);
			}
		} else {
			return null;
		}

		return $scheduleTime;
	}
}