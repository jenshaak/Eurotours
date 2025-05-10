<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.17
 * Time: 10:35
 */

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\Activity;
use AppBundle\Entity\Carrier;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\RouteTariff;
use AppBundle\Entity\RouteTariffCombination;
use AppBundle\Entity\Search;
use AppBundle\Exceptions\FreeSeatNotAvailableException;
use AppBundle\Service\ActivityService;
use AppBundle\Service\BookService;
use AppBundle\Service\BuyService;
use AppBundle\Service\CurrencyService;
use AppBundle\Service\EmailService;
use AppBundle\Service\ExtraOrderPersonInputService;
use AppBundle\Service\JobService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\OrderPersonRouteTariffService;
use AppBundle\Service\OrderService;
use AppBundle\Service\PaymentService;
use AppBundle\Service\RouteCombinationService;
use AppBundle\Service\RouteService;
use AppBundle\Service\RouteTariffService;
use AppBundle\Service\SeatsPlanService;
use AppBundle\Service\SelectSeatService;
use AppBundle\Service\UserService;
use AppBundle\Widget\Frontend\SearchFormWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.frontend.order")
 */
class OrderController
{
	const PARAM_ROUTE_THERE = "routeThere";
	const PARAM_ROUTE_BACK = "routeBack";
	const PARAM_ROUTE_TARIFF = "routeTariff";

	/**
	 * @var OrderService
	 */
	private $orderService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var SearchFormWidget
	 */
	private $searchFormWidget;
	/**
	 * @var RouteTariffService
	 */
	private $routeTariffService;
	/**
	 * @var RouteCombinationService
	 */
	private $routeCombinationService;
	/**
	 * @var CurrencyService
	 */
	private $currencyService;
	/**
	 * @var BuyService
	 */
	private $buyService;
	/**
	 * @var PaymentService
	 */
	private $paymentService;
	/**
	 * @var JobService
	 */
	private $jobService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var EmailService
	 */
	private $emailService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var OrderPersonRouteTariffService
	 */
	private $orderPersonRouteTariffService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var SelectSeatService
	 */
	private $selectSeatService;
	/**
	 * @var BookService
	 */
	private $bookService;
	/**
	 * @var ExtraOrderPersonInputService
	 */
	private $extraOrderPersonInputService;
	/**
	 * @var ActivityService
	 */
	private $activityService;

	public function __construct(OrderService $orderService,
	                            Router $router,
	                            RouteService $routeService,
	                            SearchFormWidget $searchFormWidget,
	                            RouteTariffService $routeTariffService,
	                            RouteCombinationService $routeCombinationService,
	                            CurrencyService $currencyService,
	                            BuyService $buyService,
	                            PaymentService $paymentService,
	                            JobService $jobService,
	                            LanguageService $languageService,
	                            EmailService $emailService,
	                            UserService $userService,
	                            OrderPersonRouteTariffService $orderPersonRouteTariffService,
	                            SeatsPlanService $seatsPlanService,
	                            SelectSeatService $selectSeatService,
	                            BookService $bookService,
	                            ExtraOrderPersonInputService $extraOrderPersonInputService,
	                            ActivityService $activityService)
	{
		$this->orderService = $orderService;
		$this->router = $router;
		$this->routeService = $routeService;
		$this->searchFormWidget = $searchFormWidget;
		$this->routeTariffService = $routeTariffService;
		$this->routeCombinationService = $routeCombinationService;
		$this->currencyService = $currencyService;
		$this->buyService = $buyService;
		$this->paymentService = $paymentService;
		$this->jobService = $jobService;
		$this->languageService = $languageService;
		$this->emailService = $emailService;
		$this->userService = $userService;
		$this->orderPersonRouteTariffService = $orderPersonRouteTariffService;
		$this->seatsPlanService = $seatsPlanService;
		$this->selectSeatService = $selectSeatService;
		$this->bookService = $bookService;
		$this->extraOrderPersonInputService = $extraOrderPersonInputService;
		$this->activityService = $activityService;
	}

