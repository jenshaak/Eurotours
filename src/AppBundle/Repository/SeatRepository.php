<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 15:57
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Seat;
use Doctrine\ORM\EntityRepository;

class SeatRepository extends EntityRepository
{
	public function save(Seat $seat)
	{
		$this->getEntityManager()->persist($seat);
		$this->getEntityManager()->flush($seat);
	}
}
