<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 11:06
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
	const ROLE_SUPER_ADMIN = "ROLE_SUPER_ADMIN";
	const ROLE_ADMIN = "ROLE_ADMIN";
	const ROLE_CARRIER = "ROLE_CARRIER";
	const ROLE_SELLER = "ROLE_SELLER";
	const ROLE_EMPLOYEE = "ROLE_EMPLOYEE";

	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="user_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="username", type="string", length=255)
	 */
	private $username;

	/**
	 * @var string
	 * @ORM\Column(name="password", type="string", length=32)
	 */
	private $password;

	/**
	 * @var string
	 * @ORM\Column(name="email", type="string", length=255)
	 */
	private $email;

	/**
	 * @var Carrier|null
	 * @ORM\ManyToOne(targetEntity="Carrier")
	 * @ORM\JoinColumn(name="carrier_id", referencedColumnName="carrier_id", nullable=true)
	 */
	private $carrier;

	/**
	 * @var string[]
	 * @ORM\Column(name="roles", type="array", nullable=false)
	 */
	private $roles = [];

	/**
	 * @var bool
	 * @ORM\Column(name="enabled", type="boolean", nullable=false)
	 */
	private $enabled = true;

	/**
	 * @var string
	 * @ORM\Column(name="name", type="string", length=255, nullable=true)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(name="phone", type="string", length=64, nullable=true)
	 */
	private $phone;

	/** @ORM\Column(name="external_routes_allowed", type="boolean", nullable=true) */
	private ?bool $externalRoutesAllowed = null;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = $password;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return Carrier|null
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}

	/**
	 * @param Carrier|null $carrier
	 */
	public function setCarrier($carrier)
	{
		$this->carrier = $carrier;
	}

	/**
	 * @return string[]
	 */
	public function getRoles()
	{
		return $this->roles;
	}

	/**
	 * @param string[] $roles
	 */
	public function setRoles($roles)
	{
		$this->roles = $roles;
	}

	public function getSalt()
	{
		return null;
	}

	public function eraseCredentials()
	{

	}

	/**
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @param bool $enabled
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
	}

	public function hasExternalRoutesAllowed(): ?bool
	{
		return $this->externalRoutesAllowed;
	}

	public function setExternalRoutesAllowed(?bool $externalRoutesAllowed): void
	{
		$this->externalRoutesAllowed = $externalRoutesAllowed;
	}
}
