<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.06.17
 * Time: 20:34
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Payment;
use Doctrine\ORM\EntityRepository;

class PaymentRepository extends EntityRepository
{
	/**
	 * @param Payment $payment
	 */
	public function save(Payment $payment)
	{
		$this->getEntityManager()->persist($payment);
		$this->getEntityManager()->flush($payment);
	}
}
