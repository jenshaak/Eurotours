<?php


namespace AppBundle\Connectors;


use AppBundle\Entity\BookTransTempo;
use AppBundle\Entity\Order;
use AppBundle\Entity\Route;
use AppBundle\Exceptions\TransTempoConnectorException;
use AppBundle\VO\ExternalRouteTransTempo;
use Curl\Curl;

class TransTempoConnector
{
	//const URL = "https://api.test.transtempo.ua";
	//const KEY = "d12995cd6603cff25525abb4a0d57613";
	//const ADMIN_URL = "https://brs.test.transtempo.ua";

	const URL = "https://api.transtempo.ua";
	const KEY = "736f09d3d088ce48c0d40511c8f37248";
	const ADMIN_URL = "https://brs.transtempo.ua";

	const ADMIN_LOGIN_EMAIL = "transtempo67@ukr.net";
	const ADMIN_LOGIN_PASSWORD = "NJKlc#vjnDm23fnc28n";

	CONST CURRENCY_CZK = 203;

	public function __construct()
	{
	}

	/**
	 * @param array|object $params
	 * @return array
	 */
	private function processParams($params)
	{
		if (is_object($params)) {
			$params = (array) $params;
		}

		$params['key'] = self::KEY;

		return $params;
	}

	/**
	 * @param string $action
	 * @param array|object $params
	 * @return object|array
	 */
	private function request($action, $params = [])
	{
		$curl = new Curl();
		$curl->post(self::URL . "/" . $action, $this->processParams($params));
		$json = json_decode($curl->response);
		if (isset($json->error)) throw new TransTempoConnectorException($json->error);
		return $json;
	}

	public function getCities()
	{
		return $this->request("get_cities")->response;
	}

	public function getBuses($fromCity, $toCity, \DateTime $dateDay)
	{
		return $this->request("get_buses", [
			"from_city_id" => $fromCity,
			"to_city_id" => $toCity,
			"date" => $dateDay->format("Y-m-d"),
			"currency" => self::CURRENCY_CZK
		])->response;
	}

	public function getSeats(Route $route)
	{
		return $this->request("get_seats", [
			"bus_id" => $route->getExternalIdent(),
			"from_city_id" => $route->getFromExternalCity()->getIdent(),
			"to_city_id" => $route->getToExternalCity()->getIdent(),
			"date" => $route->getDatetimeDeparture()->format("Y-m-d"),
			"currency" => self::CURRENCY_CZK
		])->response;
	}

	public function getTicketHtml($ticketId)
	{
		$curl = new Curl;
		$curl->post(self::ADMIN_URL . "/?controller=pjAuth&action=pjActionLogin", [
			"login_email" => self::ADMIN_LOGIN_EMAIL,
			"login_password" => self::ADMIN_LOGIN_PASSWORD,
			"login_user" => "1"
		]);

		$session = null;
		foreach ($curl->response_headers as $header) {
			if (preg_match("~BusSchedule=(?P<session>[a-z0-9]+)~", $header, $buff)) {
				$session = $buff['session'];
			}
		}

		$curl = new Curl;
		$curl->setCookie("BusSchedule", $session);
		$curl->get(self::ADMIN_URL, [
			"controller" => "pjAdminBookings",
			"action" => "pjActionPrintTickets",
			"id" => $ticketId,
			"template" => "o_ticket_template"
		]);

		if ($curl->http_status_code !== 200) {
			throw new \Exception("Nefunguje pristup do TransTempo pro stazeni jizdenky.");
		}

		$html = $curl->response;
		$html = str_replace("/app/web/ticket/", "https://brs.transtempo.ua/app/web/ticket/", $html);
		$html = str_replace("window.print();", "", $html);

		return $html;
	}

	public function bookRoute(Order $order, Route $route, $books)
	{
		list($firstName, $lastName) = explode(" ", $order->getName());

		$response = $this->request("simple_book", [
			"bus_id" => $route->getExternalIdent(),
			"from_city_id" => $route->getFromExternalCity()->getIdent(),
			"to_city_id" => $route->getToExternalCity()->getIdent(),
			"date" => $route->getDatetimeDeparture()->format("Y-m-d"),
			"currency" => self::CURRENCY_CZK,
			"first_name" => $firstName,
			"last_name" => $lastName,
			"phone" => $order->getPhone(),
			"email" => $order->getEmail(),
			"passengers" => array_map(function (BookTransTempo $book) use ($order, $route) {
				$orderPerson = $book->getOrderPersonRouteTariff()->getOrderPerson();
				list($firstName, $lastName) = explode(" ", $orderPerson->getName());

				/** @var ExternalRouteTransTempo $routeExternalObject */
				$routeExternalObject = $route->getExternalObject();

				$seatId = array_reduce($routeExternalObject->getSeats(), function ($seatId, $seat) use ($book) {
					if ((string) $book->getSeatNumber() === $seat->name) $seatId = $seat->seat_id;
					return $seatId;
				}, null);

				return [
					"seat_id" => $seatId,
					"ticket_type_id" => $book->getOrderPersonRouteTariff()->getRouteTariff()->getExternalBookingIdent(),
					"first_name" => $firstName,
					"last_name" => $lastName,
					"phone" => $orderPerson->getPhone()
				];
			}, $books)
		])->response;

		return $response;
	}

	public function confirmBooking($hash)
	{
		$this->request("confirm_booking", [
			"hash" => $hash,
			"currency" => self::CURRENCY_CZK
		])->response;
	}

}
