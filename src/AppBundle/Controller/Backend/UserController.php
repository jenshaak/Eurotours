<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 11:11
 */

namespace AppBundle\Controller\Backend;


use AppBundle\Entity\User;
use AppBundle\Service\CarrierService;
use AppBundle\Service\FlashService;
use AppBundle\Service\UserService;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @Route(service="controller.backend.user")
 */
class UserController
{
	const PARAM_PASSWORD = "password";
	const PARAM_USERNAME = "username";
	const PARAM_EMAIL = "email";
	const PARAM_CARRIER = "carrier";
	const PARAM_ROLE = "role";
	const PARAM_NAME = "name";
	const PARAM_PHONE = "phone";
	const PARAM_EDITABLE_NAME = "name";
	const PARAM_EDITABLE_KEY_USERNAME = "username";
	const PARAM_EDITABLE_KEY_EMAIL = "email";
	const PARAM_EDITABLE_KEY_EXTERNAL_ROUTES_ALLOWED = "externalRoutesAllowed";
	const PARAM_EDITABLE_VALUE = "value";

	/**
	 * @var Router
	 */
	private $router;
	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var TokenStorage
	 */
	private $tokenStorage;
	/**
	 * @var EventDispatcher
	 */
	private $eventDispatcher;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var FlashService
	 */
	private $flashService;
	/**
	 * @var RememberMeServicesInterface
	 */
	private $rememberMeServices;

	public function __construct(RouterInterface $router,
	                            UserService $userService,
	                            TokenStorageInterface $tokenStorage,
	                            EventDispatcherInterface $eventDispatcher,
	                            MenuWidget $menuWidget,
	                            CarrierService $carrierService,
	                            FlashService $flashService,
	                            RememberMeServicesInterface $rememberMeServices)
	{
		$this->router = $router;
		$this->userService = $userService;
		$this->tokenStorage = $tokenStorage;
		$this->eventDispatcher = $eventDispatcher;
		$this->menuWidget = $menuWidget;
		$this->carrierService = $carrierService;
		$this->flashService = $flashService;
		$this->rememberMeServices = $rememberMeServices;
	}

	/**
	 * @Route(path="/login", name="backend_login", methods={"GET"})
	 * @Template()
	 * @return array
	 */
	public function loginAction()
	{
		return [];
	}

	/**
	 * @Route(path="/login/check", name="backend_login_check", methods={"POST"})
	 */
	public function loginCheckAction(Request $request)
	{
		$username = $request->request->get(self::PARAM_USERNAME);
		$password = $request->request->get(self::PARAM_PASSWORD);

		$user = $this->userService->getValidUserByUsernameAndPassword($username, $password);
		if ($user === null) {
			return RedirectResponse::create($this->router->generate("backend_lines"));
		}

		$token = new UsernamePasswordToken($user, null, "main", $user->getRoles());
		$this->tokenStorage->setToken($token);

		if (in_array(User::ROLE_CARRIER, $user->getRoles())) {
			$response = RedirectResponse::create($this->router->generate("backend_seats_sold"));
		} elseif (in_array(User::ROLE_SELLER, $user->getRoles())) {
			$response = RedirectResponse::create($this->router->generate("backend_orders"));
		} else {
			$response = RedirectResponse::create($this->router->generate("backend_lines"));
		}

		$this->rememberMeServices->loginSuccess($request, $response, $token);

		$event = new InteractiveLoginEvent($request, $token);
		$this->eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);

