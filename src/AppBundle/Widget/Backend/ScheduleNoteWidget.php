<?php


namespace AppBundle\Widget\Backend;


use AppBundle\Entity\Schedule;
use Motvicka\WidgetBundle\Widget\Widget;

class ScheduleNoteWidget extends Widget
{
	const NAME = "backend.scheduleNote";

	public function fetch(Schedule $schedule)
	{
		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Backend/Widget/ScheduleNote.html.twig", [
			"schedule" => $schedule
		]));
	}
}
