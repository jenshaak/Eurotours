<?php


namespace AppBundle\Connectors;


use AppBundle\Entity\Book;
use AppBundle\Entity\Language;
use AppBundle\Entity\Order;
use AppBundle\Entity\Route;
use AppBundle\VO\BookBusSystemInterface;
use AppBundle\VO\ExternalRouteBusSystem;
use Curl\Curl;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DomCrawler\Crawler;

class BusSystemConnector
{
	/** @return null|string */
	protected function getLogin()
	{
		return null;
	}

	/** @return null|string */
	protected function getPassword()
	{
		return null;
	}

	protected function getUrl($method)
	{
		return "https://api.bussystem.eu/server/curl/{$method}.php";
	}

	/**
	 * @return string
	 */
	private function sendRequest($method, $params = [])
	{
		$params = array_merge($params, [
			"login" => $this->getLogin(),
			"password" => $this->getPassword()
		]);

		$curl = new Curl;
		$curl->setOpt(CURLOPT_PROXY, "http://eurotours:w5podz7AiRXb2fX@207.154.216.20:9305");
		$curl->post($this->getUrl($method), $params);
		return $curl->response;
	}

	/**
	 * @param int $fromCity
	 * @param int $toCity
	 * @param \DateTime $dateDay
	 * @return array|ArrayCollection
	 */
	public function findRoute($fromCity, $toCity, \DateTime $dateDay, $currency)
	{
		$routes = new ArrayCollection;

		$data = $this->sendRequest("get_routes", [
			"id_from" => $fromCity,
			"id_to" => $toCity,
			"date" => $dateDay->format("Y-m-d"),
			"trans" => "bus",
			"currency" => $currency,
			"request_get_discount" => 1
		]);

		$crawler = new Crawler($data);
		$crawler->filterXPath("//root/item")->each(function (Crawler $item) use ($routes) {
			$route = [];
			$discounts = new ArrayCollection;
			$seats = new ArrayCollection;

			$route['id'] = $item->filterXPath("//route_id")->text();
			$route['carrierTitle'] = $item->filterXPath("//carrier")->text();
			$route['pointFromId'] = $item->filterXPath("//point_from_id")->text();
			$route['pointToId'] = $item->filterXPath("//point_to_id")->text();
			if ($item->filterXPath("//change_route")->count() === 0) {
				$route["departureTime"] = new \DateTime($item->filterXPath("//date_from")->text() . " " . $item->filterXPath("//time_from")->text());
			} else {
				$route["departureTime"] = new \DateTime($item->filterXPath("//date_from[count(../change_route) = 1]")->text() . " " . $item->filterXPath("//time_from[count(../change_route) = 1]")->text());
			}

			if ($item->filterXPath("//change_route")->count() === 0) {
				$route["arrivalTime"] = new \DateTime($item->filterXPath("//date_to")->text() . " " . $item->filterXPath("//time_to")->text());
			} else {
				$route["arrivalTime"] = new \DateTime($item->filterXPath("//date_to[count(../change_route) = 1]")->text() . " " . $item->filterXPath("//time_to[count(../change_route) = 1]")->text());
			}

			if ($item->filterXPath("//change_route")->count() === 0) {
				$route["departureStation"] = $item->filterXPath("//station_from")->text();
			} else {
				$route["departureStation"] = $item->filterXPath("//station_from[count(../change_route) = 1]")->text();
			}

			if ($item->filterXPath("//change_route")->count() === 0) {
				$route["arrivalStation"] = $item->filterXPath("//station_to")->text();
			} else {
				$route["arrivalStation"] = $item->filterXPath("//station_to[count(../change_route) = 1]")->text();
			}

			$route['dateBirthRequired'] = (bool) $item->filterXPath("//need_birth")->text();
			$route['documentRequired'] = (bool) $item->filterXPath("//need_doc")->text();

			$route['price'] = $item->filterXPath("//price_one_way")->text();
			$route['currency'] = $item->filterXPath("//currency")->text();

			if ($item->filterXPath("//price_one_way_full")->count() !== 0) {
				$route['maxPrice'] = $item->filterXPath("//price_one_way_full")->text();
			} else if ($item->filterXPath("//price_one_way_max")->count() !== 0) {
				$route['maxPrice'] = $item->filterXPath("//price_one_way_max")->text();
			} else {
				$route['maxPrice'] = $item->filterXPath("//price_one_way")->text();
			}

			$item->filterXPath("//discounts/item")->each(function (Crawler $discount) use ($discounts) {
				try {
					$discounts->add((object) [
						"id" => $discount->filterXPath("//discount_id")->text(),
						"name" => $discount->filterXPath("//discount_name")->text(),
						"price" => (float) $discount->filterXPath("//discount_price")->text(),
					]);
				} catch (\Exception $e) { }
			});

			$item->filterXPath("//free_seats/item")->each(function (Crawler $seat) use ($seats) {
				try { $seats->add($seat->text());}
				catch (\Exception $e) { }
			});

			$route['discounts'] = $discounts->toArray();
			$route['freeSeats'] = $seats->toArray();
			$route['intervalId'] = $item->filterXPath("//interval_id")->text();

			$routes->add((object) $route);
		});

		return $routes;
	}

