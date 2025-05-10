<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-04-05
 * Time: 11:04
 */

namespace AppBundle\VO;

/**
 * @deprecated
 */
class ExternalRouteSvitgo
{
	/** @var int */
	private $routeNameId;

	/** @var int */
	private $busesId;

	/** @var int */
	private $ferrymanId;

	/** @var int[] */
	private $seats;

	/**
	 * @return int
	 */
	public function getRouteNameId()
	{
		return $this->routeNameId;
	}

	/**
	 * @param int $routeNameId
	 */
	public function setRouteNameId($routeNameId)
	{
		$this->routeNameId = $routeNameId;
	}

	/**
	 * @return int
	 */
	public function getBusesId()
	{
		return $this->busesId;
	}

	/**
	 * @param int $busesId
	 */
	public function setBusesId($busesId)
	{
		$this->busesId = $busesId;
	}

	/**
	 * @return int
	 */
	public function getFerrymanId()
	{
		return $this->ferrymanId;
	}

	/**
	 * @param int $ferrymanId
	 */
	public function setFerrymanId($ferrymanId)
	{
		$this->ferrymanId = $ferrymanId;
	}

	/**
	 * @return int[]
	 */
	public function getSeats()
	{
		return $this->seats;
	}

	/**
	 * @param int[] $seats
	 */
	public function setSeats($seats)
	{
		$this->seats = $seats;
	}
}
