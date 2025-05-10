<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 07.07.17
 * Time: 16:17
 */

namespace AppBundle\Controller\Backend;

use AppBundle\Entity\Station;
use AppBundle\Service\CityService;
use AppBundle\Service\CountryService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\LineService;
use AppBundle\Service\StationService;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.station")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class StationController
{
	const PARAM_CITY = "city";
	const PARAM_LANGUAGE = "language";
	const PARAM_VALUE = "value";
	const PARAM_NAME = "name";

	/**
	 * @var StationService
	 */
	private $stationService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var CountryService
	 */
	private $countryService;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var LineService
	 */
	private $lineService;

	public function __construct(StationService $stationService,
	                            CityService $cityService,
	                            LanguageService $languageService,
	                            CountryService $countryService,
	                            MenuWidget $menuWidget,
	                            Router $router, LineService $lineService)
	{
		$this->stationService = $stationService;
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->countryService = $countryService;
		$this->menuWidget = $menuWidget;
		$this->router = $router;
		$this->lineService = $lineService;

		$this->menuWidget->setActive(MenuWidget::ACTIVE_STATIONS);
	}

	/**
	 * @Route(path="/backend/stations", name="backend_stations")
	 * @Template()
	 * @param Request $request
	 * @return array
	 */
	public function stationsAction(Request $request)
	{
		if ($request->query->has(self::PARAM_CITY)) {
			$city = $this->cityService->getCity($request->query->get(self::PARAM_CITY));
		} else {
			$city = null;
		}

		return [
			"city" => $city,
			"countries" => $this->countryService->findAllCountries(),
			"englishLanguage" => $this->languageService->getEnglish(),
			"czechLanguage" => $this->languageService->getCzech(),
			"russianLanguage" => $this->languageService->getRussian(),
			"ukrainianLanguage" => $this->languageService->getUkrainian(),
			"bulgarianLanguage" => $this->languageService->getBulgarion(),
			"lines" => $this->lineService->getAllLines()
		];
	}

	/**
	 * @Route(path="/backend/stations/add", name="backend_stations_add", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function stationAddAction()
	{
		return [
			"countries" => $this->countryService->findAllCountries()
		];
	}

	/**
	 * @Route(path="/backend/stations/add", name="backend_stations_add_save", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function stationAddSaveAction(Request $request)
	{
		$station = new Station;
		foreach ($request->request->get(self::PARAM_NAME) as $lng => $name) {
			$station->setName($this->languageService->getLanguage($lng), $name);
		}
		$station->setCity($this->cityService->getCity($request->request->get(self::PARAM_CITY)));
		$this->stationService->saveStation($station);

		return RedirectResponse::create($this->router->generate("backend_stations"));
	}


	/**
	 * @Route(path="/backend/_ajax/station/{station}", name="ajax_backend_station_save", methods={"POST"})
	 * @param Station $station
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function ajaxStationSaveAction(Station $station, Request $request)
	{
		if ($request->request->has(self::PARAM_LANGUAGE) and $request->request->has(self::PARAM_VALUE)) {
			$station->setName(
				$this->languageService->getLanguage($request->request->get(self::PARAM_LANGUAGE)),
				$request->request->get(self::PARAM_VALUE)
			);
			$this->stationService->saveStation($station);
		}

		return JsonResponse::create();
	}
}
