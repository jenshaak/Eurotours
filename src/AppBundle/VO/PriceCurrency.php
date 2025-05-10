<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.08.17
 * Time: 16:03
 */

namespace AppBundle\VO;


class PriceCurrency
{
	const EUR = "EUR";
	const CZK = "CZK";
	const PLN = "PLN";
	const UAH = "UAH";

	/** @var float */
	private $price;

	/** @var string */
	private $currency;

	/**
	 * @param float $price
	 * @param string $currency
	 * @return PriceCurrency
	 */
	public static function create($price, $currency)
	{
		$priceCurrency = new PriceCurrency;
		$priceCurrency->setPrice($price);
		$priceCurrency->setCurrency($currency);
		return $priceCurrency;
	}

	/**
	 * @return array|string[]
	 */
	public static function currencies()
	{
		return [ self::CZK, self::EUR, self::PLN, self::UAH ];
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param float $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}

	public function addPrice($price)
	{
		$this->price += $price;
	}

}
