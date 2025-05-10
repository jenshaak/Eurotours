<?php

namespace AppBundle\Widget\Frontend;

use AppBundle\Service\LanguageService;
use Motvicka\WidgetBundle\Widget\Widget;

class ContactBoxWidget extends Widget
{
	const NAME = "frontend.contactBox";
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(LanguageService $languageService)
	{
		$this->languageService = $languageService;
	}

	public function fetch()
	{
		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Frontend/ContactBox/contactBox.twig", [
			"currentLanguage" => $this->languageService->getCurrentLanguage()
		]));
	}
}
