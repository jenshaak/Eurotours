<?php


namespace AppBundle\Repository;


use AppBundle\Entity\SeoCityCombination;
use Doctrine\ORM\EntityRepository;

class SeoCityCombinationRepository extends EntityRepository
{
	public function save(SeoCityCombination $seoCityCombination)
	{
		$this->getEntityManager()->persist($seoCityCombination);
		$this->getEntityManager()->flush($seoCityCombination);
	}
}
