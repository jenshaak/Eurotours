<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.17
 * Time: 11:22
 */

namespace AppBundle\Service;


use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Repository\OrderRepository;
use AppBundle\VO\DateRange;
use AppBundle\VO\OrderFilter;
use DateTime;
use Symfony\Component\HttpFoundation\Request;

class OrderService
{
	const PARAM_NAME = "name";
	const PARAM_EMAIL = "email";
	const PARAM_PHONE_PREFIX = "phonePrefix";
	const PARAM_PHONE = "phone";
	const PARAM_DATE_BIRTH = 'dateBirth';
	const PARAM_DOCUMENT_NUMBER = 'documentNumber';
	const PARAM_PERSONS = "persons";
	const VALUE_KEY_ALLOW_ORDER_DAYS = "allow-order-days";
	const PARAM_ADMIN_NO_SEAT = "adminNoSeat";
	const PARAM_ADMIN_FREE = "adminFree";
	const PARAM_ADMIN_CHANGE_PRICE = "adminChangePrice";
	const PARAM_PRICE_THERE = "priceThere";
	const PARAM_PRICE_BACK = "priceBack";
	const PARAM_SELLER_FEE = "sellerFee";
	const PARAM_SELLER_FEE_THERE = "sellerFeeThere";
	const PARAM_SELLER_FEE_BACK = "sellerFeeBack";
	const PARAM_RESERVATION_DATE = "reservationDate";
	const PARAM_FILTER_SEARCH = "filterSearch";
	const PARAM_FILTER_CARRIER = "filterCarrier";
	const PARAM_ORDER_BY = "orderBy";
	const PARAM_ORDER_BY_DIRECTION = "orderByDirection";
	const PARAM_OFFSET = "offset";
	const PARAM_FILTER_SELLER = "filterSeller";
	const PARAM_FILTER_CASHIER = "filterCashier";
	const PARAM_FILTER_SELLER_PAID = "filterSellerPaid";
	const PARAM_FILTER_COLOR_TYPE = "filterColorType";
	const PARAM_FILTER_BILLED = "filterBilled";
	const PARAM_FILTER_MONTH_ORDER = "filterMonthOrder";
	const PARAM_FILTER_MONTH_DEPARTURE = "filterMonthDeparture";
	const VALUE_KEY_ALLOW_ORDER_HOURS = "allow-order-hours";
	const PARAM_TICKET_CURRENCY = "ticketCurrency";
	const PARAM_FILTER_MONTH_BILLED = "filterMonthBilled";
	const PARAM_DATE_RANGE_ORDER = "dateRangeOrder";
	const PARAM_DATE_RANGE_BILLED = "dateRangeBilled";
	const PARAM_DATE_RANGE_PAID = "dateRangePaid";
	const PARAM_DATE_RANGE_DEPARTURE = "dateRangeDeparture";
	const PARAM_FILTER_USER_CANCELLED = "filterUserCancelled";
	const PARAM_FILTER_MONTH_PAID = "filterMonthPaid";
	const PARAM_FILTER_ONLY_CANCELLED = "onlyCancelled";

	/**
	 * @var OrderRepository
	 */
	private $orderRepository;
	/**
	 * @var OrderPersonService
	 */
	private $orderPersonService;
	/**
	 * @var BuyService
	 */
	private $buyService;
	/**
	 * @var KeyValueService
	 */
	private $keyValueService;
	/**
	 * @var BookService
	 */
	private $bookService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var OrderPersonRouteTariffService
	 */
	private $orderPersonRouteTariffService;

