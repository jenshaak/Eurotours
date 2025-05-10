<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 31.10.17
 * Time: 17:33
 */

namespace AppBundle\Connectors;


use Goutte\Client;

class FlixbusConnector
{
	/** @var Client */
	private $client;

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
	 * @return array|\stdClass[]
	 */
	public function getCities()
	{
		$data = json_decode(file_get_contents("https://search.k8s.mfb.io/api/v1/cities"));

		return $data->cities;
	}

	/**
	 * @return array|\stdClass[]
	 */
	public function getStations()
	{
		$data = json_decode(file_get_contents("https://search.k8s.mfb.io/api/v1/stations"));

		return $data->stations;
	}

	/**
	 * @param int $fromCity
	 * @param int $toCity
	 * @param \DateTime $dateDay
	 * @param int $adult
	 * @param int $children
	 * @return array|\stdClass[]
	 */
	public function findRoutes($fromCity, $toCity, \DateTime $dateDay, $adult, $children)
	{
		$routes = [];
		$directRoutes = [];
		$params = [
			"from_city_id" => $fromCity,
			"to_city_id" => $toCity,
			"departure_date" => $dateDay->format("d.m.Y"),
			"search_by" => "cities",
			"currency" => "CZK",
			"include_after_midnight_rides" => 0,
			"bike_slot" => 0,
			"_locale" => "cs"
		];

		if ($adult > 0) {
			$params['products'] = "{\"adult\":1}";
		} elseif ($children > 0) {
			$params['products'] = "{\"children\":1}";
		}

		$client = $this->getClient();
		$client->request("GET", "https://search.k8s.mfb.io/api/v2/search?" . http_build_query($params), [], [], [
			CURLOPT_REFERER => "https://shop.flixbus.cz/search",
			CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3163.100 Safari/537.36"
		]);

		$data = json_decode($client->getResponse()->getContent());

		if (isset($data->trips) and !empty($data->trips)) {
			foreach ($data->trips[0]->results as $key => $trip) {
				$routes[$key] = $trip;
				if (preg_match("~direct~", $key)) {
					$directRoutes[$key] = $trip;
				}
			}
		}

		if (!empty($directRoutes)) {
			$routes = $directRoutes;
		}

		return $routes;
	}


}
