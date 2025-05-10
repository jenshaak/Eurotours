<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 18:39
 */

namespace AppBundle\Entity;


use AppBundle\VO\ScheduleTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ScheduleLineStationRepository")
 * @ORM\Table(name="schedules_lines_stations")
 */
class ScheduleLineStation
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="schedule_line_station_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var Schedule
	 * @ORM\ManyToOne(targetEntity="Schedule", cascade={"persist"}, inversedBy="scheduleLineStations")
	 * @ORM\JoinColumn(name="schedule_id", referencedColumnName="schedule_id", nullable=false)
	 */
	private $schedule;

	/**
	 * @var LineStation
	 * @ORM\ManyToOne(targetEntity="LineStation", inversedBy="scheduleLineStations", cascade={"persist"})
	 * @ORM\JoinColumn(name="line_station_id", referencedColumnName="line_station_id", nullable=false, onDelete="CASCADE")
	 */
	private $lineStation;

	/**
	 * @var ScheduleTime
	 * @ORM\Column(name="time", type="object", nullable=false)
	 */
	private $time;

	/**
	 * @var string
	 * @ORM\Column(name="platform", type="string", length=32, nullable=true)
	 */
	private $platform;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	public function __construct()
	{
		$this->time = new ScheduleTime;
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
	 * @return Schedule
	 */
	public function getSchedule()
	{
		return $this->schedule;
	}

	/**
	 * @param Schedule $schedule
	 */
	public function setSchedule($schedule)
	{
		$this->schedule = $schedule;
	}

	/**
	 * @return LineStation
	 */
	public function getLineStation()
	{
		return $this->lineStation;
	}

	/**
	 * @param LineStation $lineStation
	 */
	public function setLineStation($lineStation)
	{
		$this->lineStation = $lineStation;
	}

	/**
	 * @return ScheduleTime
	 */
	public function getTime()
	{
		return $this->time;
	}

	/**
	 * @param ScheduleTime $time
	 */
	public function setTime($time)
	{
		$this->time = $time;
	}

	function __clone()
	{
		$this->setId(null);
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

	/**
	 * @return string
	 */
	public function getPlatform()
	{
		return $this->platform;
	}

	/**
	 * @param string $platform
	 */
	public function setPlatform($platform)
	{
		$this->platform = $platform;
	}

}
