<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.06.17
 * Time: 15:31
 */

namespace AppBundle\Controller\Frontend;


use AppBundle\Entity\Payment;
use AppBundle\Service\EmailService;
use AppBundle\Service\PaymentService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * @Route(service="controller.frontend.payment")
 */
class PaymentController
{
	/**
	 * @var PaymentService
	 */
	private $paymentService;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var Router
	 */
	private $router;

	public function __construct(PaymentService $paymentService,
								EmailService $emailService,
								Router $router)
	{
		$this->paymentService = $paymentService;
		$this->emailService = $emailService;
		$this->router = $router;
	}

	/**
	 * @Route(path="/payment/{payment}", name="payment")
	 * @Template()
	 * @param Payment $payment
	 * @return array
	 */
	public function paymentAction(Payment $payment)
	{
		return [
			"payment" => $payment,
			"order" => $payment->getOrder()
		];
	}

	/**
	 * @Route(path="/payment/d/{payment}", name="payment_dummy")
	 * @return RedirectResponse
	 */
	public function dummyPaymentAction(Request $request, Payment $payment)
	{
		if ($request->query->has(PaymentService::PARAM_ORDERNUMBER)) {
			if ($this->paymentService->verifyPayment($request, $payment)) {
				if ($this->paymentService->processPaymentFromRequest($request, $payment)) {
					$this->emailService->sendDummyPaymentPaidToAdminEmail($payment);
				}
				$this->paymentService->savePayment($payment);
			}
			return RedirectResponse::create(
				$this->router->generate("payment_dummy_status", [ "payment" => $payment->getId() ])
			);
		}

		if ($payment->getDatetimeExpire() < new \DateTime) return RedirectResponse::create(
			$this->router->generate("payment_dummy_status", [ "payment" => $payment->getId() ])
		);

		if (!$payment->isPaid()) {
			$this->paymentService->initDummyPaymentUrl($payment);
		}

		return RedirectResponse::create($payment->getUrl());
	}

	/**
	 * @Route(path="/payment/d/{payment}/status", name="payment_dummy_status")
	 * @Template()
	 * @return array
	 */
	public function dummyPaymentStatusAction(Request $request, Payment $payment)
	{
		return [
			"payment" => $payment,
			"paymentExpired" => $payment->getDatetimeExpire() < new \DateTime
		];
	}


}
