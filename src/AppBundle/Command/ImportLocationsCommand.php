<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 17:33
 */

namespace AppBundle\Command;


use AppBundle\Entity\City;
use AppBundle\Entity\Country;
use AppBundle\Entity\Station;
use AppBundle\Service\CityService;
use AppBundle\Service\CountryService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\StationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportLocationsCommand extends Command
{
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var CountryService
	 */
	private $countryService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var StationService
	 */
	private $stationService;

	public function __construct(CityService $cityService,
	                            CountryService $countryService,
	                            LanguageService $languageService,
	                            StationService $stationService)
	{
		parent::__construct();
		$this->cityService = $cityService;
		$this->countryService = $countryService;
		$this->languageService = $languageService;
		$this->stationService = $stationService;
	}

	protected function configure()
	{
		$this->setName("eurotours:import:locations");
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

		$countryResult = \dibi::query("SELECT id, cs.name cs, en.name en FROM countries_engl en JOIN countries cs USING (id)")->fetchAll();

		foreach ($countryResult as $r) {
			if ($this->countryService->getCountry((int) $r->id) === null) {
				$country = new Country;
				$country->setId((int) $r->id);
				$country->getName()->setString($this->languageService->getEnglish(), $r->en);
				$country->getName()->setString($this->languageService->getCzech(), $r->cs);
				$this->countryService->saveCountry($country);
			}
		}

		$cityResult = \dibi::query("SELECT id, cs.name cs, en.name en, cs.sub_id country FROM cities en JOIN cities_cz cs USING (id)")->fetchAll();

		foreach ($cityResult as $r) {
			if ($this->cityService->getCity((int) $r->id) === null) {
				$city = new City;
				$city->setId((int) $r->id);
				$city->setCountry($this->countryService->getCountry((int) $r->country));
				$city->getName()->setString($this->languageService->getEnglish(), $r->en);
				$city->getName()->setString($this->languageService->getCzech(), $r->cs);
				$this->cityService->saveCity($city);
			}
		}

		$tables = \dibi::query("SHOW TABLES")->fetchPairs(null, "Tables_in_eurotours");

		$stationResult = \dibi::query("SELECT id, cs.name cs, en.name en FROM stations_engl en JOIN stations cs USING (id)")->fetchAll();
		$stationsBuff = [];
		foreach ($stationResult as $r) {
			$cityId = null;
			foreach ($tables as $table) {
				if (preg_match("~^t([0-9]+)_~", $table)) {
					$buff = \dibi::query("SELECT city FROM `{$table}` WHERE station = {$r->id}")->fetchPairs(null, "city");
					foreach ($buff as $cityId) {
						if (!isset($stationsBuff[$r->id . "-" . $cityId])) {
							$station = new Station;
							$station->setCity($this->cityService->getCity((int) $cityId));
							$station->getName()->setString($this->languageService->getEnglish(), $r->en);
							$station->getName()->setString($this->languageService->getCzech(), $r->cs);
							$station->setOldStationId((int) $r->id);
							$this->stationService->saveStation($station);
							$stationsBuff[$r->id . "-" . $cityId] = $station;
						}
					}
				}
			}
		}

	}
}