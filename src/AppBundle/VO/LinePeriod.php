<?php


namespace AppBundle\VO;


class LinePeriod
{
	/** @var int */
	private $days;

	/** @var \DateTime */
	private $dateBegin;

	/**
	 * @return int
	 */
	public function getDays()
	{
		return $this->days;
	}

	/**
	 * @param int $days
	 */
	public function setDays($days)
	{
		$this->days = $days;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateBegin()
	{
		return $this->dateBegin;
	}

	/**
	 * @param \DateTime $dateBegin
	 */
	public function setDateBegin($dateBegin)
	{
		$this->dateBegin = $dateBegin;
	}

	/**
	 * @param \DateTime $dateDay
	 * @return boolean
	 */
	public function isValidForDate(\DateTime $dateDay)
	{
		return (int) ((new \DateTime($dateDay->format("Y-m-d"))))->diff(new \DateTime($this->dateBegin->format("Y-m-d")))->format("%a") % $this->getDays() === 0;
	}
}
