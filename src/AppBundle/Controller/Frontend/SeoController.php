<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.09.18
 * Time: 0:55
 */

namespace AppBundle\Controller\Frontend;


use AppBundle\Entity\SeoCityCombination;
use AppBundle\Service\CityService;
use AppBundle\Service\RouteService;
use AppBundle\Service\SeoCityCombinationService;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\RouteFilter;
use AppBundle\Widget\Frontend\MenuWidget;
use AppBundle\Widget\Frontend\RouteWidget;
use AppBundle\Widget\Frontend\SearchFormWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="controller.frontend.seo")
 */
class SeoController
{
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var RouteWidget
	 */
	private $routeWidget;
	/**
	 * @var SearchFormWidget
	 */
	private $searchFormWidget;
	/**
	 * @var SeoCityCombinationService
	 */
	private $seoCityCombinationService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;

	public function __construct(RouteService $routeService,
	                            RouteWidget $routeWidget,
	                            SearchFormWidget $searchFormWidget,
	                            SeoCityCombinationService $seoCityCombinationService,
	                            CityService $cityService,
	                            MenuWidget $menuWidget)
	{
		$this->routeService = $routeService;
		$this->routeWidget = $routeWidget;
		$this->searchFormWidget = $searchFormWidget;
		$this->seoCityCombinationService = $seoCityCombinationService;
		$this->cityService = $cityService;
		$this->menuWidget = $menuWidget;
	}

	/**
	 * @Route(path="/autobus/{from}/{to}", name="seo_timetables")
	 * @ParamConverter(name="seoCityCombination", options={"mapping": {"from": "fromSlug", "to": "toSlug"}})
	 * @Template()
	 * @return array
	 */
	public function seoTimetablesAction(SeoCityCombination $seoCityCombination)
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_TIMETABLES);
		$this->routeWidget->setForSeo(true);

		$routeFilter = new RouteFilter;
		$routeFilter->setFromCity($seoCityCombination->getFromCity());
		$routeFilter->setToCity($seoCityCombination->getToCity());
		$this->searchFormWidget->setRouteFilter($routeFilter);

		$routes = array_filter(array_map(function ($routeId) {
			return $this->routeService->getRoute($routeId);
		}, $seoCityCombination->getRoutes()), function ($route) {
			return $route instanceof \AppBundle\Entity\Route;
		});

		/** @var PriceCurrency|null $price */
		$price = array_reduce($routes, function ($price, \AppBundle\Entity\Route $route) {
			if ($route->getPriceCurrency()->getCurrency() !== PriceCurrency::CZK) return $price;
			if ($price === null) return $route->getPriceCurrency();
			if ($route->getPriceCurrency()->getPrice() === null) return $price;
			if ($price instanceof PriceCurrency and $price->getPrice() > $route->getPriceCurrency()->getPrice()) {
				return $route->getPriceCurrency();
			}
			return $price;
		}, null);

		if ($price instanceof PriceCurrency) {
			$price->setPrice(ceil($price->getPrice()));
		}

		usort($routes, function (\AppBundle\Entity\Route $r1, \AppBundle\Entity\Route $r2) {
			return $r1->getDatetimeDeparture()->format("Hi") > $r2->getDatetimeDeparture()->format("Hi") ? +1 : -1;
		});

		return [
			"routes" => $routes,
			"fromCity" => $seoCityCombination->getFromCity(),
			"toCity" => $seoCityCombination->getToCity(),
			"price" => $price
		];
	}

	/**
	 * @Route(path="/jizdni-rady.html", name="seo_timetables_list")
	 * @Template()
	 * @return array
	 */
	public function seoTimetablesListAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_TIMETABLES);

		$countries = [];
		array_map(function (SeoCityCombination $c) use (&$countries) {
			$city = $c->getToCity();
			$country = $city->getCountry();
			if (!isset($countries[$country->getId()])) {
				$countries[$country->getId()] = (object) [
					"country" => $country,
					"cities" => []
				];
			}

			if (!isset($countries[$country->getId()]->cities[$city->getId()])) {
				$countries[$country->getId()]->cities[$city->getId()] = (object) [
					"city" => $city,
					"fromSlug" => $c->getFromSlug(),
					"toSlug" => $c->getToSlug()
				];
			}
		}, $this->seoCityCombinationService->findSeoCitiesCombinations($this->cityService->getPragueCity()));

		return [
			"countries" => $countries,
			"prague" => $this->cityService->getPragueCity()
		];
	}

	/**
	 * @Route(path="/bulharsko.html", name="seo_bulharsko")
	 * @Template()
	 * @return array
	 */
	public function seoBulharskoAction()
	{
		$seoCityCombination = $this->seoCityCombinationService->findSeoCityCombination(
			$this->cityService->getPragueCity(),
			$this->cityService->getCity(19)
		);

		$this->menuWidget->setActive(MenuWidget::ACTIVE_BULHARSKO);
		$this->routeWidget->setForSeo(true);

		$routeFilter = new RouteFilter;
		$routeFilter->setFromCity($seoCityCombination->getFromCity());
		$routeFilter->setToCity($seoCityCombination->getToCity());
		$this->searchFormWidget->setRouteFilter($routeFilter);

		$routes = array_filter(array_map(function ($routeId) {
			return $this->routeService->getRoute($routeId);
		}, $seoCityCombination->getRoutes()), function ($route) {
			return $route instanceof \AppBundle\Entity\Route;
		});

		usort($routes, function (\AppBundle\Entity\Route $r1, \AppBundle\Entity\Route $r2) {
			return $r1->getDatetimeDeparture()->format("Hi") > $r2->getDatetimeDeparture()->format("Hi") ? +1 : -1;
		});

		return [
			"routes" => $routes
		];
	}

}
