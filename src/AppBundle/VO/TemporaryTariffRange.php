<?php


namespace AppBundle\VO;


use AppBundle\Entity\LineStation;

class TemporaryTariffRange
{
	/** @var string */
	private $randomIdent;

	/**
	 * @var \DateTime
	 */
	private $dateFrom;

	/**
	 * @var \DateTime
	 */
	private $dateTo;

	/**
	 * @var int[]|array
	 */
	private $excludeTariffs = [];

	/**
	 * @var bool
	 */
	private $there = false;

	/**
	 * @var bool
	 */
	private $back = false;

	public function __construct()
	{
		$this->randomIdent = rand(10000, 99999) . rand(10000, 99999) . rand(10000, 99999) . rand(10000, 99999);
	}

	/**
	 * @return string
	 */
	public function getRandomIdent()
	{
		return $this->randomIdent;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateFrom()
	{
		return $this->dateFrom;
	}

	/**
	 * @param \DateTime $dateFrom
	 */
	public function setDateFrom($dateFrom)
	{
		$this->dateFrom = $dateFrom;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateTo()
	{
		return $this->dateTo;
	}

	/**
	 * @param \DateTime $dateTo
	 */
	public function setDateTo($dateTo)
	{
		$this->dateTo = $dateTo;
	}

	/**
	 * @return array|int[]
	 */
	public function getExcludeTariffs()
	{
		return $this->excludeTariffs;
	}

	/**
	 * @param array|int[] $excludeTariffs
	 */
	public function setExcludeTariffs($excludeTariffs)
	{
		$this->excludeTariffs = $excludeTariffs;
	}

	/**
	 * @return bool
	 */
	public function isThere()
	{
		return $this->there;
	}

	/**
	 * @param bool $there
	 */
	public function setThere($there)
	{
		$this->there = $there;
	}

	/**
	 * @return bool
	 */
	public function isBack()
	{
		return $this->back;
	}

	/**
	 * @param bool $back
	 */
	public function setBack($back)
	{
		$this->back = $back;
	}

	/**
	 * @param string $randomIdent
	 */
	public function setRandomIdent($randomIdent)
	{
		$this->randomIdent = $randomIdent;
	}

	/**
	 * @return DayRange
	 */
	public function getTariffRange()
	{
		return new DayRange(
			Day::createFromDateTime($this->getDateFrom()),
			Day::createFromDateTime($this->getDateTo())
		);
	}

	/**
	 * @param string $direction
	 * @return bool
	 */
	public function isForDirection($direction)
	{
		if ($direction === LineStation::DIRECTION_THERE) {
			return $this->isThere();
		} elseif ($direction === LineStation::DIRECTION_BACK) {
			return $this->isBack();
		}

		return false;
	}

}
