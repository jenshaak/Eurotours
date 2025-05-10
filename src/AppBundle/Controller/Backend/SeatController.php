<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 14:24
 */

namespace AppBundle\Controller\Backend;
use AppBundle\Entity\BookInternal;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\SeatsPlan;
use AppBundle\Service\BookService;
use AppBundle\Service\DateFormatService;
use AppBundle\Service\EmailService;
use AppBundle\Service\SeatsPlanService;
use AppBundle\Service\UserService;
use AppBundle\Widget\Backend\SeatsPlanWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.seat")
 * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_CARRIER')")
 */
class SeatController
{
	const PARAM_DATE_DAY = "dateDay";
	const PARAM_ONLY_FOR_CARRIER = "onlyForCarrier";

	/**
	 * @var SeatsPlanWidget
	 */
	private $seatsPlanWidget;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var BookService
	 */
	private $bookService;

	public function __construct(SeatsPlanWidget $seatsPlanWidget,
	                            SeatsPlanService $seatsPlanService,
	                            DateFormatService $dateFormatService,
	                            EmailService $emailService,
	                            UserService $userService,
	                            BookService $bookService)
	{
		$this->seatsPlanWidget = $seatsPlanWidget;
		$this->seatsPlanService = $seatsPlanService;
		$this->dateFormatService = $dateFormatService;
		$this->emailService = $emailService;
		$this->userService = $userService;
		$this->bookService = $bookService;
	}

	/**
	 * @Route(path="/backend/line/{line}/schedule/{schedule}/seatsPlan", name="backend_ajax_schedule_seats_plan", methods={"GET"})
	 * @param Schedule $schedule
	 * @param Request $request
	 * @return JsonResponse
	 * @throws \Twig\Error\Error
	 */
	public function seatsPlanForScheduleAction(Schedule $schedule, Request $request)
	{
		$dateDay = $this->parseDateDay($request);
		$seatsPlan = $this->getSeatsPlan($schedule, $dateDay);
		$onlyForCarrier = $this->isOnlyForCarrier($request);

		return JsonResponse::create([
			"seatsPlanWidget" => $this->seatsPlanWidget->fetch($seatsPlan, $dateDay, $onlyForCarrier)
		]);
	}

	/**
	 * @Route(path="/backend/line/{line}/schedule/{schedule}/seatsPlan", name="backend_ajax_schedule_seats_plan_save", methods={"POST"})
	 * @param Schedule $schedule
	 * @return JsonResponse
	 */
	public function seatsPlanForScheduleSaveAction(Schedule $schedule, Request $request)
	{
		$dateDay = $this->parseDateDay($request);
		$seatsPlan = $this->getSeatsPlan($schedule, $dateDay);

		$seatsChange = $this->seatsPlanService->fillSeatsPlanFromRequest($request, $seatsPlan);
		if ($dateDay instanceof \DateTime) {
			$seatsPlan->setDateDay($dateDay);
			$seatsChange->setDateDay($dateDay);
		}
		$this->seatsPlanService->saveSeatsPlan($seatsPlan);

		$seatsChange->setSchedule($schedule);

		$globalSeatsPlan = $this->seatsPlanService->getSeatsPlanForSchedule($schedule);
		if ($globalSeatsPlan->getId() !== $seatsPlan->getId()) {
			array_map(function(BookInternal $book) use ($globalSeatsPlan, $seatsPlan) {
				$book->setSeatsPlan($seatsPlan);
				$book->setSeat($seatsPlan->getSeatByNumber($book->getSeat()->getNumber()));
				$this->bookService->saveBook($book);
			}, $this->bookService->findInternalBooksForSeatsPlanAndDay($globalSeatsPlan, $dateDay));
		}

		if ($this->userService->getCurrentUser()->getCarrier() !== null and $seatsChange->getDateDay() instanceof \DateTime) {
			$this->emailService->sendSeatsChange($seatsChange);
		}

		return JsonResponse::create([]);
	}

	/**
	 * @param Request $request
	 * @return \DateTime|null
	 */
	private function parseDateDay(Request $request)
	{
		$dateDay = null;
		if ($request->get(self::PARAM_DATE_DAY)) {
			$dateDay = $this->dateFormatService->dateParse($request->get(self::PARAM_DATE_DAY));
		}
		return $dateDay;
	}

	/**
	 * @param Schedule $schedule
	 * @param $dateDay
	 * @return SeatsPlan|null
	 */
	private function getSeatsPlan(Schedule $schedule, $dateDay)
	{
		if ($dateDay instanceof \DateTime) {
			$seatsPlan = $this->seatsPlanService->getSeatsPlanForScheduleAndDate($schedule, $dateDay);
		} else {
			$seatsPlan = $this->seatsPlanService->getSeatsPlanForSchedule($schedule);
		}

		if ($seatsPlan === null and $dateDay === null) {
			$seatsPlan = new SeatsPlan;
			$seatsPlan->setSchedule($schedule);
		} elseif ($seatsPlan === null and $dateDay instanceof \DateTime) {
			$seatsPlan = clone $this->seatsPlanService->getSeatsPlanForSchedule($schedule);
			$seatsPlan->setDateDay($dateDay);
		}

		return $seatsPlan;
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	private function isOnlyForCarrier(Request $request)
	{
		return $request->get(self::PARAM_ONLY_FOR_CARRIER) === "1";
	}

}
