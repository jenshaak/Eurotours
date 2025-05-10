<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.18
 * Time: 15:45
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BookInternal extends Book
{
	/**
	 * @var SeatsPlan
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\SeatsPlan")
	 * @ORM\JoinColumn(name="seats_plan_id", referencedColumnName="seats_plan_id", nullable=true)
	 */
	private $seatsPlan;

	/**
	 * @var Seat
	 * @ORM\ManyToOne(targetEntity="Seat")
	 * @ORM\JoinColumn(name="seats_id", referencedColumnName="seat_id", nullable=true)
	 */
	private $seat;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="date_day", type="date", nullable=true)
	 */
	private $dateDay;

	/**
	 * @var int
	 * @ORM\Column(name="seat_number", type="integer", nullable=true)
	 */
	private $seatNumber;

	/**
	 * @var InternalTicket|null
	 * @ORM\OneToOne(targetEntity="AppBundle\Entity\InternalTicket", inversedBy="book")
	 * @ORM\JoinColumn(name="internal_ticket_id", referencedColumnName="internal_ticket_id", nullable=true)
	 */
	private $internalTicket;

	public static function create(Seat $seat, \DateTime $dateDay, Order $order)
	{
		$book = BookInternal::createWithoutSeat($dateDay, $order);
		$book->setSeatsPlan($seat->getSeatsPlan());
		$book->setSeat($seat);
		$book->setSeatNumber($seat->getNumber());
		return $book;
	}

	public static function createWithoutSeat(\DateTime $dateDay, Order $order)
	{
		$book = new BookInternal;
		$book->setDateDay($dateDay);
		$book->setOrder($order);
		return $book;
	}

	/**
	 * @return SeatsPlan
	 */
	public function getSeatsPlan()
	{
		return $this->seatsPlan;
	}

	/**
	 * @param SeatsPlan $seatsPlan
	 */
	public function setSeatsPlan($seatsPlan)
	{
		$this->seatsPlan = $seatsPlan;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateDay()
	{
		return $this->dateDay;
	}

	/**
	 * @param \DateTime $dateDay
	 */
	public function setDateDay($dateDay)
	{
		$this->dateDay = $dateDay;
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
	 * @return InternalTicket|null
	 */
	public function getInternalTicket()
	{
		return $this->internalTicket;
	}

	/**
	 * @param InternalTicket|null $internalTicket
	 */
	public function setInternalTicket($internalTicket)
	{
		$this->internalTicket = $internalTicket;
	}

	/**
	 * @return Seat
	 */
	public function getSeat()
	{
		return $this->seat;
	}

	/**
	 * @param Seat $seat
	 */
	public function setSeat($seat)
	{
		$this->seat = $seat;
	}
}