	/**
	 * @Route(path="/search/order/{search}", name="order_create", methods={"POST"})
	 * @param Search $search
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function createOrderAction(Search $search, Request $request)
	{
		$order = $this->orderService->createOrder();
		$order->setSearch($search);
		$order->setLanguage($this->languageService->getCurrentLanguage());

		if ($this->userService->isSeller() || $this->userService->isSuperAdmin()) {
			$order->setUserSeller($this->userService->getCurrentUser());
			$order->setName($order->getUserSeller()->getName());
			$order->setEmail($order->getUserSeller()->getEmail());
			$order->setPhone($order->getUserSeller()->getPhone());
		}

		if ($request->request->has(self::PARAM_ROUTE_THERE)) {
			$order->setRouteThere($this->routeService->getRoute($request->request->get(self::PARAM_ROUTE_THERE)));
		}

		if ($request->request->has(self::PARAM_ROUTE_BACK)) {
			$order->setRouteBack($this->routeService->getRoute($request->request->get(self::PARAM_ROUTE_BACK)));
		}

		if ($order->getRouteBack()) {
			$routeCombination = $this->routeCombinationService->findRouteCombinationBetweenRoutes(
				$order->getRouteThere(), $order->getRouteBack()
			);

			if ($routeCombination !== null) {
				$order->getRouteThere()->getRouteTariffs()->map(function (RouteTariff $rt1) use ($routeCombination, $order) {
					$order->getRouteBack()->getRouteTariffs()->map(function (RouteTariff $rt2) use ($routeCombination, $rt1) {
						$routeCombination->getRouteTariffCombinations()->map(function (RouteTariffCombination $rtc) use ($rt1, $rt2) {
							if ($rt1 === $rtc->getThereRouteTariff() and $rt2 === $rtc->getBackRouteTariff()) {
								$rt2->setPrice($rtc->getPrice() - $rt1->getPrice());
								if ($rt2->getTariff()->getPercentFromTariff()) {
									$rt2->setPrice(($rt2->getPrice() / 100) * (100 - $rt2->getTariff()->getPercentForDay($rt2->getRoute()->getDatetimeDeparture(), $rt2->getRoute()->getSchedule()->getDirection())));
								}
								$this->routeTariffService->saveRouteTariff($rt2);
							}
						});
					});
				});
			}
		}

		$routeTariffs = $request->request->get(self::PARAM_ROUTE_TARIFF);
		$personCountThere = isset($routeTariffs[LineStation::DIRECTION_THERE]) ? array_sum($routeTariffs[LineStation::DIRECTION_THERE]) : 0;
		$personCountBack = isset($routeTariffs[LineStation::DIRECTION_BACK]) ? array_sum($routeTariffs[LineStation::DIRECTION_BACK]) : 0;
		$personCount = $personCountThere > $personCountBack ? $personCountThere : $personCountBack;
		unset($personCountThere, $personCountBack);

		foreach (range(1, $personCount) as $i) {
			$order->createOrderPerson();
		}

		$price = 0;
		$maxPrice = 0;
		foreach ($request->request->get(self::PARAM_ROUTE_TARIFF) as $direction => $array) {
			foreach ($array as $routeTariffId => $cnt) {
				if (empty($cnt) or $cnt <= 0) continue;
				foreach (range(1, $cnt) as $i) {
					$routeTariff = $this->routeTariffService->getRouteTariff($routeTariffId);

					$order->getOrderPersons()->forAll(function ($key, OrderPerson $orderPerson) use ($direction, $routeTariff) {
						if ($orderPerson->getRouteTariffThere() === null) {
							$orderPerson->setRouteTariffThere($routeTariff);
							return false;
						} elseif ($direction == LineStation::DIRECTION_BACK and $orderPerson->getRouteTariffBack() === null) {
							$orderPerson->setRouteTariffBack($routeTariff);
							return false;
						}
						return true;
					});

					$price += $this->currencyService->currencyConvert($routeTariff->getPriceCurrencyIncludeSurcharge(), $this->currencyService->getCurrentCurrency())->getPrice();

					if ($routeTariff->getMaxPrice()) {
						$maxPrice += $this->currencyService->currencyConvert($routeTariff->getMaxPriceCurrencyIncludeSurcharge(), $this->currencyService->getCurrentCurrency())->getPrice();
					} else {
						$maxPrice += $this->currencyService->currencyConvert($routeTariff->getPriceCurrencyIncludeSurcharge(), $this->currencyService->getCurrentCurrency())->getPrice();
					}
				}
			}
		}

		$order->setPrice($price);
		if ($maxPrice > $price) {
			$order->setMaxPrice($maxPrice);
		}
		$order->setCurrency($this->currencyService->getCurrentCurrency());
		$order->setTicketCurrency($this->currencyService->getCurrentCurrency());
		$this->orderService->saveOrder($order);

		$this->orderPersonRouteTariffService->createOrderPersonRouteTariffs($order);

		return RedirectResponse::create($this->router->generate("order", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/order/{order}", name="order")
	 * @Template()
	 * @param Order $order
	 * @return array|RedirectResponse
	 */
	public function orderAction(Order $order)
	{
		if ($order->getStatus() !== Order::STATUS_DRAFT and $order->getStatus() !== Order::STATUS_SELECT_SEATS) {
			return RedirectResponse::create($this->router->generate("order_detail", [ "order" => $order->getId() ]));
		}

		$this->searchFormWidget->setRouteFilter($order->getSearch()->createRouteFilter());

		$adminFreePossible = true;
		if ($order->getRouteThere()->getLine() === null or !$order->getRouteThere()->getLine()->isAdminFreePossible()) {
			$adminFreePossible = false;
		}
		if ($order->getRouteBack()) {
			if ($order->getRouteBack()->getLine() === null or !$order->getRouteBack()->getLine()->isAdminFreePossible()) {
				$adminFreePossible = false;
			}
		}
		if ($order->getRouteThere()->getCarrier()->isExternalSearch()) $adminFreePossible = true;

		$adminNoSeatPossible = false;
		if ($order->getRouteThere()->getLine() and ($order->getRouteThere()->getSchedule()->isSeatsWithoutNumbers() or $order->getRouteThere()->getLine()->isSeatsWithoutNumbers($order->getRouteThere()->getSchedule()->getDirection()))) {
			$adminNoSeatPossible = true;
		}
		if ($order->getRouteBack() and $order->getRouteBack()->getLine() and $order->getRouteThere()->getLine() and ($order->getRouteBack()->getSchedule()->isSeatsWithoutNumbers() or $order->getRouteBack()->getLine()->isSeatsWithoutNumbers($order->getRouteBack()->getSchedule()->getDirection()))) {
			$adminNoSeatPossible = true;
		}

		$canBuyOrder = $this->orderService->canBuyOrder($order);

		$freeSeatNotAvailable = $this->orderService->isThereIsNotEnoughSeatsForOrder($order);

		$canReservationOrder = !$order->getCarriers()->exists(function ($i, Carrier $carrier) {
			return $carrier->isExternalBuy();
		});

		if (!$canBuyOrder) {
			$canReservationOrder = true;
		}

		return [
			"order" => $order,
			"routeThereExternalObject" => $order->getRouteThere()->getExternalObject(),
			"routeBackExternalObject" => $order->getRouteBack() ? $order->getRouteBack()->getExternalObject() : null,
			"canBuyOrder" => $canBuyOrder,
			"canReservationOrder" => $canReservationOrder,
			"freeSeatNotAvailable" => $freeSeatNotAvailable,
			"isAdmin" => $this->userService->isCurrentUserSuperAdmin(),
			"adminFreePossible" => $adminFreePossible,
			"adminNoSeatPossible" => $adminNoSeatPossible,
			"extraOrderPersonInputs" => $this->extraOrderPersonInputService->getInputsForOrder($order)
		];
	}

