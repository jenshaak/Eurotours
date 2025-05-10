<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 18:49
 */

namespace AppBundle\VO;


class ScheduleTime
{
	/** @var string */
	private $arrivalTime;

	/** @var int */
	private $arrivalDayModify = 0;

	/** @var string */
	private $departureTime;

	/** @var int */
	private $departureDayModify = 0;

	/** @var string */
	private $platform;

	/**
	 * @return string
	 */
	public function getArrivalTime()
	{
		return $this->arrivalTime;
	}

	/**
	 * @param string $arrivalTime
	 */
	public function setArrivalTime($arrivalTime)
	{
		$this->arrivalTime = $arrivalTime;
	}

	/**
	 * @return int
	 */
	public function getArrivalDayModify()
	{
		return $this->arrivalDayModify;
	}

	/**
	 * @param int $arrivalDayModify
	 */
	public function setArrivalDayModify($arrivalDayModify)
	{
		$this->arrivalDayModify = $arrivalDayModify;
	}

	/**
	 * @return string
	 */
	public function getDepartureTime()
	{
		return $this->departureTime;
	}

	/**
	 * @param string $departureTime
	 */
	public function setDepartureTime($departureTime)
	{
		$this->departureTime = $departureTime;
	}

	/**
	 * @return int
	 */
	public function getDepartureDayModify()
	{
		return $this->departureDayModify;
	}

	/**
	 * @param int $departureDayModify
	 */
	public function setDepartureDayModify($departureDayModify)
	{
		$this->departureDayModify = $departureDayModify;
	}

	/**
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	/**
	 * @param string $platform
	 */
	public function setPlatform($platform)
	{
		$this->platform = $platform;
	}

}
