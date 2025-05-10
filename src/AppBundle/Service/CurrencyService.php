<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.08.17
 * Time: 16:17
 */

namespace AppBundle\Service;


use AppBundle\Entity\Route;
use AppBundle\VO\CurrencyBag;
use AppBundle\VO\PriceCurrency;

class CurrencyService
{
	/**
	 * @var CurrencyBag
	 */
	private $currencyBag;
	/**
	 * @var KeyValueService
	 */
	private $keyValueService;

	/**
	 * @var float[]
	 */
	private $currencyConvertValues;

	public function __construct(CurrencyBag $currencyBag,
	                            KeyValueService $keyValueService)
	{
		$this->currencyBag = $currencyBag;
		$this->keyValueService = $keyValueService;
	}

	/**
	 * @return string
	 */
	public function getCurrentCurrency()
	{
		if ($this->currencyBag->getCurrency() === null) {
			$this->currencyBag->setCurrency(PriceCurrency::CZK);
		}

		return $this->currencyBag->getCurrency();
	}

	/**
	 * @param string $currency
	 */
	public function setCurrentCurrency($currency)
	{
		$this->currencyBag->setCurrency($currency);
	}

	/**
	 * @param string $fromCurrency
	 * @param string $toCurrency
	 * @return string
	 */
	private function getCurrencyConvertValueKey($fromCurrency, $toCurrency)
	{
		return "currency-convert-{$fromCurrency}-{$toCurrency}";
	}

	public function getMainCurrency(): string
	{
		return PriceCurrency::CZK;
	}

	/**
	 * @param string $fromCurrency
	 * @param string $toCurrency
	 * @return float
	 */
	public function getCurrencyConvertValue($fromCurrency, $toCurrency)
	{
		$key = $fromCurrency . "-" . $toCurrency;

		if (!isset($this->currencyConvertValues[$key])) {
			$keyValue = $this->keyValueService->get($this->getCurrencyConvertValueKey($fromCurrency, $toCurrency));
			$this->currencyConvertValues[$key] = (float) $keyValue->getValue();
		}

		if ($this->currencyConvertValues[$key] === (float) 0) throw new \Exception("Neni nastavena konverze měny $fromCurrency => $toCurrency");

		return $this->currencyConvertValues[$key];
	}

	/**
	 * @param string $fromCurrency
	 * @param string $toCurrency
	 * @param float $value
	 */
	public function setCurrencyConvertValue($fromCurrency, $toCurrency, $value)
	{
		$this->currencyConvertValues[$fromCurrency . "-" . $toCurrency] = $value;
		$this->keyValueService->set($this->getCurrencyConvertValueKey($fromCurrency, $toCurrency), $value);
	}

	/**
	 * @param PriceCurrency $priceCurrency
	 * @param string $currency
	 * @return PriceCurrency
	 */
	public function currencyConvert(PriceCurrency $priceCurrency, $currency)
	{
		$mainCurrency = $this->getMainCurrency();
		if ($priceCurrency->getCurrency() === $currency) return $priceCurrency;

		if ($priceCurrency->getCurrency() === $mainCurrency or $currency === $mainCurrency) {
			$amount = $priceCurrency->getPrice() * $this->getCurrencyConvertValue($priceCurrency->getCurrency(), $currency);
		} else {
			# Meny vztahujeme k CZK. Pokud to je ze dvou ruznych narodnich men, pak musime pres CZK.
			$amount = $priceCurrency->getPrice() * $this->getCurrencyConvertValue($priceCurrency->getCurrency(), $mainCurrency);
			$amount = $amount * $this->getCurrencyConvertValue($mainCurrency, $currency);
		}

		if ($currency === "CZK") {
			$amount = ceil($amount);
		} else{
			$amount = round($amount, 2);
		}

		return PriceCurrency::create($amount, $currency);
	}

}