	/**
	 * @Route(path="/order/{order}/noSeats", name="order_no_seats", methods={"GET"})
	 * @Template()
	 * @param Order $order
	 * @return array
	 */
	public function orderNoSeatsAction(Order $order)
	{
		return [
			"order" => $order
		];
	}

	/**
	 * @Route(path="/order/{order}/buy", name="order_buy", methods={"POST"})
	 * @param Order $order
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws \Exception
	 * @throws \Twig\Error\Error
	 */
	public function orderBuyAction(Order $order, Request $request)
	{
		if ($order->getStatus() === Order::STATUS_SELECT_SEATS) {
			$this->bookService->bookSelectedSeatsForOrderByRequest($order, $request);
		}

		$order->setOrderType(Order::ORDER_TYPE_ORDER);
		$this->orderService->updateOrderByRequest($order, $request);
		try {
			if ($this->selectSeatService->needSelectSeats($this->selectSeatService->getSelectSeatsForOrder($order))) {
				$order->setStatus(Order::STATUS_SELECT_SEATS);
				$this->orderService->saveOrder($order);
				return RedirectResponse::create($this->router->generate("order_seats", [ "order" => $order->getId() ]));
			}
		} catch (FreeSeatNotAvailableException $e) {
			$order->setOrderType(Order::STATUS_RESERVATION);
			$activity = Activity::create($order, Activity::TYPE_NO_SEATS);
			$activity->setContentText("V autobuse není dostatek míst. Uživatel dostal informaci o tom, že ho bude kontaktovat administrátor.");
			$this->activityService->saveActivity($activity);
			$order->setStatus(Order::STATUS_NO_SEATS);
			$this->orderService->saveOrder($order);
			$this->emailService->sendOrderToAdminEmail($order);
			return RedirectResponse::create($this->router->generate("order_no_seats", [ "order" => $order->getId() ]));
		}

		$order->setStatus(Order::STATUS_WAITING_PAY);
		$order->setDatetimeOrdered(new \DateTime);

		$this->orderService->saveOrder($order);

		if (!$order->isAdminNoSeat()) {
			# TODO: Zabrat místa v případě rezervace. Pokud se jedná o objednávku, o rezervaci se postarají InternalTickets.
			# Asi pomocí Book? A ty pak taky zohledňovat při výběru, ve ScheduleSeatsOverviewWidget a ještě někde?
			# Nebo nějaký jiný nápad?
		}

		$this->orderService->bookOrder($order);
		/*
		 * TODO: Zároveň přidat možnost že se tady vyhodí výjimka o změně ceny. Ta se zobrazí na nové stránce
		 * a po kliku se přesměruje na nové vyhledávání. S tím, ať se i ukládají informace o formuláři do session.
		 * Pokud je cena nizsi, pak se muze jen upravit. Pokud vyssi, pak se vyhodi tato vyjimka.
		 */

		$payment = $this->paymentService->createPaymentForOrder($order);
		if ($order->isAdminFree()) {
			$order->setUserCashier($this->userService->getCurrentUser());
			$this->paymentService->paidPayment($payment);
		}
		$this->paymentService->savePayment($payment);
		$this->orderService->saveOrder($order);

		if ($order->isAdminFree()) {
			$this->jobService->buyOrder($order);
		}

		$this->emailService->sendOrderToAdminEmail($order);

		if ($this->userService->getCurrentUser() !== null) {
			$activity = Activity::create($order, Activity::TYPE_ORDER_CREATED);
			if ($this->userService->isSeller()) {
				$activity->setContentText("Objednávka vytvořena prodejcem.");
			} elseif ($this->userService->isEmployee()) {
				$activity->setContentText("Objednávka vytvořena zaměstnancem.");
			} else {
				$activity->setContentText("Objednávka vytvořena administrátorem.");
			}
			$activity->setUser($this->userService->getCurrentUser());
			$this->activityService->saveActivity($activity);
		}

		if ($order->isAdminFree()) {
			return RedirectResponse::create($this->router->generate("backend_order", [ "order" => $order->getId() ]));

		} else {
			return RedirectResponse::create($payment->getUrl());
		}
	}

