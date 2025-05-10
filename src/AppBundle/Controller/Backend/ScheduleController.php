<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.11.17
 * Time: 16:12
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\LineStation;
use AppBundle\Entity\Schedule;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CountryService;
use AppBundle\Service\DateFormatService;
use AppBundle\Service\LineService;
use AppBundle\Service\RouteService;
use AppBundle\Service\ScheduleService;
use AppBundle\VO\RouteFilter;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.schedule")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class ScheduleController
{
	const PARAM_FILTER_FROM_CITY = "filterFromCity";
	const PARAM_FILTER_TO_CITY = "filterToCity";
	const PARAM_FILTER_DAY = "filterDay";

	/**
	 * @var ScheduleService
	 */
	private $scheduleService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var CountryService
	 */
	private $countryService;

	public function __construct(ScheduleService $scheduleService,
	                            CarrierService $carrierService,
	                            MenuWidget $menuWidget,
	                            RouteService $routeService,
	                            CityService $cityService,
	                            DateFormatService $dateFormatService,
	                            LineService $lineService,
	                            CountryService $countryService)
	{
		$this->scheduleService = $scheduleService;
		$this->carrierService = $carrierService;
		$this->menuWidget = $menuWidget;
		$this->routeService = $routeService;
		$this->cityService = $cityService;
		$this->dateFormatService = $dateFormatService;
		$this->lineService = $lineService;
		$this->countryService = $countryService;
	}

	/**
	 * @Route(path="/backend/schedules", name="backend_schedules")
	 * @Template()
	 */
	public function schedulesAction(Request $request)
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_SCHEDULES);

		$routeFilter = new RouteFilter;
		if ($request->query->has(self::PARAM_FILTER_FROM_CITY)) {
			$routeFilter->setFromCity($this->cityService->getCity($request->query->get(self::PARAM_FILTER_FROM_CITY)));
		} else {
			$routeFilter->setFromCity($this->cityService->getCity(41));
		}
		if ($request->query->has(self::PARAM_FILTER_FROM_CITY)) {
			$routeFilter->setToCity($this->cityService->getCity($request->query->get(self::PARAM_FILTER_TO_CITY)));
		} else {
			$routeFilter->setToCity($this->cityService->getCity(2));
		}
		if ($request->query->has(self::PARAM_FILTER_DAY)) {
			$routeFilter->setDateDay($this->dateFormatService->dateParse($request->query->get(self::PARAM_FILTER_DAY)));
		} else {
			$routeFilter->setDateDay(new \DateTime);
		}

		/** @var Schedule[] $schedules */
		$schedules = [];

		$lineFilter = $routeFilter->createLineFilterThere();
		$lineFilter->setOnlyShow(true);
		$lines = $this->lineService->findLines($lineFilter);
		foreach ($lines as $line) {
			foreach ($this->routeService->getSchedulesForLine($routeFilter, $line, LineStation::DIRECTION_THERE) as $schedule) {
				$schedules[] = $schedule;
			}
		}

		return [
			"carriers" => $this->carrierService->findAllCarriers(),
			"schedules" => $schedules,
			"routeFilter" => $routeFilter,
			"countries" => $this->countryService->findAllCountries(),
		];
	}
}
