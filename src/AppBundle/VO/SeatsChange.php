<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 19.07.18
 * Time: 12:09
 */

namespace AppBundle\VO;


use AppBundle\Entity\Schedule;

class SeatsChange
{
	/** @var Schedule */
	private $schedule;

	/** @var \DateTime */
	private $dateDay;

	/** @var int[] */
	private $oldAvailableSeats = [];

	/** @var int[] */
	private $newAvailableSeats = [];

	/**
	 * @return int[]
	 */
	public function getOldAvailableSeats()
	{
		return $this->oldAvailableSeats;
	}

	public function addOldAvailableSeat($seatNumber)
	{
		$this->oldAvailableSeats[] = $seatNumber;
	}

	/**
	 * @return int[]
	 */
	public function getNewAvailableSeats()
	{
		return $this->newAvailableSeats;
	}

	public function addNewAvailableSeat($seatNumber)
	{
		$this->newAvailableSeats[] = $seatNumber;
	}

	/**
	 * @return Schedule
	 */
	public function getSchedule()
	{
		return $this->schedule;
	}

	/**
	 * @param Schedule $schedule
	 */
	public function setSchedule($schedule)
	{
		$this->schedule = $schedule;
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

}
