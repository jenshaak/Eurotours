<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.05.17
 * Time: 13:00
 */

namespace AppBundle\Widget\Frontend;


use Motvicka\WidgetBundle\Widget\Widget;

class MenuWidget extends Widget
{
	const ACTIVE_SEARCH = "search";
	const ACTIVE_CONTACT = "contact";
	const ACTIVE_TIMETABLES = "timetables";
	const ACTIVE_FLIGHTS = "flights";
	const ACTIVE_BULHARSKO = "bulharsko";

	/** @var string */
	private $active = self::ACTIVE_SEARCH;

	public function fetch()
	{
		return $this->getTwigEngine()->render("AppBundle:Frontend/Widget:menu.html.twig", [
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
