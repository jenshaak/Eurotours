<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.01.18
 * Time: 11:00
 */

namespace AppBundle\Service;


use AppBundle\Entity\Book;
use AppBundle\Entity\BookInfobus;
use AppBundle\Entity\BookInternal;
use AppBundle\Entity\BookNikolo;
use AppBundle\Entity\BookRegabus;
use AppBundle\Entity\BookTransTempo;
use AppBundle\Entity\Carrier;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderPersonRouteTariff;
use AppBundle\Entity\Route;
use AppBundle\Entity\SeatsPlan;
use AppBundle\Repository\BookRepository;
use AppBundle\Routers\InfobusRouter;
use AppBundle\Routers\LikeBusRouter;
use AppBundle\Routers\NikoloRouter;
use AppBundle\Routers\RegabusRouter;
use AppBundle\Routers\StudentAgencyRouter;
use Symfony\Component\HttpFoundation\Request;

class BookService
{
	const PARAM_SELECTED_SEAT = "selectedSeat";

	/**
	 * @var BookRepository
	 */
	private $bookRepository;
	/**
	 * @var StudentAgencyRouter
	 */
	private $studentAgencyRouter;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var NikoloRouter
	 */
	private $nikoloRouter;
	/**
	 * @var OrderPersonRouteTariffService
	 */
	private $orderPersonRouteTariffService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var RegabusRouter
	 */
	private $regabusRouter;

	private InfobusRouter $infobusRouter;

	private LikeBusRouter $likeBusRouter;

	public function __construct(BookRepository $bookRepository,
	                            StudentAgencyRouter $studentAgencyRouter,
	                            RouteService $routeService,
	                            NikoloRouter $nikoloRouter,
	                            OrderPersonRouteTariffService $orderPersonRouteTariffService,
	                            SeatsPlanService $seatsPlanService,
	                            RegabusRouter $regabusRouter,
								LikeBusRouter $likeBusRouter,
								InfobusRouter $infobusRouter
	)
	{
		$this->bookRepository = $bookRepository;
		$this->studentAgencyRouter = $studentAgencyRouter;
		$this->routeService = $routeService;
		$this->nikoloRouter = $nikoloRouter;
		$this->orderPersonRouteTariffService = $orderPersonRouteTariffService;
		$this->seatsPlanService = $seatsPlanService;
		$this->regabusRouter = $regabusRouter;
		$this->likeBusRouter = $likeBusRouter;
		$this->infobusRouter = $infobusRouter;
	}

	/**
	 * @param Book $book
	 */
	public function saveBook(Book $book)
	{
		$this->bookRepository->save($book);
	}

	/**
	 * @param Carrier $carrier
	 * @return BookInternal[]
	 */
	public function findInternalBooksForCarrier(Carrier $carrier, \DateTime $fromDate, \DateTime $toDate, $includeCancelled = false)
	{
		return $this->bookRepository->findInternalBooksForCarrier($carrier, $fromDate, $toDate, $includeCancelled);
	}

	/**
	 * @param SeatsPlan $seatsPlan
	 * @param \DateTime $dateDay
	 * @return BookInternal[]
	 */
	public function findInternalBooksForSeatsPlanAndDay(SeatsPlan $seatsPlan, \DateTime $dateDay)
	{
		return $this->bookRepository->findInternalBooksForSeatsPlanAndDay($seatsPlan, $dateDay);
	}

	/**
	 * @param Route $route
	 * @throws \Exception
	 */
	public function bookRoute(Order $order, Route $route)
	{
		if ($route->getCarrier()->getCode() == "RegioJet") {
			$book = $this->studentAgencyRouter->bookRoute($order, $route, $route->getOrderPersonRouteTariffs());
			$this->saveBook($book);
			$route->setBook($book);
			$this->routeService->saveRoute($route);

		} elseif ($route->getCarrier()->getCode() == "BlaBla") {
			$books = $this->regabusRouter->bookRoute($route);
			array_map(function (BookRegabus $book) {
				$this->saveBook($book);
			}, $books);

		} else if ($route->getCarrier()->getCode() == InfobusRouter::CARRIER_CODE) {
			$bookResponse = $this->infobusRouter->bookRoute($route);
			$order->getBooks()->forAll(function (int $key, BookInfobus $bookInfobus) use ($bookResponse) {
				$bookInfobus->setBookingId($bookResponse->orderId);
				$this->saveBook($bookInfobus);
			});
			$order->setPrice($bookResponse->priceTotal);
		} elseif ($route->getCarrier()->getCode() == LikeBusRouter::CARRIER_CODE) {
			$books = $this->likeBusRouter->bookRoute($order, $route);

			foreach ($books as $book) {
				$book->countPrices();
				$this->saveBook($book);
				$book->setRoute($route);
				$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($book->getOrderPersonRouteTariff());
				$this->routeService->saveRoute($route);
			}
		}
	}

