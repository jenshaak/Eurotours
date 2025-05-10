<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 18.04.17
 * Time: 10:25
 */

namespace AppBundle\Twig;


use AppBundle\Service\LanguageService;
use AppBundle\VO\LanguageString;

class LanguageStringExtension extends \Twig_Extension
{
	/**
	 * @var LanguageService
	 */
	private $languageService;

	/**
	 * @param LanguageService $languageService
	 */
	public function setLanguageService($languageService)
	{
		$this->languageService = $languageService;
	}

	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter("language", [ $this, "language" ]),
			new \Twig_SimpleFilter("languageEN", [ $this, "languageEN" ]),
			new \Twig_SimpleFilter("languageCS", [ $this, "languageCS" ]),
			new \Twig_SimpleFilter("languageRU", [ $this, "languageRU" ]),
			new \Twig_SimpleFilter("languageBG", [ $this, "languageBG" ]),
			new \Twig_SimpleFilter("languageUK", [ $this, "languageUK" ])
		];
	}

	public function getName()
	{
		return "languageString";
	}

	/**
	 * @param LanguageString $languageString
	 * @return string|mixed
	 */
	public function languageEN($languageString)
	{
		if ($languageString instanceof LanguageString) {
			return $languageString->getString($this->languageService->getEnglish());
		} else {
			return $languageString;
		}
	}

	/**
	 * @param LanguageString $languageString
	 * @return string|mixed
	 */
	public function languageCS($languageString)
	{
		if ($languageString instanceof LanguageString) {
			return $languageString->getString($this->languageService->getCzech());
		} else {
			return $languageString;
		}
	}

	/**
	 * @param LanguageString $languageString
	 * @return string|mixed
	 */
	public function languageRU($languageString)
	{
		if ($languageString instanceof LanguageString) {
			return $languageString->getString($this->languageService->getRussian());
		} else {
			return $languageString;
		}
	}

	/**
	 * @param LanguageString $languageString
	 * @return string|mixed
	 */
	public function languageBG($languageString)
	{
		if ($languageString instanceof LanguageString) {
			return $languageString->getString($this->languageService->getBulgarion());
		} else {
			return $languageString;
		}
	}

	/**
	 * @param LanguageString $languageString
	 * @return string|mixed
	 */
	public function languageUK($languageString)
	{
		if ($languageString instanceof LanguageString) {
			return $languageString->getString($this->languageService->getUkrainian());
		} else {
			return $languageString;
		}
	}

	/**
	 * @param LanguageString $languageString
	 * @return string
	 */
	public function language($languageString)
	{
		if ($languageString instanceof LanguageString) {
			return $languageString->getString($this->languageService->getCurrentLanguage());
		} else {
			return print_r($languageString, true);
		}
	}
}
