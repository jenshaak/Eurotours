<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:51
 */

namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LineStationRepository")
 * @ORM\Table(name="lines_stations")
 */
class LineStation
{
	const DIRECTION_THERE = "there";
	const DIRECTION_BACK = "back";

	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="line_station_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var City
	 * @ORM\ManyToOne(targetEntity="City")
	 * @ORM\JoinColumn(name="city_id", referencedColumnName="city_id", nullable=false)
	 */
	private $city;

	/**
	 * @var Station
	 * @ORM\ManyToOne(targetEntity="Station")
	 * @ORM\JoinColumn(name="station_id", referencedColumnName="station_id", nullable=false)
	 */
	private $station;

	/**
	 * @var Line
	 * @ORM\ManyToOne(targetEntity="Line", inversedBy="lineStations")
	 * @ORM\JoinColumn(name="line_id", referencedColumnName="line_id", nullable=false)
	 */
	private $line;

	/**
	 * @var int
	 * @ORM\Column(name="weight", type="smallint", nullable=false)
	 */
	private $weight = 0;

	/**
	 * @var ScheduleLineStation[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="ScheduleLineStation", mappedBy="lineStation", cascade={"persist"})
	 */
	private $scheduleLineStations;

	/**
	 * @var string
	 * @ORM\Column(name="direction", type="string", length=32, nullable=false)
	 */
	private $direction;

	/**
	 * @var bool
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	public function __construct()
	{
		$this->scheduleLineStations = new ArrayCollection;
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
	 * @return int
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * @param int $weight
	 */
	public function setWeight($weight)
	{
		$this->weight = $weight;
	}

	/**
	 * @return Line
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * @param Line $line
	 */
	public function setLine($line)
	{
		$this->line = $line;
	}

	/**
	 * @return ScheduleLineStation[]|ArrayCollection
	 */
	public function getScheduleLineStations()
	{
		return $this->scheduleLineStations;
	}

	/**
	 * @param ScheduleLineStation[]|ArrayCollection $scheduleLineStations
	 */
	public function setScheduleLineStations($scheduleLineStations)
	{
		$this->scheduleLineStations = $scheduleLineStations;
	}

	/**
	 * @param ScheduleLineStation $scheduleLineStation
	 */
	public function addScheduleLineStation(ScheduleLineStation $scheduleLineStation)
	{
		$this->getScheduleLineStations()->add($scheduleLineStation);
		$scheduleLineStation->setLineStation($this);
	}

	/**
	 * @return string
	 */
	public function getDirection()
	{
		return $this->direction;
	}

	/**
	 * @param string $direction
	 */
	public function setDirection($direction)
	{
		$this->direction = $direction;
	}

	/**
	 * @param Schedule $schedule
	 * @return ScheduleLineStation|null
	 */
	public function getScheduleLineStationBySchedule(Schedule $schedule)
	{
		$scheduleLineStation = $this->getScheduleLineStations()->filter(function (ScheduleLineStation $sls) use ($schedule) {
			return $sls->getSchedule() == $schedule;
		});

		return $scheduleLineStation->isEmpty() ? null : $scheduleLineStation->first();
	}

	/**
	 * @return boolean
	 */
	public function isDeleted()
	{
		return $this->deleted;
	}

	/**
	 * @param boolean $deleted
	 */
	public function setDeleted($deleted)
	{
		$this->deleted = $deleted;
	}

	function __clone()
	{
		$this->setId(null);
	}

}