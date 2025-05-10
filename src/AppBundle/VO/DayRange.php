<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 15:25
 */

namespace AppBundle\VO;


class DayRange
{
	/** @var Day */
	private $from;

	/** @var Day */
	private $to;

	public function __construct(Day $from, Day $to)
	{
		$this->setFrom($from);
		$this->setTo($to);
	}

	/**
	 * @return Day
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param Day $from
	 */
	public function setFrom($from)
	{
		$this->from = $from;
	}

	/**
	 * @return Day
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @param Day $to
	 */
	public function setTo($to)
	{
		$this->to = $to;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public function isBetween(\DateTime $dateTime)
	{
		$format = $this->getFrom()->getYear() ? "Ymd" : "md";
		return (int)$dateTime->format($format) >= $this->getFrom()->getDateForSorting() and (int)$dateTime->format($format) <= $this->getTo()->getDateForSorting();
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public function isDateTime(\DateTime $dateTime)
	{
		return $this->getFrom()->getDateTime($dateTime->format("Y")) <= $dateTime and $this->getTo()->getDateTime($dateTime->format("Y")) >= $dateTime;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->getFrom() . "-" . (string) $this->getTo();
	}

}
