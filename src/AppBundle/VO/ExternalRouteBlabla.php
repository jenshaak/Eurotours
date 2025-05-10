<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.05.18
 * Time: 16:26
 */

namespace AppBundle\VO;


class ExternalRouteBlabla
{
	/** @var \DateTime */
	private $datetimeUpdated;

	/** @var string */
	private $raceId;

	/** @var array */
	private $seats;

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

	/**
	 * @return string
	 */
	public function getRaceId()
	{
		return $this->raceId;
	}

	/**
	 * @param string $raceId
	 */
	public function setRaceId($raceId)
	{
		$this->raceId = $raceId;
	}

	/**
	 * @return array
	 */
	public function getSeats()
	{
		return $this->seats;
	}

	/**
	 * @param array $seats
	 */
	public function setSeats($seats)
	{
		$this->seats = $seats;
	}

}
