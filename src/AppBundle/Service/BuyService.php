<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.09.17
 * Time: 14:57
 */

namespace AppBundle\Service;


use AppBundle\Entity\Activity;
use AppBundle\Entity\Book;
use AppBundle\Entity\BookInternal;
use AppBundle\Entity\ExternalTicket;
use AppBundle\Entity\ExternalTicketBusSystem;
use AppBundle\Entity\ExternalTicketRegabus;
use AppBundle\Entity\ExternalTicketTransTempo;
use AppBundle\Entity\InternalTicket;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPerson;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Routers\EastExpressRouter;
use AppBundle\Routers\EurolinesRouter;
use AppBundle\Routers\FlixbusRouter;
use AppBundle\Routers\InfobusRouter;
use AppBundle\Routers\LikeBusRouter;
use AppBundle\Routers\NikoloRouter;
use AppBundle\Routers\RegabusRouter;
use AppBundle\Routers\StudentAgencyRouter;
use AppBundle\Routers\TransTempoRouter;

class BuyService
{
	/**
	 * @var EastExpressRouter
	 */
	private $eastExpressRouter;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var ExternalTicketService
	 */
	private $externalTicketService;
	/**
	 * @var StudentAgencyRouter
	 */
	private $studentAgencyRouter;
	/**
	 * @var EurolinesRouter
	 */
	private $eurolinesRouter;
	/**
	 * @var ExternalTicketGeneratorService
	 */
	private $externalTicketGeneratorService;
	/**
	 * @var BookService
	 */
	private $bookService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var FlixbusRouter
	 */
	private $flixbusRouter;
	/**
	 * @var NikoloRouter
	 */
	private $nikoloRouter;
	/**
	 * @var InternalTicketGeneratorService
	 */
	private $internalTicketGeneratorService;
	/**
	 * @var RegabusRouter
	 */
	private $regabusRouter;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var ActivityService
	 */
	private $activityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var TransTempoRouter
	 */
	private $transTempoRouter;
	/**
	 * @var InfobusRouter
	 */
	private $infobusRouter;

	private LikeBusRouter $likeBusRouter;

	public function __construct(EastExpressRouter $eastExpressRouter,
	                            CarrierService $carrierService,
	                            ExternalTicketService $externalTicketService,
	                            StudentAgencyRouter $studentAgencyRouter,
	                            EurolinesRouter $eurolinesRouter,
	                            ExternalTicketGeneratorService $externalTicketGeneratorService,
	                            BookService $bookService,
	                            SeatsPlanService $seatsPlanService,
	                            InternalTicketService $internalTicketService,
	                            FlixbusRouter $flixbusRouter,
	                            NikoloRouter $nikoloRouter,
	                            InternalTicketGeneratorService $internalTicketGeneratorService,
	                            RegabusRouter $regabusRouter,
	                            UserService $userService,
	                            ActivityService $activityService,
	                            LanguageService $languageService,
	                            TransTempoRouter $transTempoRouter,
								InfobusRouter $infobusRouter,
								LikeBusRouter $likeBusRouter)
	{
		$this->eastExpressRouter = $eastExpressRouter;
		$this->carrierService = $carrierService;
		$this->externalTicketService = $externalTicketService;
		$this->studentAgencyRouter = $studentAgencyRouter;
		$this->eurolinesRouter = $eurolinesRouter;
		$this->externalTicketGeneratorService = $externalTicketGeneratorService;
		$this->bookService = $bookService;
		$this->seatsPlanService = $seatsPlanService;
		$this->internalTicketService = $internalTicketService;
		$this->flixbusRouter = $flixbusRouter;
		$this->nikoloRouter = $nikoloRouter;
		$this->internalTicketGeneratorService = $internalTicketGeneratorService;
		$this->regabusRouter = $regabusRouter;
		$this->userService = $userService;
		$this->activityService = $activityService;
		$this->languageService = $languageService;
		$this->transTempoRouter = $transTempoRouter;
		$this->infobusRouter = $infobusRouter;
		$this->likeBusRouter = $likeBusRouter;
	}

