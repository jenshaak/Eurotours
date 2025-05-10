<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 16:49
 */

namespace AppBundle\Repository;


use AppBundle\Entity\SearchExternal;
use Doctrine\ORM\EntityRepository;

class SearchExternalRepository extends EntityRepository
{
	public function save(SearchExternal $searchExternal)
	{
		$this->getEntityManager()->persist($searchExternal);
		$this->getEntityManager()->flush();
	}
}
