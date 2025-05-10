<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BookTransTempo extends Book
{
	/**
	 * @var int
	 * @ORM\Column(name="seat_number", type="integer", nullable=true)
	 */
	protected $seatNumber;

	/**
	 * @var ExternalTicketTransTempo
	 * @ORM\OneToOne(targetEntity="ExternalTicket")
	 * @ORM\JoinColumn(name="external_ticket_id", referencedColumnName="external_ticket_id", nullable=true)
	 */
	protected $externalTicket;

	/**
	 * @param int $seatNumber
	 * @return BookRegabus
	 */
	public static function create($seatNumber, Order $order)
	{
		$book = new BookTransTempo;
		$book->setSeatNumber($seatNumber);
		$book->setOrder($order);
		return $book;
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
	 * @return ExternalTicketTransTempo
	 */
	public function getExternalTicket(): ExternalTicketTransTempo
	{
		return $this->externalTicket;
	}

	/**
	 * @param ExternalTicketTransTempo $externalTicket
	 */
	public function setExternalTicket(ExternalTicketTransTempo $externalTicket): void
	{
		$this->externalTicket = $externalTicket;
	}
}
