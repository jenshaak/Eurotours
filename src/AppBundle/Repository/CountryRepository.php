<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 13:58
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Country;
use Doctrine\ORM\EntityRepository;

class CountryRepository extends EntityRepository
{
	public function save(Country $country)
	{
		$this->getEntityManager()->persist($country);
		$this->getEntityManager()->flush($country);
	}
}
