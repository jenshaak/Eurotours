<?php


namespace AppBundle\VO;


use AppBundle\Entity\Order;

interface BookBusSystemInterface
{
	public function getTicketIdentifier();
	public function getExternalTicket();
	public function getSeatNumber();
	public function getBookingId(): string;
	/**
	 * @return Order
	 */
	public function getOrder();

}
