<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 11:01
 */

namespace AppBundle\Connectors;

use AppBundle\Entity\BookStudentAgency;
use AppBundle\Entity\ExternalTicketStudentAgency;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\Route;
use AppBundle\Service\WebDriverService;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\DomCrawler\Crawler;

class StudentAgencyConnector
{
	const USERNAME = "eurotours15062016";
	const PASSWORD = "I8ry0OUqDb";
	//const URL = "https://dpl-dev-ybus-api.sa.cz/v2/r0/AffiliateBookingService?WSDL";
	const URL = "https://brn-ybus-api.sa.cz/v2/r0/AffiliateBookingService?WSDL=";
	const AUTHORIZATION = "Basic ZXVyb3RvdXJzMTUwNjIwMTY6SThyeTBPVXFEYg==";
	const XML_DECLARATION = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	const BOOK_PART_URL = "https://ybus.sa.cz/wicket/bookmarkable/";

	/**
	 * @var WebDriverService
	 */
	private $webDriverService;

	public function __construct(WebDriverService $webDriverService)
	{
		$this->webDriverService = $webDriverService;
	}

	/**
	 * @param Order $order
	 * @param Route $route
	 * @param string $locale
	 * @param string $stationFrom
	 * @param string $stationTo
	 * @param \DateTime $date
	 * @param string $currency
	 * @param string[] $tariffs
	 * @return BookStudentAgency
	 * @throws \Exception
	 */
	public function bookRoute(Order $order, Route $route, $locale, $stationFrom, $stationTo, \DateTime $date, $currency, $tariffs)
	{
		$response = $this->sendRequest($this->createRequestForFindSingleRoutesMultipleTariffs($locale, $stationFrom, $stationTo, $date, $tariffs, $currency));

		$crawler = new \Symfony\Component\DomCrawler\Crawler(self::XML_DECLARATION . $response);

		/** @var Crawler|null $routeCrawler */
		$routeCrawler = null;
		$crawler->filterXPath("//y:route")->each(function (Crawler $rt) use ($route, &$routeCrawler) {
			$routeIdent = [];
			$rt->filterXPath("//y:connection/y:id")->each(function (Crawler $idCrawler) use (&$routeIdent, $route) {
				$routeIdent[] = $idCrawler->text();
			});
			$routeIdent = implode("-", $routeIdent);

			if ($route->getExternalIdent() == $routeIdent) {
				$routeCrawler = $rt;
			}
		});

		if ($routeCrawler === null) {
			throw new \Exception("Nenasel jsem routu kterou jsem mel hledat");
		}

		$bookingCode = $routeCrawler->filterXPath("//y:bookingCode")->text();

		$response = $this->sendRequest($this->createRequestForBookSingleTicket($bookingCode, $order->getOrderPersons()));

		$book = new BookStudentAgency;
		preg_match("~<y:ticketIdentifier>(?P<ticketIdentifier>.+)</y:ticketIdentifier>~iU", $response, $buff);
		$book->setTicketIdentifier($buff['ticketIdentifier']);
		preg_match("~<y:accountCode>(?P<accountCode>.+)</y:accountCode>~iU", $response, $buff);
		$book->setAccountCode($buff['accountCode']);

		return $book;
	}

	/**
	 * @param string $bookingCode
	 * @param OrderPerson[] $orderPersons
	 * @return string
	 */
	private function createRequestForBookSingleTicket($bookingCode, $orderPersons)
	{
		$return = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ws.ybus.sa.cz"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> <soapenv:Header/>
<soapenv:Body>
	<urn:bookSingleTicket>
		<urn:request>
			<urn:bookingCode>' . $bookingCode . '</urn:bookingCode>
			<urn:passengers>';

		foreach ($orderPersons as $orderPerson) {
			list($firstName, $lastName) = explode(" ", $orderPerson->getName());
			$return .= '
				<urn:passenger>
					<urn:firstname>' . $firstName . '</urn:firstname>
					<urn:surname>' . $lastName . '</urn:surname>
					<urn:phoneNumber>' . $orderPerson->getPhone() . '</urn:phoneNumber>
					<urn:email>' . $orderPerson->getOrder()->getEmail() . '</urn:email>
					<urn:birthDate>2000-05-11T15:00:00</urn:birthDate>
				</urn:passenger>
			';
		}

		$return .=
		'</urn:passengers>
		</urn:request>
	</urn:bookSingleTicket>
</soapenv:Body>
</soapenv:Envelope>';

		return $return;
	}

