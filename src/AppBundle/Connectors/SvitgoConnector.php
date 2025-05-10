<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-04-05
 * Time: 10:29
 */

namespace AppBundle\Connectors;


use AppBundle\Exceptions\SvitgoBookTicketException;
use AppBundle\VO\SvitgoSellTicket;
use AppBundle\Exceptions\SvitgoBuyBookTicketException;
use Curl\Curl;
use Goutte\Client;

class SvitgoConnector
{
	/**
	 * @return null|string
	 */
	protected function getApiKey()
	{
		return null;
	}

	/**
	 * @return mixed
	 * @throws \ErrorException
	 */
	private function sendRequest($action, $params = [])
	{
		$params = array_merge($params, [
			"key" => $this->getApiKey(),
			"culture" => "en",
			"format" => "json"
		]);

		$curl = new Curl();
		$curl->get("http://ticket.svitgo.com/api/{$action}", $params);
		return json_decode($curl->response);
	}

	/**
	 * @return array
	 * @throws \ErrorException
	 */
	public function getAllRoutes()
	{
		return $this->sendRequest("all_routes");
	}

	/**
	 * @param string $routeNameId
	 * @return array
	 * @throws \ErrorException
	 */
	public function getAllRouteStations($routeNameId)
	{
		return $this->sendRequest("all_route_stations", [ "route_name_id" => $routeNameId ]);
	}

	/**
	 * @param string $from
	 * @param string $to
	 * @param \DateTime $dateDay
	 * @return array
	 * @throws \ErrorException
	 */
	public function findRoute($from, $to, \DateTime $dateDay)
	{
		return $this->sendRequest("search", [
			"from" => $from,
			"to" => $to,
			"search_date" => $dateDay->format("j/n/Y"),
			"return" => 0
		]);
	}

	/**
	 * @return array
	 * @throws \ErrorException
	 */
	public function getAllDiscounts()
	{
		$buff = [];
		$discounts = $this->sendRequest("all_discounts");
		return array_filter($discounts, function ($discount) use (&$buff) {
			if ($discount->main_id === "38") return false; # Nechceme "Return" slevu
			if (in_array($discount->main_id, $buff)) {
				return false;
			} else {
				$buff[] = $discount->main_id;
				return true;
			}
		});
	}

	/**
	 * @param SvitgoSellTicket $sellTicket
	 * @return object
	 * @throws SvitgoBookTicketException
	 * @throws \ErrorException
	 */
	public function bookTicket(SvitgoSellTicket $sellTicket)
	{
		$request = [
			'route_name_id' => $sellTicket->getRouteNameId(),
			'from' => $sellTicket->getFrom(),
			'to' => $sellTicket->getTo(),
			'route_date' => $sellTicket->getRouteDate()->format("d/m/y"),
			'buses_id' => $sellTicket->getBusesId(),
			'ferryman_id' => $sellTicket->getFerrymanId(),
			'seat' => $sellTicket->getSeat(),
			'rice' => $sellTicket->getRice(),
			'discount' => $sellTicket->getDiscount(),
			'name' => $sellTicket->getName(),
			'surname' => $sellTicket->getSurname(),
			'userbd' => /*$sellTicket->getUserbd()->format("d/m/y")*/"01/01/88",
			'tel' => $sellTicket->getTel(),
			'sell_or_order' => $sellTicket->getSellOrOrder(),
			'valute' => 'CZK'
		];

		$data = $this->sendRequest("sell_ticket", $request);

		if (is_numeric($data)) {
			throw new SvitgoBookTicketException($data, $request);
		}

		return $data;
	}

	/**
	 * @param int $ticketId
	 * @return string
	 */
	public function getTicketHtmlBody($ticketId)
	{
		$client = new Client;

		$client->request("POST", "http://ticket.svitgo.com/disp/login", [
			"admin_login" => "eurotours",
			"admin_pass" => "eurotourseu"
		]);

		var_dump($ticketId);

		$client->request("POST", "http://ticket.svitgo.com/tiket/ajax_sell_tikets_new_print", [
			"tik_arrays" => [ $ticketId ],
			"lang" => "cs"
		]);

		return $client->getInternalResponse()->getContent();
	}

	/**
	 * @param $ticketId
	 * @return string
	 * @throws SvitgoBuyBookTicketException
	 * @throws \ErrorException
	 */
	public function buyBook($ticketId)
	{
		$request = [ "ticket_id" => $ticketId ];
		$data = $this->sendRequest("sale_booking", $request);

		if (is_numeric($data)) {
			throw new SvitgoBuyBookTicketException($data, $request);
		}

		return $this->getTicketHtmlBody($ticketId);
	}
}
