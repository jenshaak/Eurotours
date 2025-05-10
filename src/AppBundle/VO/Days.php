<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 15:12
 */

namespace AppBundle\VO;


use Doctrine\Common\Collections\ArrayCollection;

class Days
{
	/** @var boolean */
	private $all = false;

	/** @var ArrayCollection|Day[] */
	private $days;

	/** @var ArrayCollection|DayRange[] */
	private $ranges;

	public function __construct()
	{
		$this->days = new ArrayCollection;
		$this->ranges = new ArrayCollection;
	}

	/**
	 * @param Day $day
	 */
	public function addDay(Day $day)
	{
		if (!$this->days->containsKey((string) $day)) {
			$this->setAll(false);
			$this->days->set((string) $day, $day);
		}
	}

	/**
	 * @param DayRange $dayRange
	 */
	public function addRange(DayRange $dayRange)
	{
		if (!$this->ranges->containsKey((string) $dayRange)) {
			$this->setAll(false);
			$this->ranges->set((string) $dayRange, $dayRange);
		}
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public function isDateTime(\DateTime $dateTime)
	{
		if ($this->isAll()) return true;

		$is = $this->days->exists(function ($key, Day $day) use ($dateTime) {
			return $day->isDateTime($dateTime);
		});

		if ($is) return true;

		$is = $this->ranges->exists(function ($key, DayRange $dayRange) use ($dateTime) {
			return $dayRange->isDateTime($dateTime);
		});

		if ($is) return true;

		return false;
	}

	/**
	 * @param boolean $all
	 */
	public function setAll($all)
	{
		$this->all = $all;
		if ($all) {
			$this->days->clear();
			$this->ranges->clear();
		}
	}

	/**
	 * @return boolean
	 */
	public function isAll()
	{
		return $this->all;
	}

	/**
	 * @return Day[]|ArrayCollection
	 */
	public function getDays()
	{
		return $this->days;
	}

	/**
	 * @return DayRange[]|ArrayCollection
	 */
	public function getRanges()
	{
		return $this->ranges;
	}

	/**
	 * @param string $string
	 * @throws \Exception
	 */
	public function addFromString($string)
	{
		if (preg_match("~^(?P<left>[0-9\.]+)\-(?P<right>[0-9\.]+)$~", trim($string, " ."), $buff)) {
			$this->addRange($this->getRangeFromString($buff['left'], $buff['right']));
		} else {
			$this->addDay($this->getDayFromString($string));
		}
	}

	/**
	 * @param string $string
	 * @return Day
	 * @throws \Exception
	 */
	private function getDayFromString($string)
	{
		if (preg_match("~^(?P<day>[0-9]+)\.(?P<month>[0-9]+)\.(?P<year>[0-9]+)$~", trim($string, " ."), $buff)) {
			return new Day($buff['day'], $buff['month'], $buff['year']);
		} elseif (preg_match("~^(?P<day>[0-9]+)\.(?P<month>[0-9]+)$~", trim($string, " ."), $buff)) {
			return new Day($buff['day'], $buff['month']);
		} else {
			throw new \Exception("error parse $string");
		}
	}

	/**
	 * @param string $leftString
	 * @param string $rightString
	 * @return DayRange
	 * @throws \Exception
	 */
	private function getRangeFromString($leftString, $rightString)
	{
		return new DayRange($this->getDayFromString($leftString), $this->getDayFromString($rightString));
	}

	/**
	 * @return string
	 */
	public function getString()
	{
		if ($this->isAll()) return "all";

		$return = "";

		foreach ($this->getRanges() as $range) {
			if ($range->getFrom()->getYear()) {
				$return .= $range->getFrom()->getDay() . "." . $range->getFrom()->getMonth() . "." . $range->getFrom()->getYear() . "-";
				$return .= $range->getTo()->getDay() . "." . $range->getTo()->getMonth() . "." . $range->getTo()->getYear();
			} else {
				$return .= $range->getFrom()->getDay() . "." . $range->getFrom()->getMonth() . "-";
				$return .= $range->getTo()->getDay() . "." . $range->getTo()->getMonth();
			}
			$return .= ",";
		}

		foreach ($this->getDays() as $day) {
			if ($day->getYear()) {
				$return .= $day->getDay() . "." . $day->getMonth() . "." . $day->getYear();
				$return .= ",";
			} else {
				$return .= $day->getDay() . "." . $day->getMonth();
				$return .= ",";
			}
		}

		return trim($return, ",");
	}

	public function clear()
	{
		$this->days->clear();
		$this->ranges->clear();
	}

}