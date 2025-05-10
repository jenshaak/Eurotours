<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.17
 * Time: 13:13
 */

namespace AppBundle\Controller\Frontend;

use AppBundle\Service\EmailService;
use AppBundle\Service\HomepageNoticeService;
use AppBundle\Widget\Frontend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.frontend.homepage")
 */
class HomepageController
{
	const PARAM_EMAIL = "email";
	const PARAM_MESSAGE = "message";
	const PARAM_PHONE = "phone";
	const PARAM_BOT_PROTECTION_PRVNI = "prvni";
	const PARAM_BOT_PROTECTION_DRUHY = "druhy";

	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var HomepageNoticeService
	 */
	private $homepageNoticeService;

	public function __construct(Router $router,
	                            EmailService $emailService,
	                            MenuWidget $menuWidget,
								HomepageNoticeService $homepageNoticeService)
	{
		$this->router = $router;
		$this->emailService = $emailService;
		$this->menuWidget = $menuWidget;
		$this->homepageNoticeService = $homepageNoticeService;
	}

	/**
	 * @Route(path="/", name="homepage")
	 * @Template()
	 * @return array
	 */
	public function homepageAction()
	{
		return [
			"homepageNotice" => $this->homepageNoticeService->load()
		];
	}

	/**
	 * @Route(path="/documents/gdpr", name="documents_gdpr")
	 * @Template()
	 * @return array
	 */
	public function documentGdprAction()
	{
		return [];
	}

	/**
	 * @Route(path="/documents/terms-and-conditions", name="documents_terms")
	 * @Template()
	 * @return array
	 */
	public function documentTermsAction()
	{
		return [];
	}

	/**
	 * @Route(path="/kontakt", name="contact", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function contactAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_CONTACT);

		return [];
	}

	/**
	 * @Route(path="/kontakt", name="contact_send", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function contactSendAction(Request $request)
	{
		$email = $request->request->get(self::PARAM_EMAIL);
		$phone = $request->request->get(self::PARAM_PHONE);
		$message = $request->request->get(self::PARAM_MESSAGE);

		if ($request->request->get(self::PARAM_BOT_PROTECTION_PRVNI) !== self::PARAM_BOT_PROTECTION_PRVNI
			or $request->request->get(self::PARAM_BOT_PROTECTION_DRUHY) !== self::PARAM_BOT_PROTECTION_DRUHY) {
			return RedirectResponse::create($this->router->generate("contact"));
		}

		$this->emailService->sendContactForm($phone, $email, $message);

		return RedirectResponse::create($this->router->generate("contact", [ "sent" => 1 ]));
	}


}
