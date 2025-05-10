<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.01.18
 * Time: 16:36
 */

namespace AppBundle\Widget\Frontend;


use AppBundle\Entity\City;
use AppBundle\Entity\Schedule;
use Motvicka\WidgetBundle\Widget\Widget;

class ScheduleTimeTableWidget extends Widget
{
	const NAME = "frontend.scheduleTimeTable";

	public function fetch(Schedule $schedule, City $fromCity = null, City $toCity = null)
	{
		if ($fromCity) {
			$fromScheduleLineStation = $schedule->getScheduleLineStationForCity($fromCity);
		}

		if ($toCity) {
			$toScheduleLineStation = $schedule->getScheduleLineStationForCity($toCity);
		}

		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Frontend/Widget/scheduleTimeTable.html.twig", [
			"schedule" => $schedule,
			"fromLineStation" => isset($fromScheduleLineStation) ? $fromScheduleLineStation->getLineStation() : null,
			"toLineStation" => isset($toScheduleLineStation) ? $toScheduleLineStation->getLineStation() : null
		]));
	}
}
