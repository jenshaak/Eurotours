<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 29.01.18
 * Time: 17:19
 */

namespace AppBundle\Controller\Frontend;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @Route(service="controller.frontend.login")
 */
class LoginController
{
	/**
	 * @var Router
	 */
	private $router;

	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * @Route(path="/login", name="login")
	 * @return RedirectResponse
	 */
	public function loginAction()
	{
		return RedirectResponse::create($this->router->generate("homepage"));
	}
}
