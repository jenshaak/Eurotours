<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 02.10.17
 * Time: 14:11
 */

namespace AppBundle\Service;


use AppBundle\Entity\ExternalTicket;
use AppBundle\Entity\InternalTicket;
use AppBundle\Entity\Line;
use AppBundle\Entity\Order;
use AppBundle\Entity\Payment;
use AppBundle\Entity\TicketInterface;
use AppBundle\Entity\User;
use AppBundle\VO\SeatsChange;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Translation\TranslatorInterface;

class EmailService
{
	/**
	 * @var \Swift_Mailer
	 */
	private $mailer;
	/**
	 * @var TwigEngine
	 */
	private $twigEngine;

	/** @var LanguageService */
	private $languageService;

	/** @var Container */
	private $container;

	/** @var TranslatorInterface */
	private $translator;

	/** @var UploadService */
	private $uploadService;

	/**
	 * @param \Swift_Mailer $mailer
	 */
	public function setMailer($mailer)
	{
		$this->mailer = $mailer;
	}

	/**
	 * @param TwigEngine $twigEngine
	 */
	public function setTwigEngine($twigEngine)
	{
		$this->twigEngine = $twigEngine;
	}

	/**
	 * @param LanguageService $languageService
	 */
	public function setLanguageService($languageService)
	{
		$this->languageService = $languageService;
	}

	/**
	 * @param Container $container
	 */
	public function setContainer($container)
	{
		$this->container = $container;
	}

