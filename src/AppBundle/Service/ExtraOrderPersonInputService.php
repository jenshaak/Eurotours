<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.05.18
 * Time: 18:26
 */

namespace AppBundle\Service;


use AppBundle\Entity\Order;
use AppBundle\Entity\Route;
use AppBundle\VO\ExtraOrderPersonInput;
use Doctrine\Common\Collections\ArrayCollection;

class ExtraOrderPersonInputService
{
	/**
	 * @param Order $order
	 * @return array|string[]
	 */
	public function getInputsForOrder(Order $order)
	{
		$inputs = new ArrayCollection;

		$order->getRoutes()->map(function (Route $route) use ($inputs) {
			foreach ($this->getInputsForRoute($route) as $input) {
				$inputs->set($input, $input);
			}
		});

		return $inputs->toArray();
	}

	/**
	 * @param Route $route
	 * @return string[]|array
	 */
	private function getInputsForRoute(Route $route)
	{
		if ($route->getCarrier()->getCode() === "Ni") {
			return [ ExtraOrderPersonInput::DATE_BIRTH ];
		}

		return [];
	}
}
