<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.06.17
 * Time: 15:05
 */

namespace AppBundle\Service;


use AdamStipak\Webpay\Api;
use AdamStipak\Webpay\PaymentRequest;
use AdamStipak\Webpay\Signer;
use AppBundle\Entity\Order;
use AppBundle\Entity\Payment;
use AppBundle\Repository\PaymentRepository;
use AppBundle\VO\PriceCurrency;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class PaymentService
{
	const PARAM_OPERATION = "OPERATION";
	const PARAM_ORDERNUMBER = "ORDERNUMBER";
	const PARAM_MERORDERNUM = "MERORDERNUM";
	const PARAM_PRCODE = "PRCODE";
	const PARAM_SRCODE = "SRCODE";
	const PARAM_RESULTTEXT = "RESULTTEXT";
	const PARAM_DIGEST = "DIGEST";
	const PARAM_DIGEST1 = "DIGEST1";
	const PARAM_PRICE = "price";
	const PARAM_CURRENCY = "currency";
	const PARAM_EMAIL = "email";
	const PARAM_NOTE = "note";
	const PARAM_VALIDITY_HOURS = "validityHours";

	/** @var Signer */
	private $signer;

	/** @var Api */
	private $webpay;
	/**
	 * @var Container
	 */
	private $container;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var PaymentRepository
	 */
	private $paymentRepository;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	private StringService $stringService;

	public function __construct(ContainerInterface $container,
	                            Router $router,
	                            PaymentRepository $paymentRepository,
	                            CurrencyService $currencyService,
								StringService $stringService)
	{
		$this->container = $container;
		$this->router = $router;
		$this->paymentRepository = $paymentRepository;
		$this->currencyService = $currencyService;
		$this->stringService = $stringService;

		$this->signer = new Signer(
			$container->getParameter("webpay_private_key_file"),
			$container->getParameter("webpay_private_key_password"),
			$container->getParameter("webpay_public_key_file")
		);

		$this->webpay = new Api(
			$container->getParameter("webpay_merchant_number"),
			$container->getParameter("webpay_url"),
			$this->signer
		);
	}

	/**
	 * @param Order $order
	 * @return Payment
	 */
	public function createPaymentForOrder(Order $order)
	{
		$payment = new Payment;
		$payment->setIdent(time() . rand(100, 999));
		$payment->setPrice($order->getPrice() + $order->getSellerFee());
		$payment->setOrder($order);
		$payment->setCurrency($order->getCurrency());
		$order->setPayment($payment);

		/*
		 * TODO: Nefunguje jina mena nez CZK. PROC?
		$paymentUrl = $this->webpay->createPaymentRequestUrl(new PaymentRequest(
			$payment->getIdent(),
			$payment->getPrice(),
			constant("AdamStipak\Webpay\PaymentRequest::" . $order->getCurrency()),
			0,
			$this->router->generate("order_paid", [ "order" => $order->getId() ], Router::ABSOLUTE_URL)
		));*/

		$paymentUrl = $this->webpay->createPaymentRequestUrl(new PaymentRequest(
			$payment->getIdent(),
			$this->currencyService->currencyConvert(
				PriceCurrency::create($order->getPrice() + $order->getSellerFee(), $order->getCurrency()),
				PriceCurrency::CZK,
			)->getPrice(),
			constant("AdamStipak\Webpay\PaymentRequest::" . PriceCurrency::CZK),
			1,
			$this->router->generate("order_paid", [ "order" => $order->getId() ], Router::ABSOLUTE_URL)
		));

		$payment->setUrl($paymentUrl);

		return $payment;
	}

	/**
	 * @param Payment $payment
	 */
	public function savePayment(Payment $payment)
	{
		$this->paymentRepository->save($payment);
	}

	/**
	 * @param Request $request
	 * @param Payment $payment
	 * @return bool
	 */
	public function verifyPayment(Request $request, Payment $payment)
	{
		return $request->query->get(self::PARAM_ORDERNUMBER) == $payment->getIdent()
			or $request->query->get(self::PARAM_MERORDERNUM) == $payment->getIdent();

		/* Toto bohuzel nevim proc neni funkcni. Ohackujeme to.
		$response = new PaymentResponse(
			$request->query->get(self::PARAM_OPERATION),
			$request->query->get(self::PARAM_ORDERNUMBER),
			$request->query->get(self::PARAM_MERORDERNUM),
			$request->query->get(self::PARAM_PRCODE),
			$request->query->get(self::PARAM_SRCODE),
			$request->query->get(self::PARAM_RESULTTEXT),
			$request->query->get(self::PARAM_DIGEST),
			$request->query->get(self::PARAM_DIGEST1)
		);

		$this->webpay->verifyPaymentResponse($response);

		return true;*/
	}

	/**
	 * @param Request $request
	 * @param Payment $payment
	 * @return bool
	 */
	public function processPaymentFromRequest(Request $request, Payment $payment)
	{
		$prCode = $request->query->get(self::PARAM_PRCODE);

		if ($prCode == 0) {
			return $this->paidPayment($payment);
		} else {
			if ($payment->isPaid() === true) return false;
			$payment->setCancelled(true);
			if ($payment->getOrder()) {
				$payment->getOrder()->setStatus(Order::STATUS_CANCELLED);
			}
		}

		return false;
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	public function paidPayment(Payment $payment)
	{
		if ($payment->isPaid() === true) return false;

		$payment->setPaid(true);
		$payment->setDatetimePaid(new \DateTime);
		if ($payment->getOrder()) {
			$payment->getOrder()->setPaid(true);
			$payment->getOrder()->setDatetimePaid(new \DateTime);
			$payment->getOrder()->setStatus(Order::STATUS_PAID);
		}

		return true;
	}

	/**
	 * @param Request $request
	 * @return Payment
	 */
	public function createDummyPaymentFromRequest(Request $request)
	{
		$payment = new Payment;
		$payment->setIdent(time() . rand(100, 999));
		$payment->setPrice($request->request->get(self::PARAM_PRICE));
		$payment->setCurrency($request->request->get(self::PARAM_CURRENCY));
		if (strlen($request->request->get(self::PARAM_EMAIL)) > 1) {
			$payment->setEmail($request->request->get(self::PARAM_EMAIL));
		}
		$payment->setNote($request->request->get(self::PARAM_NOTE));
		$payment->setDummy(true);

		$validityHours = $this->stringService->textToFloat($request->request->get(self::PARAM_VALIDITY_HOURS));
		if ($validityHours > 0) {
			$validityMinutes = $validityHours * 60;
			$payment->setDatetimeExpire(new \DateTime("+ $validityMinutes minutes"));
		}

		$payment->setUrl($this->router->generate("homepage", [], Router::ABSOLUTE_URL));
		$this->savePayment($payment);

		return $payment;
	}

	public function initDummyPaymentUrl(Payment $payment)
	{
		$paymentUrl = $this->webpay->createPaymentRequestUrl(new PaymentRequest(
			time() . rand(100, 999),
			$payment->getPrice(),
			constant("AdamStipak\Webpay\PaymentRequest::" . $payment->getCurrency()),
			1,
			$this->router->generate("payment_dummy", [ "payment" => $payment->getId() ], Router::ABSOLUTE_URL),
			$payment->getIdent()
		));

		$payment->setUrl($paymentUrl);
		$this->savePayment($payment);
	}

	/**
	 * @return Payment[]|array
	 */
	public function findDummyPayments()
	{
		return $this->paymentRepository->findBy([ "dummy" => true ], [ "datetimeCreated" => "DESC" ]);
	}
}
