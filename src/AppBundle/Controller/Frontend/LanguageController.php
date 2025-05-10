<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.08.17
 * Time: 11:45
 */

namespace AppBundle\Controller\Frontend;


use AppBundle\Entity\Language;
use AppBundle\Service\LanguageService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.frontend.language")
 */
class LanguageController
{
	const REFERER = "referer";

	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var Router
	 */
	private $router;

	public function __construct(LanguageService $languageService,
	                            Router $router)
	{
		$this->languageService = $languageService;
		$this->router = $router;
	}

	/**
	 * @Route(path="/switch/language/{language}", name="language_switch")
	 * @param Language $language
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function switchLanguageAction(Language $language, Request $request)
	{
		$this->languageService->setCurrentLanguage($language);

		return RedirectResponse::create($request->headers->get(self::REFERER) ?: $this->router->generate("homepage"));
	}
}