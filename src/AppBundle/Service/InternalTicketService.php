<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 05.03.18
 * Time: 15:54
 */

namespace AppBundle\Service;


use AppBundle\Entity\Carrier;
use AppBundle\Entity\InternalTicket;
use AppBundle\Entity\Schedule;
use AppBundle\Repository\InternalTicketRepository;
use AppBundle\VO\TicketFilter;

class InternalTicketService
{
	/**
	 * @var InternalTicketRepository
	 */
	private $internalTicketRepository;

	public function __construct(InternalTicketRepository $internalTicketRepository)
	{
		$this->internalTicketRepository = $internalTicketRepository;
	}

	/**
	 * @param InternalTicket $internalTicket
	 */
	public function saveInternalTicket(InternalTicket $internalTicket)
	{
		$this->internalTicketRepository->save($internalTicket);
	}

	/**
	 * @param Schedule $schedule
	 * @param \DateTime $dateDay
	 * @param bool $includeCancelled
	 * @return InternalTicket[]
	 */
	public function findInternalTicketsForScheduleAndDay(Schedule $schedule, \DateTime $dateDay, $includeCancelled = false)
	{
		$filter = [
			"schedule" => $schedule,
			"dateDay" => $dateDay
		];

		if (!$includeCancelled) {
			$filter['cancelled'] = false;
		}

		return $this->internalTicketRepository->findBy($filter);
	}

	/**
	 * @param Schedule $schedule
	 * @param bool $includeCancelled
	 * @return InternalTicket[]
	 */
	public function findInternalTicketsForScheduleInFuture(Schedule $schedule, $includeCancelled = false)
	{
		return $this->internalTicketRepository->findInternalTicketsForScheduleInFuture($schedule, $includeCancelled);
	}

	/**
	 * @param Carrier $carrier
	 * @param \DateTime $fromDate
	 * @param \DateTime $toDate
	 * @param bool $includeCancelled
	 * @return InternalTicket[]
	 */
	public function findInternalTicketsForCarrier(Carrier $carrier, \DateTime $fromDate, \DateTime $toDate, $includeCancelled = false)
	{
		return $this->internalTicketRepository->findInternalTicketsForCarrier($carrier, $fromDate, $toDate, $includeCancelled);
	}

	/**
	 * @param int $id
	 * @return InternalTicket|null
	 */
	public function getInternalTicket($id)
	{
		/** @var InternalTicket|null $internalTicket */
		$internalTicket = $this->internalTicketRepository->find($id);
		return $internalTicket;
	}

	/**
	 * @param TicketFilter $filter
	 * @return InternalTicket[]
	 */
	public function findTickets(TicketFilter $filter)
	{
		return $this->internalTicketRepository->findAll();
	}
}
