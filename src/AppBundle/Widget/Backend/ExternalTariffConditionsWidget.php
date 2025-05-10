<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 27.11.17
 * Time: 21:07
 */

namespace AppBundle\Widget\Backend;


use AppBundle\Entity\ExternalTariff;
use Motvicka\WidgetBundle\Widget\Widget;

class ExternalTariffConditionsWidget extends Widget
{
	public function fetch(ExternalTariff $tariff)
	{
		return $this->getTwigEngine()->render("AppBundle:Backend/Widget:ExternalTariffConditions.html.twig", [
			"tariff" => $tariff
		]);
	}
}