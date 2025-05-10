<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 13:57
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Line;
use AppBundle\VO\LineFilter;
use Doctrine\ORM\EntityRepository;

class LineRepository extends EntityRepository
{
	public function save(Line $line)
	{
		$this->getEntityManager()->persist($line);
		$this->getEntityManager()->flush($line);
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function createSearchQuery()
	{
		$query = $this->createQueryBuilder("l");
		$query->join("l.lineStations", "lsFrom");
		$query->join("l.lineStations", "lsTo");
		$query->select("l");
		return $query;
	}

	/**
	 * @param LineFilter $filter
	 * @return Line[]
	 */
	public function findLines(LineFilter $filter)
	{
		$query = $this->createSearchQuery();

		if ($filter->getFromCity() and $filter->getToCity()) {
			$query->andWhere("lsFrom.city = :fromCity AND lsTo.city = :toCity")
				->setParameter("fromCity", $filter->getFromCity())
				->setParameter("toCity", $filter->getToCity());
		}

		if ($filter->getCarrier()) {
			$query->andWhere("l.carrier = :carrier")->setParameter("carrier", $filter->getCarrier());
		}

		if ($filter->isOnlyShow()) {
			$query->andWhere("l.hidden = :hidden")->setParameter("hidden", false);
		}

		$query->andWhere("l.deleted = :deleted")->setParameter("deleted", false);

		return $query->getQuery()->getResult();
	}

}
