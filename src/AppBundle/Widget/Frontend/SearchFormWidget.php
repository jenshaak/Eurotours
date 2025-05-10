<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 11:05
 */

namespace AppBundle\Widget\Frontend;


use AppBundle\Service\CityService;
use AppBundle\Service\CountryService;
use AppBundle\VO\RouteFilter;
use Motvicka\WidgetBundle\Widget\Widget;

class SearchFormWidget extends Widget
{
	const NAME = "frontend.searchForm";
	
	const TYPE_LEFT = "left";
	const TYPE_HOME = "home";

	/** @var RouteFilter */
	private $routeFilter;
	/**
	 * @var CountryService
	 */
	private $countryService;
	/**
	 * @var CityService
	 */
	private $cityService;

	public function __construct(CountryService $countryService, CityService $cityService)
	{
		$this->countryService = $countryService;
		
		$this->routeFilter = new RouteFilter;
		$this->cityService = $cityService;
	}

	public function fetch($type = self::TYPE_LEFT)
	{
		$template = "@App/Frontend/Widget/searchForm.html.twig";
		if ($type == self::TYPE_HOME) {
			$template = "@App/Frontend/Widget/searchFormHome.html.twig";
		}

		return $this->generate(self::NAME, $this->getTwigEngine()->render($template, [
			"routeFilter" => $this->getRouteFilter(),
			"countries" => $this->countryService->findAllCountries(),
			"fromCity" => $this->cityService->getPragueCity()
		]));
	}

	/**
	 * @return RouteFilter
	 */
	public function getRouteFilter()
	{
		return $this->routeFilter;
	}

	/**
	 * @param RouteFilter $routeFilter
	 */
	public function setRouteFilter($routeFilter)
	{
		$this->routeFilter = $routeFilter;
	}

}