<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 13:58
 */

namespace AppBundle\Repository;


use AppBundle\Entity\City;
use Doctrine\ORM\EntityRepository;

class CityRepository extends EntityRepository
{
	public function save(City $city)
	{
		$this->getEntityManager()->persist($city);
		$this->getEntityManager()->flush($city);
	}
}
