<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 12:13
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LanguageRepository")
 * @ORM\Table(name="languages")
 */
class Language
{
	const EN = "en";
	const CS = "cs";
	const RU = "ru";
	const BG = "bg";
	const UK = "uk";

	/**
	 * @var string
	 * @ORM\Column(name="language_id", type="string", length=2)
	 * @ORM\Id
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="name", type="string", length=64, nullable=false)
	 */
	private $name;

	/**
	 * @var Country
	 * @ORM\ManyToOne(targetEntity="Country")
	 * @ORM\JoinColumn(name="country_id", referencedColumnName="country_id", nullable=true)
	 */
	private $country;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function getIdForLikeBus(): ?string
	{
		if ($this->id === self::UK) return 'ua';
		else if ($this->id === self::BG) return null;
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
	 * @return Country
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * @param Country $country
	 */
	public function setCountry($country)
	{
		$this->country = $country;
	}

}
