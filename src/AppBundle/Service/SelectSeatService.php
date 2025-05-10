<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 17.05.18
 * Time: 12:16
 */

namespace AppBundle\Service;


use AppBundle\Entity\BookInternal;
use AppBundle\Entity\BookNikolo;
use AppBundle\Entity\BookRegabus;
use AppBundle\Entity\BookTransTempo;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Seat;
use AppBundle\Exceptions\FreeSeatNotAvailableException;
use AppBundle\Routers\InfobusRouter;
use AppBundle\Routers\NikoloRouter;
use AppBundle\Routers\RegabusRouter;
use AppBundle\Routers\TransTempoRouter;
use AppBundle\VO\BookBusSystemInterface;
use AppBundle\VO\ExternalRouteBusSystem;
use AppBundle\VO\SelectSeatVO;
use Doctrine\Common\Collections\ArrayCollection;

class SelectSeatService
{
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var BookService
	 */
	private $bookService;
	/**
	 * @var NikoloRouter
	 */
	private $nikoloRouter;
	/**
	 * @var OrderPersonRouteTariffService
	 */
	private $orderPersonRouteTariffService;
	/**
	 * @var RegabusRouter
	 */
	private $regabusRouter;
	/**
	 * @var TransTempoRouter
	 */
	private $transTempoRouter;

	public function __construct(SeatsPlanService $seatsPlanService,
	                            InternalTicketService $internalTicketService,
	                            BookService $bookService,
	                            NikoloRouter $nikoloRouter,
	                            OrderPersonRouteTariffService $orderPersonRouteTariffService,
	                            RegabusRouter $regabusRouter,
	                            TransTempoRouter $transTempoRouter)
	{
		$this->seatsPlanService = $seatsPlanService;
		$this->internalTicketService = $internalTicketService;
		$this->bookService = $bookService;
		$this->nikoloRouter = $nikoloRouter;
		$this->orderPersonRouteTariffService = $orderPersonRouteTariffService;
		$this->regabusRouter = $regabusRouter;
		$this->transTempoRouter = $transTempoRouter;
	}

