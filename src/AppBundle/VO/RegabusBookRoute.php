<?php


namespace AppBundle\VO;


class RegabusBookRoute
{
	/** @var string */
	private $id;

	/** @var string */
	private $buyId;

	/** @var RegabusBookRoutePerson[] */
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
	 * @return RegabusBookRoutePerson[]
	 */
	public function getPersons()
	{
		return $this->persons;
	}

	/**
	 * @param RegabusBookRoutePerson $person
	 */
	public function addPerson($person)
	{
		$this->persons[] = $person;
	}


}
