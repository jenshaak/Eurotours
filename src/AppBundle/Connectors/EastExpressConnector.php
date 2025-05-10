<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 07.09.17
 * Time: 13:55
 */

namespace AppBundle\Connectors;


use AppBundle\Service\WebDriverService;
use Facebook\WebDriver\WebDriverBy;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class EastExpressConnector
{
	const USERNAME = "nayden";
	const PASSWORD = "florenc19";

	/** @var Client */
	private $client;
	/**
	 * @var WebDriverService
	 */
	private $webDriverService;

	public function __construct(WebDriverService $webDriverService)
	{
		$this->webDriverService = $webDriverService;
	}

	/**
	 * @return Client
	 */
	private function getClient()
	{
		if ($this->client === null) {
			$this->client = new Client;
		}

		return $this->client;
	}

	/**
	 * @return array
	 */
	public function getCities()
	{
		$return = [];
		$client = $this->getClient();
		$crawler = $client->request("GET", "http://east-express.cz/rezervace");

		$crawler->filterXPath("//select[@name='pointFrom']/option")->each(function (Crawler $option) use (&$return) {
			$return[$option->attr("value")] = $option->text();
		});

		return $return;
	}

	/**
	 * @return array
	 */
	public function getTariffs()
	{
		$return = [];
		$client = $this->getClient();
		$crawler = $client->request("GET", "http://east-express.cz/rezervace");

		$crawler->filterXPath("//select[@name='discount']/option")->each(function (Crawler $option) use (&$return) {
			$return[$option->attr("value")] = $option->text();
		});

		return $return;
	}

	/**
	 * @param int $fromCity
	 * @param int $toCity
	 * @param \DateTime $dateDay
	 * @return array
	 */
	public function findRoute($fromCity, $toCity, \DateTime $dateDay)
	{
		$params = [
			"pointFrom" => $fromCity,
			"pointTo" => $toCity,
			"date" => $dateDay->format("d.m.Y"),
			"dateInterval" => 1,
			"discount" => 0
		];

		$content = shell_exec("curl 'https://www.east-express.cz/checkRoutes' -s -X POST -d '" . http_build_query($params) . "'");

		$return = \GuzzleHttp\json_decode($content);

		if (isset($return->status) and $return->status == 500) return [];

		return $return;
	}

	/**
	 * @return Client
	 */
	private function doLogin()
	{
		$crawler = $this->getClient()->request("GET", "http://www.east-express.cz/sale/login.php");
		if ($crawler->filterXPath("//form[@name='login']")->count() > 0) {
			$this->getClient()->submit(
				$crawler->filterXPath("//form[@name='login']")->form([
					"jmeno" => self::USERNAME,
					"heslo" => self::PASSWORD,
				])
			);
		}

		return $this->getClient();
	}

	public function buyRoute($lineNumber,
	                          \DateTime $datetimeDeparture,
	                          $fullName,
	                          $phone,
	                          $from,
	                          $to,
	                          $tariff)
	{
		$lineParam = base64_encode(base64_encode(base64_encode($lineNumber)));
		$timeParamDateTime = clone $datetimeDeparture;
		$timeParamDateTime->setTime(0, 0, 0);
		$timeParam = base64_encode(base64_encode(base64_encode($timeParamDateTime->getTimestamp())));
		unset($timeParamDateTime);

		$driver = $this->webDriverService->createWebDriver();
		$driver->get("http://www.east-express.cz/sale/login.php");
		$driver->findElement(WebDriverBy::name("jmeno"))->sendKeys(self::USERNAME);
		$driver->findElement(WebDriverBy::name("heslo"))->sendKeys(self::PASSWORD);
		$driver->findElement(WebDriverBy::name("login"))->submit();
		$driver->wait(10, 2000);

		$driver->get("http://www.east-express.cz/sale/index.php?page=mistenky&linka=" . $lineParam . "&time=" . $timeParam);

		sleep(5);

		$seatString = "01";
		foreach (range(0, 60) as $seat) {
			$seatString = str_pad($seat, 2, "0", STR_PAD_LEFT);
			if (count($driver->findElements(WebDriverBy::xpath("//input[@name='sedadlo' and @value='{$seatString}']"))) > 0) {
				break;
			}
		}

		$driver->findElement(WebDriverBy::xpath("//input[@name='sedadlo' and @value='{$seatString}']"))->click();
		sleep(5);

		$driver->findElement(WebDriverBy::name("jmeno"))->sendKeys($fullName);
		sleep(5);

		$driver->findElement(WebDriverBy::name("tel"))->sendKeys($phone);
		sleep(5);

		$driver->findElement(WebDriverBy::xpath("//select[@name='od']/option[@value='{$from}']"))->click();
		sleep(5);
		$driver->findElement(WebDriverBy::xpath("//select[@name='kam']/option[@value='{$to}']"))->click();
		sleep(5);

		$driver->findElement(WebDriverBy::xpath("//select[@name='sleva']/option[@value='{$tariff}']"))->click();

		sleep(5);

		$driver->findElement(WebDriverBy::name("ulozit"))->click();

		# V tenhle moment se otevira Print okno. Na tom se proces zasekne.
		# Musime doufat, ze se pak jednou za cas restartuje stroj a vyhnila okna se zavrou.

		$driver = $this->webDriverService->createWebDriver();
		$driver->get("http://www.east-express.cz/sale/login.php");
		$driver->findElement(WebDriverBy::name("jmeno"))->sendKeys(self::USERNAME);
		$driver->findElement(WebDriverBy::name("heslo"))->sendKeys(self::PASSWORD);
		$driver->findElement(WebDriverBy::name("login"))->submit();
		$driver->wait(10, 2000);

		$driver->get("http://www.east-express.cz/sale/index.php?page=mistenky&linka=" . $lineParam . "&time=" . $timeParam);

		sleep(5);

		# ziskat samotnou jizdenku
		$ticketLink = $driver->findElement(WebDriverBy::xpath(
			"//tr[td[position() = 1 and contains(text(), '{$seatString}')]]/td/a[contains(text(), 'Tisk')]"
		))->getAttribute("href");

		$crawler = $this->doLogin()->request("GET", $ticketLink);
		$crawler->filterXPath("//script")->clear();
		$style = "<style>" . $crawler->filterXPath("//style")->html() . "</style>";
		$html = str_replace("src=\"", "src=\"http://www.east-express.cz/sale/", $crawler->filterXPath("//body")->html());

		$driver->close();

		return $style . $html;
	}

}
