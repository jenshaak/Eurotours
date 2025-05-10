<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 21.06.17
 * Time: 12:34
 */

namespace AppBundle\Repository;


use AppBundle\Entity\OrderPerson;
use Doctrine\ORM\EntityRepository;

class OrderPersonRepository extends EntityRepository
{
	/**
	 * @param OrderPerson $orderPerson
	 */
	public function save(OrderPerson $orderPerson)
	{
		$this->getEntityManager()->persist($orderPerson);
		$this->getEntityManager()->flush($orderPerson);
	}
}