	/**
	 * @param Order $order
	 * @return bool
	 */
	public function canBuyOrder(Order $order)
	{
		return $order->getOrderPersons()->filter(function (OrderPerson $orderPerson) use ($order) {
			if (!$this->canBuyOrderPersonRoute($orderPerson->getRouteTariffThere(), $order->getRouteThere())) {
				return false;
			}

			if ($orderPerson->getRouteTariffBack()) {
				if (!$this->canBuyOrderPersonRoute($orderPerson->getRouteTariffBack(), $order->getRouteBack())) {
					return false;
				}
			}

			return true;
		})->count() == $order->getOrderPersons()->count();
	}

	/**
	 * @param Order $order
	 */
	public function buyOrder(Order $order)
	{
		$order->getOrderPersonRouteTariffs()->map(function (OrderPersonRouteTariff $orderPersonRouteTariff) {
			$this->buyOrderPersonRouteTariff($orderPersonRouteTariff);
		});

		$order->getRoutes()->map(function (Route $route) {
			$this->buyRoute($route);
		});

		$order->getExternalTickets()->map(function (ExternalTicket $externalTicket) {
			# vsechny external ticket maji i interni alternativu
			$internalTicket = InternalTicket::create(
				$externalTicket->getRoute(),
				$externalTicket->getRouteTariff(),
				$externalTicket->getOrderPerson()
			);
			$internalTicket->setAlternativeExternalTicket($externalTicket);
			$externalTicket->setAlternativeInternalTicket($internalTicket);
			$this->internalTicketService->saveInternalTicket($internalTicket);
			$this->externalTicketService->saveExternalTicket($externalTicket);
		});

		# For LikeBus integration - they don't provide ticket to download via API
		if ($order->getRouteThere()->getCarrier()->getCode() == LikeBusRouter::CARRIER_CODE) {
			$order->getOrderPersonRouteTariffs()->map(function(OrderPersonRouteTariff $orderPersonRouteTariff) {
				$internalTicket = InternalTicket::create(
					$orderPersonRouteTariff->getRoute(),
					$orderPersonRouteTariff->getRouteTariff(),
					$orderPersonRouteTariff->getOrderPerson(),
				);

				$this->internalTicketService->saveInternalTicket($internalTicket);
			});
		}
	}

	/**
	 * @param RouteTariff $routeTariff
	 * @param Route $route
	 * @return bool
	 */
	private function canBuyOrderPersonRoute(RouteTariff $routeTariff, Route $route)
	{
		if ($route and $route->getCarrier()->isCantPayOnline()) {
			return false;
		}

		if ($this->seatsPlanService->canBuyRoute($route)) {
			return true;
		}

		if ($this->eastExpressRouter->canBuyRoute($route, $routeTariff)) {
			return true;
		}

		if ($this->eurolinesRouter->canBuyRoute($route, $routeTariff)) {
			return true;
		}

		if ($this->studentAgencyRouter->canBuyRoute($route, $routeTariff)) {
			return true;
		}

		if ($this->flixbusRouter->canBuyRoute($route, $routeTariff)) {
			return true;
		}

		if ($this->nikoloRouter->canBuyRoute($route)) {
			return true;
		}

		if ($this->infobusRouter->canBuyRoute($route)) {
			return true;
		}

		if ($this->regabusRouter->canBuyRoute($route)) {
			return true;
		}

		if ($this->transTempoRouter->canBuyRoute($route)) {
			return true;
		}

		if ($this->likeBusRouter->canBuyRoute($route)) {
			return true;
		}

		return false;
	}

	/**
	 * @param OrderPerson $orderPerson
	 * @param RouteTariff $routeTariff
	 * @param Route $route
	 * @throws \Exception
	 */
	private function buyOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		$orderPerson = $orderPersonRouteTariff->getOrderPerson();
		$routeTariff = $orderPersonRouteTariff->getRouteTariff();
		$route = $orderPersonRouteTariff->getRoute();
		$order = $orderPersonRouteTariff->getOrder();

		# TODO: Toto asi vyhodit uplne? zaridi to book pro interni jizdenky.
		#if ($this->seatsPlanService->canBuyRoute($route)) {
	    # 	$internalTicket = $this->seatsPlanService->buyRoute($route, $routeTariff, $orderPerson);
		#}