	/**
	 * @param string $locale
	 * @param string $cityFrom
	 * @param string $cityTo
	 * @param \DateTime $date
	 * @param string $tariffName
	 * @param string $currency
	 * @return array|null
	 */
	public function findSingleRoutes($locale, $cityFrom, $cityTo, \DateTime $date, $tariffName, $currency)
	{
		$response = $this->sendRequest($this->createRequestForFindSingleRoutes($locale, $cityFrom, $cityTo, $date, $tariffName, $currency));

		echo $response . "\n\n\n\n\n\n\n";

		if (preg_match("~faultstring~", $response)) return null;

		$crawler = new \Symfony\Component\DomCrawler\Crawler(self::XML_DECLARATION . $response);

		$routes = [
			"url" => $crawler->filterXPath("//y:searchURL")->text(),
			"routes" => []
		];

		$crawler->filterXPath("//y:route")->each(function (\Symfony\Component\DomCrawler\Crawler $routeCrawler) use (&$routes, $date) {
			$route = [];
			$route['url'] = $routes['url'];
			$htmlRoute = $routeCrawler->html();

			preg_match("~<y:freeSeats>(?P<freeSeats>.+)</y:freeSeats>~", $htmlRoute, $buff);
			$route["freeSeats"] = $buff['freeSeats'];

			if ($route["freeSeats"] < 5) return;

			preg_match_all("~<y\:routePart (.*)>(?P<content>.+)</y\:routePart>~muU", $htmlRoute, $buff, PREG_SET_ORDER);
			$fromCrawlerHtmlBuff = $buff[0]['content'];
			$toCrawlerHtmlBuff = $buff[count($buff)-1]['content'];

			preg_match_all("~<y\:fromStation>(?P<content>.+)</y\:fromStation>~muU", $fromCrawlerHtmlBuff, $buff, PREG_SET_ORDER);
			$fromCrawlerHtml = $buff[count($buff)-1]['content'];

			preg_match_all("~<y\:toStation>(?P<content>.+)</y\:toStation>~muU", $toCrawlerHtmlBuff, $buff, PREG_SET_ORDER);
			$toCrawlerHtml = $buff[count($buff)-1]['content'];

			preg_match("~<y:departure>(?P<date>.+)</y:departure>~", $fromCrawlerHtml, $buff);
			$route["departureTime"] = new \DateTime($buff['date']);
			if ($route['departureTime']->format("Y-m-d") != $date->format("Y-m-d")) return;

			preg_match("~<y:arrival>(?P<date>.+)</y:arrival>~", $toCrawlerHtml, $buff);
			$route["arrivalTime"] = new \DateTime($buff['date']);

			preg_match("~<y:stationId>(?P<stationId>.+)</y:stationId>~", $fromCrawlerHtml, $buff);
			$route["departureStationId"] = $buff['stationId'];

			preg_match("~<y:stationId>(?P<stationId>.+)</y:stationId>~", $toCrawlerHtml, $buff);
			$route["arrivalStationId"] = $buff['stationId'];

			preg_match("~<y:bookingCode>(?P<bookingCode>.+)</y:bookingCode>~", $htmlRoute, $buff);
			$route["bookingCode"] = $buff['bookingCode'];

			$route['price'] = 9999999;
			$routeCrawler->filterXPath("//y:ticketPrice/y:totalPrice/y:amount")->each(function (\Symfony\Component\DomCrawler\Crawler $amountCrawler) use (&$route) {
				$route['price'] = (int)$amountCrawler->text() < $route['price'] ? (int)$amountCrawler->text() : $route['price'];
			});

			preg_match("~<y\:currency>(?P<content>.+)</y\:currency>~muU", $htmlRoute, $buff);
			$route['currency'] = $buff[1];

			$uniqueIdent = [];
			preg_match_all("~<y\:id>(?P<connectionId>.+)</y\:id>~muU", $htmlRoute, $buff, PREG_SET_ORDER);
			foreach ($buff as $b) $uniqueIdent[] = $b['connectionId'];
			$route['uniqueIdent'] = implode("-", $uniqueIdent);

			$routes['routes'][] = $route;
		});

		return $routes;
	}

