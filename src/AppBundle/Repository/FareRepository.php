<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.04.17
 * Time: 0:00
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Fare;
use Doctrine\ORM\EntityRepository;

class FareRepository extends EntityRepository
{
	public function save(Fare $fare, $flush = true)
	{
		$this->getEntityManager()->persist($fare);
		if ($flush) {
			$this->getEntityManager()->flush();
		}
	}

	public function remove(Fare $fare)
	{
		$this->getEntityManager()->remove($fare);
		$this->getEntityManager()->flush($fare);
	}
}
