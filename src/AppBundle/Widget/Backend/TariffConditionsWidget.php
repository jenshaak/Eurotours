<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 27.11.17
 * Time: 19:40
 */

namespace AppBundle\Widget\Backend;


use AppBundle\Entity\Tariff;
use Motvicka\WidgetBundle\Widget\Widget;

class TariffConditionsWidget extends Widget
{
	public function fetch(Tariff $tariff)
	{
		return $this->getTwigEngine()->render("AppBundle:Backend/Widget:TariffConditions.html.twig", [
			"tariff" => $tariff
		]);
	}
}