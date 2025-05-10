<?php


namespace AppBundle\VO;


class RegabusBookRoutePerson
{
	/** @var string */
	private $raceId;

	/** @var string */
	private $firstName;

	/** @var string */
	private $lastName;

	/** @var string */
	private $phone;

	/** @var string */
	private $email;

	/** @var int */
	private $baggage;

	/** @var string */
	private $passport;

	/** @var string */
	private $tariffIdent;

	/** @var int */
	private $seatNumber;

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
	 * @return int
	 */
	public function getBaggage()
	{
		return $this->baggage;
	}

	/**
	 * @param int $baggage
	 */
	public function setBaggage($baggage)
	{
		$this->baggage = $baggage;
	}

	/**
	 * @return string
	 */
	public function getPassport()
	{
		return $this->passport;
	}

	/**
	 * @param string $passport
	 */
	public function setPassport($passport)
	{
		$this->passport = $passport;
	}

	/**
	 * @return string
	 */
	public function getTariffIdent()
	{
		return $this->tariffIdent;
	}

	/**
	 * @param string $tariffIdent
	 */
	public function setTariffIdent($tariffIdent)
	{
		$this->tariffIdent = $tariffIdent;
	}

	/**
	 * @return int
	 */
	public function getSeatNumber()
	{
		return $this->seatNumber;
	}

	/**
	 * @param int $seatNumber
	 */
	public function setSeatNumber($seatNumber)
	{
		$this->seatNumber = $seatNumber;
	}

}
