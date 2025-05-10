<?php


namespace AppBundle\Entity;

use AppBundle\VO\BookBusSystemInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BookInfobus extends Book implements BookBusSystemInterface
{
	/**
	 * @var string
	 * @ORM\Column(name="ticket_identifier", type="string", length=255, nullable=true)
	 */
	protected $ticketIdentifier;

	/**
	 * @var ExternalTicketBusSystem
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
	 * @ORM\Column(name="booking_id", type="string", length=255, nullable=true)
	 */
	protected $bookingId;

	/**
	 * @param int $seatNumber
	 * @return BookInfobus
	 */
	public static function create($seatNumber, Order $order)
	{
		$book = new BookInfobus;
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
	 * @return ExternalTicketBusSystem
	 */
	public function getExternalTicket()
	{
		return $this->externalTicket;
	}

	/**
	 * @param ExternalTicketBusSystem $externalTicket
	 */
	public function setExternalTicket($externalTicket): void
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
	public function getBookingId(): string
	{
		return $this->bookingId;
	}

	/**
	 * @param string $bookingId
	 */
	public function setBookingId(string $bookingId): void
	{
		$this->bookingId = $bookingId;
	}
}
