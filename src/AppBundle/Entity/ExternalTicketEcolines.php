<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-03-18
 * Time: 22:35
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTicketEcolines extends ExternalTicket
{
	/**
	 * @var int[]
	 * @ORM\Column(name="seat_numbers", type="simple_array", nullable=true)
	 */
	protected $seatNumbers = [];

	/**
	 * @var string
	 * @ORM\Column(name="ticket_number", type="string", length=255, nullable=true)
	 */
	protected $ticketIdent;

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketEcolines
	 */
	public static function create(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		return parent::createFromChildren(new self, $route, $routeTariff, $orderPerson);
	}

	/**
	 * @return string
	 */
	public function getTicketIdent()
	{
		return $this->ticketIdent;
	}

	/**
	 * @param string $ticketIdent
	 */
	public function setTicketIdent($ticketIdent)
	{
		$this->ticketIdent = $ticketIdent;
	}

	/**
	 * @return int[]
	 */
	public function getSeatNumbers()
	{
		return $this->seatNumbers;
	}

	/**
	 * @param int[] $seatNumbers
	 */
	public function setSeatNumbers($seatNumbers)
	{
		$this->seatNumbers = $seatNumbers;
	}

}
