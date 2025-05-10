<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.11.17
 * Time: 12:36
 */

namespace AppBundle\Service;



use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserService
{
	/**
	 * @var Container
	 */
	private $container;
	/**
	 * @var UserRepository
	 */
	private $userRepository;
	/**
	 * @var AuthorizationChecker
	 */
	private $authorizationChecker;

	public function __construct(ContainerInterface $container,
	                            UserRepository $userRepository,
	                            AuthorizationCheckerInterface $authorizationChecker)
	{
		$this->container = $container;
		$this->userRepository = $userRepository;
		$this->authorizationChecker = $authorizationChecker;
	}

	/**
	 * @return User|null
	 */
	public function getCurrentUser()
	{
		/** @var User $user */
		try {
			if ($user = $this->container->get('security.token_storage')->getToken() === null) return null;
			$user = $this->container->get('security.token_storage')->getToken()->getUser();
		} catch (\Exception $e) {
			return null;
		}

		return $user instanceof User ? $user : null;
	}

	/**
	 * @return bool
	 */
	public function isCurrentUserSuperAdmin()
	{
		return $this->getCurrentUser() instanceof User and $this->isSuperAdmin();
	}

	/**
	 * @param string $username
	 * @return \AppBundle\Entity\User
	 */
	public function getUserByUsername($username)
	{
		return $this->userRepository->getUserByUsername($username);
	}

	/**
	 * @param string $username
	 * @param string $password
	 * @return User|null
	 */
	public function getValidUserByUsernameAndPassword($username, $password)
	{
		$user = $this->getUserByUsername($username);
		if ($user instanceof User and $this->isValidPasswordForUser($user, $password)) {
			return $user;
		}

		return null;
	}

	/**
	 * @param User $user
	 * @param string $password
	 * @return bool
	 */
	public function isValidPasswordForUser(User $user, $password)
	{
		return $user->getPassword() === md5($password);
	}

	/**
	 * @return User[]
	 */
	public function findAllUsers()
	{
		return $this->userRepository->findBy([ "enabled" => true ]);
	}

	/**
	 * @return User[]
	 */
	public function findAllSellers()
	{
		return array_filter($this->findAllUsers(), function (User $user) {
			return in_array(User::ROLE_SELLER, $user->getRoles());
		});
	}

	public function saveUser(User $user)
	{
		$this->userRepository->save($user);
	}

	/**
	 * @param string $email
	 * @return User|null
	 */
	public function getUserByEmail($email)
	{
		return $this->userRepository->getUserByEmail($email);
	}

	/**
	 * @param int $id
	 * @return User|null
	 */
	public function getUser($id)
	{
		/** @var User $user */
		$user = $this->userRepository->find($id);
		return $user;
	}

	/**
	 * @return bool
	 */
	public function isSuperAdmin()
	{
		return $this->isCurrentUserGranted(User::ROLE_SUPER_ADMIN);
	}

	/**
	 * @return bool
	 */
	public function isAdmin()
	{
		return $this->isCurrentUserGranted(User::ROLE_ADMIN);
	}

	/**
	 * @return bool
	 */
	public function isCarrier()
	{
		return $this->isCurrentUserGranted(User::ROLE_CARRIER);
	}

	/**
	 * @return bool
	 */
	public function isSeller()
	{
		return $this->isCurrentUserGranted(User::ROLE_SELLER);
	}

	/**
	 * @return bool
	 */
	public function isEmployee()
	{
		return $this->isCurrentUserGranted(User::ROLE_EMPLOYEE);
	}

	/**
	 * @param string $role
	 * @return bool
	 */
	public function isCurrentUserGranted($role)
	{
		return $this->getCurrentUser() !== null and $this->authorizationChecker->isGranted($role);
	}

}
