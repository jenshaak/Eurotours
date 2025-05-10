<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.05.17
 * Time: 10:54
 */

namespace AppBundle\VO;


use AppBundle\Entity\City;
use AppBundle\Entity\ExternalStation;
use AppBundle\Entity\Line;
use AppBundle\Entity\Station;

/**
 * @deprecated 
 */
class Route
{
	/** @var City */
	private $fromCity;

	/** @var City */
	private $toCity;

	/** @var Station */
	private $fromStation;

	/** @var Station */
	private $toStation;

	/** @var ExternalStation */
	private $fromExternalStation;

	/** @var ExternalStation */
	private $toExternalStation;

	/** @var ScheduleTime */
	private $scheduleTimeDeparture;

	/** @var ScheduleTime */
	private $scheduleTimeArrival;

	/** @var \DateTime */
	private $datetimeDeparture;

	/** @var \DateTime */
	private $datetimeArrival;

	/** @var int */
	private $price;

	/** @var Line */
	private $line;

	/**
	 * @return City
	 */
	public function getFromCity()
	{
		return $this->fromCity;
	}

	/**
	 * @param City $fromCity
	 */
	public function setFromCity($fromCity)
	{
		$this->fromCity = $fromCity;
	}

	/**
	 * @return City
	 */
	public function getToCity()
	{
		return $this->toCity;
	}

	/**
	 * @param City $toCity
	 */
	public function setToCity($toCity)
	{
		$this->toCity = $toCity;
	}

	/**
	 * @return int
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param int $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return ScheduleTime
	 */
	public function getScheduleTimeDeparture()
	{
		return $this->scheduleTimeDeparture;
	}

	/**
	 * @param ScheduleTime $scheduleTimeDeparture
	 */
	public function setScheduleTimeDeparture($scheduleTimeDeparture)
	{
		$this->scheduleTimeDeparture = $scheduleTimeDeparture;
	}

	/**
	 * @return ScheduleTime
	 */
	public function getScheduleTimeArrival()
	{
		return $this->scheduleTimeArrival;
	}

	/**
	 * @param ScheduleTime $scheduleTimeArrival
	 */
	public function setScheduleTimeArrival($scheduleTimeArrival)
	{
		$this->scheduleTimeArrival = $scheduleTimeArrival;
	}

	/**
	 * @return Line
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * @param Line $line
	 */
	public function setLine($line)
	{
		$this->line = $line;
	}

	/**
	 * @return Station
	 */
	public function getFromStation()
	{
		return $this->fromStation;
	}

	/**
	 * @param Station $fromStation
	 */
	public function setFromStation($fromStation)
	{
		$this->fromStation = $fromStation;
	}

	/**
	 * @return Station
	 */
	public function getToStation()
	{
		return $this->toStation;
	}

	/**
	 * @param Station $toStation
	 */
	public function setToStation($toStation)
	{
		$this->toStation = $toStation;
	}

	/**
	 * @return ExternalStation
	 */
	public function getFromExternalStation()
	{
		return $this->fromExternalStation;
	}

	/**
	 * @param ExternalStation $fromExternalStation
	 */
	public function setFromExternalStation($fromExternalStation)
	{
		$this->fromExternalStation = $fromExternalStation;
	}

	/**
	 * @return ExternalStation
	 */
	public function getToExternalStation()
	{
		return $this->toExternalStation;
	}

	/**
	 * @param ExternalStation $toExternalStation
	 */
	public function setToExternalStation($toExternalStation)
	{
		$this->toExternalStation = $toExternalStation;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeDeparture()
	{
		return $this->datetimeDeparture;
	}

	/**
	 * @param \DateTime $datetimeDeparture
	 */
	public function setDatetimeDeparture($datetimeDeparture)
	{
		$this->datetimeDeparture = $datetimeDeparture;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeArrival()
	{
		return $this->datetimeArrival;
	}

	/**
	 * @param \DateTime $datetimeArrival
	 */
	public function setDatetimeArrival($datetimeArrival)
	{
		$this->datetimeArrival = $datetimeArrival;
	}

}