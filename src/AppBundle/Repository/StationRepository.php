<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:43
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Station;
use Doctrine\ORM\EntityRepository;

class StationRepository extends EntityRepository
{
	public function save(Station $station)
	{
		$this->getEntityManager()->persist($station);
		$this->getEntityManager()->flush($station);
	}
}