	public function __construct(OrderRepository $orderRepository,
	                            OrderPersonService $orderPersonService,
	                            BuyService $buyService,
	                            KeyValueService $keyValueService,
	                            BookService $bookService,
	                            SeatsPlanService $seatsPlanService,
	                            DateFormatService $dateFormatService,
	                            CarrierService $carrierService,
	                            UserService $userService,
	                            OrderPersonRouteTariffService $orderPersonRouteTariffService)
	{
		$this->orderRepository = $orderRepository;
		$this->orderPersonService = $orderPersonService;
		$this->buyService = $buyService;
		$this->keyValueService = $keyValueService;
		$this->bookService = $bookService;
		$this->seatsPlanService = $seatsPlanService;
		$this->dateFormatService = $dateFormatService;
		$this->carrierService = $carrierService;
		$this->userService = $userService;
		$this->orderPersonRouteTariffService = $orderPersonRouteTariffService;
	}

	/**
	 * @param Order $order
	 */
	public function saveOrder(Order $order)
	{
		$this->orderRepository->save($order);
		$order->getOrderPersons()->map(function (OrderPerson $orderPerson) {
			$this->orderPersonService->saveOrderPerson($orderPerson);
		});
	}

	/**
	 * @param Order $order
	 * @param Request $request
	 */
	public function updateOrderByRequest(Order $order, Request $request)
	{
		if ($request->request->has(self::PARAM_NAME)) {
			$order->setName($request->request->get(self::PARAM_NAME));
		}

		if ($request->request->has(self::PARAM_EMAIL)) {
			$order->setEmail($request->request->get(self::PARAM_EMAIL));
		}

		if ($request->request->has(self::PARAM_PHONE)) {
			$order->setPhone($request->request->get(self::PARAM_PHONE_PREFIX) . $request->request->get(self::PARAM_PHONE));
		}

		if ($request->request->has(self::PARAM_PERSONS)) {
			foreach ($request->request->get(self::PARAM_PERSONS) as $personId => $array) {
				$orderPerson = $order->getOrderPersonById($personId);

				if (isset($array[self::PARAM_NAME])) {
					$orderPerson->setName($array[self::PARAM_NAME]);
				}

				if (isset($array[self::PARAM_PHONE])) {
					$orderPerson->setPhone($array[self::PARAM_PHONE_PREFIX] . $array[self::PARAM_PHONE]);
				}

				if (isset($array[self::PARAM_DATE_BIRTH])) {
					$orderPerson->setDateBirth(new DateTime(str_replace(' ', '', $array[self::PARAM_DATE_BIRTH])));
				}

				if (isset($array[self::PARAM_DOCUMENT_NUMBER])) {
					$orderPerson->setDocumentNumber($array[self::PARAM_DOCUMENT_NUMBER]);
				}
			}
		}

		if ($request->request->has(self::PARAM_ADMIN_FREE)) {
			$order->setAdminFree($request->request->get(self::PARAM_ADMIN_FREE) === "1");
		}

		if ($request->request->has(self::PARAM_TICKET_CURRENCY)) {
			$order->setTicketCurrency($request->request->get(self::PARAM_TICKET_CURRENCY));
		}

		if ($request->request->has(self::PARAM_RESERVATION_DATE)) {
			$order->setDateReservationDay(
				$this->dateFormatService->dateParse($request->request->get(self::PARAM_RESERVATION_DATE))
			);
		}

		if ($request->request->has(self::PARAM_ADMIN_NO_SEAT)) {
			$order->setAdminNoSeat($request->request->get(self::PARAM_ADMIN_NO_SEAT) === "1");
		}

		if ($request->request->has(self::PARAM_ADMIN_CHANGE_PRICE)) {
			$order->setAdminChangePrice($request->request->get(self::PARAM_ADMIN_CHANGE_PRICE) === "1");
		}

		if($request->request->has(self::PARAM_SELLER_FEE)) {
			$sellerFee = 0;
			foreach ($request->request->get(self::PARAM_PERSONS) as $personId => $array) {
				$orderPerson = $order->getOrderPersonById($personId);

				if(isset($array[self::PARAM_SELLER_FEE_THERE])) {
					$orderPersonRouteTariff = $order->getOrderPersonRouteTariff($orderPerson, $orderPerson->getRouteTariffThere());
					$orderPersonRouteTariff->setSellerFee($array[self::PARAM_SELLER_FEE_THERE]);
					$sellerFee += $array[self::PARAM_SELLER_FEE_THERE];
					$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($orderPersonRouteTariff);
				}

				if(isset($array[self::PARAM_SELLER_FEE_BACK])) {
					$orderPersonRouteTariff = $order->getOrderPersonRouteTariff($orderPerson, $orderPerson->getRouteTariffBack());
					$orderPersonRouteTariff->setSellerFee($array[self::PARAM_SELLER_FEE_BACK]);
					$sellerFee += $array[self::PARAM_SELLER_FEE_BACK];
					$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($orderPersonRouteTariff);
				}
			}

			$order->setSellerFee($sellerFee);
		}

		if ($order->isAdminChangePrice() and $request->request->has(self::PARAM_PERSONS)) {
			$changedPrice = 0;
			foreach ($request->request->get(self::PARAM_PERSONS) as $personId => $array) {
				$orderPerson = $order->getOrderPersonById($personId);

				if (isset($array[self::PARAM_PRICE_THERE])) {
					$orderPersonRouteTariff = $order->getOrderPersonRouteTariff($orderPerson, $orderPerson->getRouteTariffThere());
					$orderPersonRouteTariff->setChangedPrice($array[self::PARAM_PRICE_THERE]);
					$changedPrice += $array[self::PARAM_PRICE_THERE];
					$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($orderPersonRouteTariff);
				}

				if (isset($array[self::PARAM_PRICE_BACK])) {
					$orderPersonRouteTariff = $order->getOrderPersonRouteTariff($orderPerson, $orderPerson->getRouteTariffBack());
					$orderPersonRouteTariff->setChangedPrice($array[self::PARAM_PRICE_BACK]);
					$changedPrice += $array[self::PARAM_PRICE_BACK];
					$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($orderPersonRouteTariff);
				}
			}

			$order->setPrice($changedPrice);
		}
	}

