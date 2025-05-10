<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 14:35
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SeatsPlanRepository")
 * @ORM\Table(name="seats_plans")
 */
class SeatsPlan
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="seats_plan_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var Schedule|null
	 * @ORM\ManyToOne(targetEntity="Schedule")
	 * @ORM\JoinColumn(name="schedule_id", referencedColumnName="schedule_id", nullable=true)
	 */
	private $schedule;

	/**
	 * @var \DateTime|null
	 * @ORM\Column(name="date_day", type="date", nullable=true)
	 */
	private $dateDay;

	/**
	 * @var Seat[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Seat", mappedBy="seatsPlan", cascade={"persist", "remove"}, orphanRemoval=true)
	 */
	private $seats;

	public function __construct()
	{
		$this->seats = new ArrayCollection;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return Schedule|null
	 */
	public function getSchedule()
	{
		return $this->schedule;
	}

	/**
	 * @param Schedule|null $schedule
	 */
	public function setSchedule(Schedule $schedule)
	{
		$this->schedule = $schedule;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateDay()
	{
		return $this->dateDay;
	}

	/**
	 * @param \DateTime|null $dateDay
	 */
	public function setDateDay(\DateTime $dateDay)
	{
		$this->dateDay = $dateDay;
	}

	/**
	 * @return Seat[]|ArrayCollection
	 */
	public function getSeats()
	{
		return $this->seats;
	}

	/**
	 * @param Seat[]|ArrayCollection $seats
	 */
	public function setSeats($seats)
	{
		$this->seats = $seats;
	}

	/**
	 * @return ArrayCollection|Seat[]
	 */
	public function getAvailableSeats()
	{
		return $this->getSeats()->filter(function (Seat $seat) {
			return $seat->isAvailable();
		});
	}

	/**
	 * @return int
	 */
	public function getCountOfAvailableSeats()
	{
		return $this->getSeats()->filter(function (Seat $seat) {
			return $seat->isAvailable();
		})->count();
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @return Seat|null
	 */
	public function getSeatForPosition($x, $y)
	{
		$seats = $this->getSeats()->filter(function (Seat $seat) use ($x, $y) {
			return $seat->getPositionX() == $x and $seat->getPositionY() == $y;
		});

		return $seats->isEmpty() ? null : $seats->first();
	}

	/**
	 * @param int $seatNumber
	 * @return Seat|null
	 */
	public function getSeatByNumber($seatNumber)
	{
		$seats = $this->getSeats()->filter(function (Seat $seat) use ($seatNumber) {
			return $seat->getNumber() == $seatNumber;
		});

		return $seats->isEmpty() ? null : $seats->first();
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @return Seat|null
	 */
	public function createSeatForPosition($x, $y, int $floor = 0)
	{
		$seat = new Seat;
		$seat->setPositionX($x);
		$seat->setPositionY($y);
		$seat->setFloor($floor);

		$this->getSeats()->add($seat);

		return $seat;
	}

	/**
	 * @return int
	 */
	public function getCountOfRows()
	{
		$count = 0;

		$this->getSeats()->map(function (Seat $seat) use (&$count) {
			if ($seat->getPositionY() > $count) $count = $seat->getPositionY();
		});

		return $count;
	}

	/**
	 * @return array
	 */
	public function getRowsOfSeats()
	{
		$rows = [];

		$this->getSeats()->map(function (Seat $seat) use (&$rows) {
			if ($seat->getFloor() !== null) {
				if (!isset($rows[$seat->getFloor()])) {
					$rows[$seat->getFloor()] = [];
				}

				if (!isset($rows[$seat->getFloor()][$seat->getPositionY()])) {
					$rows[$seat->getFloor()][$seat->getPositionY()] = [];
				}

				$rows[$seat->getFloor()][$seat->getPositionY()][$seat->getPositionX()] = $seat;

				return;
			}

			if (!isset($rows[0])) {
				$rows[0] = [];
			}

			if (!isset($rows[0][$seat->getPositionY()])) {
				$rows[0][$seat->getPositionY()] = [];
			}

			$rows[0][$seat->getPositionY()][$seat->getPositionX()] = $seat;
		});

		return $rows;
	}

	public function __clone()
	{
		$this->id = null;
		$seats = new ArrayCollection;
		$this->seats->map(function (Seat $seat) use ($seats) {
			$seats->add(clone $seat);
		});
		$this->seats = $seats;
	}

}