	/**
	 * @Route(path="/order/{order}/reservation", name="order_reservation", methods={"POST"})
	 * @param Order $order
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws \Twig\Error\Error
	 */
	public function orderReservationAction(Order $order, Request $request)
	{
		if ($order->getStatus() === Order::STATUS_SELECT_SEATS) {
			$this->bookService->bookSelectedSeatsForOrderByRequest($order, $request);
		}

		$order->setOrderType(Order::ORDER_TYPE_RESERVATION);
		$this->orderService->updateOrderByRequest($order, $request);
		try {
			if ($this->selectSeatService->needSelectSeats($this->selectSeatService->getSelectSeatsForOrder($order))) {
				$order->setStatus(Order::STATUS_SELECT_SEATS);
				$this->orderService->saveOrder($order);
				return RedirectResponse::create($this->router->generate("order_seats", [ "order" => $order->getId() ]));
			}
		} catch (FreeSeatNotAvailableException $e) { }

		$order->setStatus(Order::STATUS_RESERVATION);
		$order->setDatetimeOrdered(new \DateTime);
		$this->orderService->saveOrder($order);

		$this->emailService->sendOrderToAdminEmail($order);

		$this->orderService->reservationBookOrder($order);

		if ($this->userService->getCurrentUser() !== null) {
			$activity = Activity::create($order, Activity::TYPE_ORDER_CREATED);
			$dueDateString = $order->getDateReservationDay() ? (" Platnost do " . $order->getDateReservationDay()->format("j.n.Y") . ".") : "";
			if ($this->userService->isSeller()) {
				$activity->setContentText("Rezervace vytvořena prodejcem.{$dueDateString}");
			} elseif ($this->userService->isEmployee()) {
				$activity->setContentText("Rezervace vytvořena zaměstnancem.{$dueDateString}");
			} else {
				$activity->setContentText("Rezervace vytvořena administrátorem.{$dueDateString}");
			}
			$activity->setUser($this->userService->getCurrentUser());
			$this->activityService->saveActivity($activity);
		}

		return RedirectResponse::create($this->router->generate("order_detail", [ "order" => $order->getId() ]));
	}

