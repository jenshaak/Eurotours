<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 22.01.18
 * Time: 12:08
 */

namespace AppBundle\Connectors;


use Curl\Curl;
use Doctrine\Common\Collections\ArrayCollection;
use Goutte\Client;
use GuzzleHttp\HandlerStack;
use Symfony\Component\Process\Process;

/**
 * @deprecated
 */
class EurolinesConnector
{
	/** @var Client */
	private $client;

	/** @var string */
	private $apiCookie;

	private function getClient()
	{
		if ($this->client === null) {
			$this->client = new Client();
			$this->client->request("GET", "https://touringbohemia.amtis.eu/Account/Login");
			$crawler = $this->client->getCrawler();
			$form = $crawler->filterXPath("//form")->form([ "UserName" => "ag152", "Password" => "Jana2017" ]);
			$this->client->submit($form);
			$this->apiCookie = $this->client->getCookieJar()->get("Amtis.B2B");
		}

		return $this->client;
	}

	/**
	 * @return \stdClass[]
	 */
	public function getCities()
	{
		$client = $this->getClient();
		$client->request("GET", "https://touringbohemia.amtis.eu/CityStopSearch/FromCityStops");
		$stations = json_decode($client->getInternalResponse()->getContent());

		$return = [];
		foreach (array_filter($stations->d->results, function ($station) { return $station->busStopName === null; }) as $s) {
			preg_match("/~([0-9\-]+)/", $s->id, $buff);
			if (!isset($return[$buff[1]])) {
				$return[$buff[1]] = (object) [ "names" => [], "id" => $s->id ];
			}

			$return[$buff[1]]->names[] = $s->cityName;
		}

		return $return;
	}

	/**
	 * @param string $url
	 * @param array $body
	 * @return mixed
	 */
	private function apiRequest($url, $body)
	{
		$this->getClient();
		$process = new Process(implode(" ", [
			"curl 'https://touringbohemia.amtis.eu{$url}'",
			"-H 'Cookie: {$this->apiCookie}'",
			"-H 'Content-Type: application/json; charset=UTF-8'",
			"--data-binary '" . json_encode($body) . "'"
		]));
		$process->run();

		return json_decode($process->getOutput());
	}

	/**
	 * @param string $fromCity
	 * @param string $toCity
	 * @param \DateTime $dateDay
	 * @param bool $promoTariff
	 * @return \stdClass[]
	 */
	public function findRoute($fromCity, $toCity, \DateTime $dateDay)
	{
		$requestBody = [
			'openTickets' => false,
			'directOnly' => false,
			'basicPriceTypes' =>
				[
					0 => 1,
				],
			'fromId' => $fromCity,
			'toId' => $toCity,
			'currencyId' => '43eb12bc-0b85-47f9-a34a-6730365ee9b6',
			'searchType' => 1,
			'etFromId1' => $fromCity,
			'etToId1' => $toCity,
			'etFromId2' => NULL,
			'etToId2' => NULL,
			'dep' => $dateDay->format("Y-m-d\T00:00:00.000\Z"),
			'ret' => NULL,
			'searchOrigin' => 0,
			'startTime' => $dateDay->format("Y-m-d\T00:00:00.000\Z"),
			'endTime' => $dateDay->format("Y-m-d\T23:59:59.000\Z"),
		];

		$array = $this->apiRequest("/Connection/Find", $requestBody);

		//var_dump($array);

		foreach ($array->connections as $connection) {
			if ($connection->availableSeats < 5) continue;
			$data = [
				"departureConnectionId" => $connection->id,
				"returnConnectionId" => null,
				"count" => 1,
				"currencyId" => "43eb12bc-0b85-47f9-a34a-6730365ee9b6",
				"open" => false
			];
			$connection->prices = $this->apiRequest("/Connection/GetAvailablePrices", $data);
		}

		return $array->connections;
	}
}