	public function bookSelectedSeatsForOrderByRequest(Order $order, Request $request)
	{
		if ($request->request->has(self::PARAM_SELECTED_SEAT)) {
			$selectedSeats = $request->request->get(self::PARAM_SELECTED_SEAT);
			foreach ($selectedSeats as $orderPersonRouteTariffId => $seatNumbers) {
				$orderPersonRouteTariff = $this->orderPersonRouteTariffService->getOrderPersonRouteTariff(
					$orderPersonRouteTariffId
				);
				$dateDay = $orderPersonRouteTariff->getRoute()->getDatetimeDeparture();

				foreach ($seatNumbers as $seatNumber) {
					if ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() == "Ni") {
						$book = BookNikolo::create($seatNumber, $order);

					} elseif ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() == "BlaBla") {
						$book = BookRegabus::create($seatNumber, $order);
						$book->setRoute($orderPersonRouteTariff->getRoute());

					} elseif ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() == "TTbus") {
						$book = BookTransTempo::create($seatNumber, $order);
						$book->setRoute($orderPersonRouteTariff->getRoute());

					} elseif ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() == NikoloRouter::CARRIER_CODE) {
						$book = BookNikolo::create($seatNumber, $order);
						$book->setRoute($orderPersonRouteTariff->getRoute());

					} elseif ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() == InfobusRouter::CARRIER_CODE) {
						$book = BookInfobus::create($seatNumber, $order);
						$book->setRoute($orderPersonRouteTariff->getRoute());

					} else {
						$seatsPlan = $this->seatsPlanService->getSeatsPlanForScheduleAndDateIfItIsPossible(
							$orderPersonRouteTariff->getRoute()->getSchedule(), $dateDay
						);
						$book = BookInternal::create($seatsPlan->getSeatByNumber($seatNumber), $dateDay, $order);
					}
				}

				if (isset($book)) {
					$book->setOrderPersonRouteTariff($orderPersonRouteTariff);
					$orderPersonRouteTariff->setBook($book);
					$book->countPrices();
					$this->saveBook($book);
					$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($orderPersonRouteTariff);
				}

				if (isset($route)) {
					$this->routeService->saveRoute($route);
				}
			}
		}
	}

	public function bookOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		if ($orderPersonRouteTariff->getRoute()->getCarrier()->getCode() == "Ni") {
			$book = $this->nikoloRouter->bookOrderPersonRouteTariff($orderPersonRouteTariff);
		}

		if (isset($book)) $this->saveBook($book);
	}

	/**
	 * Vytvorim InternalBook pokud nejsou jiz vytvoreny kvuli zadrzeni mist. Slouzi k tomu, aby
	 * sly prenastavit pripadne ceny pro jizdenky.
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 */
	public function reservationBookOrderPersonRouteTariff(OrderPersonRouteTariff $orderPersonRouteTariff)
	{
		# TODO: Zkontrolovat jak je to s rezervacemi v pripade dateDay. Ma tam byt datum odjezdu linky asi spis nez datum odjezdu z mista z ktereho chci jet.

		$book = BookInternal::createWithoutSeat($orderPersonRouteTariff->getRoute()->getDatetimeDeparture(), $orderPersonRouteTariff->getOrder());
		$book->setOrderPersonRouteTariff($orderPersonRouteTariff);
		$book->countPrices();
		$orderPersonRouteTariff->setBook($book);
		$orderPersonRouteTariff->getOrder()->getBooks()->add($book);
		$this->saveBook($book);
		$this->orderPersonRouteTariffService->saveOrderPersonRouteTariff($orderPersonRouteTariff);
	}
}
