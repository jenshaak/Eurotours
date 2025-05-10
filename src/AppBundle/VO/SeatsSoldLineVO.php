<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 19:46
 */

namespace AppBundle\VO;


use AppBundle\Entity\City;
use AppBundle\Entity\Line;

class SeatsSoldLineVO
{
	/** @var Line */
	private $line;

	/** @var \DateTime */
	private $dateDay;

	/** @var string */
	private $fromTime;

	/** @var string */
	private $toTime;

	/** @var City */
	private $fromCity;

	/** @var City */
	private $toCity;

	/** @var \AppBundle\Entity\InternalTicket[] */
	private $tickets;

	/** @var \AppBundle\Entity\Book[] */
	private $books;

	/**
	 * @return Line
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * @param Line $line
	 */
	public function setLine($line)
	{
		$this->line = $line;
	}

	/**
	 * @return City
	 */
	public function getFromCity()
	{
		return $this->fromCity;
	}

	/**
	 * @param City $fromCity
	 */
	public function setFromCity($fromCity)
	{
		$this->fromCity = $fromCity;
	}

	/**
	 * @return City
	 */
	public function getToCity()
	{
		return $this->toCity;
	}

	/**
	 * @param City $toCity
	 */
	public function setToCity($toCity)
	{
		$this->toCity = $toCity;
	}

	/**
	 * @return \AppBundle\Entity\InternalTicket[]
	 */
	public function getTickets()
	{
		return $this->tickets;
	}

	/**
	 * @param \AppBundle\Entity\InternalTicket[] $tickets
	 */
	public function setTickets($tickets)
	{
		$this->tickets = $tickets;
	}

	public function addTicket($ticket)
	{
		$this->tickets[] = $ticket;
	}

	/**
	 * @return \AppBundle\Entity\Book[]
	 */
	public function getBooks()
	{
		return $this->books;
	}

	/**
	 * @param \AppBundle\Entity\Book $book
	 */
	public function addBook($book)
	{
		$this->books[] = $book;
	}

	/**
	 * @return string
	 */
	public function getFromTime()
	{
		return $this->fromTime;
	}

	/**
	 * @param string $fromTime
	 */
	public function setFromTime($fromTime)
	{
		$this->fromTime = $fromTime;
	}

	/**
	 * @return string
	 */
	public function getToTime()
	{
		return $this->toTime;
	}

	/**
	 * @param string $toTime
	 */
	public function setToTime($toTime)
	{
		$this->toTime = $toTime;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateDay()
	{
		return $this->dateDay;
	}

	/**
	 * @param \DateTime $dateDay
	 */
	public function setDateDay($dateDay)
	{
		$this->dateDay = $dateDay;
	}

}
