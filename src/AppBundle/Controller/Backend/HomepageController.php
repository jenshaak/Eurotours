<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.06.17
 * Time: 22:57
 */

namespace AppBundle\Controller\Backend;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route(service="")
 */
class HomepageController
{
	/**
	 * @Route(path="/backend", name="backend_homepage", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function homepageAction()
	{
		return [

		];
	}
}