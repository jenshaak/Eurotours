<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 11:38
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Search;
use Doctrine\ORM\EntityRepository;

class SearchRepository extends EntityRepository
{
	public function save(Search $search)
	{
		$this->getEntityManager()->persist($search);
		$this->getEntityManager()->flush();
	}
}