	/**
	 * @param OrderFilter $filter
	 * @return Order[]
	 */
	public function findOrders(OrderFilter $filter)
	{
		return $this->orderRepository->findOrders($filter);
	}

	/**
	 * @param $id
	 * @return null|Order
	 */
	public function getOrder($id)
	{
		return $this->orderRepository->find($id);
	}

	/**
	 * @param Order $order
	 * @return bool
	 */
	public function isThereIsNotEnoughSeatsForOrder(Order $order)
	{
		if ($order->getRouteThere() and $order->getRouteThere()->getSchedule() !== null and $this->canBuyOrderRouteWithSeats($order, $order->getRouteThere()) === false) {
			return true;
		}

		if ($order->getRouteBack() and $order->getRouteBack()->getSchedule() !== null and $this->canBuyOrderRouteWithSeats($order, $order->getRouteBack()) === false) {
			return true;
		}

		return false;
	}

	/**
	 * @param Order $order
	 * @param Route $route
	 * @return bool|null
	 */
	public function canBuyOrderRouteWithSeats(Order $order, Route $route)
	{
		if ($route->getSchedule() === null) return false;

		$seatsPlan = $this->seatsPlanService->getSeatsPlanForScheduleAndDateIfItIsPossible(
			$route->getSchedule(),
			$route->getDatetimeDeparture()
		);

		if ($seatsPlan) {
			$availableSeats = $this->seatsPlanService->getAvailableSeatsFromPlan(
				$seatsPlan,
				$order->getOrderPersons()->count(),
				$route
			);

			if ($availableSeats === false) return false;
			if (count($availableSeats) === $order->getOrderPersons()->count()) return true;
		} else {
			return null;
		}

		return false;
	}

