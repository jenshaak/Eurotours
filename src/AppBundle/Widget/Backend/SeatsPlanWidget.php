<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 14:26
 */

namespace AppBundle\Widget\Backend;


use AppBundle\Entity\SeatsPlan;
use AppBundle\Service\BookService;
use AppBundle\Service\InternalTicketService;
use AppBundle\Service\SeatsPlanService;
use Motvicka\WidgetBundle\Widget\Widget;
use Symfony\Component\Routing\RouterInterface;

class SeatsPlanWidget extends Widget
{
	const NAME = "backend.seatsPlan";
	/**
	 * @var RouterInterface
	 */
	private $router;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var BookService
	 */
	private $bookService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;

	public function __construct(RouterInterface $router,
	                            InternalTicketService $internalTicketService,
	                            BookService $bookService,
	                            SeatsPlanService $seatsPlanService)
	{
		$this->router = $router;
		$this->internalTicketService = $internalTicketService;
		$this->bookService = $bookService;
		$this->seatsPlanService = $seatsPlanService;
	}

	/**
	 * @param SeatsPlan $seatsPlan
	 * @param \DateTime|null $dateDay
	 * @param bool $onlyForCarrier
	 * @return mixed
	 * @throws \Twig\Error\Error
	 */
	public function fetch(SeatsPlan $seatsPlan, \DateTime $dateDay = null, $onlyForCarrier = false)
	{
		if ($seatsPlan->getSchedule()) {
			$actionUrl = $this->router->generate("backend_ajax_schedule_seats_plan", [
				"line" => $seatsPlan->getSchedule()->getLine()->getId(),
				"schedule" => $seatsPlan->getSchedule()->getId()
			]);
		} else {
			throw new \Exception("Nelze vytvorit actionUrl k SeatsPlanWidget");
		}

		if ($dateDay !== null) {
			$internalTickets = $this->internalTicketService->findInternalTicketsForScheduleAndDay(
				$seatsPlan->getSchedule(), $dateDay
			);

			$books = $this->bookService->findInternalBooksForSeatsPlanAndDay(
				($seatsPlan->getId() === null)  ? $this->seatsPlanService->getSeatsPlanForSchedule($seatsPlan->getSchedule()) : $seatsPlan,
				$dateDay
			);
		} else {
			$internalTickets = [];
			$books = [];
		}

		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Backend/Widget/SeatsPlan.html.twig", [
			"seatsPlan" => $seatsPlan,
			"dateDay" => $dateDay,
			"actionUrl" => $actionUrl,
			"internalTickets" => $internalTickets,
			"books" => $books,
			"onlyForCarrier" => $onlyForCarrier
		]));
	}
}
