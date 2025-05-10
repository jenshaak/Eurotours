<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 12:22
 */

namespace AppBundle\Widget\Backend;


use AppBundle\Service\FlashService;
use Motvicka\WidgetBundle\Widget\Widget;

class FlashWidget extends Widget
{
	/**
	 * @var FlashService
	 */
	private $flashService;

	public function __construct(FlashService $flashService)
	{
		$this->flashService = $flashService;
	}

	/**
	 * @return string
	 * @throws \Twig\Error\Error
	 */
	public function fetch()
	{
		return $this->getTwigEngine()->render("AppBundle:Backend/Widget:Flash.html.twig");
	}
}
