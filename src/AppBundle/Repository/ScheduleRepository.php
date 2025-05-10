<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 15:46
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Schedule;
use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository
{
	public function save(Schedule $schedule)
	{
		$this->getEntityManager()->persist($schedule);
		$this->getEntityManager()->flush($schedule);
	}
}
