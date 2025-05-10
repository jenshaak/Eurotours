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
use AppBundle\Entity\Tariff;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\FareService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\LineService;
use AppBundle\Service\ScheduleService;
use AppBundle\Service\StationService;
use AppBundle\Service\TariffService;
use AppBundle\VO\PriceCurrency;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTariffsCommand extends Command
{
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
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(LineService $lineService,
	                            CityService $cityService,
	                            StationService $stationService,
	                            CarrierService $carrierService,
	                            FareService $fareService,
	                            TariffService $tariffService,
	                            ScheduleService $scheduleService,
	                            LanguageService $languageService)
	{
		$this->lineService = $lineService;
		$this->cityService = $cityService;
		$this->stationService = $stationService;
		$this->carrierService = $carrierService;
		$this->fareService = $fareService;
		$this->tariffService = $tariffService;
		$this->scheduleService = $scheduleService;
		$this->languageService = $languageService;

		parent::__construct();
	}

	protected function configure()
	{
		$this->setName("eurotours:import:tariffs");
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

		$tables = \dibi::query("SHOW TABLES")->fetchPairs(null, "Tables_in_eurotours");

		$percentSales = \dibi::query("SELECT * FROM sales")->fetchAll();
		foreach ($percentSales as &$sale) {
			$sale->lines = explode("+", $sale->line);
		}

		foreach ($this->lineService->getAllLines() as $line) {
			$line->getSchedules()->map(function (Schedule $schedule) use ($tables, $line, $percentSales) {
				$priceTable = "p" . $schedule->getOldPriceTableNumber() . "_" . $schedule->getLine()->getCode();
				if (in_array($priceTable, $tables)) {
					$this->processTable($priceTable, $line, $schedule);
				}

				$sales = \dibi::query("SELECT * FROM sales2 WHERE line = '{$line->getCode()}'")->fetchAll();
				foreach ($sales as $sale) {
					$this->processTable($sale->c_table, $line, $schedule, $sale->name);
				}

				foreach ($percentSales as $percentSale) {
					/** @var mixed $percentSale */
					if (in_array($line->getCode(), $percentSale->lines)) {

						$salesCode = "sales-" . $percentSale->id;
						$tariff = $this->tariffService->getTariffByOldPriceTable($salesCode);
						if ($tariff === null) {
							$tariff = new Tariff;
							$tariff->setCarrier($line->getCarrier());
							$tariff->setLine($line);
							$tariff->setOldPriceTable($salesCode);
							$tariff->setType(Tariff::TYPE_PERCENT);
							$tariff->setPercentFromTariff($tariff->getLine()->getTariffs()->first());
							$tariff->setPercent($percentSale->value);
							$tariff->setName($this->languageService->getCzech(), $percentSale->name);
						}

						$this->tariffService->saveTariff($tariff);
						if (!$schedule->getTariffs()->contains($tariff)) {
							var_dump("Add tariff " . $tariff->getOldPriceTable());
							$schedule->getTariffs()->add($tariff);
						}

						$this->scheduleService->saveSchedule($schedule);
					}
				}
			});
		}


	}

	/**
	 * @param string $priceTable
	 * @param Line $line
	 * @param Schedule $schedule
	 * @param string $tariffName
	 */
	private function processTable($priceTable, Line $line, Schedule $schedule, $tariffName = "Standard")
	{
		foreach ($this->lineService->getAllLines() as $line) {
			if ($line->getTariffs()->isEmpty()) {
				$tariff = new Tariff;
				$tariff->setCarrier($line->getCarrier());
				$tariff->setLine($line);
				$tariff->setCurrency(PriceCurrency::CZK);
				$tariff->setName($this->languageService->getEnglish(), "Standard");
				$this->tariffService->saveTariff($tariff);
				$line->getTariffs()->add($tariff);
			}

			foreach ([ LineStation::DIRECTION_THERE, LineStation::DIRECTION_BACK ] as $direction) {
				$line->getLineStations($direction)->map(function (LineStation $fromLineStation) use ($line, $direction) {
					$line->getLineStations($direction)->map(function (LineStation $toLineStation) use ($line, $fromLineStation) {
						if ($fromLineStation->getWeight() >= $toLineStation->getWeight()) return;
						$line->getTariffs()->map(function (Tariff $tariff) use ($fromLineStation, $toLineStation) {
							$fare = $this->fareService->getFareByLineStations($fromLineStation, $toLineStation, $tariff);
							if ($fare == null) {
								$fare = new Fare;
								$fare->setFromLineStation($fromLineStation);
								$fare->setToLineStation($toLineStation);
								$fare->setTariff($tariff);

								if ($fromLineStation->getCity()->getCountry() == $toLineStation->getCity()->getCountry()) {
									$fare->setNotAvailable(true);
									$fare->setNotAvailableReturn(true);
								} else {
									$fare->setVariablePrice(true);
									$fare->setVariablePriceReturn(true);
								}

								$this->fareService->saveFare($fare);
							}
						});

					});
				});
			}
		}


		# TODO: oba bloky maji byt otoceny. je to ted takhle protoze
		# se po importu musi spustit jeste ten vrsek aby se donacetli neexistujici tariffs
		exit;


		$priceTableData = \dibi::query("SELECT * FROM `{$priceTable}`")->fetchAll();
		foreach ($priceTableData as $v) {
			foreach ([ "there", "back" ] as $direction) {
				$fromCity = $this->cityService->getCity((int) $v->x);
				/** @var LineStation $fromLineStation */
				$fromLineStation = $line->getLineStations()->filter(function (LineStation $lineStation) use ($fromCity, $direction) {
					return $lineStation->getCity() == $fromCity and $lineStation->getDirection() == $direction;
				})->first();

				if (!$fromLineStation) {
					#var_dump("FROM error " . $fromCity->getId() . " in " . $priceTable);
					continue;
				}

				foreach ($v as $xid => $price) {
					if ($xid == "x") continue;
					$toCity = $this->cityService->getCity(str_replace("c_", "", $xid));
					/** @var LineStation $toLineStation */
					$toLineStation = $line->getLineStations()->filter(function (LineStation $lineStation) use ($toCity, $direction) {
						return $lineStation->getCity() == $toCity and $lineStation->getDirection() == $direction;
					})->first();

					if (!$toLineStation) {
						#var_dump("TO error " . $toCity->getId() . " in " . $priceTable);
						continue;
					}

					if ($fromLineStation->getWeight() >= $toLineStation->getWeight()) continue;

					$tariff = $this->tariffService->getTariffByOldPriceTable($priceTable);
					if ($tariff === null) {
						$tariff = new Tariff;
						$tariff->setCarrier($line->getCarrier());
						$tariff->setLine($line);
						$tariff->setOldPriceTable($priceTable);
						$tariff->setCurrency(PriceCurrency::CZK);
						$tariff->setName($this->languageService->getEnglish(), $tariffName);
					}
					$this->tariffService->saveTariff($tariff);
					if (!$schedule->getTariffs()->contains($tariff)) {
						var_dump("Add tariff " . $tariff->getOldPriceTable());
						$schedule->getTariffs()->add($tariff);
					}
					$this->scheduleService->saveSchedule($schedule);

					$fare = $this->fareService->getFareByLineStations($fromLineStation, $toLineStation, $tariff);
					if ($fare == null) {
						$fare = new Fare;
						$fare->setFromLineStation($fromLineStation);
						$fare->setToLineStation($toLineStation);
						$fare->setTariff($tariff);

						$price = explode("+", $price);
						if ($price and $price[0] > 0) {
							$fare->setPrice($price[0]);
							if (isset($price[1])) {
								$fare->setPriceReturnAdd($price[1] - $price[0]);
							}
						} elseif ($price and $price[0] == "n") {
							$fare->setNotAvailable(true);
							$fare->setNotAvailableReturn(true);
						} else {
							$fare->setVariablePrice(true);
							$fare->setVariablePriceReturn(true);
						}

						$this->fareService->saveFare($fare);
					}
				}
			}

		}
	}
}