	/**
	 * @param string $locale
	 * @return array
	 */
	public function getCities($locale)
	{
		$response = $this->sendRequest($this->createRequestForListCities($locale));

		$cities = [];
		if (preg_match_all("~<y:city>(.*)</y:city>~U", $response, $buff, PREG_SET_ORDER)) {
			foreach ($buff as $cityP) {
				$city = [];
				if (preg_match_all("~<y:(?P<key>[^>]+)>(?P<value>.*)</~U", $cityP[1], $buffCity, PREG_SET_ORDER)) {
					foreach ($buffCity as $valueP) {
						$city[$valueP['key']] = $valueP['value'];
					}
				}
				$cities[] = $city;
			}
		}

		return $cities;
	}

	/**
	 * @param string $locale
	 * @return array
	 */
	public function getStations($locale)
	{
		$response = $this->sendRequest($this->createRequestForListStations($locale));

		$stations = [];

		$crawler = new \Symfony\Component\DomCrawler\Crawler(self::XML_DECLARATION . $response);
		$crawler->filterXPath("//y:station")->each(function (\Symfony\Component\DomCrawler\Crawler $crawler) use (&$stations) {
			$station = [];
			$station['id'] = $crawler->filterXPath("//y:id")->text();
			$station['name'] = $this->removeEmoji($crawler->filterXPath("//y:name")->text());
			$station['cityId'] = $crawler->filterXPath("//y:cityId")->text();
			$stations[] = $station;
		});

		return $stations;
	}