	/**
	 * @param Order $order
	 * @return bool
	 */
	public function canBuyOrder(Order $order)
	{
		if ($order->getRouteThere() and $order->getRouteThere()->getLine()) {
			if (!$order->getRouteThere()->getLine()->isPayOnline()) {
				return false;
			}

			if ($order->getRouteThere()->getSchedule()->isPayOnlineDisabled()) {
				return false;
			}

			if ($order->getRouteThere()->getCarrier()->isCantPayOnline()) {
				return false;
			}

			if ($order->getRouteBack() === null) {
				$canBuyOrderRouteWithSeats = $this->canBuyOrderRouteWithSeats($order, $order->getRouteThere());
				if (is_bool($canBuyOrderRouteWithSeats)) {
					return $canBuyOrderRouteWithSeats;
				}
			}
		}

		if ($order->getRouteBack() and $order->getRouteBack()->getLine()) {
			if (!$order->getRouteBack()->getLine()->isPayOnline()) {
				return false;
			}

			if ($order->getRouteBack()->getSchedule()->isPayOnlineDisabled()) {
				return false;
			}

			if ($order->getRouteBack()->getCarrier()->isCantPayOnline()) {
				return false;
			}

			$canBuyOrderRouteWithSeats = $this->canBuyOrderRouteWithSeats($order, $order->getRouteThere());
			$canBuyOrderRouteWithSeatsBack = $this->canBuyOrderRouteWithSeats($order, $order->getRouteBack());
			if (is_bool($canBuyOrderRouteWithSeats) and is_bool($canBuyOrderRouteWithSeatsBack)) {
				return $canBuyOrderRouteWithSeats and $canBuyOrderRouteWithSeatsBack;
			}

			if ($this->canBuyOrderRouteWithSeats($order, $order->getRouteThere())
				and $this->canBuyOrderRouteWithSeats($order, $order->getRouteBack())) {
				return true;
			}
		}

		if ($order->getRouteThere()->getLine() and $order->getRouteThere()->getLine()->isBackWayOnlyAsReturnPayPossible()) {
			return false;
		}

		if ($order->getRouteBack()
			and $order->getRouteBack()->getLine()
			and $order->getRouteBack()->getLine()->isBackWayOnlyAsReturnPayPossible()
			and $order->getRouteBack()->getLine() !== $order->getRouteThere()->getLine()) {
			return false;
		}

		if (!$order->getPrice() or $order->getPrice() == 0) {
			return false;
		}

		if ($this->buyService->canBuyOrder($order)) {
			return true;
		}

		if ($this->isAllowOrderHoursForOrder($order)) {
			return true;
		}

		return false;
	}

	/**
	 * @return int|null
	 * @deprecated
	 */
	public function getGlobalAllowOrderDays()
	{
		$value = $this->keyValueService->get(self::VALUE_KEY_ALLOW_ORDER_DAYS)->getValue();
		if ($value === null) return null;
		else return (int) $value;
	}

	/**
	 * @return int|null
	 */
	public function getGlobalAllowOrderHours()
	{
		$value = $this->keyValueService->get(self::VALUE_KEY_ALLOW_ORDER_HOURS)->getValue();
		if ($value === null) return null;
		else return (int) $value;
	}

