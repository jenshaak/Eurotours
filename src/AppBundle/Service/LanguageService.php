<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:02
 */

namespace AppBundle\Service;


use AppBundle\Entity\Language;
use AppBundle\Repository\LanguageRepository;
use AppBundle\VO\LanguageBag;

class LanguageService
{
	/**
	 * @var LanguageRepository
	 */
	private $languageRepository;
	/**
	 * @var LanguageBag
	 */
	private $languageBag;

	public function __construct(LanguageRepository $languageRepository,
	                            LanguageBag $languageBag)
	{
		$this->languageRepository = $languageRepository;
		$this->languageBag = $languageBag;
	}

	/**
	 * @return Language[]
	 */
	public function getAllLanguages()
	{
		return $this->languageRepository->findAll();
	}

	/**
	 * @return Language
	 */
	public function getEnglish()
	{
		return $this->languageRepository->find(Language::EN);
	}

	/**
	 * @return Language
	 */
	public function getCzech()
	{
		return $this->languageRepository->find(Language::CS);
	}

	/**
	 * @return Language
	 */
	public function getRussian()
	{
		return $this->languageRepository->find(Language::RU);
	}

	/**
	 * @return Language
	 */
	public function getBulgarion()
	{
		return $this->languageRepository->find(Language::BG);
	}

	/**
	 * @return Language
	 */
	public function getUkrainian()
	{
		return $this->languageRepository->find(Language::UK);
	}

	/**
	 * @param string $id
	 * @return Language
	 */
	public function getLanguage($id)
	{
		return $this->languageRepository->find($id);
	}

	/**
	 * @return Language
	 */
	public function getCurrentLanguage()
	{
		$languageId = $this->languageBag->getLanguage();
		if ($languageId === null) {
			// Default to Czech language instead of first in database
			$this->setCurrentLanguage($this->getCzech());
			return $this->getCurrentLanguage();
		}

		return $this->languageRepository->find($languageId);
	}

	/**
	 * @return bool
	 */
	public function isSetCurrentLanguage()
	{
		return (bool) $this->languageBag->getLanguage();
	}

	/**
	 * @param Language $language
	 */
	public function setCurrentLanguage(Language $language)
	{
		$this->languageBag->setLanguage($language);
	}
}