	private function removeEmoji($text){
		return preg_replace('/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F6FF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
	}

	/**
	 * @param string $locale
	 * @return array
	 */
	public function getSymbols($locale)
	{
		$response = $this->sendRequest($this->createRequestForListSymbols($locale));

		$stations = [];

		$crawler = new \Symfony\Component\DomCrawler\Crawler(self::XML_DECLARATION . $response);
		$crawler->filterXPath("//y:station")->each(function (\Symfony\Component\DomCrawler\Crawler $crawler) use (&$stations) {
			$station = [];
			$station['id'] = $crawler->filterXPath("//y:id")->text();
			$station['name'] = $crawler->filterXPath("//y:name")->text();
			$station['cityId'] = $crawler->filterXPath("//y:cityId")->text();
			$stations[] = $station;
		});

		return $stations;
	}

	/**
	 * @param string $request
	 * @return string
	 */
	private function sendRequest($request)
	{
		$request = self::XML_DECLARATION . $request;

		$headers = [
			"Content-type: text/xml;charset=\"utf-8\"",
			"Accept: text/xml",
			"Cache-Control: no-cache",
			"Pragma: no-cache",
			"SOAPAction: \"\"",
			"Authorization: " . self::AUTHORIZATION,
			"Content-length: " . strlen($request)
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, self::URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, self::USERNAME . ":" . self::PASSWORD);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		curl_close($ch);

		return $response;
	}

	/**
	 * @param string $locale
	 * @param string $cityFrom
	 * @param string $cityTo
	 * @param \DateTime $date
	 * @param string[] $tariffs
	 * @param string $currency
	 * @return string
	 */
	private function createRequestForFindSingleRoutesMultipleTariffs($locale, $cityFrom, $cityTo, \DateTime $date, $tariffs, $currency)
	{
		$tariffsXml = [];
		foreach ($tariffs as $tariff) {
			$tariffsXml[] = "<urn:tarif>" . $tariff . "</urn:tarif>";
		}

		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ws.ybus.sa.cz"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> <soapenv:Header/>
<soapenv:Body>
	<urn:findSingleRoutes>
		<urn:request>
			<locale>' . $locale . '</locale>
			<urn:params>
				<urn:fromLocation xsi:type="urn:cityId">
					<urn:id>' . $cityFrom . '</urn:id>
				</urn:fromLocation>
				<urn:toLocation xsi:type="urn:cityId">
					<urn:id>' . $cityTo . '</urn:id>
				</urn:toLocation>
				<urn:tarifs>
					' . implode("", $tariffsXml) . '
				</urn:tarifs>
				<urn:currency>' . $currency . '</urn:currency>
			</urn:params>
			<urn:departure>' . $date->format("Y-m-d") . 'T' . $date->format("H:00:00") . '</urn:departure>
		</urn:request>
	</urn:findSingleRoutes>
</soapenv:Body>
</soapenv:Envelope>';
	}

	/**
	 * @param string $locale
	 * @param string $cityFrom
	 * @param string $cityTo
	 * @param \DateTime $date
	 * @param string $tariff
	 * @param string $currency
	 * @return string
	 */
	private function createRequestForFindSingleRoutes($locale, $cityFrom, $cityTo, \DateTime $date, $tariff, $currency)
	{
		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ws.ybus.sa.cz"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"> <soapenv:Header/>
<soapenv:Body>
	<urn:findSingleRoutes>
		<urn:request>
			<locale>' . $locale . '</locale>
			<urn:params>
				<urn:fromLocation xsi:type="urn:cityId">
					<urn:id>' . $cityFrom . '</urn:id>
				</urn:fromLocation>
				<urn:toLocation xsi:type="urn:cityId">
					<urn:id>' . $cityTo . '</urn:id>
				</urn:toLocation>
				<urn:tarifs>
					<urn:tarif>' . $tariff . '</urn:tarif>
				</urn:tarifs>
				<urn:currency>' . $currency . '</urn:currency>
			</urn:params>
			<urn:departure>' . $date->format("Y-m-d") . 'T' . $date->format("H:00:00") . '</urn:departure>
		</urn:request>
	</urn:findSingleRoutes>
</soapenv:Body>
</soapenv:Envelope>';
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	private function createRequestForListCities($locale)
	{
		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ws.ybus.sa.cz">
   <soapenv:Header/>
   <soapenv:Body>
      <urn:listCities>
         <urn:request>
            <locale>' . $locale . '</locale>
         </urn:request>
      </urn:listCities>
   </soapenv:Body>
</soapenv:Envelope>';
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	private function createRequestForListStations($locale)
	{
		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ws.ybus.sa.cz">
   <soapenv:Header/>
   <soapenv:Body>
      <urn:listStations>
         <urn:request>
            <locale>' . $locale . '</locale>
         </urn:request>
      </urn:listStations>
   </soapenv:Body>
</soapenv:Envelope>';
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	private function createRequestForListSymbols($locale)
	{
		return '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:urn="urn:ws.ybus.sa.cz">
   <soapenv:Header/>
   <soapenv:Body>
      <urn:listSymbols>
         <urn:request>
            <locale>' . $locale . '</locale>
         </urn:request>
      </urn:listSymbols>
   </soapenv:Body>
</soapenv:Envelope>';
	}

	/**
	 * @param ExternalTicketStudentAgency $externalTicketStudentAgency
	 * @param $bookedTicket
	 */
	public function buyRoute(ExternalTicketStudentAgency $externalTicketStudentAgency, $bookedTicket)
	{
		$driver = $this->webDriverService->createWebDriver();

		$driver->get("https://ybus.sa.cz/wicket/bookmarkable/cz.sa.ybus.server.web.admin.base.LoginPage");

		sleep(5);

		$driver->findElement(WebDriverBy::name("login"))->clear()->sendKeys("eurotours1");
		$driver->findElement(WebDriverBy::name("password"))->clear()->sendKeys("krasi2017");
		$driver->findElement(WebDriverBy::tagName("form"))->submit();

		sleep(5);

		$driver->findElement(WebDriverBy::name("search"))->clear()->sendKeys($bookedTicket);
		$driver->findElement(WebDriverBy::xpath("//div[@class='header-form']/form"))->submit();

		sleep(5);

		$driver->findElement(WebDriverBy::xpath("//a/span[contains(text(), 'Zaplatit')]"))->click();

		sleep(5);

		$driver->findElement(WebDriverBy::id("paymentTypeCash"))->click();

		sleep(5);

		$driver->findElement(WebDriverBy::name("payButton"))->click();

		sleep(10);

		$crawler = new Crawler($driver->getPageSource());

		$externalTicketStudentAgency->setTicketIdent($crawler->filterXPath("//span[@class='accountCode']")->text());
		$externalTicketStudentAgency->setImageBody($driver->takeScreenshot());

		$driver->close();
	}
}
