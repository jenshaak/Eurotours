<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 22.05.17
 * Time: 16:34
 */

namespace AppBundle\Widget\Frontend;

use Motvicka\WidgetBundle\Widget\Widget;

class RoutesWidget extends Widget
{
	const NAME = "frontend.routes";

	public function fetch($direction, $routes, $routeFilter)
	{
		return $this->generate(self::NAME, $this->getTwigEngine()->render("@App/Frontend/Widget/routes.html.twig", [
			"direction" => $direction,
			"routes" => $routes,
			"routeFilter" => $routeFilter
		]));
	}
}