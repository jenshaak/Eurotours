<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 10.07.17
 * Time: 17:10
 */

namespace AppBundle\Repository;


use AppBundle\Entity\RouteCombination;
use Doctrine\ORM\EntityRepository;

class RouteCombinationRepository extends EntityRepository
{
	public function save(RouteCombination $routeCombination)
	{
		$this->getEntityManager()->persist($routeCombination);
		$this->getEntityManager()->flush();
	}
}
