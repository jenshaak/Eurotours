<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.05.17
 * Time: 10:45
 */

namespace AppBundle\VO;


use AppBundle\Entity\City;
use AppBundle\Entity\Search;

class RouteFilter
{
	const TYPE_ONE_WAY = "one-way";
	const TYPE_RETURN = "return";
	const TYPE_RETURN_OPEN = "return-open";

	/** @var City */
	private $fromCity;

	/** @var City */
	private $toCity;

	/** @var \DateTime */
	private $dateDay;

	/** @var \DateTime */
	private $dateBack;

	/** @var string */
	private $type = self::TYPE_ONE_WAY;

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

	/**
	 * @return bool
	 */
	public function isReady()
	{
		return $this->getFromCity() instanceof City
			and $this->getToCity() instanceof City
			and $this->getDateDay() instanceof \DateTime;
	}

	/**
	 * @return LineFilter
	 */
	public function createLineFilterThere()
	{
		$lineFilter = new LineFilter;

		$lineFilter->setFromCity($this->getFromCity());
		$lineFilter->setToCity($this->getToCity());

		return $lineFilter;
	}

	/**
	 * @return LineFilter
	 */
	public function createLineFilterBack()
	{
		$lineFilter = new LineFilter;

		$lineFilter->setFromCity($this->getToCity());
		$lineFilter->setToCity($this->getFromCity());

		return $lineFilter;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateBack()
	{
		return $this->dateBack;
	}

	/**
	 * @param \DateTime $dateBack
	 */
	public function setDateBack($dateBack)
	{
		$this->dateBack = $dateBack;
	}

	/**
	 * @return Search
	 */
	public function createSearch()
	{
		$search = new Search;
		$search->setFromCity($this->getFromCity());
		$search->setToCity($this->getToCity());
		$search->setDateDay($this->getDateDay());
		$search->setDateBack($this->getDateBack());
		$search->setType($this->getType());

		return $search;
	}

}