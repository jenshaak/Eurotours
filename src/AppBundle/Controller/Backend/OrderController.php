<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 21.06.17
 * Time: 13:30
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\Activity;
use AppBundle\Entity\Book;
use AppBundle\Entity\BookInternal;
use AppBundle\Entity\ExternalTicket;
use AppBundle\Entity\InternalTicket;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\TicketInterface;
use AppBundle\Entity\User;
use AppBundle\Service\ActivityService;
use AppBundle\Service\BookService;
use AppBundle\Service\BuyService;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CountryService;
use AppBundle\Service\EmailService;
use AppBundle\Service\ExternalTicketService;
use AppBundle\Service\FlashService;
use AppBundle\Service\InternalTicketGeneratorService;
use AppBundle\Service\InternalTicketService;
use AppBundle\Service\JobService;
use AppBundle\Service\OrderPersonService;
use AppBundle\Service\OrderService;
use AppBundle\Service\PaymentService;
use AppBundle\Service\RouteTariffService;
use AppBundle\Service\UploadService;
use AppBundle\Service\UserService;
use AppBundle\VO\PriceCurrency;
use AppBundle\Widget\Backend\MenuWidget;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route(service="controller.backend.order")
 * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_SELLER')")
 */
class OrderController
{
	const PARAM_PRIMARY_KEY = "pk";
	const PARAM_VALUE = "value";
	const PARAM_NOTE = "note";
	const PARAM_RESERVATION_DATE = "reservationDate";
	const PARAM_MONTH = "month";

	/**
	 * @var OrderService
	 */
	private $orderService;
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
	 * @var BuyService
	 */
	private $buyService;
	/**
	 * @var CountryService
	 */
	private $countryService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var OrderPersonService
	 */
	private $orderPersonService;
	/**
	 * @var RouteTariffService
	 */
	private $routeTariffService;
	/**
	 * @var FlashService
	 */
	private $flashService;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var BookService
	 */
	private $bookService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var ExternalTicketService
	 */
	private $externalTicketService;
	/**
	 * @var InternalTicketGeneratorService
	 */
	private $internalTicketGeneratorService;
	/**
	 * @var ActivityService
	 */
	private $activityService;
	/**
	 * @var UploadService
	 */
	private $uploadService;
	/**
	 * @var JobService
	 */
	private $jobService;

	public function __construct(OrderService $orderService,
	                            MenuWidget $menuWidget,
	                            PaymentService $paymentService,
	                            Router $router,
	                            BuyService $buyService,
	                            CountryService $countryService,
	                            CarrierService $carrierService,
	                            EmailService $emailService,
	                            OrderPersonService $orderPersonService,
	                            RouteTariffService $routeTariffService,
	                            FlashService $flashService,
	                            InternalTicketService $internalTicketService,
	                            BookService $bookService,
	                            UserService $userService,
	                            ExternalTicketService $externalTicketService,
	                            InternalTicketGeneratorService $internalTicketGeneratorService,
	                            ActivityService $activityService,
	                            UploadService $uploadService,
	                            JobService $jobService)
	{
		$this->orderService = $orderService;
		$this->menuWidget = $menuWidget;
		$this->paymentService = $paymentService;
		$this->router = $router;
		$this->buyService = $buyService;
		$this->countryService = $countryService;
		$this->carrierService = $carrierService;
		$this->emailService = $emailService;
		$this->orderPersonService = $orderPersonService;
		$this->routeTariffService = $routeTariffService;
		$this->flashService = $flashService;
		$this->internalTicketService = $internalTicketService;
		$this->bookService = $bookService;
		$this->userService = $userService;
		$this->externalTicketService = $externalTicketService;
		$this->internalTicketGeneratorService = $internalTicketGeneratorService;
		$this->activityService = $activityService;
		$this->uploadService = $uploadService;
		$this->jobService = $jobService;

		$this->menuWidget->setActive(MenuWidget::ACTIVE_ORDERS);
	}

	/**
	 * @Route(path="/backend/orders", name="backend_orders", methods={"GET"})
	 * @Template()
	 * @param Request $request
	 * @return array
	 */
	public function ordersAction(Request $request)
	{
		$orderFilter = $this->orderService->createOrderFilterFromRequest($request);
		$orderFilter->setOnlyNonDraft(true);
		$orderFilter->setLimit(200);

		if ($this->userService->isSeller()) {
			$orderFilter->setUserSeller($this->userService->getCurrentUser());
		}

		$orders = $this->orderService->findOrders($orderFilter);

		$orderSummarizeFilter = clone $orderFilter;
		$orderSummarizeFilter->setLimit(0);
		$orderSummarizeFilter->setOffset(0);
		$summarizedOrders = $this->orderService->summarizeOrders($orderSummarizeFilter);

		$nextPageUrl = null;
		if (count($orders) > 0) {
			$nextPageQuery = $request->query->all();
			$nextPageQuery['offset'] = $orderFilter->getOffset() + $orderFilter->getLimit();
			$nextPageUrl = $this->router->generate("backend_orders", $nextPageQuery);
		}

		return [
			"orders" => $orders,
			"carriers" => $this->carrierService->findAllCarriers(),
			"sellers" => array_filter(
				$this->userService->findAllUsers(),
				fn (User $user) => in_array(User::ROLE_SELLER, $user->getRoles()) || in_array(User::ROLE_SUPER_ADMIN, $user->getRoles())
			),
			"orderFilter" => $orderFilter,
			"nextPageUrl" => $nextPageUrl,
			"monthBefore" => [ "month" => (new \DateTime("-1 month"))->format("n"), "year" => (new \DateTime("-1 month"))->format("Y") ],
			"datesForFilter" => $this->createDatesForFilter(),
			"datesForFilterWithFuture" => $this->createDatesForFilter(6),
			"commissionPrice" => isset($commissionPrice) ? $commissionPrice : null,
			"summarize" => $summarizedOrders,
		];
	}

