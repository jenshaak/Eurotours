<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 24.09.18
 * Time: 21:38
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BookEcolines extends Book
{
	/**
	 * @var string
	 * @ORM\Column(name="booking_id", type="string", length=255, nullable=true)
	 */
	protected $bookingId;

	/**
	 * @var string
	 * @ORM\Column(name="ticket_id", type="string", length=255, nullable=true)
	 */
	protected $ticketId;

	/**
	 * @var ExternalTicketEcolines
	 * @ORM\OneToOne(targetEntity="ExternalTicket")
	 * @ORM\JoinColumn(name="external_ticket_id", referencedColumnName="external_ticket_id", nullable=true)
	 */
	protected $externalTicket;

	/**
	 * @var int[]
	 * @ORM\Column(name="seat_numbers", type="simple_array", nullable=true)
	 */
	protected $seatNumbers = [];

	/**
	 * @param int[] $seatNumbers
	 * @return BookEcolines
	 */
	public static function create($seatNumbers, Order $order)
	{
		$book = new BookEcolines;
		$book->setSeatNumbers($seatNumbers);
		$book->setOrder($order);
		return $book;
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

	/**
	 * @return string
	 */
	public function getBookingId()
	{
		return $this->bookingId;
	}

	/**
	 * @param string $bookingId
	 */
	public function setBookingId($bookingId)
	{
		$this->bookingId = $bookingId;
	}

	/**
	 * @return string
	 */
	public function getTicketId()
	{
		return $this->ticketId;
	}

	/**
	 * @param string $ticketId
	 */
	public function setTicketId($ticketId)
	{
		$this->ticketId = $ticketId;
	}

	/**
	 * @return ExternalTicketEcolines
	 */
	public function getExternalTicket()
	{
		return $this->externalTicket;
	}

	/**
	 * @param ExternalTicketEcolines $externalTicket
	 */
	public function setExternalTicket($externalTicket)
	{
		$this->externalTicket = $externalTicket;
	}

}
