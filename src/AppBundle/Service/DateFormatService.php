<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 11:08
 */

namespace AppBundle\Service;




use AppBundle\Entity\Language;
use Psr\Log\LoggerInterface;

class DateFormatService
{
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(LanguageService $languageService)
	{
		$this->languageService = $languageService;
	}

	/**
	 * @param Language $language
	 * @return string
	 */
	private function getDateFormatFromLanguage(Language $language)
	{
		if ($language->getId() == Language::CS) {
			return "j. n. Y";
		} elseif ($language->getId() == Language::EN) {
			return "m/d/Y";
		}

		return "Y-m-d";
	}

	/**
	 * @return string
	 */
	public function onlyDateFormat()
	{
		return $this->getDateFormatFromLanguage($this->languageService->getCurrentLanguage());
	}

	/**
	 * @param \DateTime $dateTime
	 * @return string
	 */
	public function dateFormat(\DateTime $dateTime)
	{
		return $dateTime->format($this->getDateFormatFromLanguage($this->languageService->getCurrentLanguage()));
	}

	/**
	 * @param \DateTime $dateTime
	 * @return string
	 */
	public function dateTimeFormat(\DateTime $dateTime)
	{
		return $dateTime->format($this->getDateFormatFromLanguage($this->languageService->getCurrentLanguage()) . " H:i");
	}

	/**
	 * @param string $dateTimeString
	 * @return \DateTime|null
	 */
	public function dateTimeParse($dateTimeString)
	{
		$formats = [
			"j.n.Y H:i", "j. n. Y H:i", "m/d/Y H:i"
		];

		foreach ($formats as $format) {
			if ($return = (new \DateTime)->createFromFormat($format, $dateTimeString)) {
				return $return;
			}
		}

		return null;
	}

	/**
	 * @param string $dateString
	 * @return \DateTime|null
	 */
	public function dateParse($dateString)
	{
		$formats = [
			"j.n.Y", "j. n. Y", "m/d/Y", "Y-m-d"
		];

		foreach ($formats as $format) {
			if ($return = (new \DateTime)->createFromFormat($format, $dateString)) {
				return $return;
			}
		}

		return null;
	}

}