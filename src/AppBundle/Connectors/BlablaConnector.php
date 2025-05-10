<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 10.04.18
 * Time: 16:44
 */

namespace AppBundle\Connectors;


use AppBundle\Entity\BookRegabus;
use AppBundle\Entity\ExternalTicketRegabus;
use AppBundle\Entity\Route;
use AppBundle\Exceptions\RegabusConnectorException;
use AppBundle\Service\UploadService;
use AppBundle\VO\RegabusBookRoute;
use AppBundle\VO\RegabusBookRoutePerson;
use Curl\Curl;

class BlablaConnector
{
	const URL = "https://ims.blablacar.pro/cgi-bin/gtmapp/wapi/";
	const LOGIN = "eurotours_v2_api";
	const PASSWORD = "x5Y22ex6sX3kFD";

//	test environment
//	const URL = "https://ims-preprod.blablacar.pro/cgi-bin/gtmapp/wapi/";
//	const LOGIN = "eurotours_v2_api";
//	const PASSWORD = "BkSNz6a2ky9e4H";

	/** @var string|null */
	private $token = null;
	/**
	 * @var UploadService
	 */
	private $uploadService;

	public function __construct(UploadService $uploadService)
	{
		$this->uploadService = $uploadService;
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

		if (!isset($params['lang'])) {
			$params['lang'] = "en";
		}

		return $params;
	}

	/**
	 * @param string $action
	 * @param array|object $params
	 * @param bool $includeToken
	 * @return object|array
	 * @throws RegabusConnectorException
	 */
	private function requestPost(string $action, $params = [], bool $includeToken = true)
	{
		$curl = new Curl();
		if ($includeToken) {
			$curl->setHeader('session', $this->getToken());
		}
		$curl->post(self::URL . $action, $this->processParams($params));
		$json = json_decode($curl->response);
		if (isset($json->error)) throw new RegabusConnectorException($json->error);
		return $json;
	}

	/**
	 * @param string $action
	 * @param array|object $params
	 * @return object|array
	 * @throws RegabusConnectorException
	 */
	private function requestGet(string $action, $params = [])
	{
		$curl = new Curl();
		$curl->setHeader('session', $this->getToken());
		$curl->get(self::URL . $action, $this->processParams($params));
		$json = json_decode($curl->response);
		if (isset($json->error)) throw new RegabusConnectorException($json->error);
		return $json;
	}

	/**
	 * @return string
	 * @throws RegabusConnectorException
	 */
	private function getToken(): string
	{
		if ($this->token === null) {
			$response = $this->requestPost("login", [
				"login" => self::LOGIN,
				"password" => self::PASSWORD
			], false);
			$this->token = $response->token;
		}

		return $this->token;
	}

	public function getCities()
	{
		return $this->requestGet("cities");
	}

	public function getStations()
	{
		return $this->requestGet("stations");
	}

	public function getTrip($currency, $id)
	{
		try {
			return $this->requestGet("trip", [
					"id" => $id,
					"currency" => $currency
				]
			);
		} catch (RegabusConnectorException $e) {
			if (preg_match("~E_NOPLACES~", $e->getMessage())) {
				return null;
			} else {
				throw $e;
			}
		}
	}

	public function getExportPDF($buyId, $ticketId)
	{
		$params = [
			"buyid" => $buyId,
			"ticketid" => $ticketId
		];

		$curl = new Curl();
		$curl->setHeader('session', $this->getToken());
		$curl->get(self::URL . "exportPDF", $this->processParams($params));
		return $curl->response;
	}

	/**
	 * @return string
	 * @throws RegabusConnectorException
	 */
	public function buyInit()
	{
		$response = $this->requestPost("buyinit", [ "token" => $this->getToken() ]);
		return $response->buyid;
	}

	/**
	 * @param Route $route
	 * @param RegabusBookRoute $bookRoute
	 * @return BookRegabus[]
	 * @throws RegabusConnectorException
	 */
	public function bookRoute(Route $route, RegabusBookRoute $bookRoute)
	{
		$places = array_map(function (RegabusBookRoutePerson $person) {
			return [
				"raceId" => $person->getRaceId(),
				"name" => $person->getFirstName(),
				"lastName" => $person->getLastName(),
				"phone" => $person->getPhone(),
				"email" => $person->getEmail(),
				"baggage" => $person->getBaggage(),
				"passport" => $person->getPassport(),
				"lgot" => $person->getTariffIdent(),
				"place" => $person->getSeatNumber()
			];
		}, $bookRoute->getPersons());

		$params = [
			"currency" => $route->getCurrency(),
			"id" => $bookRoute->getId(),
			"reserve" => 1,
			"preliminary" => 1,
			"buyid" => $bookRoute->getBuyId(),
			"places" => base64_encode(json_encode($places))
		];

		$this->requestPost("buy", $params);

		return $route->getBooks()->getValues();
	}

	/**
	 * @param Route $route
	 * @return ExternalTicketRegabus[]
	 * @throws RegabusConnectorException
	 */
	public function buyRoute(Route $route): array
	{
		$buyId = $route->getBooks()->map(function (BookRegabus $book) {
			return $book->getBuyId();
		})->first();

		$buyResponse = $this->requestPost("buy", [
			"currency" => $route->getCurrency(),
			"buyid" => $buyId,
			"reserve" => 0
		]);

		$tickets = [];

		foreach ($route->getOrderPersonRouteTariffs() as $orderPersonRouteTariff) {
			/** @var BookRegabus $book */
			$book = $orderPersonRouteTariff->getBook();
			$ticketData = array_shift($buyResponse->bl);
			$externalTicket = ExternalTicketRegabus::create(
				$orderPersonRouteTariff->getRoute(),
				$orderPersonRouteTariff->getRouteTariff(),
				$orderPersonRouteTariff->getOrderPerson()
			);
			$book->setExternalTicket($externalTicket);
			$pdfContent = $this->getExportPDF($buyId, $ticketData->ticketId);
			$file = $this->uploadService->createFile("pdf");
			file_put_contents($this->uploadService->getWebDir() . $file, $pdfContent);
			$externalTicket->setTicketIdent($ticketData->ticketId);
			$externalTicket->setFile($file);
			$externalTicket->setContentType("application/pdf");
			$tickets[] = $externalTicket;
		}

		return $tickets;
	}

	/**
	 * @param string $currency
	 * @param int $fromCity
	 * @param int $toCity
	 * @param \DateTime $dateDay
	 * @return array
	 * @throws RegabusConnectorException
	 */
	public function findRoute(string $currency, int $fromCity, int $toCity, \DateTime $dateDay)
	{
		$params = [
			"from" => $fromCity,
			"to" => $toCity,
			"when" => $dateDay->format("Y-m-d"),
			"currency" => $currency
		];

		try {
			$response = $this->requestGet("trips", $params);
		} catch (RegabusConnectorException $e) {
			if ($e->getError()->code === "E_NODATA") {
				return [];
			}
			throw $e;
		}

		return $response;
	}
}
