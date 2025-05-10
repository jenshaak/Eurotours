<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 15:38
 */

namespace AppBundle\Service;


use AppBundle\Entity\InternalTicket;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Seat;
use AppBundle\Entity\SeatsPlan;
use AppBundle\Repository\SeatsPlanRepository;
use AppBundle\VO\SeatsChange;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;

class SeatsPlanService
{
	const PARAM_SEATS = "seats";

	/**
	 * @var SeatsPlanRepository
	 */
	private $seatsPlanRepository;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;

	public function __construct(SeatsPlanRepository $seatsPlanRepository,
	                            InternalTicketService $internalTicketService)
	{
		$this->seatsPlanRepository = $seatsPlanRepository;
		$this->internalTicketService = $internalTicketService;
	}

	/**
	 * @param Schedule $schedule
	 * @return SeatsPlan|null
	 */
	public function getSeatsPlanForSchedule(Schedule $schedule)
	{
		return $this->seatsPlanRepository->getSeatsPlanForSchedule($schedule);
	}

	/**
	 * @param Schedule $schedule
	 * @param \DateTime $dateDay
	 * @return SeatsPlan|null
	 */
	public function getSeatsPlanForScheduleAndDate(Schedule $schedule, \DateTime $dateDay)
	{
		return $this->seatsPlanRepository->getSeatsPlanForScheduleAndDate($schedule, $dateDay);
	}

	/**
	 * @param Request $request
	 * @param SeatsPlan $seatsPlan
	 * @return SeatsChange
	 */
	public function fillSeatsPlanFromRequest(Request $request, SeatsPlan $seatsPlan)
	{
		$seatsChange = new SeatsChange;
		$seatsPlan->getAvailableSeats()->map(function (Seat $seat) use ($seatsChange) {
			$seatsChange->addOldAvailableSeat($seat->getNumber());
		});

		$seatsData = $request->request->get(self::PARAM_SEATS);
		$seatsBuff = new ArrayCollection;
		foreach ($seatsData as $seatData) {
			$seat = $seatsPlan->getSeatForPosition($seatData['x'], $seatData['y']);
			if ($seat === null) {
				$seat = $seatsPlan->createSeatForPosition($seatData['x'], $seatData['y']);
			}
			$seat->setSeatsPlan($seatsPlan);
			$seat->setAvailable($seatData['available'] === "true");
			$seat->setNumber($seatData['number']);
			$seatsBuff->add($seat);
		}

		$seatsPlan->getSeats()->map(function (Seat $seat) use ($seatsBuff, $seatsPlan) {
			if (!$seatsBuff->contains($seat)) $seatsPlan->getSeats()->removeElement($seat);
		});

		$seatsPlan->getAvailableSeats()->map(function (Seat $seat) use ($seatsChange) {
			$seatsChange->addNewAvailableSeat($seat->getNumber());
		});

		return $seatsChange;
	}

	/**
	 * @param SeatsPlan $seatsPlan
	 */
	public function saveSeatsPlan(SeatsPlan $seatsPlan)
	{
		$this->seatsPlanRepository->save($seatsPlan);
	}

	/**
	 * @param Schedule $schedule
	 * @param \DateTime $dateDay
	 * @return SeatsPlan|null
	 */
	public function getSeatsPlanForScheduleAndDateIfItIsPossible(Schedule $schedule, \DateTime $dateDay)
	{
		$seatPlan = $this->getSeatsPlanForSchedule($schedule);
		if ($seatPlan) {
			$seatPlanDateDay = $this->getSeatsPlanForScheduleAndDate($schedule, $dateDay);
		}

		return isset($seatPlanDateDay) ? $seatPlanDateDay : $seatPlan;
	}

	/**
	 * @param SeatsPlan $seatsPlan
	 * @param int|null $countSeats
	 * @return Seat[]|false
	 */
	public function getAvailableSeatsFromPlan(SeatsPlan $seatsPlan, $countSeats, Route $route)
	{
		$internalTickets = new ArrayCollection(
			$this->internalTicketService->findInternalTicketsForScheduleAndDay(
				$route->getSchedule(), $route->getDatetimeDeparture()
			)
		);

		$internalTickets = $internalTickets->filter(function (InternalTicket $internalTicket) use ($route) {
			return $internalTicket->getRoute()->isCollidingWithRoute($route);
		});

		$seats = $seatsPlan->getAvailableSeats()->filter(function (Seat $seat) use ($internalTickets) {
			return !$internalTickets->exists(function ($i, InternalTicket $internalTicket) use ($seat) {
				return $internalTicket->getNumber() === $seat->getNumber();
			});
		});

		if ($seats->count() < $countSeats) return false;
		return array_slice($seats->toArray(), 0, $countSeats);
	}

	/**
	 * @param Route $route
	 * @return bool|null
	 * Pokud vrati null, tak spoj nema nastavena sedadla. Jinak vraci bool hodnotu podle toho jesli je volne misto
	 */
	public function canBuyRoute(Route $route)
	{
		if ($route->getSchedule() === null) return null;
		$seatsPlane = $this->getSeatsPlanForScheduleAndDateIfItIsPossible($route->getSchedule(), $route->getDatetimeDeparture());
		if ($seatsPlane === null) return null;

		$seats = $this->getAvailableSeatsFromPlan($seatsPlane, 1, $route);

		if ($seats === false) return false;

		return count($seats) == 1;
	}

	/**
	 * @param Schedule $schedule
	 * @return SeatsPlan[]
	 */
	public function getSeatsPlansForSchedule(Schedule $schedule)
	{
		return $this->seatsPlanRepository->getSeatsPlansForSchedule($schedule);
	}
}
