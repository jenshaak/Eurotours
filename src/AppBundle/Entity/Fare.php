<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 24.04.17
 * Time: 13:18
 */

namespace AppBundle\Entity;


use AppBundle\VO\PriceCurrency;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FareRepository")
 * @ORM\Table(name="fares")
 */
class Fare
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="fare_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var LineStation
	 * @ORM\ManyToOne(targetEntity="LineStation")
	 * @ORM\JoinColumn(name="from_line_station_id", referencedColumnName="line_station_id", nullable=false, onDelete="CASCADE")
	 */
	private $fromLineStation;

	/**
	 * @var LineStation
	 * @ORM\ManyToOne(targetEntity="LineStation")
	 * @ORM\JoinColumn(name="to_line_station_id", referencedColumnName="line_station_id", nullable=false, onDelete="CASCADE")
	 */
	private $toLineStation;

	/**
	 * @var Tariff
	 * @ORM\ManyToOne(targetEntity="Tariff", inversedBy="fares", cascade={"persist"})
	 * @ORM\JoinColumn(name="tariff_id", referencedColumnName="tariff_id", nullable=false)
	 */
	private $tariff;

	/**
	 * @var int
	 * @ORM\Column(name="price", type="decimal", precision=8, scale=2, nullable=true)
	 */
	private $price;

	/**
	 * @var int
	 * @ORM\Column(name="price_return_add", type="decimal", precision=8, scale=2, nullable=true)
	 */
	private $priceReturnAdd;

	/**
	 * @var bool
	 * @ORM\Column(name="variable_price", type="boolean", nullable=false)
	 */
	private $variablePrice = false;

	/**
	 * @var bool
	 * @ORM\Column(name="variable_price_return", type="boolean", nullable=false)
	 */
	private $variablePriceReturn = false;

	/**
	 * @var bool
	 * @ORM\Column(name="not_available", type="boolean", nullable=false)
	 */
	private $notAvailable = false;

	/**
	 * @var bool
	 * @ORM\Column(name="not_available_return", type="boolean", nullable=false)
	 */
	private $notAvailableReturn = false;

	/**
	 * @var bool
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return Tariff
	 */
	public function getTariff()
	{
		return $this->tariff;
	}

	/**
	 * @param Tariff $tariff
	 */
	public function setTariff($tariff)
	{
		$this->tariff = $tariff;
	}

	/**
	 * @return int
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param int $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return int
	 */
	public function getPriceReturnAdd()
	{
		return $this->priceReturnAdd;
	}

	/**
	 * @param int $priceReturnAdd
	 */
	public function setPriceReturnAdd($priceReturnAdd)
	{
		$this->priceReturnAdd = $priceReturnAdd;
	}

	/**
	 * @return LineStation
	 */
	public function getFromLineStation()
	{
		return $this->fromLineStation;
	}

	/**
	 * @param LineStation $fromLineStation
	 */
	public function setFromLineStation($fromLineStation)
	{
		$this->fromLineStation = $fromLineStation;
	}

	/**
	 * @return LineStation
	 */
	public function getToLineStation()
	{
		return $this->toLineStation;
	}

	/**
	 * @param LineStation $toLineStation
	 */
	public function setToLineStation($toLineStation)
	{
		$this->toLineStation = $toLineStation;
	}

	/**
	 * @return boolean
	 */
	public function isVariablePrice()
	{
		return $this->variablePrice;
	}

	/**
	 * @param boolean $variablePrice
	 */
	public function setVariablePrice($variablePrice)
	{
		$this->variablePrice = $variablePrice;
	}

	/**
	 * @return boolean
	 */
	public function isVariablePriceReturn()
	{
		return $this->variablePriceReturn;
	}

	/**
	 * @param boolean $variablePriceReturn
	 */
	public function setVariablePriceReturn($variablePriceReturn)
	{
		$this->variablePriceReturn = $variablePriceReturn;
	}

	/**
	 * @return boolean
	 */
	public function isNotAvailable()
	{
		return $this->notAvailable;
	}

	/**
	 * @param boolean $notAvailable
	 */
	public function setNotAvailable($notAvailable)
	{
		$this->notAvailable = $notAvailable;
	}

	/**
	 * @return boolean
	 */
	public function isNotAvailableReturn()
	{
		return $this->notAvailableReturn;
	}

	/**
	 * @param boolean $notAvailableReturn
	 */
	public function setNotAvailableReturn($notAvailableReturn)
	{
		$this->notAvailableReturn = $notAvailableReturn;
	}

	/**
	 * @return PriceCurrency
	 */
	public function getPriceCurrency()
	{
		return PriceCurrency::create($this->getPrice(), $this->getTariff()->getCurrency());
	}

	/**
	 * @return PriceCurrency
	 */
	public function getPriceReturnAddCurrency()
	{
		return PriceCurrency::create($this->getPriceReturnAdd(), $this->getTariff()->getCurrency());
	}

	/**
	 * @return PriceCurrency
	 */
	public function getPriceReturnCurrency()
	{
		return PriceCurrency::create($this->getPrice() + $this->getPriceReturnAdd(), $this->getTariff()->getCurrency());
	}

	/**
	 * @return boolean
	 */
	public function isDeleted()
	{
		return $this->deleted;
	}

	/**
	 * @param boolean $deleted
	 */
	public function setDeleted($deleted)
	{
		$this->deleted = $deleted;
	}

	public function __clone()
	{
		$this->setId(null);
	}

}
