<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.04.17
 * Time: 13:42
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\ExternalTariff;
use AppBundle\Entity\Fare;
use AppBundle\Entity\Schedule;
use AppBundle\Entity\Tariff;
use AppBundle\Service\CityService;
use AppBundle\Service\DateFormatService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\FareService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\LineStationService;
use AppBundle\Service\ScheduleService;
use AppBundle\Service\StationService;
use AppBundle\Service\TariffService;
use AppBundle\VO\DuplicatorVO;
use AppBundle\VO\TemporaryPercentTariffRange;
use AppBundle\VO\TemporaryTariffRange;
use AppBundle\Widget\Backend\ExternalTariffConditionsWidget;
use AppBundle\Widget\Backend\TariffConditionsWidget;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.tariff")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class TariffController
{
	const PARAM_PRICE_RETURN_ADD = "priceReturnAdd";
	const PARAM_PRICE = "price";
	const PARAM_NAME = "name";
	const PARAM_PERCENT = "percent";
	const PARAM_PERCENT_FROM_TARIFF = "percentFromTariff";
	const PARAM_CURRENCY = "currency";
	const PARAM_OTHER_CURRENCY_TARIFF = "otherCurrencyTariff";
	const PARAM_ALLOW_DAYS = "allowDays";
	const PARAM_TARIFF_CONDITIONS = "tariffConditions";
	const PARAM_EXCLUDE_DAYS = "excludeDays";
	const PARAM_DATE_FROM = "dateFrom";
	const PARAM_DATE_TO = "dateTo";
	const PARAM_TEMPORARY_DATE_FROM = "temporaryDateFrom";
	const PARAM_TEMPORARY_DATE_TO = "temporaryDateTo";
	const PARAM_TEMPORARY_EXCLUDE_TARIFFS = "temporaryExcludeTariffs";
	const PARAM_TEMPORARY_WAY = "temporaryWay";
	const PARAM_TEMPORARY_PERCENT_DATE_FROM = "temporaryPercentDateFrom";
	const PARAM_TEMPORARY_PERCENT_DATE_TO = "temporaryPercentDateTo";
	const PARAM_TEMPORARY_PERCENT = "temporaryPercent";
	const PARAM_TEMPORARY_PERCENT_WAY = "temporaryPercentWay";
	const PARAM_ADD_TO_TEMPORARY_TARIFF = "addToTemporaryTariff";
	const PARAM_ADD_TO_TEMPORARY_TARIFF_VALUE = "addToTemporaryTariffValue";
	const PERCENT = "percent";
	const FIX = "fix";
	const PARAM_BACK_WAY_BY_PERCENT_DISCOUNT_ENABLED = "backWayByPercentDiscountEnabled";
	const PARAM_BACK_WAY_BY_PERCENT_DISCOUNT = "backWayByPercentDiscount";

	/**
	 * @var TariffService
	 */
	private $tariffService;
	/**
	 * @var FareService
	 */
	private $fareService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var LineStationService
	 */
	private $lineStationService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var ScheduleService
	 */
	private $scheduleService;
	/**
	 * @var TariffConditionsWidget
	 */
	private $tariffConditionsWidget;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;
	/**
	 * @var ExternalTariffConditionsWidget
	 */
	private $externalTariffConditionsWidget;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var StationService
	 */
	private $stationService;

	public function __construct(TariffService $tariffService,
	                            FareService $fareService,
	                            LineStationService $lineStationService,
	                            Router $router,
	                            LanguageService $languageService,
	                            ScheduleService $scheduleService,
	                            TariffConditionsWidget $tariffConditionsWidget,
	                            ExternalTariffService $externalTariffService,
	                            ExternalTariffConditionsWidget $externalTariffConditionsWidget,
	                            DateFormatService $dateFormatService,
	                            CityService $cityService,
	                            StationService $stationService)
	{
		$this->tariffService = $tariffService;
		$this->fareService = $fareService;
		$this->router = $router;
		$this->lineStationService = $lineStationService;
		$this->languageService = $languageService;
		$this->scheduleService = $scheduleService;
		$this->tariffConditionsWidget = $tariffConditionsWidget;
		$this->externalTariffService = $externalTariffService;
		$this->externalTariffConditionsWidget = $externalTariffConditionsWidget;
		$this->dateFormatService = $dateFormatService;
		$this->cityService = $cityService;
		$this->stationService = $stationService;
	}

	/**
	 * @Route(path="/backend/tariff/{tariff}", name="backend_tariff", methods={"GET"})
	 * @Template()
	 */
	public function tariffAction(Tariff $tariff)
	{
		// TODO: Toto jen nez stara data budou upravena v DB
		if ($tariff->getTemporaryTariffRanges() === null) {
			$tariff->setTemporaryTariffRanges(new ArrayCollection);
		}

		// TODO: Toto jen nez stara data budou upravena v DB
		if ($tariff->getTemporaryPercentTariffRanges() === null) {
			$tariff->setTemporaryPercentTariffRanges(new ArrayCollection);
		}

		$newTemporaryTariffRange = new TemporaryTariffRange;
		$newTemporaryTariffRange->setRandomIdent("new");
		$tariff->getTemporaryTariffRanges()->add($newTemporaryTariffRange);

		$newTemporaryPercentTariffRange = new TemporaryPercentTariffRange;
		$newTemporaryPercentTariffRange->setRandomIdent("new");
		$tariff->getTemporaryPercentTariffRanges()->add($newTemporaryPercentTariffRange);

		return [
			"tariff" => $tariff,
			"line" => $tariff->getLine()
		];
	}

	/**
	 * @Route(path="/backend/_ajax/tariff/{tariff}", name="backend_ajax_tariff_conditions", methods={"GET"})
	 * @param Tariff $tariff
	 * @return JsonResponse
	 */
	public function ajaxTariffConditionsAction(Tariff $tariff)
	{
		return JsonResponse::create([
			"tariffConditionsWidget" => $this->tariffConditionsWidget->fetch($tariff)
		]);
	}

	/**
	 * @Route(path="/backend/_ajax/tariff/{tariff}", name="backend_ajax_tariff_conditions_save", methods={"POST"})
	 * @param Tariff $tariff
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function ajaxTariffConditionsSaveAction(Tariff $tariff, Request $request)
	{
		$tariffConditions = $request->request->get(self::PARAM_TARIFF_CONDITIONS);
		foreach ($tariffConditions as $lng => $c) {
			$tariff->setConditions($this->languageService->getLanguage($lng), $c);
		}
		$this->tariffService->saveTariff($tariff);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/_ajax/external-tariff/{tariff}", name="backend_ajax_external_tariff_conditions", methods={"GET"})
	 * @param ExternalTariff $tariff
	 * @return JsonResponse
	 */
	public function ajaxExternalTariffConditionsAction(ExternalTariff $tariff)
	{
		return JsonResponse::create([
			"tariffConditionsWidget" => $this->externalTariffConditionsWidget->fetch($tariff)
		]);
	}

	/**
	 * @Route(path="/backend/_ajax/external-tariff/{tariff}", name="backend_ajax_external_tariff_conditions_save", methods={"POST"})
	 * @param ExternalTariff $tariff
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function ajaxExternalTariffConditionsSaveAction(ExternalTariff $tariff, Request $request)
	{
		$tariffConditions = $request->request->get(self::PARAM_TARIFF_CONDITIONS);
		foreach ($tariffConditions as $lng => $c) {
			$tariff->setConditions($this->languageService->getLanguage($lng), $c);
		}
		$this->externalTariffService->saveExternalTariff($tariff);

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/tariff/{tariff}", name="backend_tariff_save", methods={"POST"})
	 * @param Tariff $tariff
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function tariffSaveAction(Tariff $tariff, Request $request)
	{
		$this->cityService->findAllCities();
		$this->stationService->findAllStations();

		$prices = $request->request->get(self::PARAM_PRICE);
		$pricesReturnAdd = $request->request->get(self::PARAM_PRICE_RETURN_ADD);
		$name = $request->request->get(self::PARAM_NAME);

		if ($request->request->has(self::PARAM_PERCENT)) {
			$tariff->setPercent($request->request->get(self::PARAM_PERCENT));
		}

		if ($request->request->has(self::PARAM_CURRENCY)) {
			$tariff->setCurrency($request->request->get(self::PARAM_CURRENCY));
		}

		$this->processTemporaryTariffRanges($request, $tariff);

		if ($request->request->has(self::PARAM_ALLOW_DAYS)) {
			$allowDays = $request->request->get(self::PARAM_ALLOW_DAYS);
			$tariff->setAllowDays($allowDays === "" ? null : $allowDays);
		}

		if ($request->request->has(self::PARAM_OTHER_CURRENCY_TARIFF)) {
			$otherCurrencyTariff = $this->tariffService->getTariff($request->request->get(self::PARAM_OTHER_CURRENCY_TARIFF));
			$tariff->setOtherCurrencyTariff($otherCurrencyTariff);
			if ($otherCurrencyTariff !== null) {
				$otherCurrencyTariff->setOtherCurrencyTariff($tariff);
				$this->tariffService->saveTariff($otherCurrencyTariff);
			}
		}

		if ($request->request->has(self::PARAM_EXCLUDE_DAYS)) {
			$days = $request->request->get(self::PARAM_EXCLUDE_DAYS);
			$tariff->getExcludeDays()->clear();
			$tariff->getExcludeDays()->setAll(false);
			foreach (explode(",", $days) as $d) {
				if ($d) $tariff->getExcludeDays()->addFromString($d);
			}
			$tariff->setExcludeDays(clone $tariff->getExcludeDays());
		}

		if ($request->request->has(self::PARAM_PERCENT_FROM_TARIFF)) {
			$percentFromTariff = $this->tariffService->getTariff($request->request->get(self::PARAM_PERCENT_FROM_TARIFF));
			$tariff->setPercentFromTariff($percentFromTariff);
		}

		if ($request->request->has(self::PARAM_BACK_WAY_BY_PERCENT_DISCOUNT_ENABLED)) {
			if ($request->request->getBoolean(self::PARAM_BACK_WAY_BY_PERCENT_DISCOUNT_ENABLED)) {
				$tariff->setBackWayByPercentDiscount(
					$request->request->getInt(self::PARAM_BACK_WAY_BY_PERCENT_DISCOUNT)
				);
			} else {
				$tariff->setBackWayByPercentDiscount(null);
			}
		}

		foreach ($prices as $fromLineStationId => $v) {
			foreach ($v as $toLineStationId => $price) {
				if ($tariff->getBackWayByPercentDiscount() === null) {
					$priceReturnAdd = $pricesReturnAdd[$fromLineStationId][$toLineStationId];
				} else {
					if (is_numeric($price)) {
						$priceReturnAdd = ((int) $price / 100) * (100 - $tariff->getBackWayByPercentDiscount());
					} elseif ($price === "v") {
						$priceReturnAdd = "v";
					} else {
						$priceReturnAdd = "n";
					}
				}

				$fromLineStation = $this->lineStationService->getLineStation($fromLineStationId);
				$toLineStation = $this->lineStationService->getLineStation($toLineStationId);

				$fare = $tariff->getFareForLineStations($fromLineStation, $toLineStation);
				if ($fare === null) {
					$fare = new Fare;
					$fare->setTariff($tariff);
					$fare->setFromLineStation($fromLineStation);
					$fare->setToLineStation($toLineStation);
				}

				if (is_numeric($price)) {
					$fare->setPrice($price);
					$fare->setVariablePrice(false);
					$fare->setNotAvailable(false);
				} elseif ($price == "v") {
					$fare->setPrice(null);
					$fare->setVariablePrice(true);
					$fare->setNotAvailable(false);
				} else {
					$fare->setPrice(null);
					$fare->setVariablePrice(false);
					$fare->setNotAvailable(true);
				}

				if (is_numeric($priceReturnAdd)) {
					$fare->setPriceReturnAdd($priceReturnAdd);
					$fare->setVariablePriceReturn(false);
					$fare->setNotAvailableReturn(false);
				} elseif ($priceReturnAdd == "v") {
					$fare->setPriceReturnAdd(null);
					$fare->setVariablePriceReturn(true);
					$fare->setNotAvailableReturn(false);
				} else {
					$fare->setPriceReturnAdd(null);
					$fare->setVariablePriceReturn(false);
					$fare->setNotAvailableReturn(true);
				}

				$this->fareService->saveFareWithoutFlush($fare);
			}
		}

		foreach ($name as $lng => $n) {
			$tariff->setName($this->languageService->getLanguage($lng), $n);
		}
		$this->tariffService->saveTariff($tariff);

		return RedirectResponse::create($this->router->generate("backend_tariff", [ "tariff" => $tariff->getId() ]));
	}

	/**
	 * @Route(path="/backend/tariff/{tariff}/remove", name="backend_tariff_remove", methods={"POST"})
	 * @param Tariff $tariff
	 * @return RedirectResponse
	 */
	public function tariffRemoveAction(Tariff $tariff)
	{
		$tariff->setOtherCurrencyTariff(null);
		$tariff->setDeleted(true);
		$tariff->getLine()->getSchedules()->map(function (Schedule $schedule) use ($tariff) {
			$schedule->getTariffs()->removeElement($tariff);
			$this->scheduleService->saveSchedule($schedule);
		});
		$this->tariffService->saveTariff($tariff);
		return RedirectResponse::create($this->router->generate("backend_line", [ "line" => $tariff->getLine()->getId() ]));
	}

	/**
	 * @Route(path="/backend/_ajax/tariff/{tariff}/setPricesBackFromThere", name="backend_ajax_tariff_setPricesBackFromThere", methods={"POST"})
	 * @param Tariff $tariff
	 * @return JsonResponse
	 */
	public function setPricesBackFromThereAction(Tariff $tariff)
	{
		$line = $tariff->getLine();

		$tariff->getFares()->map(function (Fare $fare) use ($tariff, $line) {
			if ($fare->isDeleted()) return;
			$from = $line->getOppositeLineStation($fare->getToLineStation());
			$to = $line->getOppositeLineStation($fare->getFromLineStation());
			if (!$from || !$to) return;
			$returnFare = $tariff->getFareForLineStations($from, $to);
			if ($returnFare) {
				$returnFare->setPrice($fare->getPrice());
				$returnFare->setPriceReturnAdd($fare->getPriceReturnAdd());
				$returnFare->setNotAvailable($fare->isNotAvailable());
				$returnFare->setNotAvailableReturn($fare->isNotAvailableReturn());
				$returnFare->setVariablePrice($fare->isVariablePrice());
				$returnFare->setVariablePriceReturn($fare->isVariablePriceReturn());
				$this->fareService->saveFare($returnFare);
			}
		});

		return JsonResponse::create([]);
	}

	/**
	 * @Route(path="/backend/tariff/{tariff}/createTemporaryTariff", name="backend_tariff_create_temporary", methods={"POST"})
	 * @param Tariff $tariff
	 * @return RedirectResponse
	 */
	public function createTemporaryTariffAction(Tariff $tariff, Request $request)
	{
		$name = $request->request->get(self::PARAM_NAME);

		$addToTemporaryTariff = $request->request->get(self::PARAM_ADD_TO_TEMPORARY_TARIFF);
		$addToTemporaryTariffValue = $request->request->get(self::PARAM_ADD_TO_TEMPORARY_TARIFF_VALUE);

		$duplicator = new DuplicatorVO;
		/** @var Tariff $newTariff */
		$newTariff = $duplicator->duplicate($tariff);
		$newTariff->setFares($duplicator->processArrayCollection($newTariff->getFares()));
		$newTariff->getFares()->map(function (Fare $fare) use ($duplicator) {
			$fare->setTariff($duplicator->duplicate($fare->getTariff()));
		});
		$newTariff->setTemporaryFromTariff($tariff);
		foreach ($name as $lng => $n) {
			$newTariff->setName($this->languageService->getLanguage($lng), $n);
		}
		$newTariff->setOtherCurrencyTariff(null);

		if ($addToTemporaryTariff === self::FIX) {
			$newTariff->getFares()->map(function (Fare $fare) use ($addToTemporaryTariffValue) {
				if (!$fare->isNotAvailable() and !$fare->isVariablePrice()) {
					$fare->setPrice($fare->getPrice() + $addToTemporaryTariffValue);
				}

				if (!$fare->isNotAvailableReturn() and !$fare->isVariablePriceReturn()) {
					$fare->setPriceReturnAdd($fare->getPriceReturnAdd() + $addToTemporaryTariffValue); // TODO: Tohle asi nejak jinak, nedava to smysl?
				}
			});
		} elseif ($addToTemporaryTariff === self::PERCENT) {
			$newTariff->getFares()->map(function (Fare $fare) use ($addToTemporaryTariffValue) {
				if (!$fare->isNotAvailable() and !$fare->isVariablePrice()) {
					$fare->setPrice(round($fare->getPrice() + ($fare->getPrice() / 100 * $addToTemporaryTariffValue)));
				}

				if (!$fare->isNotAvailableReturn() and !$fare->isVariablePriceReturn()) {
					$fare->setPriceReturnAdd(round($fare->getPriceReturnAdd() + ($fare->getPriceReturnAdd() / 100 * $addToTemporaryTariffValue)));
				}
			});
		}

		$this->processTemporaryTariffRanges($request, $newTariff);

		$this->tariffService->saveTariff($newTariff);

		return RedirectResponse::create($this->router->generate("backend_tariff", [ "tariff" => $newTariff->getId() ]));
	}

	/**
	 * @Route(path="/backend/tariff/{tariff}/duplicate-tariff", name="backend_tarif_duplicate", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function duplicateTariffAction(Tariff $tariff)
	{
		$newTariff = $this->tariffService->duplicateTariff($tariff);
		$this->tariffService->saveTariff($newTariff);

		return RedirectResponse::create($this->router->generate("backend_tariff", [
			"tariff" => $newTariff->getId(),
		]));
	}

	/**
	 * @param Request $request
	 * @param Tariff $tariff
	 * @return void
	 */
	protected function processTemporaryTariffRanges(Request $request, Tariff $tariff): void
	{
		$ranges = new \StdClass;
		$ranges->dateFrom = $request->request->get(self::PARAM_TEMPORARY_DATE_FROM);
		$ranges->dateTo = $request->request->get(self::PARAM_TEMPORARY_DATE_TO);
		$ranges->excludeTariffs = $request->request->get(self::PARAM_TEMPORARY_EXCLUDE_TARIFFS);
		$ranges->way = $request->request->get(self::PARAM_TEMPORARY_WAY);

		if (is_array($ranges->dateFrom)) {
			$temporaryTariffRanges = new ArrayCollection;
			foreach (array_keys($ranges->dateFrom) as $key) {
				if ($ranges->dateFrom[$key] !== "" and $ranges->dateTo[$key] !== "") {
					$temporaryTariffRanges->add($temporaryTariffRange = new TemporaryTariffRange);
					$temporaryTariffRange->setDateFrom($this->dateFormatService->dateParse($ranges->dateFrom[$key]));
					$temporaryTariffRange->setDateTo($this->dateFormatService->dateParse($ranges->dateTo[$key]));
					if (isset($ranges->excludeTariffs[$key])) {
						$temporaryTariffRange->setExcludeTariffs($ranges->excludeTariffs[$key]);
					}
					if (isset($ranges->way[$key])) {
						$temporaryTariffRange->setThere(in_array("there", (array)$ranges->way[$key]));
						$temporaryTariffRange->setBack(in_array("back", (array)$ranges->way[$key]));
					}
				}
			}
			$tariff->setTemporaryTariffRanges($temporaryTariffRanges);
		}

		$ranges = new \StdClass;
		$ranges->dateFrom = $request->request->get(self::PARAM_TEMPORARY_PERCENT_DATE_FROM);
		$ranges->dateTo = $request->request->get(self::PARAM_TEMPORARY_PERCENT_DATE_TO);
		$ranges->percent = $request->request->get(self::PARAM_TEMPORARY_PERCENT);
		$ranges->way = $request->request->get(self::PARAM_TEMPORARY_PERCENT_WAY);

		if (is_array($ranges->dateFrom)) {
			$temporaryPercentTariffRanges = new ArrayCollection;
			foreach (array_keys($ranges->dateFrom) as $key) {
				if ($ranges->dateFrom[$key] !== "" and $ranges->dateTo[$key] !== "") {
					$temporaryPercentTariffRanges->add($temporaryPercentTariffRange = new TemporaryPercentTariffRange);
					$temporaryPercentTariffRange->setDateFrom($this->dateFormatService->dateParse($ranges->dateFrom[$key]));
					$temporaryPercentTariffRange->setDateTo($this->dateFormatService->dateParse($ranges->dateTo[$key]));
					$temporaryPercentTariffRange->setPercent($ranges->percent[$key]);
					if (isset($ranges->way[$key])) {
						$temporaryPercentTariffRange->setThere(in_array("there", (array)$ranges->way[$key]));
						$temporaryPercentTariffRange->setBack(in_array("back", (array)$ranges->way[$key]));
					}
				}
			}
			$tariff->setTemporaryPercentTariffRanges($temporaryPercentTariffRanges);
		}
	}
}
