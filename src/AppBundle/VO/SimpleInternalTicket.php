<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 01.11.17
 * Time: 11:29
 */

namespace AppBundle\VO;


class SimpleInternalTicket
{
	/** @var string */
	private $type;

	/** @var string */
	private $ident;

	/** @var string */
	private $name;

	/** @var string */
	private $from;

	/** @var \DateTime */
	private $datetimeDeparture;

	/** @var string */
	private $to;

	/** @var \DateTime */
	private $datetimeArrival;

	/** @var string */
	private $carrier;

	/** @var float */
	private $price;

	/** @var string */
	private $currency = PriceCurrency::CZK;

	/** @var string|null */
	private $file;

	/** @var SimpleInternalTicket|null */
	private $backTicket;

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getIdent(): string
	{
		return $this->ident;
	}

	/**
	 * @param string $ident
	 */
	public function setIdent(string $ident)
	{
		$this->ident = $ident;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getFrom(): string
	{
		return $this->from;
	}

	/**
	 * @param string $from
	 */
	public function setFrom(string $from)
	{
		$this->from = $from;
	}

	/**
	 * @return string
	 */
	public function getTo(): string
	{
		return $this->to;
	}

	/**
	 * @param string $to
	 */
	public function setTo(string $to)
	{
		$this->to = $to;
	}


	/**
	 * @return string
	 */
	public function getCarrier(): string
	{
		return $this->carrier;
	}

	/**
	 * @param string $carrier
	 */
	public function setCarrier(string $carrier)
	{
		$this->carrier = $carrier;
	}

	/**
	 * @return float
	 */
	public function getPrice(): float
	{
		return $this->price;
	}

	/**
	 * @param float $price
	 */
	public function setPrice(float $price)
	{
		$this->price = $price;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeDeparture(): \DateTime
	{
		return $this->datetimeDeparture;
	}

	/**
	 * @param \DateTime $datetimeDeparture
	 */
	public function setDatetimeDeparture(\DateTime $datetimeDeparture)
	{
		$this->datetimeDeparture = $datetimeDeparture;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeArrival(): \DateTime
	{
		return $this->datetimeArrival;
	}

	/**
	 * @param \DateTime $datetimeArrival
	 */
	public function setDatetimeArrival(\DateTime $datetimeArrival)
	{
		$this->datetimeArrival = $datetimeArrival;
	}

	/**
	 * @return string|null
	 */
	public function getFile(): ?string
	{
		return $this->file;
	}

	/**
	 * @param string|null $file
	 */
	public function setFile(?string $file): void
	{
		$this->file = $file;
	}

	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency(string $currency): void
	{
		$this->currency = $currency;
	}

	/**
	 * @return SimpleInternalTicket|null
	 */
	public function getBackTicket(): ?SimpleInternalTicket
	{
		return $this->backTicket;
	}

	/**
	 * @param SimpleInternalTicket|null $backTicket
	 */
	public function setBackTicket(?SimpleInternalTicket $backTicket): void
	{
		$this->backTicket = $backTicket;
	}

	/**
	 * @return PriceCurrency
	 */
	public function getPriceCurrency()
	{
		return PriceCurrency::create($this->price, $this->getCurrency());
	}
}
