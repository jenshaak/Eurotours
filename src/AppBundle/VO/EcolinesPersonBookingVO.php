<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 24.09.18
 * Time: 23:04
 */

namespace AppBundle\VO;


class EcolinesPersonBookingVO
{
	/** @var string */
	private $firstName;

	/** @var string */
	private $lastName;

	/** @var string */
	private $phone;

	/** @var int */
	private $tariff;

	/** @var string */
	private $discount;

	/** @var int[] */
	private $seats;

	/** @var string */
	private $email;

	/** @var string */
	private $note;

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @param string $firstName
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * @param string $lastName
	 */
	public function setLastName($lastName)
	{
		$this->lastName = $lastName;
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	/**
	 * @return int
	 */
	public function getTariff()
	{
		return $this->tariff;
	}

	/**
	 * @param int $tariff
	 */
	public function setTariff($tariff)
	{
		$this->tariff = $tariff;
	}

	/**
	 * @return string
	 */
	public function getDiscount()
	{
		return $this->discount;
	}

	/**
	 * @param string $discount
	 */
	public function setDiscount($discount)
	{
		$this->discount = $discount;
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

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getNote()
	{
		return $this->note;
	}

	/**
	 * @param string $note
	 */
	public function setNote($note)
	{
		$this->note = $note;
	}

}
