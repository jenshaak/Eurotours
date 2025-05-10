<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 11:19
 */

namespace AppBundle\Repository;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
	public function save(User $user)
	{
		$this->getEntityManager()->persist($user);
		$this->getEntityManager()->flush($user);
	}

	/**
	 * @param string $username
	 * @return User|null
	 */
	public function getUserByUsername($username)
	{
		/** @var User $user */
		$user = $this->findOneBy([ "username" => $username, "enabled" => true ]);
		return $user;
	}

	/**
	 * @param string $email
	 * @return User|null
	 */
	public function getUserByEmail($email)
	{
		/** @var User $user */
		$user = $this->findOneBy([ "email" => $email, "enabled" => true ]);
		return $user;
	}
}
