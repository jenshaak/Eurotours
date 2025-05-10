<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 12:10
 */

namespace AppBundle\Entity;


use AppBundle\VO\ExternalRouter;
use AppBundle\VO\LanguageString;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CityRepository")
 * @ORM\Table(name="cities")
 */
class City
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="city_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="name", type="object", nullable=false)
	 */
	private $name;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="next_variations", type="string", nullable=false)
	 */
	private $nextVariations = "";

	/**
	 * @var Country
	 * @ORM\ManyToOne(targetEntity="Country", inversedBy="cities")
	 * @ORM\JoinColumn(name="country_id", referencedColumnName="country_id", nullable=false)
	 */
	private $country;

	/**
	 * @var Station[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Station", mappedBy="city")
	 */
	private $stations;

	/**
	 * @var ExternalCity[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="ExternalCity", mappedBy="city")
	 */
	private $externalCities;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	public function __construct()
	{
		$this->name = new LanguageString;
		$this->stations = new ArrayCollection;
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

	/**
	 * @return \string[]
	 */
	public function getAllLanguagesNames()
	{
		return $this->getName()->getAllLanguagesStrings();
	}

	/**
	 * @return Station[]|ArrayCollection
	 */
	public function getStations()
	{
		return $this->stations;
	}

	/**
	 * @param Station[]|ArrayCollection $stations
	 */
	public function setStations($stations)
	{
		$this->stations = $stations;
	}

	/**
	 * @return ExternalCity[]|ArrayCollection
	 */
	public function getExternalCities()
	{
		return $this->externalCities;
	}

	/**
	 * @param ExternalCity[]|ArrayCollection $externalCities
	 */
	public function setExternalCities($externalCities)
	{
		$this->externalCities = $externalCities;
	}

	/**
	 * @param string $type
	 * @return ExternalCity|null
	 */
	public function getExternalCity($type)
	{
		return !$this->getExternalCitiesByType($type)->isEmpty() ? $this->getExternalCitiesByType($type)->first() : null;
	}

	/**
	 * @param string $type
	 * @return ExternalCity[]|ArrayCollection
	 */
	public function getExternalCitiesByType($type)
	{
		return $this->getExternalCities()->filter(function ($externalCity) use ($type) {
			if ($type == ExternalRouter::STUDENT_AGENCY and $externalCity instanceof ExternalCityStudentAgency) {
				return true;
			} elseif ($type == ExternalRouter::ECOLINES and $externalCity instanceof ExternalCityEcolines) {
				return true;
			} elseif ($type == ExternalRouter::INFOBUS and $externalCity instanceof ExternalCityInfobus) {
				return true;
			} elseif ($type == ExternalRouter::EAST_EXPRESS and $externalCity instanceof ExternalCityEastExpress) {
				return true;
			} elseif ($type == ExternalRouter::FLIXBUS and $externalCity instanceof ExternalCityFlixbus) {
				return true;
			} elseif ($type == ExternalRouter::NIKOLO and $externalCity instanceof ExternalCityNikoloBusSystem) {
				return true;
			} elseif ($type == ExternalRouter::EUROLINES and $externalCity instanceof ExternalCityEurolines) {
				return true;
			} elseif ($type == ExternalRouter::REGABUS and $externalCity instanceof ExternalCityRegabus) {
				return true;
			} elseif ($type == ExternalRouter::BLABLA and $externalCity instanceof ExternalCityBlabla) {
				return true;
			} elseif ($type == ExternalRouter::TRANS_TEMPO and $externalCity instanceof ExternalCityTransTempo) {
				return true;
			} elseif ($type == ExternalRouter::LIKEBUS and $externalCity instanceof ExternalCityLikeBus) {
				return true;
			}

			return false;
		});
	}

	/**
	 * @return LanguageString
	 */
	public function getNextVariations()
	{
		return $this->nextVariations;
	}

	/**
	 * @param LanguageString $nextVariations
	 */
	public function setNextVariations($nextVariations)
	{
		$this->nextVariations = $nextVariations;
	}

	/**
	 * @return bool
	 */
	public function isDeleted(): bool
	{
		return $this->deleted;
	}

	/**
	 * @param bool $deleted
	 */
	public function setDeleted(bool $deleted)
	{
		$this->deleted = $deleted;
	}

}
