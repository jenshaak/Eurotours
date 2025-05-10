<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.05.17
 * Time: 12:02
 */

namespace AppBundle\Entity;
use AppBundle\VO\LanguageString;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExternalStationRepository")
 * @ORM\Table(name="external_stations")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *         "student_agency" = "ExternalStationStudentAgency",
 *         "flixbus" = "ExternalStationFlixbus",
 *         "eurolines" = "ExternalStationEurolines",
 *         "infobus" = "ExternalStationInfobus",
 *         "nikolo" = "ExternalStationNikolo",
 *         "regabus" = "ExternalStationRegabus",
 *         "blabla" = "ExternalStationBlabla",
 *         "likebus" = "ExternalStationLikeBus"
 *     }
 * )
 */
abstract class ExternalStation
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="external_station_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="ident", type="string", length=64, nullable=false)
	 */
	private $ident;

	/**
	 * @var ExternalCity
	 * @ORM\ManyToOne(targetEntity="ExternalCity", inversedBy="externalStations")
	 * @ORM\JoinColumn(name="external_city_id", referencedColumnName="external_city_id", nullable=true)
	 */
	private $externalCity;

	/**
	 * @var Station
	 * @ORM\ManyToOne(targetEntity="Station")
	 * @ORM\JoinColumn(name="station_id", referencedColumnName="station_id", nullable=true)
	 */
	private $station;

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

	public function __construct()
	{
		$this->name = new LanguageString;
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
	 * @return Station
	 */
	public function getStation()
	{
		return $this->station;
	}

	/**
	 * @param Station $station
	 */
	public function setStation($station)
	{
		$this->station = $station;
	}

	/**
	 * @return ExternalCity
	 */
	public function getExternalCity()
	{
		return $this->externalCity;
	}

	/**
	 * @param ExternalCity $externalCity
	 */
	public function setExternalCity($externalCity)
	{
		$this->externalCity = $externalCity;
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

}
