<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 24.09.18
 * Time: 19:43
 */

namespace AppBundle\VO;


class ExternalRouteEcolines
{
	/** @var array */
	private $legs;

	/** @var \DateTime */
	private $datetimeUpdated;

	/**
	 * @return array
	 */
	public function getLegs()
	{
		return $this->legs;
	}

	/**
	 * @param array $legs
	 */
	public function setLegs($legs)
	{
		$this->legs = $legs;
	}

	public function addLegAndSeats($leg, $seats)
	{
		$leg->seats = $seats;
		$this->legs[] = $leg;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeUpdated()
	{
		return $this->datetimeUpdated;
	}

	/**
	 * @param \DateTime $datetimeUpdated
	 */
	public function setDatetimeUpdated($datetimeUpdated)
	{
		$this->datetimeUpdated = $datetimeUpdated;
	}

}
