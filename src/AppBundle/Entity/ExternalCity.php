<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 11:49
 */

namespace AppBundle\Entity;
use AppBundle\VO\LanguageString;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExternalCityRepository")
 * @ORM\Table(name="external_cities")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *         "student_agency" = "ExternalCityStudentAgency",
 *         "ecolines" = "ExternalCityEcolines",
 *         "infobus" = "ExternalCityInfobus",
 *         "east_express" = "ExternalCityEastExpress",
 *         "flixbus" = "ExternalCityFlixbus",
 *         "eurolines" = "ExternalCityEurolines",
 *         "nikolo" = "ExternalCityNikolo",
 *         "regabus" = "ExternalCityRegabus",
 *         "blabla" = "ExternalCityBlabla",
 *         "trans_tempo" = "ExternalCityTransTempo",
 *     	   "nikolo_bus_system" = "ExternalCityNikoloBusSystem",
 *         "likebus" = "ExternalCityLikeBus"
 *     }
 * )
 */
abstract class ExternalCity
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="external_city_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="ident", type="string", length=64, nullable=false)
	 */
	private $ident;

	/**
	 * @var City
	 * @ORM\ManyToOne(targetEntity="City", inversedBy="externalCities")
	 * @ORM\JoinColumn(name="city_id", referencedColumnName="city_id", nullable=true)
	 */
	private $city;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="name", type="object", nullable=false)
	 */
	private $name;

	/**
	 * @var bool
	 * @ORM\Column(name="processed", type="boolean", nullable=false)
	 */
	private $processed = false;

	/**
	 * @var ExternalStation[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="AppBundle\Entity\ExternalStation", mappedBy="externalCity")
	 */
	private $externalStations;

	public function __construct()
	{
		$this->name = new LanguageString;
		$this->externalStations = new ArrayCollection;
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
	 * @return string
	 */
	public function getIdent()
	{
		return $this->ident;
	}

	/**
	 * @param string $ident
	 */
	public function setIdent($ident)
	{
		$this->ident = $ident;
	}

	/**
	 * @return City
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @param City $city
	 */
	public function setCity($city)
	{
		$this->city = $city;
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
	 * @return boolean
	 */
	public function isProcessed()
	{
		return $this->processed;
	}

	/**
	 * @param boolean $processed
	 */
	public function setProcessed($processed)
	{
		$this->processed = $processed;
	}

	/**
	 * @return ExternalStation[]|ArrayCollection
	 */
	public function getExternalStations()
	{
		return $this->externalStations;
	}

}
