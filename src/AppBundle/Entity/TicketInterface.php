<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 09.04.18
 * Time: 15:18
 */

namespace AppBundle\Entity;


interface TicketInterface
{
	/**
	 * @return OrderPerson
	 */
	public function getOrderPerson();

	/**
	 * @return string
	 */
	public function getFile();

	/**
	 * @return Route
	 */
	public function getRoute();

	/**
	 * @return RouteTariff
	 */
	public function getRouteTariff();

	/**
	 * @return string
	 */
	public function getContentType();

	/**
	 * @return boolean
	 */
	public function isCancelled();

	/**
	 * @return Order
	 */
	public function getOrder();

	/**
	 * @return Carrier
	 */
	public function getCarrier();

	/**
	 * @return string|null
	 */
	public function getCarrierTitle();
}