	/**
	 * @Route(path="/backend/orders/export.html", name="backend_orders_export", methods={"GET"})
	 * @Template("@App/Backend/Order/orderExport.html.twig")
	 * @param Request $request
	 * @return array
	 */
	public function exportOrdersAction(Request $request)
	{
		$orderFilter = $this->orderService->createOrderFilterFromRequest($request);
		$orderFilter->setOnlyNonDraft(true);

		if ($this->userService->isSeller()) {
			$orderFilter->setUserSeller($this->userService->getCurrentUser());
		}

		$tickets = new ArrayCollection;
		array_map(function (Order $order) use ($tickets, $orderFilter) {
			$order->getInternalTicketsWithoutDuplicities()->filter(function (InternalTicket $ticket) use ($orderFilter, $order) {
				return ($orderFilter->getCarrier() === null or $orderFilter->getCarrier() === $ticket->getCarrier())
					and $ticket->isBilled()
					and !$ticket->isCancelled()
					and ($orderFilter->getMonthBilled() === null or ($ticket->getDateMonthBilled() and $ticket->getDateMonthBilled()->format("Ym") === $orderFilter->getMonthBilled()->format("Ym")));
			})->map(function (TicketInterface $ticket) use ($tickets) {
				$tickets->add($ticket);
			});
		}, $this->orderService->findOrders($orderFilter));

		$currencies = array_unique($tickets->map(fn (InternalTicket $ticket) => $ticket->getCurrency())->getValues());

		$sumPrices = array_reduce($tickets->getValues(), function(array $sumPrices, InternalTicket $ticket) {
			if (!isset($sumPrices[$ticket->getCurrency()])) {
				$sumPrices[$ticket->getCurrency()] = PriceCurrency::create(0, $ticket->getCurrency());
			}
			$sumPrices[$ticket->getCurrency()]->addPrice($ticket->getPriceCurrency()->getPrice());
			return $sumPrices;
		}, []);

		if ($orderFilter->getCarrier()) {
			if ($commission = $orderFilter->getCarrier()->getCommission()) {
				$commissionPrices = array_reduce(
					$sumPrices,
					function (array $prices, PriceCurrency $price) use ($commission) {
						$prices[$price->getCurrency()] = PriceCurrency::create(
							$price->getPrice() / 100 * $commission, $price->getCurrency()
						);
						return $prices;
					}, []
				);

				$toPaidPrices = array_reduce(
					$sumPrices,
					function (array $prices, PriceCurrency $price) use ($commission) {
						$prices[$price->getCurrency()] = PriceCurrency::create(
							$price->getPrice() - ($price->getPrice() / 100 * $commission), $price->getCurrency()
						);
						return $prices;
					}, []
				);
			}
		}

		$return = [
			"tickets" => $tickets->getValues(),
			"orderFilter" => $orderFilter,
			"sumPrices" => $sumPrices,
			"commissionPrices" => isset($commissionPrices) ? $commissionPrices : null,
			"toPaidPrices" => isset($toPaidPrices) ? $toPaidPrices : null,
			"currencies" => $currencies
		];

		return $return;
	}

	private function createDatesForFilter($futureMonths = 0)
	{
		static $monthsLanguage = [
			"Leden",
			"Únor",
			"Březen",
			"Duben",
			"Květen",
			"Červen",
			"Červenec",
			"Srpen",
			"Září",
			"Říjen",
			"Listopad",
			"Prosinec"
		];

		$return = [];
		$datetime = new \DateTime("+" . ($futureMonths + 1) . " month");
		foreach (range(0, 35) as $m) {
			$datetime->modify("-1 months");
			if (!isset($return[$datetime->format("Y")])) {
				$return[$datetime->format("Y")] = (object) [
					"months" => [],
					"year" => $datetime->format("Y"),
				];
			}
			$return[$datetime->format("Y")]->months[] = (object) [
				"month" => $datetime->format("m"),
				"monthText" => $monthsLanguage[$datetime->format("n")-1]
			];
		}
		return $return;
	}

