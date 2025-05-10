<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.05.17
 * Time: 10:58
 */

namespace AppBundle\VO;


use AppBundle\Entity\Carrier;
use AppBundle\Entity\City;

class LineFilter
{
	/** @var City */
	private $fromCity;

	/** @var City */
	private $toCity;

	/**
	 * @var Carrier|null
	 */
	private $carrier;

	/**
	 * @var boolean
	 */
	private $onlyShow = false;

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
	 * @return Carrier|null
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}

	/**
	 * @param Carrier|null $carrier
	 */
	public function setCarrier($carrier)
	{
		$this->carrier = $carrier;
	}

	/**
	 * @return bool
	 */
	public function isOnlyShow(): bool
	{
		return $this->onlyShow;
	}

	/**
	 * @param bool $onlyShow
	 */
	public function setOnlyShow(bool $onlyShow): void
	{
		$this->onlyShow = $onlyShow;
	}

}
