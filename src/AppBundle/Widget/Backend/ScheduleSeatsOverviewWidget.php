<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 02.05.18
 * Time: 21:02
 */

namespace AppBundle\Widget\Backend;


use AppBundle\Entity\InternalTicket;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\SeatsPlan;
use AppBundle\Service\InternalTicketService;
use AppBundle\Service\SeatsPlanService;
use Motvicka\WidgetBundle\Widget\Widget;

class ScheduleSeatsOverviewWidget extends Widget
{
	const NAME = "backend.scheduleSeatsOverview";

	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;

	public function __construct(InternalTicketService $internalTicketService,
	                            SeatsPlanService $seatsPlanService)
	{
		$this->internalTicketService = $internalTicketService;
		$this->seatsPlanService = $seatsPlanService;
	}

	/**
	 * @param Schedule $schedule
	 * @return string
	 * @throws \Twig\Error\Error
	 */
	public function fetch(Schedule $schedule)
	{
		$days = [];

		array_map(function (InternalTicket $internalTicket) use (&$days) {
			$key = $internalTicket->getDateDay()->format("Ymd");
			if (!isset($days[$key])) {
				$days[$key] = [
					"internalTickets" => [],
					"seatsPlan" => null,
					"dateDay" => $internalTicket->getDateDay()
				];
			}
			$days[$key]['internalTickets'][] = $internalTicket;
		}, $this->internalTicketService->findInternalTicketsForScheduleInFuture($schedule));

		array_map(function (SeatsPlan $seatsPlan) use (&$days) {
			if ($seatsPlan->getDateDay() === null) return;
			$key = $seatsPlan->getDateDay()->format("Ymd");
			if (!isset($days[$key])) {
				$days[$key] = [
					"internalTickets" => [],
					"seatsPlan" => $seatsPlan,
					"dateDay" => $seatsPlan->getDateDay()
				];
			} else {
				$days[$key]['seatsPlan'] = $seatsPlan;
			}
		}, $this->seatsPlanService->getSeatsPlansForSchedule($schedule));

		usort($days, function ($day1, $day2) {
			return $day1['dateDay'] < $day2['dateDay'] ? +1 : -1;
		});

		$days = array_filter($days, function ($day) {
			return $day['dateDay'] > new \DateTime("-2 months");
		});

		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Backend/Widget/ScheduleSeatsOverview.html.twig", [
			"days" => $days
		]));
	}
}
