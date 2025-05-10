<?php


namespace AppBundle\VO;


class ExternalRouteTransTempo
{
	/** @var \DateTime */
	private $datetimeUpdated;

	/** @var array */
	private $seats;

	/** @var string[] */
	private $seatsBooked;

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

	/**
	 * @return string[]
	 */
	public function getSeatsBooked()
	{
		return $this->seatsBooked;
	}

	/**
	 * @param string[] $seatsBooked
	 */
	public function setSeatsBooked($seatsBooked)
	{
		$this->seatsBooked = $seatsBooked;
	}
}