	/**
	 * @param Order $order
	 * @return bool
	 */
	public function isAllowOrderHoursForOrder(Order $order)
	{
		if (!$this->isAllowOrderHoursForRoute($order->getRouteThere())) {
			return false;
		}

		if ($order->getRouteBack()) {
			if (!$this->isAllowOrderHoursForRoute($order->getRouteBack())) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param Route $route
	 * @return bool
	 */
	public function isAllowOrderHoursForRoute(Route $route)
	{
		if ($route->getLine() === null) return false;

		$allowOrderHours = $route->getLine()->getAllowOrderDays($route->getSchedule()->getDirection())
			? $route->getLine()->getAllowOrderDays($route->getSchedule()->getDirection())
			: $this->getGlobalAllowOrderHours();

		$departureHours = round(($route->getDatetimeDeparture()->getTimestamp() - time()) / 60 / 60);
		if ($departureHours < $allowOrderHours) return false;

		return true;
	}

	/**
	 * @param Order $order
	 * @throws \Exception
	 */
	public function bookOrder(Order $order)
	{
		# ty, ktere se bookuji po Route
		$this->bookService->bookRoute($order, $order->getRouteThere());
		if ($order->getRouteBack()) {
			$this->bookService->bookRoute($order, $order->getRouteBack());
		}

		# ty, ktere se bookuji po OrderPersonRouteTariff
		$order->getOrderPersonRouteTariffs()->map(function (OrderPersonRouteTariff $orderPersonRouteTariff) {
			$this->bookService->bookOrderPersonRouteTariff($orderPersonRouteTariff);
		});
	}

	/**
	 * @param Order $order
	 * @throws \Exception
	 */
	public function reservationBookOrder(Order $order)
	{
		$order->getOrderPersonRouteTariffs()->map(function (OrderPersonRouteTariff $orderPersonRouteTariff) {
			if ($orderPersonRouteTariff->getBook() === null) {
				$this->bookService->reservationBookOrderPersonRouteTariff($orderPersonRouteTariff);
			}
		});
	}

	/**
	 * @return Order
	 */
	public function createOrder()
	{
		$dateIdent = (new \DateTime)->format("ymd");
		foreach (range(1, 999) as $i) {
			$suffix = str_pad($i, 3, 0, STR_PAD_LEFT);
			$o = $this->orderRepository->getOrderByIdent($dateIdent . $suffix);
			if ($o === null) {
				$order = new Order;
				$order->setIdent($dateIdent . $suffix);
				return $order;
			}
		}
		return new Order;
	}

	/**
	 * @param Request $request
	 * @return OrderFilter
	 */
	public function createOrderFilterFromRequest(Request $request)
	{
		$filter = new OrderFilter;

		if ($request->query->has(self::PARAM_FILTER_SEARCH)) {
			$filter->setFulltextSearch($request->query->get(self::PARAM_FILTER_SEARCH));
		}

		if ($request->query->has(self::PARAM_FILTER_COLOR_TYPE)) {
			$filter->setColorType($request->query->get(self::PARAM_FILTER_COLOR_TYPE));
		}

		if ($request->query->has(self::PARAM_FILTER_CARRIER)) {
			$filter->setCarrier(
				$this->carrierService->getCarrier($request->query->get(self::PARAM_FILTER_CARRIER))
			);
		}

		if ($request->query->has(self::PARAM_FILTER_SELLER)) {
			$filter->setUserSeller(
				$this->userService->getUser($request->query->get(self::PARAM_FILTER_SELLER))
			);
		}

		if ($request->query->has(self::PARAM_FILTER_CASHIER)) {
			$filter->setUserCashier(
				$this->userService->getUser($request->query->get(self::PARAM_FILTER_CASHIER))
			);
		}

		if ($request->query->has(self::PARAM_FILTER_USER_CANCELLED)) {
			$filter->setUserCancelled(
				$this->userService->getUser($request->query->get(self::PARAM_FILTER_USER_CANCELLED))
			);
		}

		if ($request->query->has(self::PARAM_FILTER_SELLER_PAID)) {
			if ($request->query->get(self::PARAM_FILTER_SELLER_PAID) === "") {
				$filter->setSellerPaid(null);
			} else {
				$filter->setSellerPaid($request->query->get(self::PARAM_FILTER_SELLER_PAID) === "1");
			}
		}

		if ($request->query->has(self::PARAM_FILTER_BILLED)) {
			if ($request->query->get(self::PARAM_FILTER_BILLED) === "") {
				$filter->setBilled(null);
			} else {
				$filter->setBilled($request->query->get(self::PARAM_FILTER_BILLED) === "1");
			}
		}

		if ($request->query->has(self::PARAM_ORDER_BY)) {
			$filter->setOrderBy($request->query->get(self::PARAM_ORDER_BY));
		}

		if ($request->query->has(self::PARAM_FILTER_MONTH_ORDER) and $request->query->get(self::PARAM_FILTER_MONTH_ORDER) !== "") {
			$filter->setMonthOrder(new \DateTime($request->query->get(self::PARAM_FILTER_MONTH_ORDER) . "-01"));
		}

		if ($request->query->has(self::PARAM_FILTER_MONTH_PAID) and $request->query->get(self::PARAM_FILTER_MONTH_PAID) !== "") {
			$filter->setMonthPaid(new \DateTime($request->query->get(self::PARAM_FILTER_MONTH_PAID) . "-01"));
		}

		if ($request->query->has(self::PARAM_FILTER_MONTH_DEPARTURE) and $request->query->get(self::PARAM_FILTER_MONTH_DEPARTURE) !== "") {
			$filter->setMonthDeparture(new \DateTime($request->query->get(self::PARAM_FILTER_MONTH_DEPARTURE) . "-01"));
		}

		if ($request->query->has(self::PARAM_FILTER_MONTH_BILLED) and $request->query->get(self::PARAM_FILTER_MONTH_BILLED) !== "") {
			$filter->setMonthBilled(new \DateTime($request->query->get(self::PARAM_FILTER_MONTH_BILLED) . "-01"));
		}

		if ($request->query->has(self::PARAM_DATE_RANGE_ORDER)) {
			$filter->setDateRangeOrder(
				$this->processDateRangeRequest($request->query->get(self::PARAM_DATE_RANGE_ORDER))
			);
		}

		if ($request->query->has(self::PARAM_DATE_RANGE_BILLED)) {
			$filter->setDateRangeBilled(
				$this->processDateRangeRequest($request->query->get(self::PARAM_DATE_RANGE_BILLED))
			);
		}

		if ($request->query->has(self::PARAM_DATE_RANGE_PAID)) {
			$filter->setDateRangePaid(
				$this->processDateRangeRequest($request->query->get(self::PARAM_DATE_RANGE_PAID))
			);
		}

		if ($request->query->has(self::PARAM_DATE_RANGE_DEPARTURE)) {
			$filter->setDateRangeDeparture(
				$this->processDateRangeRequest($request->query->get(self::PARAM_DATE_RANGE_DEPARTURE))
			);
		}

		if ($request->query->has(self::PARAM_ORDER_BY_DIRECTION)) {
			$filter->setOrderByDirection($request->query->get(self::PARAM_ORDER_BY_DIRECTION));
		}

		if ($request->query->has(self::PARAM_OFFSET)) {
			$filter->setOffset($request->query->get(self::PARAM_OFFSET));
		}

		if ($request->query->has(self::PARAM_FILTER_ONLY_CANCELLED)) {
			$filter->setOnlyCancelled($request->query->get(self::PARAM_FILTER_ONLY_CANCELLED));
		}

		return $filter;
	}

	private function processDateRangeRequest(?array $request): ?DateRange {
		if (!is_array($request)) return null;
		if (!isset($request['from'])) return null;
		if (!isset($request['to'])) return null;
		if ($request['from'] === "") return null;
		if ($request['to'] === "") return null;
		return new DateRange(
			$this->dateFormatService->dateParse($request["from"]),
			$this->dateFormatService->dateParse($request["to"])
		);
	}

	/**
	 * @param OrderFilter $filter
	 * @return array[]
	 */
	public function summarizeOrders(OrderFilter $filter): array
	{
		$sumOrders = [];
		$summarizedOrders = $this->orderRepository->summarizeOrders($filter);
		foreach ($summarizedOrders as $order) {
			$sumOrders[$order['currency']] = [
				'priceSum' => $order['priceSum'],
				'sellerFeeSum' => $order['sellerFeeSum'],
			];
		}

		$sumCancelledOrders = [];
		$filter->setOnlyCancelled(true);
		$summarizedCancelledOrders = $this->orderRepository->summarizeOrders($filter);
		foreach ($summarizedCancelledOrders as $order) {
			$sumCancelledOrders[$order['currency']] = [
				'priceSum' => $order['priceSum'],
				'sellerFeeSum' => $order['sellerFeeSum'],
			];
		}

		return  [
			'ordered' => $sumOrders,
			'cancelled' => $sumCancelledOrders,
		];
	}

}
