<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 12:13
 */

namespace AppBundle\Entity;


use AppBundle\VO\LanguageString;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CountryRepository")
 * @ORM\Table(name="countries")
 */
class Country
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="country_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="name", type="object", nullable=false)
	 */
	private $name;

	/**
	 * @var City[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="City", mappedBy="country")
	 */
	private $cities;

	public function __construct()
	{
		$this->name = new LanguageString;
		$this->cities = new ArrayCollection;
	}

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
	 * @return LanguageString
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param Language $language
	 * @param string $name
	 */
	public function setName(Language $language, $name)
	{
		$this->name = clone $this->getName()->setString($language, $name);
	}

	/**
	 * @return City[]|ArrayCollection
	 */
	public function getCities()
	{
		return $this->cities;
	}

	/**
	 * @return City[]|ArrayCollection
	 */
	public function getActiveCities()
	{
		return $this->getCities()->filter(function (City $city) {
			return !$city->isDeleted();
		});
	}

	/**
	 * @return City[]
	 */
	public function getActiveCitiesSortByName()
	{
		$cities = $this->getCities()->filter(function (City $city) {
			return !$city->isDeleted();
		})->toArray();

		usort($cities, function (City $a, City $b) {
			return strcmp($a->getName()->getAllLanguagesStringsInString(), $b->getName()->getAllLanguagesStringsInString());
		});

		return $cities;
	}

	/**
	 * @param City[]|ArrayCollection $cities
	 */
	public function setCities($cities)
	{
		$this->cities = $cities;
	}

}
