<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 14.08.17
 * Time: 11:06
 */

namespace AppBundle\Twig;


use AppBundle\Service\CurrencyService;
use AppBundle\VO\PriceCurrency;

class PriceCurrencyExtension extends \Twig_Extension
{
	/**
	 * @var CurrencyService
	 */
	private $currencyService;

	public function __construct(CurrencyService $currencyService)
	{
		$this->currencyService = $currencyService;
	}

	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter("priceCurrency", [ $this, "priceCurrency" ]),
			new \Twig_SimpleFilter("currency", [ $this, "currency" ])
		];
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction("currentCurrency", [ $this, "currentCurrency" ]),
			new \Twig_SimpleFunction("currentCurrencyString", [ $this, "currentCurrencyString" ])
		];
	}

	public function getName()
	{
		return "priceCurrency";
	}

	/**
	 * @return string
	 */
	public function currentCurrency()
	{
		return $this->currencyService->getCurrentCurrency();
	}

	/**
	 * @return string
	 */
	public function currentCurrencyString()
	{
		return $this->currency(
			PriceCurrency::create(0, $this->currencyService->getCurrentCurrency())
		);
	}

	/**
	 * @param PriceCurrency $priceCurrency
	 * @param bool $showCurrency
	 * @param null|string $currency
	 * @return string
	 */
	public function priceCurrency(PriceCurrency $priceCurrency, $showCurrency = true, $currency = null)
	{
		if ($currency === null) {
			$currency = $this->currencyService->getCurrentCurrency();
		}

		if ($priceCurrency->getCurrency() !== $currency) {
			$priceCurrency = $this->currencyService->currencyConvert(
				$priceCurrency, $currency
			);
		}

		$priceString = str_replace(".", ",", $priceCurrency->getPrice());
		$priceString = str_replace(",00", "", $priceString);

		if ($priceCurrency->getCurrency() == PriceCurrency::CZK) {
			if ($showCurrency) {
				return $priceString . " Kč";
			} else {
				return $priceString;
			}
		} elseif ($priceCurrency->getCurrency() == PriceCurrency::EUR) {
			if ($showCurrency) {
				return $priceString . " €";
			} else {
				return $priceString;
			}
		} elseif ($priceCurrency->getCurrency() == PriceCurrency::UAH) {
			if ($showCurrency) {
				return $priceString . " ₴";
			} else {
				return $priceString;
			}
		} elseif ($priceCurrency->getCurrency() == PriceCurrency::PLN) {
			if ($showCurrency) {
				return $priceString . " zł";
			} else {
				return $priceString;
			}
		}

		return "ERROR";
	}

	/**
	 * @param PriceCurrency $priceCurrency
	 * @return string
	 */
	public function currency(PriceCurrency $priceCurrency)
	{
		if ($priceCurrency->getCurrency() === PriceCurrency::CZK) {
			return "Kč";
		} elseif ($priceCurrency->getCurrency() === PriceCurrency::EUR) {
			return "€";
		} elseif ($priceCurrency->getCurrency() === PriceCurrency::UAH) {
			return "₴";
		} elseif ($priceCurrency->getCurrency() === PriceCurrency::PLN) {
			return "zł";
		}

		return "ERROR";
	}
}
