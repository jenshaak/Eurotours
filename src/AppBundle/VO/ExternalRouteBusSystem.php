<?php


namespace AppBundle\VO;


class ExternalRouteBusSystem
{
	/** @var int[] */
	private $seats;

	/** @var string */
	private $intervalId;

	private bool $dateOfBirthRequired;
	private bool $documentNumberRequired;

	/**
	 * @return int[]
	 */
	public function getSeats(): array
	{
		return $this->seats;
	}

	/**
	 * @param int[] $seats
	 */
	public function setSeats(array $seats): void
	{
		$this->seats = $seats;
	}

	/**
	 * @return string
	 */
	public function getIntervalId(): string
	{
		return $this->intervalId;
	}

	/**
	 * @param string $intervalId
	 */
	public function setIntervalId(string $intervalId): void
	{
		$this->intervalId = $intervalId;
	}

	public function isDateOfBirthRequired(): bool
	{
		return $this->dateOfBirthRequired;
	}

	public function setDateOfBirthRequired(bool $dateOfBirthRequired): void
	{
		$this->dateOfBirthRequired = $dateOfBirthRequired;
	}

	public function isDocumentNumberRequired(): bool
	{
		return $this->documentNumberRequired;
	}

	public function setDocumentNumberRequired(bool $documentNumberRequired): void
	{
		$this->documentNumberRequired = $documentNumberRequired;
	}
}
