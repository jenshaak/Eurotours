<?php


namespace AppBundle\Controller\Frontend;

use AppBundle\Service\CityService;
use AppBundle\Service\LineService;
use AppBundle\Service\SeoCityCombinationService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

/**
 * @Route(service="controller.frontend.oldSite")
 */
class OldSiteController
{
	const PARAM_LINKA = "linka";

	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var LineService
	 */
	private $lineService;
	/**
	 * @var CityService
	 */
	private $cityService;
	/**
	 * @var SeoCityCombinationService
	 */
	private $seoCityCombinationService;

	public function __construct(Router $router,
	                            LineService $lineService,
	                            CityService $cityService,
	                            SeoCityCombinationService $seoCityCombinationService)
	{
		$this->router = $router;
		$this->lineService = $lineService;
		$this->cityService = $cityService;
		$this->seoCityCombinationService = $seoCityCombinationService;
	}

	/**
	 * @Route(path="jizdni-rad.html")
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function jizdniRadAction(Request $request)
	{
		$code = $request->query->get(self::PARAM_LINKA);
		if ($code) {
			$line = $this->lineService->getLineByCode($code);

			if ($line) {
				$prague = $this->cityService->getPragueCity();

				$combination = $this->seoCityCombinationService->findSeoCityCombination($prague, $line->getLastLineStation()->getCity());
				if ($combination) {
					return RedirectResponse::create($this->router->generate("seo_timetables", [
						"from" => $combination->getFromSlug(),
						"to" => $combination->getToSlug()
					]));
				}

				$combination = $this->seoCityCombinationService->findSeoCityCombination(
					$line->getFirstLineStation()->getCity(),
					$line->getLastLineStation()->getCity()
				);
				if ($combination) {
					return RedirectResponse::create($this->router->generate("seo_timetables", [
						"from" => $combination->getFromSlug(),
						"to" => $combination->getToSlug()
					]));
				}
			}
		}

		return RedirectResponse::create($this->router->generate("homepage"));
	}
}
