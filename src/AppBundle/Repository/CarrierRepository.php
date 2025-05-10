<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 13:59
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Carrier;
use Doctrine\ORM\EntityRepository;

class CarrierRepository extends EntityRepository
{
	public function save(Carrier $carrier)
	{
		$this->getEntityManager()->persist($carrier);
		$this->getEntityManager()->flush($carrier);
	}
	
	public function findAllDeleted()
	{
		return $this->createQueryBuilder('c')
			->where('c.deleted = :deleted')
			->setParameter('deleted', true)
			->orderBy('c.name', 'ASC')
			->getQuery()
			->getResult();
	}
}
