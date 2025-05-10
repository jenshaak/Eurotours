<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.05.17
 * Time: 13:00
 */

namespace AppBundle\Widget\Backend;


use Motvicka\WidgetBundle\Widget\Widget;

class MenuWidget extends Widget
{
	const ACTIVE_SEARCH_LINES = "searchLines";
	const ACTIVE_LINES = "lines";
	const ACTIVE_SCHEDULES = "schedules";
	const ACTIVE_OWN_SEATS = "ownSeats";
	const ACTIVE_STATIONS = "stations";
	const ACTIVE_CITIES = "cities";
	const ACTIVE_EXTERNAL_CITIES = "externalCities";
	const ACTIVE_EXTERNAL_TARIFFS = "externalTariffs";
	const ACTIVE_ORDERS = "orders";
	const ACTIVE_TRANSLATES = "translates";
	const ACTIVE_TICKETS = "tickets";
	const ACTIVE_PAYMENTS = "payments";
	const ACTIVE_PAYMENT_GENERATOR = "paymentGenerator";
	const ACTIVE_EXTERNAL_CITIES_APPROVE = "externalCitiesApprove";
	const ACTIVE_USERS = "users";
	const ACTIVE_SEATS_SOLD = "seatsSold";
	const ACTIVE_SEATS_SETTINGS = "seatsSettings";
	const ACTIVE_CARRIERS = "carriers";
	const ACTIVE_HOMEPAGE_NOTICE = "homepageNotice";

	/** @var string */
	private $active;

	public function fetch()
	{
		return $this->getTwigEngine()->render("AppBundle:Backend/Widget:Menu.html.twig", [
			"active" => $this->getActive()
		]);
	}

	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param mixed $active
	 */
	public function setActive($active)
	{
		$this->active = $active;
	}

}