	/**
	 * @param Order $order
	 * @return SelectSeatVO[]|ArrayCollection|null
	 * @throws FreeSeatNotAvailableException
	 */
	public function getSelectSeatsForOrder(Order $order)
	{
		/** @var ArrayCollection|SelectSeatVO[] $selectSeats */
		$selectSeats = new ArrayCollection;
		$order->getOrderPersonRouteTariffs()->map(function (OrderPersonRouteTariff $orderPersonRouteTariff) use ($selectSeats) {

			if ($orderPersonRouteTariff->getRoute()->getSchedule() !== null) {
				# interni bookovani
				$book = $orderPersonRouteTariff->getBook();
				if ($book instanceof BookInternal and $book->getSeatsPlan()) {
					$selectSeats->add($selectSeat = SelectSeatVO::create($book->getSeatsPlan(), $orderPersonRouteTariff));
					$selectSeat->setSelectedSeat($book->getSeat());
					$selectSeat->setIsBooked(true);
				} else {
					$seatsPlan = $this->seatsPlanService->getSeatsPlanForScheduleAndDateIfItIsPossible(
						$orderPersonRouteTariff->getRoute()->getSchedule(),
						$orderPersonRouteTariff->getRoute()->getDatetimeDeparture()
					);
					if ($seatsPlan) $selectSeats->add($selectSeat = SelectSeatVO::create($seatsPlan, $orderPersonRouteTariff));
				}

			} elseif (in_array($orderPersonRouteTariff->getRoute()->getCarrier()->getCode(), [ NikoloRouter::CARRIER_CODE, InfobusRouter::CARRIER_CODE ])) {
				/** @var ExternalRouteBusSystem $externalRoute */
				$externalRoute = $orderPersonRouteTariff->getRoute()->getExternalObject();
				$selectSeats->add($selectSeat = $this->nikoloRouter->createSelectSeats($externalRoute, $orderPersonRouteTariff));
				if ($orderPersonRouteTariff->getBook()) {
					/** @var BookBusSystemInterface $book */
					$book = $orderPersonRouteTariff->getBook();
					$selectSeat->setSelectedSeat($selectSeat->getSeatsPlan()->getSeatByNumber($book->getSeatNumber()));
					$selectSeat->setIsBooked(true);
				}

			} elseif ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() === InfobusRouter::CARRIER_CODE) {
				/** @var ExternalRouteBusSystem $externalRoute */
				$externalRoute = $orderPersonRouteTariff->getRoute()->getExternalObject();
				$selectSeats->add($selectSeat = $this->nikoloRouter->createSelectSeats($externalRoute, $orderPersonRouteTariff));
				if ($orderPersonRouteTariff->getBook()) {
					/** @var BookNikolo $book */
					$book = $orderPersonRouteTariff->getBook();
					$selectSeat->setSelectedSeat($selectSeat->getSeatsPlan()->getSeatByNumber($book->getSeatNumber()));
					$selectSeat->setIsBooked(true);
				}

			} elseif ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() === "BlaBla") {

				$this->regabusRouter->loadExternalObjectForRoute($orderPersonRouteTariff->getRoute());

				$selectSeats->add($selectSeat = $this->regabusRouter->createSelectSeats($orderPersonRouteTariff));
				if ($orderPersonRouteTariff->getBook()) {
					/** @var BookRegabus $book */
					$book = $orderPersonRouteTariff->getBook();
					$selectSeat->setSelectedSeat($selectSeat->getSeatsPlan()->getSeatByNumber($book->getSeatNumber()));
					$selectSeat->setIsBooked(true);
				}

			} elseif ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() === "TTbus") {

				$this->transTempoRouter->loadExternalObjectForRoute($orderPersonRouteTariff->getRoute());

				$selectSeats->add($selectSeat = $this->transTempoRouter->createSelectSeats($orderPersonRouteTariff));
				if ($orderPersonRouteTariff->getBook()) {
					/** @var BookTransTempo $book */
					$book = $orderPersonRouteTariff->getBook();
					$selectSeat->setSelectedSeat($selectSeat->getSeatsPlan()->getSeatByNumber($book->getSeatNumber()));
					$selectSeat->setIsBooked(true);
				}

			}
		});

		$selectSeats->map(function (SelectSeatVO $selectSeat) {
			if ($selectSeat->getSeatsPlan()->getSchedule() === null) return;

			$internalTickets = $this->internalTicketService->findInternalTicketsForScheduleAndDay(
				$selectSeat->getSeatsPlan()->getSchedule(),
				$selectSeat->getRoute()->getDatetimeDeparture()
			);
			foreach ($internalTickets as $internalTicket) {
				$selectSeat->addNotAvailableSeatNumber($internalTicket->getNumber());
			}
		});

		$selectSeats->map(function (SelectSeatVO $selectSeat) use ($order) {
			if ($selectSeat->getSeatsPlan()->getSchedule() === null) return;

			$bookInternals = $this->bookService->findInternalBooksForSeatsPlanAndDay(
				$selectSeat->getSeatsPlan(),
				$selectSeat->getRoute()->getDatetimeDeparture()
			);

			foreach ($bookInternals as $bookInternal) {
				if ($bookInternal->getOrder() === $order) continue;
				if ($bookInternal->isStillValid()) {
					$selectSeat->addNotAvailableSeatNumber($bookInternal->getSeatNumber());
				}
			}
		});

		/** @var ArrayCollection|Seat[] $seats */
		$seats = new ArrayCollection;
		$selectSeats->map(function (SelectSeatVO $selectSeat) use ($seats) {
			if ($selectSeat->getSelectedSeat() !== null) return;
			foreach ($selectSeat->getSeatsPlan()->getAvailableSeats()->toArray() as $seat) {
				/** @var Seat $seat */
				if (in_array($seat->getNumber(), $selectSeat->getNotAvailableSeatNumbers())) {
					continue;
				}
				if (!$seats->contains($seat)) {
					$seats->add($seat);
					$selectSeat->setSelectedSeat($seat);
					return;
				}
			}
		});
		unset($seats);

		$selectSeats->map(function (SelectSeatVO $selectSeatVO) {
			if ($selectSeatVO->getSelectedSeat() === null) {
				throw new FreeSeatNotAvailableException;
			}
		});

		$seats = [];
		$selectSeats->map(function (SelectSeatVO $selectSeat) use (&$seats, $selectSeats) {
			if (isset($seats[$selectSeat->getBusIdent()])) {
				if (in_array($selectSeat->getSelectedSeat()->getNumber(), $seats[$selectSeat->getBusIdent()])) {
					$selectSeat->selectNextAvailableSeat($seats[$selectSeat->getBusIdent()]);
				}
				$seats[$selectSeat->getBusIdent()][] = $selectSeat->getSelectedSeat()->getNumber();
			} else {
				$seats[$selectSeat->getBusIdent()] = [ $selectSeat->getSelectedSeat()->getNumber() ];
			}
		});
		unset($seats);

		# pokud nas nezajimaj cisla na jizdenkach, rovnou bookneme
		$selectSeats->map(function (SelectSeatVO $selectSeat) use ($order) {
			if ($selectSeat->getOrderPersonRouteTariff()->getBook() === null and $selectSeat->getRoute()->getLine() and ($selectSeat->getRoute()->getSchedule()->isSeatsWithoutNumbers() or $selectSeat->getRoute()->getLine()->isSeatsWithoutNumbers($selectSeat->getRoute()->getSchedule()->getDirection()))) {
				$book = BookInternal::create(
					$selectSeat->getSelectedSeat(),
					$selectSeat->getRoute()->getDatetimeDeparture(),
					$order
				);
				$book->setOrderPersonRouteTariff($selectSeat->getOrderPersonRouteTariff());
				$selectSeat->getOrderPersonRouteTariff()->setBook($book);
				$selectSeat->setIsBooked(true);
				$book->countPrices();
				$this->bookService->saveBook($book);
				$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($selectSeat->getOrderPersonRouteTariff());
			}
		});

		return $selectSeats;
	}

	/**
	 * @param SelectSeatVO[]|ArrayCollection $selectSeats
	 * @return bool
	 */
	public function needSelectSeats(ArrayCollection $selectSeats)
	{
		return $selectSeats->exists(function ($i, SelectSeatVO $selectSeat) {
			return !$selectSeat->isBooked();
		});
	}

}