	/**
	 * @Route(path="/order/{order}/seats", name="order_seats", methods={"GET"})
	 * @Template()
	 * @param Order $order
	 * @return array|RedirectResponse
	 * @throws FreeSeatNotAvailableException
	 */
	public function orderSeatsAction(Order $order)
	{
		if ($order->getStatus() !== Order::STATUS_SELECT_SEATS) {
			return RedirectResponse::create($this->router->generate("order_detail", [ "order" => $order->getId() ]));
		}

		$this->searchFormWidget->setRouteFilter($order->getSearch()->createRouteFilter());

		$selectSeats = $this->selectSeatService->getSelectSeatsForOrder($order);

		return [
			"order" => $order,
			"canBuyOrder" => $this->orderService->canBuyOrder($order),
			"isAdmin" => $this->userService->isCurrentUserSuperAdmin(),
			"selectSeats" => $selectSeats
		];
	}

	/**
	 * @Route(path="/order/{order}/seats", name="order_seats_save", methods={"POST"})
	 * @param Order $order
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function orderSeatsSaveAction(Order $order, Request $request)
	{
		if ($order->getStatus() !== Order::STATUS_SELECT_SEATS) {
			return RedirectResponse::create($this->router->generate("order_detail", [ "order" => $order->getId() ]));
		}

		$this->bookService->bookSelectedSeatsForOrderByRequest($order, $request);
		$this->orderService->saveOrder($order);

		if ($order->getOrderType() === Order::ORDER_TYPE_ORDER) {
			return RedirectResponse::create($this->router->generate("order_buy", [ "order" => $order->getId() ]));
		} else {
			return RedirectResponse::create($this->router->generate("order_reservation", [ "order" => $order->getId() ]));
		}
	}

	/**
	 * @Route(path="/order/{order}/detail", name="order_detail")
	 * @Template()
	 * @param Order $order
	 * @return array
	 */
	public function orderDetailAction(Order $order)
	{
		return [
			"order" => $order,
			"payment" => $order->getPayment()
		];
	}

	/**
	 * @Route(path="/order/{order}/payment", name="order_payment")
	 * @param Order $order
	 * @return RedirectResponse
	 */
	public function orderPaymentAction(Order $order)
	{
		$payment = $this->paymentService->createPaymentForOrder($order);
		$this->paymentService->savePayment($payment);
		$this->orderService->saveOrder($order);

		return new RedirectResponse($order->getPayment()->getUrl());
	}

	/**
	 * @Route(path="/order/{order}/paid", name="order_paid")
	 * @param Order $order
	 * @param Request $request
	 * @return RedirectResponse
	 * @throws \Twig\Error\Error
	 */
	public function orderPaidAction(Order $order, Request $request)
	{
		if ($this->paymentService->verifyPayment($request, $order->getPayment())) {
			if ($this->paymentService->processPaymentFromRequest($request, $order->getPayment())) {
				$activity = Activity::create($order, Activity::TYPE_ORDER_PAID);
				$activity->setContentText("Objednávka uhrazena platební kartou.");
				$this->activityService->saveActivity($activity);
				$this->jobService->buyOrder($order);
				$order->setPaidBy(Order::PAID_BY_CARD);
				$this->emailService->sendOrderPaidToAdminEmail($order);
			}
			$this->paymentService->savePayment($order->getPayment());
			$this->orderService->saveOrder($order);
		}

		return RedirectResponse::create($this->router->generate("order_detail", [ "order" => $order->getId() ]));
	}
}
