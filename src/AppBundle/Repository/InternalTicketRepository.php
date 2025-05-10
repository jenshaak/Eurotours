<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 05.03.18
 * Time: 15:54
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Carrier;
use AppBundle\Entity\InternalTicket;
use AppBundle\Entity\Schedule;
use Doctrine\ORM\EntityRepository;

class InternalTicketRepository extends EntityRepository
{
	public function save(InternalTicket $internalTicket)
	{
		$this->getEntityManager()->persist($internalTicket);
		$this->getEntityManager()->flush($internalTicket);
	}

	/**
	 * @param Schedule $schedule
	 * @return InternalTicket[]
	 */
	public function findInternalTicketsForScheduleInFuture(Schedule $schedule, $includeCancelled)
	{
		$query = $this->createSearchQuery($includeCancelled);

		$query->andWhere("it.dateDay >= :dateDay")->setParameter("dateDay", (new \DateTime)->format("Y-m-d"));
		$query->andWhere("it.schedule = :schedule")->setParameter("schedule", $schedule);

		return $query->getQuery()->getResult();
	}

	/**
	 * @return InternalTicket[]
	 */
	public function findInternalTicketsForCarrier(Carrier $carrier, \DateTime $fromDate, \DateTime $toDate, $includeCancelled)
	{
		$query = $this->createSearchQuery($includeCancelled);

		$query->andWhere("it.dateDay >= :fromDate")->setParameter("fromDate", $fromDate);
		$query->andWhere("it.dateDay <= :toDate")->setParameter("toDate", $toDate);
		$query->andWhere("it.carrier = :carrier")->setParameter("carrier", $carrier);

		return $query->getQuery()->getResult();
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function createSearchQuery($includeCancelled)
	{
		$query = $this->createQueryBuilder("it");
		$query->select("it");

		if (!$includeCancelled) {
			$query->andWhere("it.cancelled = :cancelled")->setParameter("cancelled", false);
		}

		return $query;
	}
}
