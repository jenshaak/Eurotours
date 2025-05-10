<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 18:58
 */

namespace AppBundle\Repository;


use AppBundle\Entity\ScheduleLineStation;
use Doctrine\ORM\EntityRepository;

class ScheduleLineStationRepository extends EntityRepository
{
	public function save(ScheduleLineStation $scheduleLineStation)
	{
		$this->getEntityManager()->persist($scheduleLineStation);
		$this->getEntityManager()->flush($scheduleLineStation);
	}

	public function remove(ScheduleLineStation $scheduleLineStation)
	{
		$this->getEntityManager()->remove($scheduleLineStation);
		$this->getEntityManager()->flush($scheduleLineStation);
	}
}
