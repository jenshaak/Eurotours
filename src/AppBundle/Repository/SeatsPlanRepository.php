<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 15:34
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Schedule;
use AppBundle\Entity\SeatsPlan;
use Doctrine\ORM\EntityRepository;

class SeatsPlanRepository extends EntityRepository
{
	public function save(SeatsPlan $seatsPlan)
	{
		$this->getEntityManager()->persist($seatsPlan);
		$this->getEntityManager()->flush();
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function createSearchQuery()
	{
		$query = $this->createQueryBuilder("sp");
		$query->select("sp");
		return $query;
	}

	/**
	 * @param Schedule $schedule
	 * @return SeatsPlan|null
	 */
	public function getSeatsPlanForSchedule(Schedule $schedule)
	{
		$query = $this->createSearchQuery();

		$query->andWhere("sp.schedule = :schedule")->setParameter("schedule", $schedule);
		$query->andWhere("sp.dateDay IS NULL");

		return $query->getQuery()->getOneOrNullResult();
	}

	/**
	 * @param Schedule $schedule
	 * @param \DateTime $dateDay
	 * @return SeatsPlan|null
	 */
	public function getSeatsPlanForScheduleAndDate(Schedule $schedule, \DateTime $dateDay)
	{
		$query = $this->createSearchQuery();

		$query->andWhere("sp.schedule = :schedule")->setParameter("schedule", $schedule);
		$query->andWhere("sp.dateDay = :dateDay")->setParameter("dateDay", $dateDay->format("Y-m-d"));

		return $query->getQuery()->getOneOrNullResult();
	}

	/**
	 * @param Schedule $schedule
	 * @return SeatsPlan[]
	 */
	public function getSeatsPlansForSchedule(Schedule $schedule)
	{
		$query = $this->createSearchQuery();

		$query->andWhere("sp.schedule = :schedule")->setParameter("schedule", $schedule);

		return $query->getQuery()->getResult();
	}
}
