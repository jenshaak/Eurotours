<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 02.11.17
 * Time: 15:28
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\Payment;
use AppBundle\Service\EmailService;
use AppBundle\Service\PaymentService;
use AppBundle\VO\PriceCurrency;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.payment")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class PaymentController
{
	const PARAM_PRICE = "price";

	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var PaymentService
	 */
	private $paymentService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var EmailService
	 */
	private $emailService;

	public function __construct(MenuWidget $menuWidget,
	                            PaymentService $paymentService,
	                            Router $router,
								EmailService $emailService)
	{
		$this->menuWidget = $menuWidget;
		$this->paymentService = $paymentService;
		$this->router = $router;
		$this->emailService = $emailService;
	}

	/**
	 * @Route(path="/backend/payments", name="backend_payments", methods={"GET"})
	 * @Template()
	 */
	public function paymentsAction()
	{
		$payments = $this->paymentService->findDummyPayments();
		$this->menuWidget->setActive(MenuWidget::ACTIVE_PAYMENTS);

		return [
			"payments" => $payments
		];
	}

	/**
	 * @Route(path="/backend/payment/generator", name="backend_payment_generator", methods={"GET"})
	 * @Template()
	 */
	public function paymentGeneratorAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_PAYMENT_GENERATOR);

		return [
			"currencies" => PriceCurrency::currencies()
		];
	}

	/**
	 * @Route(path="/backend/payment/generator", name="backend_payment_generator_save", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function paymentGeneratorSaveAction(Request $request)
	{
		$payment = $this->paymentService->createDummyPaymentFromRequest($request);
		$this->emailService->sendDummyPayment($payment);

		return RedirectResponse::create($this->router->generate("backend_dummy_payment", [ "payment" => $payment->getId() ]));
	}

	/**
	 * @Route(path="/backend/payments/{payment}", name="backend_dummy_payment", methods={"GET"})
	 * @Template()
	 */
	public function paymentAction(Payment $payment)
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_PAYMENT_GENERATOR);

		return [
			"payment" => $payment
		];
	}
}