	/**
	 * @Route(path="/backend/orders-create", name="backend_create_order", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function createOrderAction()
	{
		return [
			"countries" => $this->countryService->findAllCountries(),
			"carriers" => $this->carrierService->findAllCarriers()
		];
	}

	/**
	 * @Route(path="/backend/orders/{order}", name="backend_order", methods={"GET"})
	 * @Template()
	 * @param Order $order
	 * @return array
	 */
	public function orderAction(Order $order)
	{
		return [
			"order" => $order,
			"activities" => $this->activityService->findActivitiesForOrder($order)
		];
	}

	/**
	 * @Route(path="/backend/orders/{order}/create-payment", name="backend_order_create_payment", methods={"GET"})
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function createPaymentAction(Order $order)
	{
		$payment = $this->paymentService->createPaymentForOrder($order);
		$this->paymentService->savePayment($payment);
		$this->orderService->saveOrder($order);

		$activity = Activity::create($order, Activity::TYPE_PAYMENT_CREATED);
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/paid-wire-transfer", name="backend_order_paid_wire_transfer", methods={"POST"})
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function paidWireTransferAction(Order $order)
	{
		$order->setPaid(true);
		$order->setPaidBy(Order::PAID_BY_WIRE_TRANSFER);
		$order->setDatetimePaid(new \DateTime);
		$order->setStatus(Order::STATUS_PAID);
		$order->setUserCashier($this->userService->getCurrentUser());
		$this->orderService->saveOrder($order);

		$activity = Activity::create($order, Activity::TYPE_ORDER_PAID);
		$activity->setContentText("Objednávka označena jako uhrazená převodem.");
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		$this->jobService->buyOrder($order);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/paid-cash", name="backend_order_paid_cash", methods={"POST"})
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function paidCashAction(Order $order)
	{
		$order->setPaid(true);
		$order->setPaidBy(Order::PAID_BY_CASH);
		$order->setDatetimePaid(new \DateTime);
		$order->setStatus(Order::STATUS_PAID);
		$order->setUserCashier($this->userService->getCurrentUser());
		$this->orderService->saveOrder($order);

		$activity = Activity::create($order, Activity::TYPE_ORDER_PAID);
		$activity->setContentText("Objednávka označena jako uhrazená hotově.");
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		$this->jobService->buyOrder($order);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/delete", name="backend_order_delete", methods={"POST"})
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function deleteOrderAction(Order $order)
	{
		$order->setDeleted(true);
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $internalTicket) {
			$internalTicket->setCancelled(true);
			$internalTicket->setDatetimeCancelled(new \DateTime);
			$this->internalTicketService->saveInternalTicket($internalTicket);
		});

		$order->getBooks()->map(function (Book $book) {
			$book->setCancelled(true);
			$book->setDatetimeCancelled(new \DateTime);
			$this->bookService->saveBook($book);
		});

		$this->flashService->addNotice("Objednávka odstraněna. Interní jízdenky byly zrušeny. Externí jízdenky je potřeba stornovat u daného dopravce.");

		return RedirectResponse::create($this->router->generate("backend_orders"));
	}

	/**
	 * @Route(path="/backend/orders/{order}/cancel", name="backend_order_cancel", methods={"POST"})
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function cancelOrderAction(Order $order)
	{
		$order->setCancelled(true);
		$order->setUserCancelled($this->userService->getCurrentUser());
		$order->setDatetimeCancelled(new \DateTime);
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $internalTicket) {
			$internalTicket->setCancelled(true);
			$internalTicket->setDatetimeCancelled(new \DateTime);
			$this->internalTicketService->saveInternalTicket($internalTicket);
		});

		$order->getBooks()->map(function (Book $book) {
			$book->setCancelled(true);
			$book->setDatetimeCancelled(new \DateTime);
			$this->bookService->saveBook($book);
		});

		if ($this->userService->isSeller()) {
			$this->emailService->sendSellerOrderCancelled(
				$this->userService->getCurrentUser(),
				$order,
				$order->getInternalTickets()->toArray()
			);
		}

		$activity = Activity::create($order, Activity::TYPE_ORDER_CANCEL);
		$activity->setContentText("Objednávka kompletně stornována.");
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		$this->flashService->addNotice("Objednávka stornována. Interní jízdenky byly zrušeny. Externí jízdenky je potřeba stornovat u daného dopravce.");

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/send-payment-email", name="backend_order_send_payment_email", methods={"POST"})
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function sendPaymentEmailAction(Order $order)
	{
		$this->emailService->sendPaymentEmail($order);
		$order->setDatetimeSentPayment(new \DateTime);
		$this->orderService->saveOrder($order);

		$activity = Activity::create($order, Activity::TYPE_PAYMENT_SEND_EMAIL);
		$activity->setContentText("Odesláno na e-mail {$order->getEmail()}.");
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/tickets/{ticket}/setName", name="backend_ajax_ticket_name", methods={"POST"})
	 * @param Order $order
	 * @param InternalTicket $internalTicket
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function ajaxTicketNameAction(Order $order, InternalTicket $ticket, Request $request)
	{
		$ticket->getOrderPerson()->setName($request->request->get(self::PARAM_VALUE));
		$this->orderPersonService->saveOrderPerson($ticket->getOrderPerson());

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/tickets/{ticket}/setPrice", name="backend_ajax_ticket_price", methods={"POST"})
	 * @param Order $order
	 * @param InternalTicket $internalTicket
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function ajaxTicketPriceAction(Order $order, InternalTicket $ticket, Request $request)
	{
		$ticket->setPrice($request->request->get(self::PARAM_VALUE));
		$ticket->setPriceIncludeSurcharge($request->request->get(self::PARAM_VALUE));
		$this->internalTicketService->saveInternalTicket($ticket);

		$order->recountPrice();
		$this->orderService->saveOrder($order);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/books/{book}/setPrice", name="backend_ajax_book_price", methods={"POST"})
	 * @param Order $order
	 * @param BookInternal $book
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function ajaxBookPriceAction(Order $order, BookInternal $book, Request $request)
	{
		if ($order->getTickets()->isEmpty()) {
			$book->setPrice($request->request->get(self::PARAM_VALUE));
			$book->setPriceIncludeSurcharge($request->request->get(self::PARAM_VALUE));
			$this->bookService->saveBook($book);

			$order->recountPrice();
			$this->orderService->saveOrder($order);
		}

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/orders/{order}/{internalTicket}/cancelTicket", name="backend_ticket_cancel", methods={"POST"})
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function cancelTicketAction(Order $order, InternalTicket $internalTicket)
	{
		if ($internalTicket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		$internalTicket->setCancelled(true);
		$internalTicket->setDatetimeCancelled(new \DateTime);
		$this->internalTicketService->saveInternalTicket($internalTicket);

		$book = $internalTicket->getBook();
		if ($book) {
			$book->setCancelled(true);
			$book->setDatetimeCancelled(new \DateTime);
			$this->bookService->saveBook($book);
		}

		$orderCancelled = !$order->getInternalTickets()->exists(function ($i, InternalTicket $ticket) {
			return !$ticket->isCancelled();
		});

		if ($orderCancelled) {
			$order->setCancelled(true);
			$order->setUserCancelled($this->userService->getCurrentUser());
			$order->setDatetimeCancelled(new \DateTime);
			$this->orderService->saveOrder($order);
		}

		if ($this->userService->isSeller()) {
			$this->emailService->sendSellerOrderCancelled(
				$this->userService->getCurrentUser(),
				$order,
				[ $internalTicket ]
			);
		}

		$this->flashService->addNotice("Jízdenka stornována.");

		$activity = Activity::create($order, Activity::TYPE_TICKET_CANCEL);
		$activity->setContentText("Stornována jízdenka pro '{$internalTicket->getOrderPerson()->getName()}'.");
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/internalTicket/{ticket}/sellerPaid", name="backend_internal_ticket_seller_paid", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function internalTicketSellerPaidAction(Order $order, InternalTicket $ticket, Request $request)
	{
		$note = $request->request->get(self::PARAM_NOTE);

		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		if (!empty($note)) {
			$ticket->setNoteSellerPaid($note);
		}

		$ticket->setSellerPaid(true);
		$ticket->setDatetimeSellerPaid(new \DateTime);

		$this->internalTicketService->saveInternalTicket($ticket);
		$this->flashService->addNotice("Jízdenka prodejce označena jako 'přijatá'.");

		$orderPaid = !$order->getInternalTickets()->exists(function ($i, InternalTicket $ticket) {
			return !$ticket->isSellerPaid();
		});

		if ($orderPaid) {
			$order->setNoteSellerPaid($note);
			$order->setSellerPaid(true);
			$order->setDatetimeSellerPaid(new \DateTime);
			$this->orderService->saveOrder($order);
		}

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/** @Route(path="/backend/orders/{order}/internalTicket/{ticket}/paidByBank", name="backend_internal_ticket_paid_by_bank", methods={"POST"}) */
	public function internalTicketPaidByBankAction(Order $order, InternalTicket $ticket, Request $request): RedirectResponse
	{
		$note = $request->request->get(self::PARAM_NOTE);

		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		if (!empty($note)) {
			$ticket->setNotePaidByBank($note);
		}

		$ticket->setPaidByBank(true);
		$ticket->setDatetimePaidByBank(new DateTime);

		$this->internalTicketService->saveInternalTicket($ticket);
		$this->flashService->addNotice("Jízdenka označena jako 'zaplacena bankou'.");

		$orderPaid = !$order->getTicketsWithoutDuplicities()->exists(function ($i, $ticket) {
			return !$ticket->isPaidByBank();
		});

		if ($orderPaid) {
			$order->setNotePaidByBank($note);
			$order->setPaidByBank(true);
			$order->setDatetimePaidByBank(new DateTime);
			$this->orderService->saveOrder($order);
		}

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/** @Route(path="/backend/orders/{order}/externalTicket/{ticket}/paidByBank", name="backend_external_ticket_paid_by_bank", methods={"POST"}) */
	public function externalTicketPaidByBankAction(Order $order, ExternalTicket $ticket, Request $request): RedirectResponse
	{
		$note = $request->request->get(self::PARAM_NOTE);

		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		if (!empty($note)) {
			$ticket->setNotePaidByBank($note);
		}

		$ticket->setPaidByBank(true);
		$ticket->setDatetimePaidByBank(new DateTime);

		$this->externalTicketService->saveExternalTicket($ticket);
		$this->flashService->addNotice("Jízdenka označena jako 'zaplacena bankou'.");

		$orderPaid = !$order->getTicketsWithoutDuplicities()->exists(function ($i, $ticket) {
			return !$ticket->isPaidByBank();
		});

		if ($orderPaid) {
			$order->setNotePaidByBank($note);
			$order->setPaidByBank(true);
			$order->setDatetimePaidByBank(new DateTime);
			$this->orderService->saveOrder($order);
		}

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/activityNote", name="backend_order_activity_note", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function orderActivityNoteAction(Order $order, Request $request)
	{
		$note = $request->request->get(self::PARAM_NOTE);

		$activity = Activity::create($order, Activity::TYPE_NOTE);
		$activity->setContentText($note);
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/setReservationDate", name="backend_set_reservation_date", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function orderSetReservationDateAction(Order $order, Request $request)
	{
		$dateReservation = new \DateTime($request->request->get(self::PARAM_RESERVATION_DATE));
		$order->setDateReservationDay($dateReservation);
		$this->orderService->saveOrder($order);

		$activity = Activity::create($order, Activity::TYPE_DATETIME_RESERVATION_CHANGED);
		$activity->setContentText("Rezervováno do {$order->getDateReservationDay()->format("j.n.Y")}.");
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/sellerPaid", name="backend_ajax_order_seller_paid", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxOrderSellerPaidAction(Order $order, Request $request)
	{
		$note = $request->request->get(self::PARAM_NOTE);

		$order->setNoteSellerPaid($note);
		$order->setSellerPaid(true);
		$order->setDatetimeSellerPaid(new \DateTime);
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $ticket) use ($note) {
			$ticket->setNoteSellerPaid($note);
			$ticket->setSellerPaid(true);
			$ticket->setDatetimeSellerPaid(new \DateTime);
			$this->internalTicketService->saveInternalTicket($ticket);
		});

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/sellerPaid/cancel", name="backend_ajax_order_seller_paid_cancel", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxOrderSellerPaidCancelAction(Order $order)
	{
		$order->setNoteSellerPaid(null);
		$order->setSellerPaid(false);
		$order->setDatetimeSellerPaid(null);
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $ticket) {
			$ticket->setNoteSellerPaid(null);
			$ticket->setSellerPaid(false);
			$ticket->setDatetimeSellerPaid(null);
			$this->internalTicketService->saveInternalTicket($ticket);
		});

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/billed", name="backend_ajax_order_billed", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxOrderBilledAction(Order $order, Request $request)
	{
		$note = $request->request->get(self::PARAM_NOTE);

		$order->setNoteBilled($note);
		$order->setBilled(true);
		$order->setDatetimeBilled(new \DateTime);
		$order->setUserBilled($this->userService->getCurrentUser());
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $ticket) use ($note, $request, $order) {
			$ticket->setDateMonthBilled(new \DateTime($request->request->get(self::PARAM_MONTH) . "-01"));
			$ticket->setNoteBilled($note);
			$ticket->setBilled(true);
			$ticket->setDatetimeBilled(new \DateTime);
			$this->internalTicketService->saveInternalTicket($ticket);

			$activity = Activity::create($order, Activity::TYPE_TICKET_BILLED);
			$activity->setContentText($ticket->getOrderPerson()->getName());
			$activity->setUser($this->userService->getCurrentUser());
			$this->activityService->saveActivity($activity);
		});

		$order->getExternalTickets()->map(function (ExternalTicket $ticket) use ($note, $request, $order) {
			$ticket->setDateMonthBilled(new \DateTime($request->request->get(self::PARAM_MONTH) . "-01"));
			$ticket->setNoteBilled($note);
			$ticket->setBilled(true);
			$ticket->setDatetimeBilled(new \DateTime);
			$this->externalTicketService->saveExternalTicket($ticket);

			$activity = Activity::create($order, Activity::TYPE_TICKET_BILLED);
			$activity->setContentText($ticket->getOrderPerson()->getName());
			$activity->setUser($this->userService->getCurrentUser());
			$this->activityService->saveActivity($activity);
		});

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/billed/cancel", name="backend_ajax_order_billed_cancel", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxOrderBilledCancelAction(Order $order)
	{
		$order->setNoteBilled(null);
		$order->setBilled(false);
		$order->setDatetimeBilled(null);
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $ticket) use ($order) {
			$ticket->setNoteBilled(null);
			$ticket->setBilled(false);
			$ticket->setDatetimeBilled(null);
			$ticket->setDateMonthBilled(null);
			$this->internalTicketService->saveInternalTicket($ticket);

			$activity = Activity::create($order, Activity::TYPE_TICKET_BILLED_CANCEL);
			$activity->setContentText($ticket->getOrderPerson()->getName());
			$activity->setUser($this->userService->getCurrentUser());
			$this->activityService->saveActivity($activity);
		});

