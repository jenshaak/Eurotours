<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 11:08
 */

namespace AppBundle\Twig;


use AppBundle\Entity\Language;
use AppBundle\Service\DateFormatService;
use AppBundle\Service\LanguageService;

class DateFormatExtension extends \Twig_Extension
{
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(DateFormatService $dateFormatService,
	                            LanguageService $languageService)
	{
		$this->dateFormatService = $dateFormatService;
		$this->languageService = $languageService;
	}

	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter("dateFormat", [$this, "dateFormat"]),
			new \Twig_SimpleFilter("dateTimeFormat", [$this, "dateTimeFormat"]),
			new \Twig_SimpleFilter("czechMonth", [$this, "czechMonth"])
		];
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction("onlyDateFormatJs", [$this, "onlyDateFormatJs"])
		];
	}

	public function getName()
	{
		return "dateFormat";
	}

	/**
	 * @param \DateTime $datetime
	 * @return string
	 */
	public function czechMonth(\DateTime $datetime)
	{
		static $months = [
			"Leden",
			"Únor",
			"Březen",
			"Duben",
			"Květen",
			"Červen",
			"Červenec",
			"Srpen",
			"Září",
			"Říjen",
			"Listopad",
			"Prosinec"
		];

		return $months[$datetime->format("n")-1];
	}

	/**
	 * @param \DateTime|string $dateTime
	 * @return string
	 */
	public function dateFormat($dateTime)
	{
		if (is_string($dateTime)) {
			$dateTime = new \DateTime($dateTime);
		}

		return $this->dateFormatService->dateFormat($dateTime);
	}

	/**
	 * @return string
	 */
	public function onlyDateFormatJs()
	{
		if ($this->languageService->getCurrentLanguage()->getId() == Language::CS) {
			return "d. m. yyyy";
		} elseif ($this->languageService->getCurrentLanguage()->getId() == Language::EN) {
			return "mm/dd/yyyy";
		}

		return "yyyy-mm-dd";
	}

	/**
	 * @param \DateTime|string $dateTime
	 * @return string
	 */
	public function dateTimeFormat($dateTime)
	{
		if (is_string($dateTime)) {
			$dateTime = new \DateTime($dateTime);
		}

		return $this->dateFormatService->dateTimeFormat($dateTime);
	}
}
