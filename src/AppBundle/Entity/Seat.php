<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.02.18
 * Time: 14:38
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SeatRepository")
 * @ORM\Table(name="seats")
 */
class Seat
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="seat_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var SeatsPlan
	 * @ORM\ManyToOne(targetEntity="SeatsPlan", inversedBy="seats")
	 * @ORM\JoinColumn(name="seats_plan_id", referencedColumnName="seats_plan_id", nullable=false)
	 */
	private $seatsPlan;

	/**
	 * @var int
	 * @ORM\Column(name="number", type="integer", nullable=false)
	 */
	private $number;

	/**
	 * @var int
	 * @ORM\Column(name="position_x", type="integer", nullable=false)
	 */
	private $positionX;

	/**
	 * @var int
	 * @ORM\Column(name="position_y", type="integer", nullable=false)
	 */
	private $positionY;

	/**
	 * @var boolean
	 * @ORM\Column(name="available", type="boolean", nullable=false)
	 */
	private $available;

	/**
	 * @ORM\Column(name="floor", type="integer", nullable=true)
	 */
	private ?int $floor = null;

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
	 * @return int|null
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @param int|null $number
	 */
	public function setNumber($number)
	{
		$this->number = $number;
	}

	/**
	 * @return int|null
	 */
	public function getPositionX()
	{
		return $this->positionX;
	}

	/**
	 * @param int|null $positionX
	 */
	public function setPositionX($positionX)
	{
		$this->positionX = $positionX;
	}

	/**
	 * @return int|null
	 */
	public function getPositionY()
	{
		return $this->positionY;
	}

	/**
	 * @param int|null $positionY
	 */
	public function setPositionY($positionY)
	{
		$this->positionY = $positionY;
	}

	/**
	 * @return bool
	 */
	public function isAvailable()
	{
		return $this->available;
	}

	/**
	 * @param bool $available
	 */
	public function setAvailable($available)
	{
		$this->available = $available;
	}

	public function getFloor(): ?int
	{
		return $this->floor;
	}

	public function setFloor(?int $floor): void
	{
		$this->floor = $floor;
	}

	public function __clone()
	{
		$this->id = null;
		$this->seatsPlan = null;
	}
}
