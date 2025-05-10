<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 17:23
 */

namespace AppBundle\Twig;


use AppBundle\Entity\User;
use AppBundle\Service\UserService;
use Twig_SimpleFunction;
use Twig_SimpleTest;

class UserRoleExtension extends \Twig_Extension
{
	/**
	 * @var UserService
	 */
	private $userService;

	public function __construct(UserService $userService)
	{
		$this->userService = $userService;
	}

	public function getFunctions()
	{
		return [
			new Twig_SimpleFunction("isSuperAdmin", function () { return $this->userService->isSuperAdmin(); }),
			new Twig_SimpleFunction("isAdmin", function () { return $this->userService->isAdmin(); }),
			new Twig_SimpleFunction("isCarrier", function () { return $this->userService->isCarrier(); }),
			new Twig_SimpleFunction("isSeller", function () { return $this->userService->isSeller(); }),
			new Twig_SimpleFunction("isEmployee", function () { return $this->userService->isEmployee(); })
		];
	}

	public function getName()
	{
		return "userRole";
	}

}