		if ($route->getLine()) {
			if ($route->getLine()->isGenerateInternalTicket()) {
				if ($orderPersonRouteTariff->getBook()) {
					$book = $orderPersonRouteTariff->getBook();
					if ($book instanceof BookInternal) {

						$internalTicket = InternalTicket::create($route, $routeTariff, $orderPerson);
						if ($book->getSeat()) {
							$internalTicket->setBySeat($book->getSeat());
						}

						$book->setInternalTicket($internalTicket);
						$internalTicket->setBook($book);
						$this->internalTicketService->saveInternalTicket($internalTicket);
						$this->bookService->saveBook($book);
					}
				}

				if (!isset($internalTicket)) {
					$internalTicket = InternalTicket::create($route, $routeTariff, $orderPerson);
					$this->internalTicketService->saveInternalTicket($internalTicket);
				}
			} else {
				$activity = Activity::create($orderPersonRouteTariff->getOrder(), Activity::TYPE_NEED_MANUAL_TICKET);
				$activity->setContentText(
					"{$route->getSimpleFromCityName()->getString($this->languageService->getCzech())} -> {$route->getSimpleToCityName()->getString($this->languageService->getCzech())} (pro '{$orderPerson->getName()}')"
				);
				$this->activityService->saveActivity($activity);
			}
		}

		if ($this->eastExpressRouter->canBuyRoute($route, $routeTariff)) {
			$externalTicket = $this->eastExpressRouter->buyRoute($route, $routeTariff, $orderPerson);
		}

		if ($this->eurolinesRouter->canBuyRoute($route, $routeTariff)) {
			$externalTicket = $this->eurolinesRouter->buyRoute($route, $routeTariff, $orderPerson);
		}

		if ($this->studentAgencyRouter->canBuyRoute($route, $routeTariff)) {
			$externalTicket = $this->studentAgencyRouter->buyRoute($route, $routeTariff, $orderPerson);
		}

		if (isset($externalTicket) and $externalTicket !== null) {
			$this->externalTicketService->saveExternalTicket($externalTicket);
			$this->externalTicketGeneratorService->generateFile($externalTicket);
			$this->externalTicketService->saveExternalTicket($externalTicket);
		}

		if (isset($internalTicket) and $internalTicket !== null) {
			$this->internalTicketService->saveInternalTicket($internalTicket);
			# generovani internich jizdenek se deje az hromadne na konci. aby byly k dispozici i InternalTicket cesty zpet.
		}

		if ($route->getBook()) {
			$this->bookService->saveBook($route->getBook());
		}
	}

	private function buyRoute(Route $route)
	{
		if ($this->regabusRouter->canBuyRoute($route)) {
			$externalTickets = $this->regabusRouter->buyRoute($route);

			array_map(function (ExternalTicketRegabus $ticket) {
				$this->externalTicketService->saveExternalTicket($ticket);
			}, $externalTickets);

			$route->getBooks()->map(function (Book $book) {
				$this->bookService->saveBook($book);
			});

		} elseif ($this->transTempoRouter->canBuyRoute($route)) {
			$externalTickets = $this->transTempoRouter->buyRoute($route);

			array_map(function (ExternalTicketTransTempo $ticket) {
				$this->externalTicketService->saveExternalTicket($ticket);
			}, $externalTickets);

			$route->getBooks()->map(function (Book $book) {
				$this->bookService->saveBook($book);
			});

		} elseif ($this->nikoloRouter->canBuyRoute($route)) {
			$externalTickets = $this->nikoloRouter->buyRoute($route);

			array_map(function (ExternalTicketBusSystem $ticket) {
				$this->externalTicketService->saveExternalTicket($ticket);
			}, $externalTickets);

			$route->getBooks()->map(function (Book $book) {
				$this->bookService->saveBook($book);
			});
		} elseif ($this->infobusRouter->canBuyRoute($route)) {
			$externalTickets = $this->infobusRouter->buyRoute($route, false);

			array_map(function (ExternalTicketBusSystem $ticket) {
				$this->externalTicketService->saveExternalTicket($ticket);
			}, $externalTickets);

			$route->getBooks()->map(function (Book $book) {
				$this->bookService->saveBook($book);
			});
		} elseif ($this->likeBusRouter->canBuyRoute($route)) {
			$this->likeBusRouter->buyRoute($route);
		}
	}
}
