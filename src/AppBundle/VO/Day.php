<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 15:23
 */

namespace AppBundle\VO;


class Day
{
	/** @var int */
	private $day;

	/** @var int */
	private $month;

	/** @var int|null */
	private $year;

	/**
	 * @param \DateTime $dateTime
	 * @return Day
	 */
	public static function createFromDateTime(\DateTime $dateTime)
	{
		return new Day($dateTime->format("j"), $dateTime->format("n"), $dateTime->format("Y"));
	}

	public function __construct($day, $month, $year = null)
	{
		$this->setDay($day);
		$this->setMonth($month);
		$this->setYear($year);
	}

	/**
	 * @return int
	 */
	public function getDay()
	{
		return $this->day;
	}

	/**
	 * @return int
	 */
	public function getDateForSorting()
	{
		if ($this->getYear() === null) {
			return (int) (str_pad($this->getMonth(), 2, "0", STR_PAD_LEFT) . str_pad($this->getDay(), 2, "0", STR_PAD_LEFT));
		} else {
			return (int) ($this->getYear() . str_pad($this->getMonth(), 2, "0", STR_PAD_LEFT) . str_pad($this->getDay(), 2, "0", STR_PAD_LEFT));
		}
	}

	/**
	 * @param int $day
	 */
	public function setDay($day)
	{
		$this->day = (int) $day;
	}

	/**
	 * @return int
	 */
	public function getMonth()
	{
		return $this->month;
	}

	/**
	 * @param int $month
	 */
	public function setMonth($month)
	{
		$this->month = (int) $month;
	}

	/**
	 * @return int|null
	 */
	public function getYear()
	{
		return $this->year;
	}

	/**
	 * @param int|null $year
	 */
	public function setYear($year)
	{
		$this->year = (int) $year;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public function isDateTime(\DateTime $dateTime)
	{
		return $this->getDateTime($dateTime->format("Y"))->format("Ymd") == $dateTime->format("Ymd");
	}

	/**
	 * @param int $year
	 * @return \DateTime
	 */
	public function getDateTime($year)
	{
		$string = $this->getYear() ? $this->getYear() : $year;
		$string .= "-" . $this->getMonth() . "-" . $this->getDay();
		return (new \DateTime($string));
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$return = $this->getDay() . "." . $this->getMonth();
		if ($this->getYear()) {
			$return .= "." . $this->getYear();
		}
		return $return;
	}

}
