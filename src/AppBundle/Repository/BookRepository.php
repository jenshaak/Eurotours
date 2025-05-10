<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.01.18
 * Time: 10:59
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Book;
use AppBundle\Entity\BookInternal;
use AppBundle\Entity\Carrier;
use AppBundle\Entity\SeatsPlan;
use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository
{
	public function save(Book $book)
	{
		$this->getEntityManager()->persist($book);
		$this->getEntityManager()->flush($book);
	}

	/**
	 * @param SeatsPlan $seatsPlan
	 * @param \DateTime $dateDay
	 * @return BookInternal[]
	 */
	public function findInternalBooksForSeatsPlanAndDay(SeatsPlan $seatsPlan, \DateTime $dateDay)
	{
		$query = $this->createSearchQuery();

		$query->innerJoin(BookInternal::class, "bi", "WITH", "bi.id = b.id");
		$query->join("b.order", "o");
		$query->andWhere("b INSTANCE OF AppBundle\\Entity\\BookInternal");
		$query->andWhere("bi.seatsPlan = :seatsPlan")->setParameter("seatsPlan", $seatsPlan);
		$query->andWhere("bi.dateDay = :dateDay")->setParameter("dateDay", $dateDay->format("Y-m-d"));
		$query->andWhere("b.cancelled = :cancelled")->setParameter("cancelled", false);
		$query->andWhere("bi.internalTicket IS NULL");
		$query->andWhere("(o.dateReservationDay IS NULL OR o.dateReservationDay > :now)")->setParameter("now", new \DateTime("-24 hours"));

		return $query->getQuery()->getResult();
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function createSearchQuery()
	{
		$query = $this->createQueryBuilder("b");
		$query->select("b");
		return $query;
	}

	/**
	 * @param Carrier $carrier
	 * @return BookInternal[]
	 */
	public function findInternalBooksForCarrier(Carrier $carrier, \DateTime $fromDate, \DateTime $toDate, $includeCancelled = false)
	{
		$query = $this->createSearchQuery();

		$query->innerJoin(BookInternal::class, "bi", "WITH", "bi.id = b.id");
		$query->andWhere("b INSTANCE OF AppBundle\\Entity\\BookInternal");
		$query->andWhere("bi.dateDay >= :fromDate")->setParameter("fromDate", $fromDate);
		$query->andWhere("bi.dateDay <= :toDate")->setParameter("toDate", $toDate);

		if (!$includeCancelled) {
			$query->andWhere("bi.cancelled = :cancelled")->setParameter("cancelled", false);
		}

		# TODO: Zajistit filtraci lepe!!

		return array_filter($query->getQuery()->getResult(), function (BookInternal $book) use ($carrier) {
			return $book->getSeatsPlan() and $book->getSeatsPlan()->getSchedule()->getLine()->getCarrier() === $carrier;
		});
	}
}
