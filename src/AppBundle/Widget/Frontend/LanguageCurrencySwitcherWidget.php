<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 03.08.17
 * Time: 16:16
 */

namespace AppBundle\Widget\Frontend;


use AppBundle\Service\CurrencyService;
use AppBundle\Service\LanguageService;
use Motvicka\WidgetBundle\Widget\Widget;

class LanguageCurrencySwitcherWidget extends Widget
{
	const NAME = "frontend.languageCurrencySwitcher";
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(CurrencyService $currencyService, LanguageService $languageService)
	{
		$this->currencyService = $currencyService;
		$this->languageService = $languageService;
	}

	public function fetch()
	{
		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Frontend/Widget/languageCurrencySwitcher.html.twig", [
			"currentCurrency" => $this->currencyService->getCurrentCurrency(),
			"currentLanguage" => $this->languageService->getCurrentLanguage()
		]));
	}
}