		$order->getExternalTickets()->map(function (ExternalTicket $ticket) use ($order) {
			$ticket->setNoteBilled(null);
			$ticket->setBilled(false);
			$ticket->setDatetimeBilled(null);
			$ticket->setDateMonthBilled(null);
			$this->externalTicketService->saveExternalTicket($ticket);

			$activity = Activity::create($order, Activity::TYPE_TICKET_BILLED_CANCEL);
			$activity->setContentText($ticket->getOrderPerson()->getName());
			$activity->setUser($this->userService->getCurrentUser());
			$this->activityService->saveActivity($activity);
		});

		return JsonResponse::create();
	}

	/** @Route(path="/backend/_ajax/orders/{order}/paidByBank", name="backend_ajax_order_paid_by_bank", methods={"POST"}) */
	public function ajaxOrderPaidByBankAction(Request $request, Order $order): JsonResponse
	{
		$note = $request->request->get(self::PARAM_NOTE);

		$order->setNotePaidByBank($note);
		$order->setPaidByBank(true);
		$order->setDatetimePaidByBank(new DateTime());
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $ticket) use ($note) {
			$ticket->setNotePaidByBank($note);
			$ticket->setPaidByBank(true);
			$ticket->setDatetimePaidByBank(new DateTime);
			$this->internalTicketService->saveInternalTicket($ticket);
		});

		$order->getExternalTickets()->map(function (ExternalTicket $ticket) use ($note) {
			$ticket->setNotePaidByBank($note);
			$ticket->setPaidByBank(true);
			$ticket->setDatetimePaidByBank(new DateTime);
			$this->externalTicketService->saveExternalTicket($ticket);
		});

		return JsonResponse::create();
	}

	/** @Route(path="/backend/_ajax/orders/{order}/paidByBank/cancel", name="backend_ajax_order_paid_by_bank_cancel", methods={"POST"}) */
	public function ajaxOrderPaidByBankCancelAction(Order $order): JsonResponse
	{
		$order->setNotePaidByBank(null);
		$order->setPaidByBank(false);
		$order->setDatetimePaidByBank(null);
		$this->orderService->saveOrder($order);

		$order->getInternalTickets()->map(function (InternalTicket $ticket) {
			$ticket->setNotePaidByBank(null);
			$ticket->setPaidByBank(false);
			$ticket->setDatetimePaidByBank(null);
			$this->internalTicketService->saveInternalTicket($ticket);
		});

		$order->getExternalTickets()->map(function (ExternalTicket $ticket) {
			$ticket->setNotePaidByBank(null);
			$ticket->setPaidByBank(false);
			$ticket->setDatetimePaidByBank(null);
			$this->externalTicketService->saveExternalTicket($ticket);
		});

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/tickets/{ticket}/billed", name="backend_ajax_ticket_billed", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxTicketBilledAction(Order $order, InternalTicket $ticket, Request $request)
	{
		$this->billedTicket($order, $ticket, $request);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/tickets/{ticket}/billed/cancel", name="backend_ajax_ticket_billed_cancel", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxTicketBilledCancelAction(Order $order, InternalTicket $ticket)
	{
		$this->billedCancelTicket($order, $ticket);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/tickets/{ticket}/sellerPaid", name="backend_ajax_ticket_seller_paid", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxTicketSellerPaidAction(Order $order, InternalTicket $ticket, Request $request)
	{
		$this->sellerPaidTicket($order, $ticket, $request);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/tickets/{ticket}/sellerPaid/cancel", name="backend_ajax_ticket_seller_paid_cancel", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxTicketSellerPaidCancelAction(Order $order, InternalTicket $ticket)
	{
		$this->sellerPaidCancelTicket($order, $ticket);

		return JsonResponse::create();
	}

	/** @Route(path="/backend/_ajax/orders/{order}/internalTickets/{ticket}/paidByBank", name="backend_ajax_internal_ticket_paid_by_bank", methods={"POST"}) */
	public function ajaxInternalTicketPaidByBankAction(Order $order, InternalTicket $ticket, Request $request): JsonResponse
	{
		$this->paidByBankTicket($order, $ticket, $request);

		return JsonResponse::create();
	}

	/** @Route(path="/backend/_ajax/orders/{order}/externalTickets/{ticket}/paidByBank", name="backend_ajax_external_ticket_paid_by_bank", methods={"POST"}) */
	public function ajaxExternalTicketPaidByBankAction(Order $order, ExternalTicket $ticket, Request $request): JsonResponse
	{
		$this->paidByBankTicket($order, $ticket, $request);

		return JsonResponse::create();
	}

	/** @Route(path="/backend/_ajax/orders/{order}/internalTickets/{ticket}/paidByBank/cancel", name="backend_ajax_internal_ticket_paid_by_bank_cancel", methods={"POST"}) */
	public function ajaxInternalTicketPaidByBankCancelAction(Order $order, InternalTicket $ticket): JsonResponse
	{
		$this->paidByBankCancelTicket($order, $ticket);

		return JsonResponse::create();
	}

	/** @Route(path="/backend/_ajax/orders/{order}/externalTickets/{ticket}/paidByBank/cancel", name="backend_ajax_external_ticket_paid_by_bank_cancel", methods={"POST"}) */
	public function ajaxExternalTicketPaidByBankCancelAction(Order $order, ExternalTicket $ticket): JsonResponse
	{
		$this->paidByBankCancelTicket($order, $ticket);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/orders/{order}/regenerateInternalTickets", name="backend_regenerate_internal_tickets", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function regenerateInternalTicketsAction(Order $order): RedirectResponse
	{
		$order->getInternalTickets()->map(function (InternalTicket $internalTicket) {
			$this->internalTicketGeneratorService->generateTicket($internalTicket);
			$this->internalTicketService->saveInternalTicket($internalTicket);
		});

		$this->flashService->addNotice("Interní jízdenky přegenerovány.");

		return RedirectResponse::create(
			$this->router->generate("backend_order", [ "order" => $order->getId() ])
		);
	}

	/**
	 * @Route(path="/backend/orders/{order}/tickets.pdf", name="backend_export_order_tickets_pdf", methods={"POST"})
	 * @return BinaryFileResponse
	 */
	public function exportOrderTicketsAction(Request $request, Order $order): Response
	{
		$files = $order->getTicketsWithoutDuplicities()->map(function (TicketInterface $ticket) use ($request) {
			$file = $request->get("withPrice", true) ? $ticket->getFile() : str_replace(".pdf", "_wop.pdf", $ticket->getFile());
			return $this->uploadService->getWebDir() . $file;
		});
		$allFilesExists =  $files->forAll(function ($i, $file) {return file_exists($file);});
		if(!$allFilesExists) {
			$this->flashService->addError("Některé soubory nebyly nalezeny. Pregenerujte prosím jízdenky.");
			return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
		}

		$file = tempnam($this->uploadService->getWebDir() . "/../tmp/", "order-tickets-" . $order->getIdent() . ".pdf");

		exec("/usr/bin/gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile={$file} " . implode(" ", $files->toArray()));
		//exec("/usr/local/bin/gs -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile={$file} " . implode(" ", $files));

		return BinaryFileResponse::create($file);
	}

	/**
	 * @Route(path="/backend/orders/{order}/internalTicket/{internalTicket}/billed", name="backend_internal_ticket_billed", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function internalTicketBilledAction(Order $order, InternalTicket $internalTicket, Request $request)
	{
		$this->billedTicket($order, $internalTicket, $request);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/backend/orders/{order}/externalTicket/{externalTicket}/billed", name="backend_external_ticket_billed", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function externalTicketBilledAction(Order $order, InternalTicket $internalTicket, Request $request)
	{
		$this->billedTicket($order, $internalTicket, $request);

		return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));
	}

	/**
	 * @param Order $order
	 * @param InternalTicket|ExternalTicket $ticket
	 * @param Request $request
	 */
	public function billedTicket(Order $order, $ticket, Request $request)
	{
		$note = $request->request->get(self::PARAM_NOTE);

		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		if (!empty($note)) {
			$ticket->setNoteBilled($note);
		}

		$ticket->setBilled(true);
		$ticket->setDatetimeBilled(new \DateTime);
		$ticket->setDateMonthBilled(new \DateTime($request->request->get(self::PARAM_MONTH) . "-01"));

		if ($ticket instanceof InternalTicket) {
			$this->internalTicketService->saveInternalTicket($ticket);
		} elseif ($ticket instanceof ExternalTicket) {
			$this->externalTicketService->saveExternalTicket($ticket);
		}

		$orderBilled = !$order->getTicketsWithoutDuplicities()->exists(function ($i, InternalTicket $ticket) {
			return !$ticket->isBilled();
		});

		$activity = Activity::create($order, Activity::TYPE_TICKET_BILLED);
		$activity->setContentText($ticket->getOrderPerson()->getName());
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);

		if ($orderBilled) {
			$order->setNoteBilled($note);
			$order->setBilled(true);
			$order->setUserBilled($this->userService->getCurrentUser());
			$order->setDatetimeBilled(new \DateTime);
			$this->orderService->saveOrder($order);
		}
	}

	/**
	 * @param Order $order
	 * @param InternalTicket|ExternalTicket $ticket
	 */
	public function billedCancelTicket(Order $order, $ticket)
	{
		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		$ticket->setNoteBilled(null);
		$ticket->setBilled(false);
		$ticket->setDatetimeBilled(null);
		$ticket->setDateMonthBilled(null);

		if ($ticket instanceof InternalTicket) {
			$this->internalTicketService->saveInternalTicket($ticket);
		} elseif ($ticket instanceof ExternalTicket) {
			$this->externalTicketService->saveExternalTicket($ticket);
		}

		$order->setBilled(false);
		$order->setDatetimeBilled(null);
		$this->orderService->saveOrder($order);

		$activity = Activity::create($order, Activity::TYPE_TICKET_BILLED_CANCEL);
		$activity->setContentText($ticket->getOrderPerson()->getName());
		$activity->setUser($this->userService->getCurrentUser());
		$this->activityService->saveActivity($activity);
	}

	/**
	 * @param Order $order
	 * @param InternalTicket|ExternalTicket $ticket
	 * @param Request $request
	 */
	public function sellerPaidTicket(Order $order, $ticket, Request $request)
	{
		$note = $request->request->get(self::PARAM_NOTE);

		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		if (!empty($note)) {
			$ticket->setNoteSellerPaid($note);
		}

		$ticket->setSellerPaid(true);
		$ticket->setDatetimeSellerPaid(new \DateTime);

		if ($ticket instanceof InternalTicket) {
			$this->internalTicketService->saveInternalTicket($ticket);
		} elseif ($ticket instanceof ExternalTicket) {
			$this->externalTicketService->saveExternalTicket($ticket);
		}

		$orderSellerPaid = !$order->getTicketsWithoutDuplicities()->exists(function ($i, InternalTicket $ticket) {
			return !$ticket->isSellerPaid();
		});

		if ($orderSellerPaid) {
			$order->setNoteSellerPaid($note);
			$order->setSellerPaid(true);
			$order->setDatetimeSellerPaid(new \DateTime);
			$this->orderService->saveOrder($order);
		}
	}

	/**
	 * @param Order $order
	 * @param InternalTicket|ExternalTicket $ticket
	 */
	public function sellerPaidCancelTicket(Order $order, $ticket)
	{
		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		$ticket->setNoteSellerPaid(null);
		$ticket->setSellerPaid(false);
		$ticket->setDatetimeSellerPaid(null);

		if ($ticket instanceof InternalTicket) {
			$this->internalTicketService->saveInternalTicket($ticket);
		} elseif ($ticket instanceof ExternalTicket) {
			$this->externalTicketService->saveExternalTicket($ticket);
		}

		$order->setNoteSellerPaid(null);
		$order->setSellerPaid(false);
		$order->setDatetimeSellerPaid(null);
		$this->orderService->saveOrder($order);
	}

	/** @param InternalTicket|ExternalTicket $ticket */
	public function paidByBankTicket(Order $order, $ticket, Request $request): void
	{
		$note = $request->request->get(self::PARAM_NOTE);

		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		if (!empty($note)) {
			$ticket->setNotePaidByBank($note);
		}

		$ticket->setPaidByBank(true);
		$ticket->setDatetimePaidByBank(new DateTime);

		if ($ticket instanceof InternalTicket) {
			$this->internalTicketService->saveInternalTicket($ticket);
		} elseif ($ticket instanceof ExternalTicket) {
			$this->externalTicketService->saveExternalTicket($ticket);
		}

		$orderPaidByBank = !$order->getTicketsWithoutDuplicities()->exists(function ($i, $ticket) {
			return !$ticket->isPaidByBank();
		});

		if ($orderPaidByBank) {
			$order->setNotePaidByBank($note);
			$order->setPaidByBank(true);
			$order->setDatetimePaidByBank(new DateTime);
			$this->orderService->saveOrder($order);
		}
	}

	/** @param InternalTicket|ExternalTicket $ticket */
	public function paidByBankCancelTicket(Order $order, $ticket): void
	{
		if ($ticket->getOrder()->getId() !== $order->getId()) {
			throw new NotFoundHttpException;
		}

		$ticket->setNotePaidByBank(null);
		$ticket->setPaidByBank(false);
		$ticket->setDatetimePaidByBank(null);

		if ($ticket instanceof InternalTicket) {
			$this->internalTicketService->saveInternalTicket($ticket);
		} elseif ($ticket instanceof ExternalTicket) {
			$this->externalTicketService->saveExternalTicket($ticket);
		}

		$order->setNotePaidByBank(null);
		$order->setPaidByBank(false);
		$order->setDatetimePaidByBank(null);
		$this->orderService->saveOrder($order);
	}

	/**
	 * @Route(path="/backend/_ajax/orders/{order}/orderPersons/{orderPerson}/setPhone", name="backend_ajax_order_person_phone", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxOrderPersonPhoneAction(Order $order, OrderPerson $orderPerson, Request $request)
	{
		if ($orderPerson->getOrder() !== $order) throw new \Exception;
		$orderPerson->setPhone($request->request->get(self::PARAM_VALUE));
		$this->orderPersonService->saveOrderPerson($orderPerson);

		return JsonResponse::create();
	}

}
