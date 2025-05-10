<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 05.01.18
 * Time: 13:31
 */

namespace AppBundle\VO;


use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\RouteTariff;

class OrderPersonRouteTariff
{
	/** @var OrderPerson */
	private $orderPerson;

	/** @var RouteTariff */
	private $routeTariff;

	/**
	 * @param OrderPerson $orderPerson
	 * @param RouteTariff $routeTariff
	 * @return OrderPersonRouteTariff
	 */
	public static function create(OrderPerson $orderPerson, RouteTariff $routeTariff)
	{
		$orderPersonRouteTariff = new OrderPersonRouteTariff;
		$orderPersonRouteTariff->setOrderPerson($orderPerson);
		$orderPersonRouteTariff->setRouteTariff($routeTariff);
		return $orderPersonRouteTariff;
	}

	/**
	 * @return OrderPerson
	 */
	public function getOrderPerson()
	{
		return $this->orderPerson;
	}

	/**
	 * @param OrderPerson $orderPerson
	 */
	public function setOrderPerson($orderPerson)
	{
		$this->orderPerson = $orderPerson;
	}

	/**
	 * @return RouteTariff
	 */
	public function getRouteTariff()
	{
		return $this->routeTariff;
	}

	/**
	 * @param RouteTariff $routeTariff
	 */
	public function setRouteTariff(RouteTariff $routeTariff)
	{
		$this->routeTariff = $routeTariff;
	}

}
