<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.05.18
 * Time: 15:08
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BookRegabus extends Book
{
	/**
	 * @var string
	 * @ORM\Column(name="ticket_identifier", type="string", length=255, nullable=true)
	 */
	protected $ticketIdentifier;

	/**
	 * @var ExternalTicketRegabus
	 * @ORM\OneToOne(targetEntity="ExternalTicket")
	 * @ORM\JoinColumn(name="external_ticket_id", referencedColumnName="external_ticket_id", nullable=true)
	 */
	protected $externalTicket;

	/**
	 * @var int
	 * @ORM\Column(name="seat_number", type="integer", nullable=true)
	 */
	protected $seatNumber;

	/**
	 * @var string
	 * @ORM\Column(name="buy_id", type="string", length=255, nullable=true)
	 */
	protected $buyId;

	/**
	 * @param int $seatNumber
	 * @return BookRegabus
	 */
	public static function create($seatNumber, Order $order)
	{
		$book = new BookRegabus;
		$book->setSeatNumber($seatNumber);
		$book->setOrder($order);
		return $book;
	}

	/**
	 * @return string
	 */
	public function getTicketIdentifier()
	{
		return $this->ticketIdentifier;
	}

	/**
	 * @param string $ticketIdentifier
	 */
	public function setTicketIdentifier($ticketIdentifier)
	{
		$this->ticketIdentifier = $ticketIdentifier;
	}

	/**
	 * @return ExternalTicketRegabus
	 */
	public function getExternalTicket()
	{
		return $this->externalTicket;
	}

	/**
	 * @param ExternalTicketRegabus $externalTicket
	 */
	public function setExternalTicket($externalTicket)
	{
		$this->externalTicket = $externalTicket;
	}

	/**
	 * @return int
	 */
	public function getSeatNumber()
	{
		return $this->seatNumber;
	}

	/**
	 * @param int $seatNumber
	 */
	public function setSeatNumber($seatNumber)
	{
		$this->seatNumber = $seatNumber;
	}

	/**
	 * @return string
	 */
	public function getBuyId()
	{
		return $this->buyId;
	}

	/**
	 * @param string $buyId
	 */
	public function setBuyId($buyId)
	{
		$this->buyId = $buyId;
	}

}
