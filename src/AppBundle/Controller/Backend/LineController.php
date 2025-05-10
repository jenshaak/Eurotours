<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 18.04.17
 * Time: 9:39
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\Carrier;
use AppBundle\Entity\Line;
use AppBundle\Entity\LineStation;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Tariff;
use AppBundle\Entity\User;
use AppBundle\Service\CarrierService;
use AppBundle\Service\CityService;
use AppBundle\Service\CountryService;
use AppBundle\Service\DateFormatService;
use AppBundle\Service\FareService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\LineService;
use AppBundle\Service\LineStationService;
use AppBundle\Service\OrderService;
use AppBundle\Service\RouteService;
use AppBundle\Service\ScheduleService;
use AppBundle\Service\StationService;
use AppBundle\Service\TariffService;
use AppBundle\Service\UserService;
use AppBundle\Widget\Backend\MenuWidget;
use AppBundle\Widget\Backend\ScheduleNoteWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.line")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class LineController
{
	const PARAM_STATION = "station";
	const PARAM_FROM_LINE_STATION = "fromLineStation";
	const PARAM_TO_STATION = "toStation";
	const PARAM_DIRECTION = "direction";
	const PARAM_DATE_DAY = "dateDay";
	const PARAM_AVAILABLE = "available";
	const PARAM_SELLERS = "sellers";
	const PARAM_PRIVATE_NOTE = "privateNote";
	const PARAM_PUBLIC_NOTE = "publicNote";

	/**
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var StationService
	 */
	private $stationService;
	/**
	 * @var LineStationService
	 */
	private $lineStationService;
	/**
	 * @var ScheduleService
	 */
	private $scheduleService;
	/**
	 * @var TariffService
	 */
	private $tariffService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var OrderService
	 */
	private $orderService;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var ScheduleNoteWidget
	 */
	private $scheduleNoteWidget;

	public function __construct(LineService $lineService,
	                            Router $router,
	                            CityService $cityService,
	                            MenuWidget $menuWidget,
	                            CarrierService $carrierService,
	                            StationService $stationService,
	                            LineStationService $lineStationService,
	                            ScheduleService $scheduleService,
	                            TariffService $tariffService,
	                            LanguageService $languageService,
	                            OrderService $orderService,
	                            UserService $userService,
	                            ScheduleNoteWidget $scheduleNoteWidget)
	{
		$this->lineService = $lineService;
		$this->router = $router;
		$this->cityService = $cityService;
		$this->menuWidget = $menuWidget;
		$this->carrierService = $carrierService;
		$this->stationService = $stationService;
		$this->lineStationService = $lineStationService;
		$this->scheduleService = $scheduleService;
		$this->tariffService = $tariffService;
		$this->languageService = $languageService;
		$this->orderService = $orderService;
		$this->userService = $userService;
		$this->scheduleNoteWidget = $scheduleNoteWidget;
	}

	/**
	 * @Route(path="/backend/lines", name="backend_lines", methods={"GET"})
	 * @Template()
	 * @param Request $request
	 * @return array
	 */
	public function linesAction(Request $request)
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_LINES);

		return [
			"carriers" => $this->carrierService->findAllCarriers()
		];
	}

	/**
	 * @Route(path="/backend/line/{line}/{direction}", name="backend_line", requirements={"direction": "there|back"}, methods={"GET"})
	 * @Template()
	 * @param Line $line
	 * @param null|string $direction
	 * @return array|RedirectResponse
	 */
	public function lineAction(Line $line, $direction = null)
	{
		if ($direction === null or !in_array($direction, [ LineStation::DIRECTION_THERE, LineStation::DIRECTION_BACK ])) {
			return RedirectResponse::create($this->router->generate("backend_line", [
				"line" => $line->getId(),
				"direction" => LineStation::DIRECTION_THERE
			]));
		}

		$tariffs = $line->getTariffs()->toArray();
		usort(
			$tariffs,
			fn (Tariff $a, Tariff $b) => $a->getNumberForOrder() > $b->getNumberForOrder() ? +1 : -1
		);

		return [
			"line" => $line,
			"tariffs" => $tariffs,
			"direction" => $direction,
			"carriers" => $this->carrierService->findAllCarriers(),
			"cities" => $this->cityService->findAllCities(),
			"allowOrderHours" => $this->orderService->getGlobalAllowOrderHours(),
			"days" => $this->generateNext30Days(),
			"sellers" => $this->userService->findAllSellers()
		];
	}

	/**
	 * @return \DateTime[]
	 */
	private function generateNext30Days()
	{
		/** @var \DateTime[] $days */
		$days = [];
		$today = new \DateTime;
		foreach (range(0, 30) as $interval) {
			$d = clone $today;
			$days[] = $d->modify("+" . $interval . " days");
		}
		return $days;
	}

	/**
	 * @Route(path="/backend/line/{line}/{direction}", name="backend_line_save", requirements={"direction": "there|back"}, methods={"POST"})
	 * @param Line $line
	 * @param null|string $direction
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function lineSaveAction(Line $line, $direction, Request $request)
	{
		$this->lineService->updateLineByRequest($line, $request);

		foreach ((array) $request->request->get(self::PARAM_SELLERS) as $sellerId => $can) {
			$this->lineService->setSellerForLine($line, $this->userService->getUser($sellerId), $can == 1);
		}

		$this->lineService->saveLine($line);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $line->getId(),
			"direction" => $direction
		]));
	}

	/**
	 * @Route(path="/backend/_ajax/line/{line}/hide", name="ajax_backend_line_hide", methods={"POST"})
	 * @param Line $line
	 * @return JsonResponse
	 */
	public function ajaxLineHideAction(Line $line)
	{
		$line->setHidden(true);
		$this->lineService->saveLine($line);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/line/{line}/show", name="ajax_backend_line_show", methods={"POST"})
	 * @param Line $line
	 * @return JsonResponse
	 */
	public function ajaxLineShowAction(Line $line)
	{
		$line->setHidden(false);
		$this->lineService->saveLine($line);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/line/{line}/payOnline/on", name="ajax_backend_pay_online_on", methods={"POST"})
	 * @param Line $line
	 * @return JsonResponse
	 */
	public function ajaxOnPayOnlineLineAction(Line $line)
	{
		$line->setPayOnline(true);
		$this->lineService->saveLine($line);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/line/{line}/payOnline/off", name="ajax_backend_pay_online_off", methods={"POST"})
	 * @param Line $line
	 * @return JsonResponse
	 */
	public function ajaxOffPayOnlineLineAction(Line $line)
	{
		$line->setPayOnline(false);
		$this->lineService->saveLine($line);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/line/{line}/remove", name="backend_line_remove", methods={"POST"})
	 * @param Line $line
	 * @return RedirectResponse
	 */
	public function lineRemoveAction(Line $line)
	{
		$line->setDeleted(true);
		$this->lineService->saveLine($line);

		return RedirectResponse::create($this->router->generate("backend_lines"));
	}

	/**
	 * @Route(path="/backend/line/create/{carrier}", name="backend_line_create", methods={"GET"})
	 * @param Carrier $carrier
	 * @return RedirectResponse
	 */
	public function createLineAction(Carrier $carrier)
	{
		$line = new Line;
		$line->setCarrier($carrier);
		$this->lineService->saveLine($line);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $line->getId(),
			"direction" => LineStation::DIRECTION_THERE
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/add-station", name="backend_line_add_station")
	 * @param Line $line
	 * @return RedirectResponse
	 */
	public function lineAddStationAction(Line $line, Request $request)
	{
		$stationId = $request->request->get(self::PARAM_STATION);
		$station = $this->stationService->getStation($stationId);

		$this->lineService->addStationToLine($line, $station);
		$this->lineService->saveLine($line);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $line->getId(),
			"direction" => LineStation::DIRECTION_THERE
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/remove-line-station/{lineStation}", name="backend_line_remove_line_station", methods={"POST"})
	 * @param LineStation $lineStation
	 * @return RedirectResponse
	 */
	public function lineRemoveLineStationAction(LineStation $lineStation)
	{
		$this->lineStationService->deleteLineStation($lineStation);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $lineStation->getLine()->getId(),
			"direction" => LineStation::DIRECTION_THERE
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/remove-schedule/{schedule}", name="backend_line_remove_schedule", methods={"POST"})
	 * @param Schedule $schedule
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function removeScheduleAction(Schedule $schedule, Request $request)
	{
		$this->scheduleService->removeSchedule($schedule);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $schedule->getLine()->getId(),
			"direction" => $request->request->get(self::PARAM_DIRECTION)
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/duplicate-schedule/{schedule}", name="backend_line_duplicate_schedule", methods={"POST"})
	 * @param Schedule $schedule
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function duplicateScheduleAction(Schedule $schedule, Request $request)
	{
		$newSchedule = $this->scheduleService->duplicateSchedule($schedule);
		$this->scheduleService->saveSchedule($newSchedule);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $schedule->getLine()->getId(),
			"direction" => $request->request->get(self::PARAM_DIRECTION)
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/duplicate-line", name="backend_line_duplicate", methods={"POST"})
	 * @param Line $line
	 * @return RedirectResponse
	 */
	public function duplicateLineAction(Line $line)
	{
		$newLine = $this->lineService->duplicateLine($line);
		$this->lineService->saveLine($newLine);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $newLine->getId(),
		]));
	}

	/**
	 * @Route(path="/backend/change-line-station", name="backend_change_line_station", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function lineChangeLineStationAction(Request $request)
	{
		$fromLineStation = $this->lineStationService->getLineStation(
			$request->request->get(self::PARAM_FROM_LINE_STATION)
		);

		$toStation = $this->stationService->getStation(
			$request->request->get(self::PARAM_TO_STATION)
		);

		$fromLineStation->setStation($toStation);
		$fromLineStation->setCity($toStation->getCity());
		$this->lineStationService->saveLineStation($fromLineStation);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $fromLineStation->getLine()->getId(),
			"direction" => $request->request->get(self::PARAM_DIRECTION)
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/{direction}/add-schedule", name="backend_line_add_schedule")
	 * @param Line $line
	 * @param string $direction
	 * @return RedirectResponse
	 */
	public function lineAddScheduleAction(Line $line, $direction)
	{
		$schedule = new Schedule;
		$schedule->setLine($line);
		$schedule->setDirection($direction);
		$this->scheduleService->saveSchedule($schedule);

		return RedirectResponse::create($this->router->generate("backend_line", [
			"line" => $line->getId(),
			"direction" => $direction
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/add-tariff/{type}", name="backend_line_add_tariff")
	 * @param Line $line
	 * @param string $type
	 * @return RedirectResponse
	 */
	public function lineAddTariffAction(Line $line, $type)
	{
		$tariff = new Tariff;
		$tariff->setLine($line);
		$tariff->setCarrier($line->getCarrier());
		$tariff->setName($this->languageService->getEnglish(), $line->getCode());
		$tariff->setType($type);
		if ($type == Tariff::TYPE_PERCENT) {
			$tariff->setPercent(0);
		} else {
			$tariff->setCurrency("CZK");
		}
		$this->tariffService->saveTariff($tariff);

		return RedirectResponse::create($this->router->generate("backend_tariff", [
			"tariff" => $tariff->getId()
		]));
	}

	/**
	 * @Route(path="/backend/line/{line}/schedule/{schedule}/note", name="backend_ajax_schedule_note", methods={"GET"})
	 * @param Schedule $schedule
	 * @return JsonResponse
	 * @throws \Twig\Error\Error
	 */
	public function scheduleNoteAction(Schedule $schedule)
	{
		return JsonResponse::create([
			"scheduleNoteWidget" => $this->scheduleNoteWidget->fetch($schedule)
		]);
	}

	/**
	 * @Route(path="/backend/line/{line}/schedule/{schedule}/note", name="backend_ajax_schedule_note_save", methods={"POST"})
	 * @param Schedule $schedule
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function scheduleNoteSaveAction(Schedule $schedule, Request $request)
	{
		if ($request->request->has(self::PARAM_PRIVATE_NOTE)) {
			foreach ($request->request->get(self::PARAM_PRIVATE_NOTE) as $lng => $note) {
				$schedule->setPrivateNote($this->languageService->getLanguage($lng), $note);
			}
		}

		if ($request->request->has(self::PARAM_PUBLIC_NOTE)) {
			foreach ($request->request->get(self::PARAM_PUBLIC_NOTE) as $lng => $note) {
				$schedule->setPublicNote($this->languageService->getLanguage($lng), $note);
			}
		}

		$this->scheduleService->saveSchedule($schedule);

		return JsonResponse::create();
	}

}
