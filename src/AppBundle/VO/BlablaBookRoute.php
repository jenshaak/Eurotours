<?php


namespace AppBundle\VO;


class BlablaBookRoute
{
	/** @var string */
	private $id;

	/** @var string */
	private $buyId;

	/** @var BlablaBookRoutePerson[] */
	private $persons = [];

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getBuyId()
	{
		return $this->buyId;
	}

	/**
	 * @param string $buyId
	 */
	public function setBuyId($buyId)
	{
		$this->buyId = $buyId;
	}

	/**
	 * @return BlablaBookRoutePerson[]
	 */
	public function getPersons()
	{
		return $this->persons;
	}

	/**
	 * @param BlablaBookRoutePerson $person
	 */
	public function addPerson($person)
	{
		$this->persons[] = $person;
	}


}
