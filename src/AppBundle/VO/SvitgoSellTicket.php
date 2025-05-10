<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-04-05
 * Time: 10:24
 */

namespace AppBundle\VO;


class SvitgoSellTicket
{
	const SELL = 1;
	const ORDER = 2;

	/**
	 * @var int
	 */
	private $routeNameId;

	/**
	 * @var int
	 */
	private $from;

	/**
	 * @var int
	 */
	private $to;

	/**
	 * @var \DateTime
	 */
	private $routeDate;

	/**
	 * @var int
	 */
	private $busesId;

	/**
	 * @var int
	 */
	private $ferrymanId;

	/**
	 * @var int
	 */
	private $seat;

	/**
	 * @var int
	 */
	private $rice;

	/**
	 * @var int
	 */
	private $discount;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $surname;

	/**
	 * @var \DateTime
	 */
	private $userbd;

	/**
	 * @var string
	 */
	private $tel;

	/**
	 * @var integer
	 */
	private $sellOrOrder;

	/**
	 * @return int
	 */
	public function getRouteNameId()
	{
		return $this->routeNameId;
	}

	/**
	 * @param int $routeNameId
	 * @return self
	 */
	public function setRouteNameId($routeNameId)
	{
		$this->routeNameId = $routeNameId;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getFrom()
	{
		return $this->from;
	}

	/**
	 * @param int $from
	 * @return self
	 */
	public function setFrom($from)
	{
		$this->from = $from;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getTo()
	{
		return $this->to;
	}

	/**
	 * @param int $to
	 * @return self
	 */
	public function setTo($to)
	{
		$this->to = $to;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getRouteDate()
	{
		return $this->routeDate;
	}

	/**
	 * @param \DateTime $routeDate
	 * @return self
	 */
	public function setRouteDate($routeDate)
	{
		$this->routeDate = $routeDate;
		return $this;
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
	 * @return self
	 */
	public function setBusesId($busesId)
	{
		$this->busesId = $busesId;
		return $this;
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
	 * @return self
	 */
	public function setFerrymanId($ferrymanId)
	{
		$this->ferrymanId = $ferrymanId;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSeat()
	{
		return $this->seat;
	}

	/**
	 * @param int $seat
	 * @return self
	 */
	public function setSeat($seat)
	{
		$this->seat = $seat;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getRice()
	{
		return $this->rice;
	}

	/**
	 * @param int $rice
	 * @return self
	 */
	public function setRice($rice)
	{
		$this->rice = $rice;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getDiscount()
	{
		return $this->discount;
	}

	/**
	 * @param int $discount
	 * @return self
	 */
	public function setDiscount($discount)
	{
		$this->discount = $discount;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSurname()
	{
		return $this->surname;
	}

	/**
	 * @param string $surname
	 * @return self
	 */
	public function setSurname($surname)
	{
		$this->surname = $surname;
		return $this;
	}

	/**
	 * @return \DateTime
	 */
	public function getUserbd()
	{
		return $this->userbd;
	}

	/**
	 * @param \DateTime $userbd
	 * @return self
	 */
	public function setUserbd($userbd)
	{
		$this->userbd = $userbd;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTel()
	{
		return $this->tel;
	}

	/**
	 * @param string $tel
	 * @return self
	 */
	public function setTel($tel)
	{
		$this->tel = $tel;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSellOrOrder()
	{
		return $this->sellOrOrder;
	}

	/**
	 * @param int $sellOrOrder
	 * @return self
	 */
	public function setSellOrOrder($sellOrOrder)
	{
		$this->sellOrOrder = $sellOrOrder;
		return $this;
	}
}
