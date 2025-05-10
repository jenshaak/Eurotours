<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.05.18
 * Time: 18:24
 */

namespace AppBundle\Service;


use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Repository\OrderPersonRouteTariffRepository;

class OrderPersonRouteTariffService
{
	/**
	 * @var OrderPersonRouteTariffRepository
	 */
	private $orderPersonRouteTariffRepository;

	public function __construct(OrderPersonRouteTariffRepository $orderPersonRouteTariffRepository)
	{
		$this->orderPersonRouteTariffRepository = $orderPersonRouteTariffRepository;
	}

	public function createOrderPersonRouteTariffs(Order $order)
	{
		$order->getOrderPersons()->map(function (OrderPerson $orderPerson) {
			if ($orderPerson->getRouteTariffThere()) {
				$object = OrderPersonRouteTariff::create($orderPerson, $orderPerson->getRouteTariffThere());
				$this->orderPersonRouteTariffRepository->save($object);
			}

			if ($orderPerson->getRouteTariffBack()) {
				$object = OrderPersonRouteTariff::create($orderPerson, $orderPerson->getRouteTariffBack());
				$this->orderPersonRouteTariffRepository->save($object);
			}
		});
	}

	/**
	 * @param $id
	 * @return OrderPersonRouteTariff|null
	 */
	public function getOrderPersonRouteTariff($id)
	{
		/** @var OrderPersonRouteTariff|null $orderPersonRouteTariff */
		$orderPersonRouteTariff = $this->orderPersonRouteTariffRepository->find($id);
		return $orderPersonRouteTariff;
	}

	public function saveOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$this->orderPersonRouteTariffRepository->save($orderPersonRouteTariff);
	}
}
