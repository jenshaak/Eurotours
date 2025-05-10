<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 07.07.17
 * Time: 16:16
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\City;
use AppBundle\Service\CityService;
use AppBundle\Service\CountryService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\LineService;
use AppBundle\Widget\Backend\MenuWidget;
use Doctrine\ORM\Mapping as ORM;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.city")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class CityController
{
	const PARAM_LANGUAGE = "language";
	const PARAM_VALUE = "value";
	const PARAM_COUNTRY = "country";
	const PARAM_NAME = "name";

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
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var Router
	 */
	private $router;

	public function __construct(CityService $cityService,
	                            LanguageService $languageService,
	                            CountryService $countryService,
	                            MenuWidget $menuWidget,
	                            LineService $lineService,
	                            Router $router)
	{
		$this->cityService = $cityService;
		$this->languageService = $languageService;
		$this->countryService = $countryService;
		$this->menuWidget = $menuWidget;
		$this->lineService = $lineService;
		$this->router = $router;

		$this->menuWidget->setActive(MenuWidget::ACTIVE_CITIES);
	}

	/**
	 * @Route(path="/backend/cities", name="backend_cities")
	 * @Template()
	 * @return array
	 */
	public function citiesAction(Request $request)
	{
		if ($request->query->has(self::PARAM_COUNTRY)) {
			$country = $this->countryService->getCountry($request->query->get(self::PARAM_COUNTRY));
		} else {
			$country = null;
		}

		return [
			"countries" => $this->countryService->findAllCountries(),
			"englishLanguage" => $this->languageService->getEnglish(),
			"czechLanguage" => $this->languageService->getCzech(),
			"russianLanguage" => $this->languageService->getRussian(),
			"ukrainianLanguage" => $this->languageService->getUkrainian(),
			"bulgarianLanguage" => $this->languageService->getBulgarion(),
			"country" => $country,
			"lines" => $this->lineService->getAllLines()
		];
	}

	/**
	 * @Route(path="/backend/cities/add", name="backend_cities_add", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function cityAddAction()
	{
		return [
			"countries" => $this->countryService->findAllCountries()
		];
	}

	/**
	 * @Route(path="/backend/cities/add", name="backend_cities_add_save", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function cityAddSaveAction(Request $request)
	{
		$city = new City();
		foreach ($request->request->get(self::PARAM_NAME) as $lng => $name) {
			$city->setName($this->languageService->getLanguage($lng), $name);
		}
		$city->setCountry($this->countryService->getCountry($request->request->get(self::PARAM_COUNTRY)));
		$this->cityService->saveCity($city);

		return RedirectResponse::create($this->router->generate("backend_cities"));
	}

	/**
	 * @Route(path="/backend/_ajax/city/{city}", name="ajax_backend_city_save", methods={"POST"})
	 * @return JsonResponse
	 */
	public function ajaxCitySaveAction(City $city, Request $request)
	{
		if ($request->request->has(self::PARAM_LANGUAGE) and $request->request->has(self::PARAM_VALUE)) {
			$lng = $request->request->get(self::PARAM_LANGUAGE);
			$value = $request->request->get(self::PARAM_VALUE);

			if ($lng == "nextVariations") {
				$city->setNextVariations($value);
			} else {
				$city->setName(
					$this->languageService->getLanguage($lng),
					$request->request->get(self::PARAM_VALUE)
				);
			}

			$this->cityService->saveCity($city);
		}

		return JsonResponse::create();
	}

	/**
	 * @Route(path="/backend/city/{city}/remove", name="ajax_backend_city_remove", methods={"POST"})
	 * @return RedirectResponse
	 */
	public function cityRemoveAction(City $city, Request $request)
	{
		$city->setDeleted(true);
		$this->cityService->saveCity($city);

		return RedirectResponse::create($this->router->generate("backend_cities"));
	}
}
