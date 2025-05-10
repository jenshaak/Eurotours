<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.05.17
 * Time: 17:19
 */

namespace AppBundle\Repository;


use AppBundle\Entity\RouteTariff;
use Doctrine\ORM\EntityRepository;

class RouteTariffRepository extends EntityRepository
{
	public function save(RouteTariff $routeTariff)
	{
		$this->getEntityManager()->persist($routeTariff);
		$this->getEntityManager()->flush($routeTariff);
	}
}