	/**
	 * @param TranslatorInterface $translator
	 */
	public function setTranslator($translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param UploadService $uploadService
	 */
	public function setUploadService(UploadService $uploadService)
	{
		$this->uploadService = $uploadService;
	}

	/**
	 * @param Order $order
	 * @throws \Exception
	 * @throws \Twig\Error\Error
	 */
	public function sendOrderToAdminEmail(Order $order)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/orderToAdmin.html.twig", [
			"order" => $order
		]);

		$subject = $order->isInternal() ? "[I] " : "[E] ";

		if ($order->getOrderType() == Order::ORDER_TYPE_ORDER) {
			$subject .= "Objednávka";
		} else {
			$subject .= "Rezervace";
		}

		$subject .= " | " .$order->getRouteThere()->getDatetimeDeparture()->format("j.n.Y H:i") . " | ";
		$subject .= $order->getRouteThere()->getFromCity()->getName()->getString($this->languageService->getCzech()) . " => ";
		$subject .= $order->getRouteThere()->getToCity()->getName()->getString($this->languageService->getCzech());

		$message = $this->mailer->createMessage()
			->setSubject($subject)
			->setFrom($this->container->getParameter("email_sender"))
			->addTo($this->container->getParameter("email_sender"))
			->setReplyTo($order->getEmail())
			->setBody($body, "text/html");

		if ($order->getUserSeller()) {
			$message->addTo($order->getUserSeller()->getEmail());
			$message->setReplyTo($order->getUserSeller()->getEmail());
		}

		$this->mailer->send($message);
	}

	/**
	 * @param Order $order
	 * @throws \Exception
	 * @throws \Twig\Error\Error
	 */
	public function sendOrderPaidToAdminEmail(Order $order)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/orderPaidToAdmin.html.twig", [
			"order" => $order
		]);

		$subject = "Objednávka uhrazena";

		$subject .= " | " .$order->getRouteThere()->getDatetimeDeparture()->format("j.n.Y H:i") . " | ";
		$subject .= $order->getRouteThere()->getFromCity()->getName()->getString($this->languageService->getCzech()) . " => ";
		$subject .= $order->getRouteThere()->getToCity()->getName()->getString($this->languageService->getCzech());

		$message = $this->mailer->createMessage()
			->setSubject($subject)
			->setFrom($this->container->getParameter("email_sender"))
			->addTo($this->container->getParameter("email_sender"))
			->setReplyTo($order->getEmail())
			->setBody($body, "text/html");

		if ($order->getUserSeller()) {
			$message->addTo($order->getUserSeller()->getEmail());
			$message->setReplyTo($order->getUserSeller()->getEmail());
		}

		$this->mailer->send($message);
	}

	/**
	 * @param Order $order
	 * @throws \Twig\Error\Error
	 */
	public function sendTickets(Order $order)
	{
		if ($order->isInternal()) return;

		$body = $this->twigEngine->render("@App/Frontend/Email/tickets.html.twig", [
			"order" => $order
		]);

		$subject = $this->translator->trans("email.tickets.subject", [], null, $order->getLanguage()->getId())
			. " - " . $order->getRouteThere()->getToCity()->getName()->getString($order->getLanguage());

		$message = $this->mailer->createMessage()
			->setSubject($subject)
			->setFrom($this->container->getParameter("email_sender"))
			->setTo($order->getEmail())
			->setReplyTo("info@eurotours.cz")
			->setBody($body, "text/html");

		$message->setBcc("info@eurotours.cz");

		$order->getTickets()->map(function (TicketInterface $ticket) use ($message) {
			if ($ticket instanceof InternalTicket and $ticket->getAlternativeExternalTicket() instanceof ExternalTicket) return;
			$fileInfo = new \SplFileInfo($this->uploadService->getWebDir() . $ticket->getFile());
			$message->attach(\Swift_Attachment::newInstance(
				file_get_contents($this->uploadService->getWebDir() . $ticket->getFile()),
				"ticket." . $fileInfo->getExtension(),
				$ticket->getContentType()
			));
		});

		$this->mailer->send($message);
	}

	/**
	 * @param Order $order
	 */
	public function sendPaymentEmail(Order $order)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/orderPayment.html.twig", [
			"order" => $order
		]);

		$subject = "Platba online";

		$message = $this->mailer->createMessage()
			->setSubject($subject)
			->setFrom($this->container->getParameter("email_sender"))
			->setTo($order->getEmail())
			->setReplyTo("info@eurotours.cz")
			->setBody($body, "text/html");

		$this->mailer->send($message);
	}

	public function sendDummyPayment(Payment $payment)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/dummyPayment.html.twig", [
			"payment" => $payment
		]);

		$subject = "Platba online";

		$message = $this->mailer->createMessage()
			->setSubject($subject)
			->setFrom($this->container->getParameter("email_sender"))
			->setTo($payment->getEmail())
			->setReplyTo("info@eurotours.cz")
			->setBody($body, "text/html");

		$this->mailer->send($message);
	}

	public function sendSeatsChange(SeatsChange $seatsChange)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/seatsChange.html.twig", [
			"seatsChange" => $seatsChange,
			"datetimeChange" => new \DateTime
		]);

		$czech = $this->languageService->getCzech();

		$message = $this->mailer->createMessage()
			->setSubject("{$seatsChange->getSchedule()->getLine()->getCarrier()->getName()}: {$seatsChange->getDateDay()->format("j.n.")} z {$seatsChange->getSchedule()->getFirstScheduleLineStation()->getLineStation()->getCity()->getName()->getString($czech)} - volno " . count($seatsChange->getNewAvailableSeats()) . " míst")
			->setFrom($this->container->getParameter("email_sender"))
			->setTo($this->container->getParameter("email_sender"))
			->setBody($body, "text/html");

		$this->mailer->send($message);
	}

	/**
	 * @param string $phone
	 * @param string $email
	 * @param string $message
	 * @throws \Twig\Error\Error
	 */
	public function sendContactForm($phone, $email, $message)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/contactForm.html.twig", [
			"phone" => $phone,
			"email" => $email,
			"message" => $message
		]);

		$message = $this->mailer->createMessage()
			->setSubject("Kontaktní formulář {$email}")
			->setFrom($this->container->getParameter("email_sender"))
			->setTo($this->container->getParameter("email_sender"))
			->setReplyTo($email)
			->setBody($body, "text/html");

		$this->mailer->send($message);
	}

	/**
	 * @param Order $order
	 * @param InternalTicket[] $tickets
	 */
	public function sendSellerOrderCancelled(User $user, Order $order, $tickets)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/sellerOrderCancelled.html.twig", [
			"order" => $order,
			"tickets" => $tickets,
			"user" => $user
		]);

		$message = $this->mailer->createMessage()
			->setSubject("Prodejce {$user->getName()} stornoval jízdenky objednávky {$order->getIdent()}")
			->setFrom($this->container->getParameter("email_sender"))
			->setTo($this->container->getParameter("email_sender"))
			->setBody($body, "text/html");

		$this->mailer->send($message);
	}

	/**
	 * @param Order[] $orders
	 * @throws \Twig\Error\Error
	 */
	public function sendExpiredOrders($orders)
	{
		$body = $this->twigEngine->render("@App/Frontend/Email/expiredOrders.html.twig", [
			"orders" => $orders,
		]);

		$message = $this->mailer->createMessage()
			->setSubject("Expirované rezervace")
			->setFrom($this->container->getParameter("email_sender"))
			->setTo($this->container->getParameter("email_sender"))
			->setBody($body, "text/html");

		$this->mailer->send($message);
	}

    public function sendDummyPaymentPaidToAdminEmail(\AppBundle\Entity\Payment $payment)
    {
		$body = $this->twigEngine->render("@App/Frontend/Email/dummyPaymentPaidToAdmin.html.twig", [
			"payment" => $payment
		]);

		$message = $this->mailer->createMessage()
			->setSubject("Uhrazení platby")
			->setFrom($this->container->getParameter("email_sender"))
			->addTo($this->container->getParameter("email_sender"))
			->setReplyTo($payment->getEmail())
			->setBody($body, "text/html");

		$this->mailer->send($message);
    }


}
