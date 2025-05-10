<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.05.18
 * Time: 17:54
 */

namespace AppBundle\Repository;


use AppBundle\Entity\OrderPersonRouteTariff;
use Doctrine\ORM\EntityRepository;

class OrderPersonRouteTariffRepository extends EntityRepository
{
	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 */
	public function save(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$this->getEntityManager()->persist($orderPersonRouteTariff);
		$this->getEntityManager()->flush($orderPersonRouteTariff);
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 */
	public function remove(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$this->getEntityManager()->remove($orderPersonRouteTariff);
		$this->getEntityManager()->flush($orderPersonRouteTariff);
	}
}
