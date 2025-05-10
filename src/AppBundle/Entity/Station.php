<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:42
 */

namespace AppBundle\Entity;


use AppBundle\VO\LanguageString;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StationRepository")
 * @ORM\Table(name="stations")
 */
class Station
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="station_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="name", type="object", nullable=false)
	 */
	private $name;

	/**
	 * @var City
	 * @ORM\ManyToOne(targetEntity="City", inversedBy="stations")
	 * @ORM\JoinColumn(name="city_id", referencedColumnName="city_id", nullable=true)
	 */
	private $city;

	/**
	 * @var int
	 * @ORM\Column(name="old_station_id", type="integer", nullable=true)
	 */
	private $oldStationId;

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
	 * @return int
	 */
	public function getOldStationId()
	{
		return $this->oldStationId;
	}

	/**
	 * @param int $oldStationId
	 */
	public function setOldStationId($oldStationId)
	{
		$this->oldStationId = $oldStationId;
	}

}