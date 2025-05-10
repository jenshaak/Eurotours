<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 20.06.18
 * Time: 15:07
 */

namespace AppBundle\Widget\Backend;


use AppBundle\Entity\BookInternal;
use AppBundle\Entity\Schedule;
use AppBundle\Service\BookService;
use AppBundle\Service\InternalTicketService;
use AppBundle\Service\SeatsPlanService;
use Motvicka\WidgetBundle\Widget\Widget;

class ScheduleSeatsSettingsWidget extends Widget
{
	const NAME = "backend.scheduleSeatsSettings";

	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var BookService
	 */
	private $bookService;

	public function __construct(InternalTicketService $internalTicketService,
	                            SeatsPlanService $seatsPlanService,
	                            BookService $bookService)
	{
		$this->internalTicketService = $internalTicketService;
		$this->seatsPlanService = $seatsPlanService;
		$this->bookService = $bookService;
	}

	/**
	 * @param Schedule $schedule
	 * @param \DateTime $dateDay
	 * @return string
	 * @throws \Twig\Error\Error
	 */
	public function fetch(Schedule $schedule, \DateTime $dateDay)
	{
		$internalTickets = $this->internalTicketService->findInternalTicketsForScheduleAndDay($schedule, $dateDay);
		$seatsPlan = $this->seatsPlanService->getSeatsPlanForScheduleAndDateIfItIsPossible($schedule, $dateDay);
		$books = $this->bookService->findInternalBooksForSeatsPlanAndDay($seatsPlan, $dateDay);

		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Backend/Widget/ScheduleSeatsSettings.html.twig", [
			"internalTickets" => $internalTickets,
			"schedule" => $schedule,
			"seatsPlan" => $seatsPlan,
			"dateDay" => $dateDay,
			"books" => $books
		]));
	}
}
