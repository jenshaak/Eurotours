<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.17
 * Time: 16:42
 */

namespace AppBundle\Connectors;


use AppBundle\Exceptions\EcolinesBookingConfirmationErrorException;
use AppBundle\Exceptions\EcolinesSeatBusyException;
use AppBundle\VO\EcolinesPersonBookingVO;
use Curl\Curl;

class EcolinesConnector
{
	private function getUrl($method)
	{
		return "https://eurotours_API:xSk2WHr8Saa@api2.ecolines.net/v1/" . $method;
		//return "http://eurotours_api:gdyr462389aq@dev.api.ecolines.net/ebs/web/v1/" . $method;
	}

	/**
	 * @return array
	 */
	public function sendRequest($method, $params = [])
	{
		$curl = new Curl();
		$curl->setHeader("Content-Type", "application/json");
		$curl->get($this->getUrl($method), $params);
		return json_decode($curl->response);
	}

	/**
	 * @param string $journey
	 * @return array
	 */
	public function getFares($journey)
	{
		return $this->sendRequest("fares", [ "journey" => $journey ]);
	}

	/**
	 * @param $fromCity
	 * @param $toCity
	 * @param \DateTime $dateDay
	 * @return array
	 */
	public function findRoute($fromCity, $toCity, \DateTime $dateDay)
	{
		return $this->sendRequest("journeys", [
			"outboundOrigin" => $fromCity,
			"outboundDestination" => $toCity,
			"outboundDate" => $dateDay->format("Y-m-d"),
			"currency" => 11, # EUR
		]);
	}

	/**
	 * @return array
	 */
	public function getStops()
	{
		return $this->sendRequest("stops");
	}

	/**
	 * @param string $journey
	 * @return array
	 */
	public function getLegs($journey)
	{
		return $this->sendRequest("legs", [ "journey" => $journey ]);
	}

	/**
	 * @param string $leg
	 * @return array
	 */
	public function getSeats($leg)
	{
		return $this->sendRequest("seats", [ "leg" => $leg ]);
	}

	/**
	 * @param string $journey
	 * @param EcolinesPersonBookingVO[] $personBookings
	 * @throws \ErrorException
	 * @return string
	 */
	public function booking($journey, $personBookings)
	{
		$data = [
			"journey" => $journey,
			"currency" => 11,
			"passengers" => array_map(function (EcolinesPersonBookingVO $personBooking) {
				return [
					"firstName" => $personBooking->getFirstName(),
					"lastName" => $personBooking->getLastName(),
					"phone" => $personBooking->getPhone(),
					"tariff" => $personBooking->getTariff(),
					"discount" => $personBooking->getDiscount(),
					"seats" => $personBooking->getSeats(),
					"email" => $personBooking->getEmail(),
					"note" => $personBooking->getNote()
				];
			}, $personBookings)
		];

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL => $this->getUrl("bookings"),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($data),
			CURLOPT_HTTPHEADER => [
				"Cache-Control: no-cache",
				"Content-Type: application/json"
			],
		]);

		$response = curl_exec($curl);

		curl_close($curl);

		$output = json_decode($response);

		if (isset($output->code)) {
			if ($output->code === EcolinesSeatBusyException::ERROR_CODE) {
				throw new EcolinesSeatBusyException;
			}
		}

		return $output->id;
	}

	/**
	 * @param string $bookingId
	 * @throws EcolinesBookingConfirmationErrorException
	 * @throws \ErrorException
	 */
	public function confirmBooking($bookingId)
	{
		$curl = new Curl();
		$curl->setHeader("Content-Type", "application/json");
		$curl->post($this->getUrl("bookings/{$bookingId}/confirmation"));
		if ($curl->http_status_code != 200) {
			throw new EcolinesBookingConfirmationErrorException;
		}
	}

	public function bookingTickets($bookingId)
	{
		return $this->sendRequest("bookings/{$bookingId}/tickets");
	}
}
