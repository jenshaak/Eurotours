<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-06-03
 * Time: 18:27
 */

namespace AppBundle\Widget\Backend;


use AppBundle\Entity\Activity;
use Motvicka\WidgetBundle\Widget\Widget;

class ActivityWidget extends Widget
{
	public function fetch(Activity $activity)
	{
		return $this->getTwigEngine()->render("AppBundle:Backend/Widget:Activity.html.twig", [
			"activity" => $activity
		]);
	}
}
