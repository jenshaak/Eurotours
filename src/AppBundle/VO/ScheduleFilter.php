<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.11.17
 * Time: 16:20
 */

namespace AppBundle\VO;


use AppBundle\Entity\City;

class ScheduleFilter
{
	/** @var City */
	private $fromCity;

	/** @var City */
	private $toCity;

	/** @var \DateTime */
	private $dateDay;

	/**
	 * @return \DateTime
	 */
	public function getDateDay(): \DateTime
	{
		return $this->dateDay;
	}

	/**
	 * @param \DateTime $dateDay
	 */
	public function setDateDay(\DateTime $dateDay)
	{
		$this->dateDay = $dateDay;
	}

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
}