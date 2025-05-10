<?php

namespace AppBundle\Command;

use AppBundle\Connectors\RegabusConnector;
use AppBundle\Service\CityService;
use AppBundle\Service\LanguageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RegabusCityNameCheckCommand extends Command
{
	private CityService $cityService;
	private RegabusConnector $regabusConnector;
	private LanguageService $languageService;

	/**
	 * @param CityService $cityService
	 * @param RegabusConnector $regabusConnector
	 * @param LanguageService $languageService
	 */
	public function __construct(CityService $cityService, RegabusConnector $regabusConnector, LanguageService $languageService)
	{
		$this->cityService = $cityService;
		$this->regabusConnector = $regabusConnector;
		$this->languageService = $languageService;

		parent::__construct();
	}


	protected function configure()
	{
		$this->setName("eurotours:regabus:cityCheck");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$io = new SymfonyStyle($input, $output);
		$io->title('Unknown cities from new RegaBus API');
		$regabusCities = $this->regabusConnector->getCities();
		$allCities = $this->cityService->findAllCities();
		$language = $this->languageService->getEnglish();

		$notFound = [];

		foreach ($regabusCities as $regabusCity) {
			foreach ($allCities as $city) {
				if ($city->getName()->getString($language) == $regabusCity->nameEn) {
					continue;
				}

				$variations = $city->getNextVariations();
				if (strpos($variations, ',') !== false) {
					$aVariations = explode(',', $variations);
					if (false === in_array($regabusCity->nameEn, $aVariations)) {
						if (false === in_array($regabusCity, $notFound)) {
							$notFound[] = $regabusCity;
						}
					}
				} else {
					if (false === in_array($regabusCity, $notFound)) {
						$notFound[] = $regabusCity;
					}
				}
			}
		}

		if ($notFound) {
			foreach ($notFound as $row) {
				$io->writeln($row->id . "\t" . $row->nameEn);
			}

			$io->error('There are ' . count($notFound) . ' cities that are not in the all cities list');
		}

		return 0;
	}
}
