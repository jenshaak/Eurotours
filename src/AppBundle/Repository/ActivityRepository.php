<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-06-03
 * Time: 18:07
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Activity;
use Doctrine\ORM\EntityRepository;

class ActivityRepository extends EntityRepository
{
	public function save(Activity $activity)
	{
		$this->getEntityManager()->persist($activity);
		$this->getEntityManager()->flush($activity);
	}
}
