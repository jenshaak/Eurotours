<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 13:46
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\City;
use AppBundle\Entity\ExternalCity;
use AppBundle\Service\CityService;
use AppBundle\Service\CountryService;
use AppBundle\Service\ExternalCityService;
use AppBundle\Service\ExternalTariffService;
use AppBundle\Service\LanguageService;
use AppBundle\Service\TariffService;
use AppBundle\VO\ExternalRouter;
use AppBundle\Widget\Backend\MenuWidget;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.external")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class ExternalController
{
	const PARAM_CITIES = "cities";
	const PARAM_TARIFFS = "tariffs";
	const PARAM_EXTERNAL_TARIFFS = "externalTariffs";
	const PARAM_EXTERNAL_CITY = "externalCity";

	/**
	 * @var ExternalCityService
	 */
	private $externalCityService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var ExternalTariffService
	 */
	private $externalTariffService;
	/**
	 * @var LanguageService
	 */
	private $languageService;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var TariffService
	 */
	private $tariffService;
	/**
	 * @var CountryService
	 */
	private $countryService;

	public function __construct(ExternalCityService $externalCityService,
	                            CityService $cityService,
	                            Router $router,
	                            ExternalTariffService $externalTariffService,
	                            LanguageService $languageService,
	                            MenuWidget $menuWidget,
	                            TariffService $tariffService,
	                            CountryService $countryService)
	{
		$this->externalCityService = $externalCityService;
		$this->cityService = $cityService;
		$this->router = $router;
		$this->externalTariffService = $externalTariffService;
		$this->languageService = $languageService;
		$this->menuWidget = $menuWidget;
		$this->tariffService = $tariffService;
		$this->countryService = $countryService;
	}

	/**
	 * @Route(path="/backend/externals/cities-approve", name="backend_externals_cities_approve", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function citiesApproveAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_EXTERNAL_CITIES_APPROVE);

		return [
			"cities" => $this->cityService->findAllCities(),
			"externalCities" => $this->externalCityService->findWaitingExternalCities()
		];
	}

	/**
	 * @Route(path="/backend/externals/cities", name="backend_externals_cities", methods={"GET"})
	 * @Template()
	 * @return array
	 * @throws \Exception
	 */
	public function citiesAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_EXTERNAL_CITIES);

		return [
			"countries" => $this->countryService->findAllCountries()
		];
	}

	/**
	 * @Route(path="/backend/externals/cities/{city}/setExternal", name="backend_externals_city_set_external", methods={"GET"})
	 * @Template()
	 * @return array
	 * @throws \Exception
	 */
	public function citySetExternalAction(City $city)
	{
		return [
			"externalCities" => $this->externalCityService->findAllExternalCities(),
			"city" => $city
		];
	}

	/**
	 * @Route(path="/backend/externals/cities/{city}/setExternal", name="backend_externals_city_set_external_save", methods={"POST"})
	 * @throws \Exception
	 */
	public function citySetExternalSaveAction(City $city, Request $request)
	{
		foreach ($request->request->get(self::PARAM_EXTERNAL_CITY) as $type => $id) {
			if (is_array($id)) {
				$city->getExternalCitiesByType($type)->map(function (ExternalCity $externalCity) use ($city) {
					$externalCity->setCity(null);
					$city->getExternalCities()->removeElement($externalCity);
					$this->externalCityService->saveExternalCity($externalCity);
				});
				(new ArrayCollection($id))->map(function ($id) use ($city) {
					$externalCity = $this->externalCityService->getExternalCity($id);
					$externalCity->setCity($city);
					$city->getExternalCities()->add($externalCity);
					$this->externalCityService->saveExternalCity($externalCity);
				});
			} else {
				if ($id > 0) {
					$oldExternalCity = $city->getExternalCity($type);
					if ($oldExternalCity) {
						$oldExternalCity->setCity(null);
						$city->getExternalCities()->removeElement($oldExternalCity);
						$this->externalCityService->saveExternalCity($oldExternalCity);
					}

					$externalCity = $this->externalCityService->getExternalCity($id);
					$externalCity->setCity($city);
					if (!$city->getExternalCities()->contains($externalCity)) {
						$city->getExternalCities()->add($externalCity);
					}
					$this->externalCityService->saveExternalCity($externalCity);
				} else {
					$externalCity = $city->getExternalCity($type);
					if ($externalCity) {
						$externalCity->setCity(null);
						$city->getExternalCities()->removeElement($externalCity);
						$this->externalCityService->saveExternalCity($externalCity);
					}
				}
			}
		}

		$english = $this->languageService->getEnglish();

		$return = [
			ExternalRouter::ECOLINES => "",
			ExternalRouter::STUDENT_AGENCY => "",
			ExternalRouter::EAST_EXPRESS => "",
			ExternalRouter::EUROLINES => "",
			ExternalRouter::FLIXBUS => "",
			ExternalRouter::INFOBUS => "",
			ExternalRouter::NIKOLO => "",
			ExternalRouter::BLABLA => "",
			ExternalRouter::LIKEBUS => "",
		];

		foreach ($return as $key => $val) {
			$return[$key] = $city->getExternalCity($key) ?
				$city->getExternalCity($key)->getName()->getString($english) : "";
		}

		return JsonResponse::create($return);
	}

	/**
	 * @Route(path="/backend/externals/cities", name="backend_externals_cities_save", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function citiesSaveAction(Request $request)
	{
		if ($request->request->has(self::PARAM_CITIES)) {
			foreach ($request->request->get(self::PARAM_CITIES) as $externalCityId => $cityId) {
				$externalCity = $this->externalCityService->getExternalCity($externalCityId);
				$city = $this->cityService->getCity($cityId);
				$externalCity->setCity($city);
				$externalCity->setProcessed(true);
				$this->externalCityService->saveExternalCity($externalCity);
			}
		}

		return RedirectResponse::create($this->router->generate("backend_externals_cities_approve"));
	}

	/**
	 * @Route(path="/backend/externals/tariffs", name="backend_externals_tariffs", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function tariffsAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_EXTERNAL_TARIFFS);

		return [
			"externalTariffs" => $this->externalTariffService->findAllExternalTariffs(),
			"tariffs" => $this->tariffService->findAllTariffs(),
			"englishLanguage" => $this->languageService->getEnglish(),
			"czechLanguage" => $this->languageService->getCzech(),
			"russianLanguage" => $this->languageService->getRussian(),
			"ukrainianLanguage" => $this->languageService->getUkrainian(),
			"bulgarianLanguage" => $this->languageService->getBulgarion()
		];
	}

	/**
	 * @Route(path="/backend/externals/tariffs", name="backend_externals_tariffs_save", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function tariffsSaveAction(Request $request)
	{
		if ($request->request->has(self::PARAM_EXTERNAL_TARIFFS)) {
			foreach ($request->request->get(self::PARAM_EXTERNAL_TARIFFS) as $tariffId => $array) {
				$externalTariff = $this->externalTariffService->getExternalTariffById($tariffId);
				foreach ($array as $languageCode => $text) {
					$externalTariff->setName($this->languageService->getLanguage($languageCode), $text);
					$this->externalTariffService->saveExternalTariff($externalTariff);
				}
			}
		}

		if ($request->request->has(self::PARAM_TARIFFS)) {
			foreach ($request->request->get(self::PARAM_TARIFFS) as $tariffId => $array) {
				$tariff = $this->tariffService->getTariff($tariffId);
				foreach ($array as $languageCode => $text) {
					$tariff->setName($this->languageService->getLanguage($languageCode), $text);
					$this->tariffService->saveTariff($tariff);
				}
			}
		}

		return RedirectResponse::create($this->router->generate("backend_externals_tariffs"));
	}
}
