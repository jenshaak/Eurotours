<?php


namespace AppBundle\Controller\Backend;

use AppBundle\Service\HomepageNoticeService;
use AppBundle\Service\LanguageService;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.homepageNotice")
 */
class HomepageNoticeController
{
	const PARAM_HOMEPAGE_NOTICE = "homepageNotice";
	const PARAM_SHOW = "show";

	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var HomepageNoticeService
	 */
	private $homepageNoticeService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(MenuWidget $menuWidget,
								HomepageNoticeService $homepageNoticeService,
								Router $router,
								LanguageService $languageService)
	{
		$this->menuWidget = $menuWidget;
		$this->homepageNoticeService = $homepageNoticeService;
		$this->router = $router;
		$this->languageService = $languageService;
	}

	/**
	 * @Route(path="/backend/homepageNotice", name="backend_homepage_notice", methods={"GET"})
	 * @Template()
	 */
	public function homepageNoticeAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_HOMEPAGE_NOTICE);

		return [
			"homepageNotice" => $this->homepageNoticeService->load()
		];
	}

	/**
	 * @Route(path="/backend/homepageNotice", name="backend_homepage_notice_save", methods={"POST"})
	 */
	public function homepageNoticeSaveAction(Request $request)
	{
		$homepageNotice = $this->homepageNoticeService->load();
		$homepageNotice->setShow($request->request->get(self::PARAM_SHOW) === "1");

		foreach ($this->languageService->getAllLanguages() as $lang) {
			$homepageNotice->setContent($lang, $request->request->get(self::PARAM_HOMEPAGE_NOTICE)[$lang->getId()]);
		}

		$this->homepageNoticeService->save($homepageNotice);

		return RedirectResponse::create($this->router->generate("backend_homepage_notice"));
	}
}
