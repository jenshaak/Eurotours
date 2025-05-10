<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 17:02
 */

namespace AppBundle\Controller\Frontend;

use AppBundle\Entity\LineStation;
use AppBundle\Entity\Search;
use AppBundle\Service\CountryService;
use AppBundle\Service\ExternalRouteService;
use AppBundle\Service\LineStationService;
use AppBundle\Service\RouteCombinationService;
use AppBundle\Service\RouteService;
use AppBundle\Service\SearchExternalService;
use AppBundle\Service\SearchService;
use AppBundle\Service\UserService;
use AppBundle\Widget\Frontend\RouteWidget;
use AppBundle\Widget\Frontend\SearchFormWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.frontend.route")
 */
class RouteController
{
	/**
	 * @var RouteService
	 */
	private $routeService;
	/**
	 * @var CountryService
	 */
	private $countryService;
	/**
	 * @var SearchFormWidget
	 */
	private $searchFormWidget;
	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var SearchService
	 */
	private $searchService;
	/**
	 * @var ExternalRouteService
	 */
	private $externalRouteService;
	/**
	 * @var SearchExternalService
	 */
	private $searchExternalService;
	/**
	 * @var RouteWidget
	 */
	private $routeWidget;
	/**
	 * @var LineStationService
	 */
	private $lineStationService;
	/**
	 * @var RouteCombinationService
	 */
	private $routeCombinationService;
	/**
	 * @var UserService
	 */
	private $userService;

	public function __construct(RouteService $routeService,
	                            CountryService $countryService,
	                            SearchFormWidget $searchFormWidget,
	                            Router $router,
	                            SearchService $searchService,
	                            ExternalRouteService $externalRouteService,
	                            SearchExternalService $searchExternalService,
	                            RouteWidget $routeWidget,
	                            LineStationService $lineStationService,
	                            RouteCombinationService $routeCombinationService,
	                            UserService $userService)
	{
		$this->routeService = $routeService;
		$this->countryService = $countryService;
		$this->searchFormWidget = $searchFormWidget;
		$this->router = $router;
		$this->searchService = $searchService;
		$this->externalRouteService = $externalRouteService;
		$this->searchExternalService = $searchExternalService;
		$this->routeWidget = $routeWidget;
		$this->lineStationService = $lineStationService;
		$this->routeCombinationService = $routeCombinationService;
		$this->userService = $userService;
	}

	/**
	 * @Route(path="/search", name="routes_search_create", methods={"POST"})
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function searchCreateAction(Request $request)
	{
		$routeFilter = $this->routeService->createRouteFilterFromRequest($request);
		$this->searchFormWidget->setRouteFilter($routeFilter);

		if ($routeFilter->isReady()) {
			$search = $routeFilter->createSearch();
			$this->searchService->saveSearch($search);

			return RedirectResponse::create($this->router->generate("routes_search", [ "search" => $search->getId() ]));
		} else {
			return RedirectResponse::create($this->router->generate("homepage", $request->query->all()));
		}
	}

	/**
	 * @Route(path="/search/{search}", name="routes_search", methods={"GET"})
	 * @Template()
	 * @param Search $search
	 * @return array
	 */
	public function searchAction(Search $search)
	{
		$currentUser = $this->userService->getCurrentUser();
		$routeFilter = $search->createRouteFilter();
		$this->searchFormWidget->setRouteFilter($routeFilter);

		$this->lineStationService->loadAllLineStations();
		$routes = $this->routeService->findRoutes($search, $this->userService->isAdmin());
		$routeCombinations = $this->routeCombinationService->createCombinations($routes);

		if (!$this->userService->isSeller() || ($currentUser !== null && $currentUser->hasExternalRoutesAllowed())) {
			$this->externalRouteService->callExternalRoutes($search);
		}

		return [
			"routeFilter" => $routeFilter,
			"routes" => $routes,
			"routeCombinations" => $routeCombinations,
			"search" => $search
		];
	}

	/**
	 * @Route(path="/_ajax/search/{search}/external-routes", name="ajax_external_routes", methods={"GET"})
	 * @param Search $search
	 * @return JsonResponse
	 */
	public function ajaxExternalRoutesAction(Search $search)
	{
		$thereRouteWidgets = [];
		$backRouteWidgets = [];
		$processing = 0;

		$searchExternals = $this->searchExternalService->findSearchExternals($search);
		foreach ($searchExternals as $searchExternal) {
			if ($searchExternal->isProcessed() and !$searchExternal->isShowed()) {
				foreach ($this->routeService->findRoutesBySearchExternal($searchExternal) as $route) {
					if ($route->getDirection() == LineStation::DIRECTION_THERE) {
						$thereRouteWidgets[] = $this->routeWidget->fetch($route);
					} elseif ($route->getDirection() == LineStation::DIRECTION_BACK) {
						$backRouteWidgets[] = $this->routeWidget->fetch($route);
					}
				}
				$searchExternal->setShowed(true);
				$searchExternal->setDatetimeShowed(new \DateTime);
				$this->searchExternalService->saveSearchExternal($searchExternal);
			} elseif (!$searchExternal->isProcessed()) {
				$processing++;
			}
		}

		return JsonResponse::create([
			"processing" => $processing,
			"there" => [ "routeWidgets" => $thereRouteWidgets ],
			"back" => [ "routeWidgets" => $backRouteWidgets ],
		]);
	}

}
