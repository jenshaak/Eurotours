<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 17:41
 */

namespace AppBundle\Widget\Frontend;


use AppBundle\Entity\Route;
use AppBundle\Entity\RouteTariff;
use AppBundle\Routers\FlixbusRouter;
use AppBundle\Routers\InfobusRouter;
use AppBundle\Routers\LikeBusRouter;
use AppBundle\Routers\NikoloRouter;
use AppBundle\Routers\RegabusRouter;
use AppBundle\Routers\TransTempoRouter;
use AppBundle\Service\CarrierService;
use AppBundle\Service\OrderService;
use AppBundle\Service\SeatsPlanService;
use AppBundle\Service\UserService;
use Motvicka\WidgetBundle\Widget\Widget;

class RouteWidget extends Widget
{
	const NAME = "frontend.route";

	/** @var bool */
	private $forSeo = false;

	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var OrderService
	 */
	private $orderService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var FlixbusRouter
	 */
	private $flixbusRouter;
	/**
	 * @var NikoloRouter
	 */
	private $nikoloRouter;
	/**
	 * @var RegabusRouter
	 */
	private $regabusRouter;
	/**
	 * @var InfobusRouter
	 */
	private $infobusRouter;
	/**
	 * @var TransTempoRouter
	 */
	private $transTempoRouter;

	private LikeBusRouter $likeBusRouter;

	public function __construct(CarrierService $carrierService,
	                            UserService $userService,
	                            OrderService $orderService,
	                            SeatsPlanService $seatsPlanService,
	                            FlixbusRouter $flixbusRouter,
	                            NikoloRouter $nikoloRouter,
	                            RegabusRouter $regabusRouter,
								InfobusRouter $infobusRouter,
								TransTempoRouter $transTempoRouter,
								LikeBusRouter $likeBusRouter)
	{
		$this->carrierService = $carrierService;
		$this->userService = $userService;
		$this->orderService = $orderService;
		$this->seatsPlanService = $seatsPlanService;
		$this->flixbusRouter = $flixbusRouter;
		$this->nikoloRouter = $nikoloRouter;
		$this->regabusRouter = $regabusRouter;
		$this->infobusRouter = $infobusRouter;
		$this->transTempoRouter = $transTempoRouter;
		$this->likeBusRouter = $likeBusRouter;
	}

	/**
	 * @param Route $route
	 * @return mixed
	 * @throws \Twig\Error\Error
	 */
	public function fetch(Route $route)
	{
		$routeTariffs = $route->getRouteTariffs()->getIterator();
		$routeTariffs->uasort(function (RouteTariff $a, RouteTariff $b) {
			return $a->getPriceIncludeSurcharge() < $b->getPriceIncludeSurcharge() ? +1 : -1;
		});

		$seatsPlan = $route->getSchedule() ? $this->seatsPlanService->getSeatsPlanForScheduleAndDateIfItIsPossible(
			$route->getSchedule(), $route->getDatetimeDeparture()
		) : null;

		if ($seatsPlan) {
			$seats = $this->seatsPlanService->getAvailableSeatsFromPlan($seatsPlan, null, $route);
		}

		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Frontend/Widget/route.html.twig", [
			"route" => $route,
			"routeTariffs" => iterator_to_array($routeTariffs),
			"canPayOnline" => $this->canPayOnline($route),
			"isAdmin" => $this->userService->isCurrentUserSuperAdmin(),
			"isSeller" => $this->userService->isSeller(),
			"isEmployee" => $this->userService->isEmployee(),
			"forSeo" => $this->isForSeo(),
			"showAvailableSeats" => isset($seats) && $seats !== null,
			"availableSeats" => (isset($seats) && $seats !== null) ? count($seats) : null
		]));
	}

	/**
	 * @return bool
	 */
	public function isForSeo()
	{
		return $this->forSeo;
	}

	/**
	 * @param bool $forSeo
	 */
	public function setForSeo($forSeo)
	{
		$this->forSeo = $forSeo;
	}

	/**
	 * @param Route $route
	 * @return bool
	 */
	private function canPayOnline(Route $route)
	{
		if ($route->getCarrier()->isCantPayOnline()) {
			return false;
		}

		if ($route->getLine() and !$route->getLine()->isPayOnline()) {
			return false;
		}

		if ($route->getSchedule() and $route->getSchedule()->isPayOnlineDisabled()) {
			return false;
		}

		if (!$route->getPrice() or $route->getPrice() == 0 or $route->isVariablePrice()) {
			return false;
		}

		$seatsExists = $this->seatsPlanService->canBuyRoute($route);
		if (is_bool($seatsExists)) {
			return $seatsExists;
		}

		if ($this->orderService->isAllowOrderHoursForRoute($route)) {
			return true;
		}

		# regiojet
		if ($route->getCarrier() === $this->carrierService->getCarrierByCode("RegioJet")) {
			return true;
		}

		# east express
		if ($route->getCarrier() === $this->carrierService->getCarrierByCode("EE")) {
			return true;
		}

		if ($this->flixbusRouter->canPayOnline($route)) {
			return true;
		}

		if ($this->nikoloRouter->canPayOnline($route)) {
			return true;
		}

		if ($this->infobusRouter->canPayOnline($route)) {
			return true;
		}

		if ($this->regabusRouter->canPayOnline($route)) {
			return true;
		}

		if ($this->transTempoRouter->canPayOnline($route)) {
			return true;
		}

		if ($this->likeBusRouter->canPayOnline($route)) {
			return true;
		}

		# eurolines - nakup mame zatim stopnuty, musime ho predelat
		#if ($route->getCarrier() === $this->carrierService->getCarrierByCode("E")) {
		#	return true;
		#}
	}
}