		return $response;
	}

	/**
	 * @Route(path="/logout", name="backend_logout", methods={"GET"})
	 */
	public function logoutAction()
	{
		$this->tokenStorage->setToken(null);

		return RedirectResponse::create($this->router->generate("homepage"));
	}

	/**
	 * @Route(path="/backend/users", name="backend_users", methods={"GET"})
	 * @Security("has_role('ROLE_SUPER_ADMIN')")
	 * @Template()
	 * @return array
	 */
	public function usersAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_USERS);

		return [
			"users" => $this->userService->findAllUsers(),
			"carriers" => $this->carrierService->findAllCarriers()
		];
	}

	/**
	 * @Route(path="/backend/users/create", name="backend_user_create", methods={"POST"})
	 * @Security("has_role('ROLE_SUPER_ADMIN')")
	 */
	public function createUserAction(Request $request)
	{
		$user = new User;
		$user->setEmail($request->request->get(self::PARAM_EMAIL));
		$user->setUsername($request->request->get(self::PARAM_USERNAME));
		$user->setRoles([ $request->request->get(self::PARAM_ROLE) ]);
		if (in_array(User::ROLE_SELLER, $user->getRoles())) {
			$user->setExternalRoutesAllowed(false);
		}
		if ($request->request->has(self::PARAM_CARRIER)) {
			$user->setCarrier($this->carrierService->getCarrier($request->request->get(self::PARAM_CARRIER)));
		}
		if ($request->request->has(self::PARAM_NAME)) {
			$user->setName($request->request->get(self::PARAM_NAME));
		}
		if ($request->request->has(self::PARAM_PHONE)) {
			$user->setPhone($request->request->get(self::PARAM_PHONE));
		}
		$password = substr(md5(time() . rand(0, 1000000)), 0, 6);
		$user->setPassword(md5($password));

		if ($this->userService->getUserByUsername($user->getUsername())) {
			$this->flashService->addError("Tento uživatel již v systému existuje. Nelze vytvořit uživatel se stejným přihlašovacím jménem.");
			return RedirectResponse::create($this->router->generate("backend_users"));
		}

		$this->userService->saveUser($user);

		$this->flashService->addNotice("Uživatel {$user->getUsername()} vytvořen. Vygenerováno heslo '{$password}'.");

		return RedirectResponse::create($this->router->generate("backend_users"));
	}

	/**
	 * @Route(path="/backend/user/{user}", name="backend_user_edit", methods={"POST"})
	 * @Security("has_role('ROLE_SUPER_ADMIN')")
	 */
	public function editUserAction(User $user, Request $request)
	{
		$key = $request->request->get(self::PARAM_EDITABLE_NAME);
		$value = $request->request->get(self::PARAM_EDITABLE_VALUE);

		if ($key === self::PARAM_EDITABLE_KEY_USERNAME) {
			$user->setUsername($value);
		} elseif ($key === self::PARAM_EDITABLE_KEY_EMAIL) {
			$user->setEmail($value);
		} elseif ($key === self::PARAM_EDITABLE_KEY_EXTERNAL_ROUTES_ALLOWED) {
			$user->setExternalRoutesAllowed((bool)$value);
		}

		$this->userService->saveUser($user);

		return RedirectResponse::create($this->router->generate("backend_users"));
	}

	/**
	 * @Route(path="/backend/user/{user}/remove", name="backend_user_remove", methods={"POST"})
	 * @Security("has_role('ROLE_SUPER_ADMIN')")
	 */
	public function removeUserAction(User $user)
	{
		$user->setEnabled(false);
		$this->userService->saveUser($user);

		$this->flashService->addNotice("Uživatel {$user->getUsername()} odstraněn.");

		return RedirectResponse::create($this->router->generate("backend_users"));
	}

	/**
	 * @Route(path="/backend/user/{user}/changePassword", name="backend_user_change_password", methods={"POST"})
	 * @Security("has_role('ROLE_SUPER_ADMIN')")
	 * @param User $user
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function changePasswordAction(User $user, Request $request)
	{
		$password = $request->request->get(self::PARAM_PASSWORD);
		$user->setPassword(md5($password));
		$this->userService->saveUser($user);

		$this->flashService->addNotice("Nastaveno nové heslo '{$password}' pro uživatele '{$user->getUsername()}'.");

		return RedirectResponse::create($this->router->generate("backend_users"));
	}
}
