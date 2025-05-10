<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 11:09
 */

namespace AppBundle\Command;


use AppBundle\Connectors\EastExpressConnector;
use AppBundle\Connectors\EurolinesConnector;
use AppBundle\Connectors\EurolinesConnectorOLD;
use AppBundle\Connectors\FlixbusConnector;
use AppBundle\Connectors\InfobusConnector;
use AppBundle\Connectors\NikoloConnector;
use AppBundle\Connectors\RegabusConnector;
use AppBundle\Connectors\StudentAgencyConnector;
use AppBundle\Connectors\TransTempoConnector;
use AppBundle\Entity\Country;
use AppBundle\Entity\ExternalTariffEurolines;
use AppBundle\Entity\ExternalTicketEurolines;
use AppBundle\Entity\ExternalTicketStudentAgency;
use AppBundle\Entity\Fare;
use AppBundle\Entity\Tariff;
use AppBundle\Routers\EastExpressRouter;
use AppBundle\Routers\EurolinesRouter;
use AppBundle\Routers\FlixbusRouter;
use AppBundle\Routers\InfobusRouter;
use AppBundle\Routers\NikoloRouter;
use AppBundle\Routers\StudentAgencyRouter;
use AppBundle\Routers\TransTempoRouter;
use AppBundle\Service\CountryService;
use AppBundle\Service\EmailService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\FareService;
use AppBundle\Service\GotenbergService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\LineService;
use AppBundle\Service\OrderService;
use AppBundle\Service\WebDriverService;
use AppBundle\VO\LanguageString;
use Doctrine\Common\Collections\ArrayCollection;
use Facebook\WebDriver\WebDriverBy;
use Goutte\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TestStudentAgencyCommand extends Command
{
	/**
	 * @var StudentAgencyRouter
	 */
	private $studentAgencyRouter;
	/**
	 * @var InfobusConnector
	 */
	private $infobusConnector;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var InfobusRouter
	 */
	private $infobusRouter;
	/**
	 * @var EastExpressConnector
	 */
	private $eastExpressConnector;
	/**
	 * @var EastExpressRouter
	 */
	private $eastExpressRouter;
	/**
	 * @var StudentAgencyConnector
	 */
	private $studentAgencyConnector;
	/**
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var FareService
	 */
	private $fareService;
	/**
	 * @var WebDriverService
	 */
	private $webDriverService;
	/**
	 * @var FlixbusConnector
	 */
	private $flixbusConnector;
	/**
	 * @var FlixbusRouter
	 */
	private $flixbusRouter;
	/**
	 * @var OrderService
	 */
	private $orderService;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;
	/**
	 * @var EurolinesConnector
	 */
	private $eurolinesConnector;
	/**
	 * @var EurolinesRouter
	 */
	private $eurolinesRouter;
	/**
	 * @var CountryService
	 */
	private $countryService;
	/**
	 * @var NikoloRouter
	 */
	private $nikoloRouter;
	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;
	/**
	 * @var RegabusConnector
	 */
	private $regabusConnector;
	/**
	 * @var TransTempoRouter
	 */
	private $transTempoRouter;
	/**
	 * @var TransTempoConnector
	 */
	private $transTempoConnector;
	/**
	 * @var GotenbergService
	 */
	private $gotenbergService;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var NikoloConnector
	 */
	private $nikoloConnector;

	public function __construct(StudentAgencyRouter $studentAgencyRouter,
	                            InfobusConnector $infobusConnector,
	                            LanguageService $languageService,
	                            InfobusRouter $infobusRouter,
	                            EastExpressConnector $eastExpressConnector,
	                            EastExpressRouter $eastExpressRouter,
	                            StudentAgencyConnector $studentAgencyConnector,
	                            LineService $lineService,
	                            FareService $fareService,
	                            WebDriverService $webDriverService,
	                            FlixbusConnector $flixbusConnector,
	                            FlixbusRouter $flixbusRouter,
	                            OrderService $orderService,
	                            ExternalTariffService $externalTariffService,
	                            EurolinesConnector $eurolinesConnector,
	                            EurolinesRouter $eurolinesRouter,
	                            CountryService $countryService,
	                            NikoloRouter $nikoloRouter,
	                            ExternalCityService $externalCityService,
	                            RegabusConnector $regabusConnector,
	                            TransTempoRouter $transTempoRouter,
	                            TransTempoConnector $transTempoConnector,
	                            GotenbergService $gotenbergService,
	                            EmailService $emailService,
								NikoloConnector $nikoloConnector)
	{
		$this->studentAgencyRouter = $studentAgencyRouter;
		$this->infobusConnector = $infobusConnector;
		$this->languageService = $languageService;
		$this->infobusRouter = $infobusRouter;
		$this->eastExpressConnector = $eastExpressConnector;
		$this->eastExpressRouter = $eastExpressRouter;
		$this->studentAgencyConnector = $studentAgencyConnector;
		$this->lineService = $lineService;
		$this->fareService = $fareService;
		$this->webDriverService = $webDriverService;
		$this->flixbusConnector = $flixbusConnector;
		$this->flixbusRouter = $flixbusRouter;
		$this->orderService = $orderService;
		$this->externalTariffService = $externalTariffService;
		$this->eurolinesConnector = $eurolinesConnector;
		$this->eurolinesRouter = $eurolinesRouter;

		parent::__construct();
		$this->countryService = $countryService;
		$this->nikoloRouter = $nikoloRouter;
		$this->externalCityService = $externalCityService;
		$this->regabusConnector = $regabusConnector;
		$this->transTempoRouter = $transTempoRouter;
		$this->transTempoConnector = $transTempoConnector;
		$this->gotenbergService = $gotenbergService;
		$this->emailService = $emailService;
		$this->nikoloConnector = $nikoloConnector;
	}

	protected function configure()
	{
		$this->setName("eurotours:test:studentagency");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$routes = $this->nikoloConnector->findRoute(3, 6, new \DateTime("+13 days"));
		var_dump($routes);
		exit;

		#$html = $this->transTempoConnector->getTicketHtml("104395");
		#file_put_contents("test.pdf", $this->gotenbergService->generatePdfFromHtml($html));

		#exit;
		/*

		#$this->flixbusRouter->syncExternals();
		exit;

		foreach ($this->transTempoConnector->getBuses(17, 13, new \DateTime("2019-12-12")) as $i) {
			print_r($i->ticket_types);
		}

		//$this->transTempoRouter->syncExternals();


		//$this->regabusConnector->buyRoute();

		//var_dump($this->regabusConnector->getStations());

		#var_dump($this->regabusConnector->findRoute(117, 1, new \DateTime("2019-11-02")));

		exit;
		/*{
		$country = new Country;
		$country->getName()->setString($this->languageService->getCzech(), "Bosna a Hercegovina");
		$this->countryService->saveCountry($country);
		exit;

		foreach ($this->externalCityService->findAllExternalCities() as $city) {
			$class = explode("\\", get_class($city));
			echo "\"" . str_replace("ExternalCity", "", array_pop($class)) . "\"";
			echo ",";
			echo "\"{$city->getName()->getString($this->languageService->getCzech())}\"";
			echo ",";
			echo "\"" . ($city->getCity() ? $city->getCity()->getName()->getString($this->languageService->getCzech()) : "") . "\"";
			echo "\n";
		}

		exit;


		print_r($this->nikoloRouter->getAllStations());

		#$country = new Country;
		#$country->getName()->setString($this->languageService->getCzech(), "Portugalsko");
		#$this->countryService->saveCountry($country);

		exit;

		$driver = $this->webDriverService->createWebDriver();
		$driver->get("http://www.seznam.cz");
		var_dump($driver->getTitle());
		$driver->close();


		#$route = $this->eurolinesConnector->findRoute("xvhu~381", "abwe~125", new \DateTime("2018-02-01"));
		#var_dump($route);

		#$this->infobusRouter->syncExternals();

		exit;

		/** @var ExternalTariffEurolines $externalTariff */
		$externalTariff = $this->externalTariffService->getExternalTariffById(34);
		$this->eurolinesConnector->buyRoute("Prague", "Amsterdam", "Test1", "Test2", new \DateTime("2018-02-01"), false, new ExternalTicketEurolines(), $externalTariff);

		exit;

		//$this->studentAgencyConnector->buyRoute(new ExternalTicketStudentAgency(), "1769524229");


		//$order = $this->orderService->getOrder("546ec512-f216-11e7-a640-705a7438bc08");
		//$order = $this->orderService->getOrder("f3e5c2a2-f21c-11e7-a640-705a7438bc08");

		/*$this->studentAgencyRouter->bookRoute(
			$order, $order->getRouteThere(), $order->getRouteThere()->getOrderPersonsRouteTariffs($order)
		);*/


		exit;

		$this->studentAgencyConnector->buyRoute(
			10204003,
			10204076,
			new \DateTime("26.2.2018 10:30:00"),
			"test1",
			"test2",
			"adam@motvicka.cz",
			"+420602316157",
			"YOUTH",
			"CZK"
		);

		exit;

		var_dump($this->eurolinesConnector->findRoute("Praha", "Brno", new \DateTime("2018-01-17"), true)->toArray());

		exit;

		//var_dump($this->flixbusConnector->findRoute(1374, 1334, (new \DateTime("31.10.2017"))));

		$this->flixbusRouter->syncExternals();

		exit;

		$driver = $this->webDriverService->createWebDriver();
		$driver->get("http://www.east-express.cz/sale/login.php");
		$driver->findElement(WebDriverBy::name("jmeno"))->sendKeys(EastExpressConnector::USERNAME);
		$driver->findElement(WebDriverBy::name("heslo"))->sendKeys(EastExpressConnector::PASSWORD);
		$driver->findElement(WebDriverBy::name("login"))->submit();

		exit;

		foreach ($this->lineService->getAllLines() as $line) {
			$line->getTariffs()->map(function (Tariff $tariff) {
				$tariff->getFares()->map(function (Fare $fare) {
					$in = $fare->getFromLineStation()->getCity()->getCountry() == $fare->getToLineStation()->getCity()->getCountry();
					if ($in) {
						$fare->setNotAvailable(true);
						$fare->setNotAvailableReturn(true);
						$fare->setVariablePrice(false);
						$fare->setVariablePriceReturn(false);
						$fare->setPrice(null);
						$fare->setPriceReturnAdd(null);
						$this->fareService->saveFare($fare);
					}
				});
			});
		}

		exit;



		#$this->studentAgencyConnector->test();

		#$this->eastExpressRouter->syncExternals();


		//print_r($this->eastExpressConnector->getLines());


		#var_dump($this->infobusConnector->findRoute(7, 6, new \DateTime("2017-07-01")));

		#$this->infobusRouter->syncExternals();

		exit;

		$p = new ArrayCollection;
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));
		$p->add($process = new Process('php bin/routers.php'));

		$p->map(function (Process $process) {
			$process->setTimeout(1)->setIdleTimeout(1);
			$process->start();
			$process->wait();
		});

		$p->map(function (Process $process) {
			var_dump($process->getOutput());

		});

	}
}
