<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.04.17
 * Time: 0:30
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Tariff;
use Doctrine\ORM\EntityRepository;

class TariffRepository extends EntityRepository
{
	public function save(Tariff $tariff)
	{
		$this->getEntityManager()->persist($tariff);
		$this->getEntityManager()->flush();
	}
}
