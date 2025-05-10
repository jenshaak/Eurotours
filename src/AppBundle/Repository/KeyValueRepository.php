<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.01.17
 * Time: 11:26
 */

namespace AppBundle\Repository;


use AppBundle\Entity\KeyValue;
use Doctrine\ORM\EntityRepository;

class KeyValueRepository extends EntityRepository
{
	/**
	 * @param KeyValue $keyValue
	 */
	public function save(KeyValue $keyValue)
	{
		$this->getEntityManager()->persist($keyValue);
		$this->getEntityManager()->flush($keyValue);
	}
}
