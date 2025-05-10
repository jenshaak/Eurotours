<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 20.06.18
 * Time: 13:17
 */

namespace AppBundle\Controller\Backend;
use AppBundle\Entity\Schedule;
use AppBundle\Service\CarrierService;
use AppBundle\Service\DateFormatService;
use AppBundle\Service\LineService;
use AppBundle\Service\RouteService;
use AppBundle\Service\SeatsPlanService;
use AppBundle\Service\UserService;
use AppBundle\VO\LineFilter;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.seatsSettings")
 * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_CARRIER')")
 */
class SeatsSettingsController
{
	const PARAM_FILTER_DAY = "filterDay";
	const PARAM_CARRIER = "carrier";
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var SeatsPlanService
	 */
	private $seatsPlanService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;

	public function __construct(MenuWidget $menuWidget,
	                            DateFormatService $dateFormatService,
	                            LineService $lineService,
	                            RouteService $routeService,
	                            UserService $userService,
	                            SeatsPlanService $seatsPlanService,
	                            CarrierService $carrierService)
	{
		$this->menuWidget = $menuWidget;
		$this->dateFormatService = $dateFormatService;
		$this->lineService = $lineService;
		$this->routeService = $routeService;
		$this->userService = $userService;
		$this->seatsPlanService = $seatsPlanService;
		$this->carrierService = $carrierService;
	}

	/**
	 * @Route(path="/backend/seatsSettings", name="backend_seats_settings")
	 * @Template()
	 * @return array
	 */
	public function seatsSettingsAction(Request $request)
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_SEATS_SETTINGS);

		$dateDay = $this->dateFormatService->dateParse($request->query->get(self::PARAM_FILTER_DAY));
		if ($dateDay === null) {
			$dateDay = new \DateTime;
		}

		$lineFilter = new LineFilter;
		if ($this->userService->getCurrentUser()->getCarrier()) {
			$lineFilter->setCarrier($this->userService->getCurrentUser()->getCarrier());
		}
		elseif ($request->query->get(self::PARAM_CARRIER)) {
			$lineFilter->setCarrier($this->carrierService->getCarrier($request->query->getInt(self::PARAM_CARRIER)));
		}

		/** @var Schedule[] $schedules */
		$schedules = [];

		if ($lineFilter->getCarrier() !== null) {
			$lines = $this->lineService->findLines($lineFilter);
			foreach ($lines as $line) {
				foreach ($line->getActiveSchedules() as $schedule) {
					if (!$schedule->getWeekDays()->contains($dateDay->format("N")-1)) {
						continue;
					}

					if ($line->isDeleted()) continue;
					if ($line->isHidden()) continue;

					$linePeriod = $schedule->getLine()->getLinePeriod();
					if ($linePeriod and !$linePeriod->isValidForDate($dateDay)) continue;

					if (!$schedule->getIncludeDays()->isDateTime($dateDay)) {
						continue;
					}

					if ($schedule->getExcludeDays()->isDateTime($dateDay)) {
						continue;
					}

					if ($this->seatsPlanService->getSeatsPlanForScheduleAndDateIfItIsPossible($schedule, $dateDay) === null) {
						continue;
					}

					$schedules[] = $schedule;
				}
			}
		}

		return [
			"schedules" => $schedules,
			"dateDay" => $dateDay,
			"carriers" => $this->carrierService->findAllCarriers(),
			"carrier" => $lineFilter->getCarrier()
		];
	}
}
