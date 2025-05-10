<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 17.05.18
 * Time: 12:09
 */

namespace AppBundle\VO;


use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\Seat;
use AppBundle\Entity\SeatsPlan;

class SelectSeatVO
{
	/** @var SeatsPlan */
	private $seatsPlan;

	/** @var \AppBundle\Entity\OrderPersonRouteTariff */
	private $orderPersonRouteTariff;

	/** @var \AppBundle\Entity\Route */
	private $route;

	/** @var OrderPerson */
	private $orderPerson;

	/** @var null|Seat */
	private $selectedSeat = null;

	/** @var bool */
	private $isBooked = false;

	/** @var array|int[] */
	private $notAvailableSeatNumbers = [];

	/** @var string|null */
	private $busIdent;

	/**
	 * @param SeatsPlan $seatsPlan
	 * @param \AppBundle\Entity\OrderPersonRouteTariff $orderPersonRouteTariff
	 * @return SelectSeatVO
	 */
	public static function create(SeatsPlan $seatsPlan, \AppBundle\Entity\OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$selectSeat = new SelectSeatVO;
		$selectSeat->setSeatsPlan($seatsPlan);
		$selectSeat->setOrderPerson($orderPersonRouteTariff->getOrderPerson());
		$selectSeat->setRoute($orderPersonRouteTariff->getRoute());
		$selectSeat->setOrderPersonRouteTariff($orderPersonRouteTariff);
		return $selectSeat;
	}

	/**
	 * @return SeatsPlan
	 */
	public function getSeatsPlan()
	{
		return $this->seatsPlan;
	}

	/**
	 * @param SeatsPlan $seatsPlan
	 */
	public function setSeatsPlan($seatsPlan)
	{
		$this->seatsPlan = $seatsPlan;
	}

	/**
	 * @return \AppBundle\Entity\OrderPersonRouteTariff
	 */
	public function getOrderPersonRouteTariff()
	{
		return $this->orderPersonRouteTariff;
	}

	/**
	 * @param \AppBundle\Entity\OrderPersonRouteTariff $orderPersonRouteTariff
	 */
	public function setOrderPersonRouteTariff($orderPersonRouteTariff)
	{
		$this->orderPersonRouteTariff = $orderPersonRouteTariff;
	}

	/**
	 * @return \AppBundle\Entity\Route
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * @param \AppBundle\Entity\Route $route
	 */
	public function setRoute($route)
	{
		$this->route = $route;
	}

	/**
	 * @return OrderPerson
	 */
	public function getOrderPerson()
	{
		return $this->orderPerson;
	}

	/**
	 * @param OrderPerson $orderPerson
	 */
	public function setOrderPerson($orderPerson)
	{
		$this->orderPerson = $orderPerson;
	}

	/**
	 * @return Seat|null
	 */
	public function getSelectedSeat()
	{
		return $this->selectedSeat;
	}

	/**
	 * @param Seat|null $selectedSeat
	 */
	public function setSelectedSeat($selectedSeat)
	{
		$this->selectedSeat = $selectedSeat;
	}

	/**
	 * @return bool
	 */
	public function isBooked()
	{
		return $this->isBooked;
	}

	/**
	 * @param bool $isBooked
	 */
	public function setIsBooked($isBooked)
	{
		$this->isBooked = $isBooked;
	}

	/**
	 * @return array|int[]
	 */
	public function getNotAvailableSeatNumbers()
	{
		return $this->notAvailableSeatNumbers;
	}

	/**
	 * @param array|int[] $notAvailableSeatNumbers
	 */
	public function setNotAvailableSeatNumbers($notAvailableSeatNumbers)
	{
		$this->notAvailableSeatNumbers = $notAvailableSeatNumbers;
	}

	/**
	 * @param int $seatNumber
	 */
	public function addNotAvailableSeatNumber($seatNumber)
	{
		$this->notAvailableSeatNumbers[] = $seatNumber;
	}

	/**
	 * @return string
	 */
	public function getBusIdent()
	{
		if ($this->busIdent !== null) {
			$return = $this->busIdent . ":";
		} else {
			$return = "";
		}

		$return .= $this->getRoute()->getId();

		return $return;
	}

	/**
	 * @param null|string $busIdent
	 */
	public function setBusIdent($busIdent)
	{
		$this->busIdent = $busIdent;
	}

	public function selectNextAvailableSeat(array $notAvailableSeats)
	{
		$next = false;
		foreach ($this->getSeatsPlan()->getSeats()->toArray() as $seat) {
			/** @var Seat $seat */
			if (!$seat->isAvailable()) continue;
			if ($next) {
				if (in_array($seat->getNumber(), $notAvailableSeats)) continue;
				$this->setSelectedSeat($seat);
				return;
			}
			if ($seat === $this->getSelectedSeat()) $next = true;
		}
	}

}
