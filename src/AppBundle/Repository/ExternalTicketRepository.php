<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.09.17
 * Time: 20:16
 */

namespace AppBundle\Repository;


use AppBundle\Entity\ExternalTicket;
use Doctrine\ORM\EntityRepository;

class ExternalTicketRepository extends EntityRepository
{
	public function save(ExternalTicket $externalTicket)
	{
		$this->getEntityManager()->persist($externalTicket);
		$this->getEntityManager()->flush($externalTicket);
	}
}
