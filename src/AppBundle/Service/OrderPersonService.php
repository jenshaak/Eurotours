<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 21.06.17
 * Time: 12:33
 */

namespace AppBundle\Service;


use AppBundle\Entity\OrderPerson;
use AppBundle\Repository\OrderPersonRepository;

class OrderPersonService
{
	/**
	 * @var OrderPersonRepository
	 */
	private $orderPersonRepository;

	public function __construct(OrderPersonRepository $orderPersonRepository)
	{
		$this->orderPersonRepository = $orderPersonRepository;
	}

	/**
	 * @param OrderPerson $orderPerson
	 */
	public function saveOrderPerson(OrderPerson $orderPerson)
	{
		$this->orderPersonRepository->save($orderPerson);
	}
}