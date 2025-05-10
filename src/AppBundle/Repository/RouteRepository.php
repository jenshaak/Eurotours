<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 16:27
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Route;
use Doctrine\ORM\EntityRepository;

class RouteRepository extends EntityRepository
{
	public function save(Route $route)
	{
		$this->getEntityManager()->persist($route);
		$this->getEntityManager()->flush();
	}
}