	/**
	 * @param Language $language
	 * @return array
	 */
	public function getPoints(Language $language)
	{
		$points = new ArrayCollection;

		if ($language->getId() == "cs") {
			$lang = "cz";
		} else {
			$lang = $language->getId();
		}

		# TODO: Tohle point_id_from udelat dynamicky
		# bereme jen linky kterymi se da dostat do Prahy. jinak maji v databazi pres 30k mest
		$crawler = new Crawler($this->sendRequest("get_points", [ "lang" => $lang, "point_id_from" => 3 ]));
		$crawler->filterXPath("//item")->each(function (Crawler $item) use ($points) {
			$points->add([
				"id" => (int) $item->filterXPath("//point_id")->text(),
				"name" => $item->filterXPath("//point_latin_name")->text()
			]);
		});
		$points->add([
			"id" => (int) 3,
			"name" => "Praha"
		]);

		return $points->toArray();
	}

	public function bookRoute(Order $order, Route $route, $books)
	{
		/** @var ExternalRouteBusSystem $externalRoute */
		$externalRoute = $route->getExternalObject();

		$response = $this->sendRequest("new_order", [
			"date" => [ $route->getDatetimeDeparture()->format("Y-m-d") ],
			"interval_id" => [ $externalRoute->getIntervalId() ],
			"seat" => [ array_map(function (BookBusSystemInterface $book) {
				return $book->getSeatNumber();
			}, $books) ],
			"name" => array_map(function (BookBusSystemInterface $book) {
				list($firstName, $lastName) = explode(" ", $book->getOrderPersonRouteTariff()->getOrderPerson()->getName());
				return $firstName;
			}, $books),
			"surname" => array_map(function (BookBusSystemInterface $book) {
				list($firstName, $lastName) = explode(" ", $book->getOrderPersonRouteTariff()->getOrderPerson()->getName());
				return $lastName;
			}, $books),
			"discount_id" => [ array_map(function (Book $book) {
				return $book->getOrderPersonRouteTariff()->getRouteTariff()->getExternalBookingIdent();
			}, $books) ],
			"birth_date" => array_map(function (Book $book) {
				return $book->getOrderPersonRouteTariff()->getOrderPerson()->getDateBirth() ? $book->getOrderPersonRouteTariff()->getOrderPerson()->getDateBirth()->format('Y-m-d') : null;
			}, $books),
			'doc_number' => array_map(function (Book $book) {
				return $book->getOrderPersonRouteTariff()->getOrderPerson()->getDocumentNumber();
			}, $books),
			"phone" => $order->getPhone(),
			"email" => $order->getEmail(),
			"currency" => $order->getCurrency(),
			"lang" => $order->getLanguage()->getId()
		]);

		$crawler = new Crawler($response);

		try {
			$errorMessage = $crawler->filterXPath("//error")->text();
			if ($errorMessage) throw new \Exception("Error BusSystem order '{$order->getId()}': " . $errorMessage);
		} catch (\InvalidArgumentException $exception) { }

		return (object) [
			"orderId" => (int) $crawler->filterXPath("//order_id")->text(),
			"priceTotal" => (int) $crawler->filterXPath("//price_total")->text(),
		];
	}

	public function buyTicket(int $orderId): object
	{
		$this->sendRequest("buy_ticket", [
			"order_id" => $orderId
		]);

		$response = $this->sendRequest("get_order", [
			"order_id" => $orderId
		]);

		preg_match("~<link>(.*)<~U", $response, $buff);

		return (object) [
			"ticketLink" => str_replace("&amp;", "&", $buff[1]),
			"orderId" => $orderId
		];
	}
}
