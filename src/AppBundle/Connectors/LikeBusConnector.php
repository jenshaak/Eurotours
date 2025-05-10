<?php

namespace AppBundle\Connectors;

use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\Route;
use AppBundle\Exceptions\LikeBusConnectorException;
use Curl\Curl;
use DateTime;

class LikeBusConnector
{
	const URL = 'https://likebus.ua/sync/v3s';
	const KEY = 'hGwn6fb4';

//	Testing credentials
//	const URL = 'https://like99.akstor.com.ua/sync/v3s';
//	const KEY = 'EvRtsApi';

	/** @param array|object $params */
	private function processParams($params): array
	{
		if (is_object($params)) {
			$params = (array) $params;
		}

		return $params;
	}

	/**
	 * @param array|object $params
	 * @return object|array
	 * @throws LikeBusConnectorException
	 */
	private function request(string $action, $params = [])
	{
		$curl = new Curl();
		$curl->setHeader('x-api-key', self::KEY);
		$curl->get(self::URL . "/" . $action, $this->processParams($params));
		$json = json_decode($curl->response, true);

		if (isset($json->error)) throw new LikeBusConnectorException($json->error);

		return $json;
	}

	/** @throws LikeBusConnectorException */
	public function getCities(): array
	{
		return $this->request('catalog/city');
	}

	/** @throws LikeBusConnectorException */
	public function getStations(): array
	{
		return $this->request('catalog/station');
	}

	/** @throws LikeBusConnectorException */
	public function getTariffs(): array
	{
		return $this->request('catalog/tickets');
	}

	/** @throws LikeBusConnectorException */
	public function getTrip(int $fromStation, int $toStation, int $routeId): array
	{
		return $this->request('routes/trip', [
			'from' => $fromStation,
			'to' => $toStation,
			'id' => $routeId,
		]);
	}

	/** @throws LikeBusConnectorException */
	public function findRoutes(int $fromCity, int $toCity, DateTime $date): array
	{
		return $this->request('routes', [
			'from' => $fromCity,
			'to' => $toCity,
			'date' => $date->format('Y-m-d'),
		]);
	}

	/** @throws LikeBusConnectorException */
	public function createOrder(Order $order, Route $route, string $currency): array
	{
		[$firstName, $lastName] = explode(' ', $order->getName());

		return $this->request('order/new', [
			'routes' => $route->getExternalIdent(),
			'currency' => $currency,
			'from' => $route->getFromExternalStation()->getIdent(),
			'to' => $route->getToExternalStation()->getIdent(),
			'mainname' => $firstName . '+' . $lastName,
			'phone' => $order->getPhone(),
			'email' => $order->getEmail(),
			'name' => $order->getOrderPersons()->map(function (OrderPerson $orderPerson) {
				[$firstName,$lastName] = explode(' ', $orderPerson->getName());
				return $lastName . ' ' . $firstName;
			})->toArray(),
			'type_ticket' => $order->getOrderPersons()->map(function (OrderPerson $orderPerson) {
				return $orderPerson->getRouteTariffThere()->getExternalBookingIdent();
			})->toArray(),
		]);
	}

	/** @throws LikeBusConnectorException */
	public function confirmOrder(int $orderId, float $price): array
	{
		return $this->request('order/confirm', [
			'id' => $orderId,
			'price' => $price,
		]);
	}
}
