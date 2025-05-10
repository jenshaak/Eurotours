<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 15:01
 */

namespace AppBundle\Entity;


use AppBundle\VO\DayRange;
use AppBundle\VO\Days;
use AppBundle\VO\LanguageString;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ScheduleRepository")
 * @ORM\Table(name="schedules")
 */
class Schedule
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="schedule_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var Line
	 * @ORM\ManyToOne(targetEntity="Line", inversedBy="schedules")
	 * @ORM\JoinColumn(name="line_id", referencedColumnName="line_id", nullable=false)
	 */
	private $line;

	/**
	 * @var int[]|ArrayCollection
	 * @ORM\Column(name="week_days", type="array", nullable=false)
	 */
	private $weekDays;

	/**
	 * @var Days
	 * @ORM\Column(name="include_days", type="object", nullable=false)
	 */
	private $includeDays;

	/**
	 * @var Days
	 * @ORM\Column(name="exclude_days", type="object", nullable=false)
	 */
	private $excludeDays;

	/**
	 * @var ScheduleLineStation[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="ScheduleLineStation", mappedBy="schedule", cascade={"all"})
	 */
	private $scheduleLineStations;

	/**
	 * @var string
	 * @ORM\Column(name="direction", type="string", length=32, nullable=false)
	 */
	private $direction;

	/**
	 * @var int
	 * @ORM\Column(name="old_price_table_number", type="integer", nullable=true)
	 */
	private $oldPriceTableNumber;

	/**
	 * @var Tariff
	 * @ORM\ManyToOne(targetEntity="Tariff")
	 * @ORM\JoinColumn(name="tariff_id", referencedColumnName="tariff_id", nullable=true)
	 */
	private $tariff;

	/**
	 * @var Tariff[]|ArrayCollection
	 * @ORM\ManyToMany(targetEntity="Tariff", cascade={"persist"})
	 * @ORM\JoinTable(
	 *     name="schedules_tariffs",
	 *     joinColumns={@ORM\JoinColumn(name="schedule_id", referencedColumnName="schedule_id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="tariff_id", referencedColumnName="tariff_id")}
	 * )
	 */
	private $tariffs;

	/**
	 * @var int
	 * @ORM\Column(name="own_seats", type="integer", nullable=true)
	 */
	private $ownSeats;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	/**
	 * @var int
	 * @ORM\Column(name="weight", type="smallint", nullable=false)
	 */
	private $weight = 0;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="private_note", type="object", nullable=false)
	 */
	private $privateNote;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="public_note", type="object", nullable=false)
	 */
	private $publicNote;

	/**
	 * @var bool
	 * @ORM\Column(name="seats_without_numbers", type="boolean", nullable=false)
	 */
	private $seatsWithoutNumbers = false;

	/**
	 * @var bool
	 * @ORM\Column(name="pay_online_disabled", type="boolean", nullable=false)
	 */
	private $payOnlineDisabled = false;

	public function __construct()
	{
		$this->weekDays = new ArrayCollection;
		$this->scheduleLineStations = new ArrayCollection;
		$this->tariffs = new ArrayCollection;
		$this->includeDays = new Days;
		$this->includeDays->setAll(true);
		$this->excludeDays = new Days;
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
	 * @return ArrayCollection|\int[]
	 */
	public function getWeekDays()
	{
		if ($this->weekDays instanceof ArrayCollection) {
			return $this->weekDays;
		} else {
			return new ArrayCollection($this->weekDays);
		}
	}

	/**
	 * @param int $day
	 */
	public function addWeekDay($day)
	{
		if (!$this->getWeekDays()->contains($day)) {
			$this->getWeekDays()->add($day);
		}
	}

	/**
	 * @param ArrayCollection|\int[] $weekDays
	 */
	public function setWeekDays($weekDays)
	{
		$this->weekDays = $weekDays;
	}

	/**
	 * @return Days
	 */
	public function getExcludeDays()
	{
		return $this->excludeDays;
	}

	/**
	 * @param Days $excludeDays
	 */
	public function setExcludeDays($excludeDays)
	{
		$this->excludeDays = $excludeDays;
	}

	/**
	 * @return Days
	 */
	public function getIncludeDays()
	{
		return $this->includeDays;
	}

	/**
	 * @param Days $includeDays
	 */
	public function setIncludeDays($includeDays)
	{
		$this->includeDays = $includeDays;
	}

	/**
	 * @return ScheduleLineStation[]|ArrayCollection
	 */
	public function getScheduleLineStations()
	{
		return $this->scheduleLineStations;
	}

	/**
	 * @return ScheduleLineStation[]|ArrayCollection
	 */
	public function getActiveScheduleLineStations()
	{
		return $this->scheduleLineStations->filter(function (ScheduleLineStation $sls) {
			return !$sls->isDeleted();
		});
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
		$this->scheduleLineStations->add($scheduleLineStation);
		$scheduleLineStation->setSchedule($this);
	}

	/**
	 * @param City $city
	 * @return ScheduleLineStation|null
	 */
	public function getScheduleLineStationForCity(City $city)
	{
		/** @var ArrayCollection|ScheduleLineStation[] $return */
		$return = $this->getActiveScheduleLineStations()->filter(function (ScheduleLineStation $scheduleLineStation) use ($city) {
			return $scheduleLineStation->getLineStation()->getCity() == $city;
		});

		return $return->isEmpty() ? null : $return->first();
	}

	/**
	 * @param City $city
	 * @return ScheduleLineStation[]|ArrayCollection|null
	 */
	public function getScheduleLineStationsForCity(City $city)
	{
		/** @var ArrayCollection|ScheduleLineStation[] $return */
		$return = $this->getActiveScheduleLineStations()->filter(function (ScheduleLineStation $scheduleLineStation) use ($city) {
			return $scheduleLineStation->getLineStation()->getCity() === $city;
		});

		return $return;
	}

	/**
	 * @param LineStation $lineStation
	 * @return ScheduleLineStation|null
	 */
	public function getScheduleLineStationsForLineStation(LineStation $lineStation)
	{
		/** @var ArrayCollection|ScheduleLineStation[] $return */
		$return = $this->getActiveScheduleLineStations()->filter(function (ScheduleLineStation $scheduleLineStation) use ($lineStation) {
			return $scheduleLineStation->getLineStation() === $lineStation;
		});

		return $return->isEmpty() ? null : $return->first();
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
	 * @return int
	 */
	public function getOldPriceTableNumber()
	{
		return $this->oldPriceTableNumber;
	}

	/**
	 * @param int $oldPriceTableNumber
	 */
	public function setOldPriceTableNumber($oldPriceTableNumber)
	{
		$this->oldPriceTableNumber = $oldPriceTableNumber;
	}

	/**
	 * @return Tariff
	 * @deprecated
	 */
	public function getTariff()
	{
		return $this->tariff;
	}

	/**
	 * @param Tariff $tariff
	 * @deprecated
	 */
	public function setTariff($tariff)
	{
		$this->tariff = $tariff;
	}

	/**
	 * @return Tariff[]|ArrayCollection
	 */
	public function getTariffs()
	{
		return $this->tariffs;
	}

	/**
	 * @return Tariff[]|ArrayCollection
	 */
	public function getActiveTariffs()
	{
		return $this->tariffs->filter(function (Tariff $tariff) {
			return !$tariff->isDeleted();
		});
	}

	/**
	 * @param Tariff[]|ArrayCollection $tariffs
	 */
	public function setTariffs($tariffs)
	{
		$this->tariffs = $tariffs;
	}

	/**
	 * @return int
	 */
	public function getOwnSeats()
	{
		return $this->ownSeats;
	}

	/**
	 * @param int $ownSeats
	 */
	public function setOwnSeats($ownSeats)
	{
		$this->ownSeats = $ownSeats;
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
	 * @param string $direction
	 * @return ScheduleLineStation|null
	 */
	public function getFirstScheduleLineStation()
	{
		/** @var ScheduleLineStation $return */
		$return = null;

		$this->getLine()->getLineStations($this->getDirection())->map(function (LineStation $lineStation) use (&$return) {
			$scheduleLineStation = $this->getScheduleLineStationsForLineStation($lineStation);
			if ($scheduleLineStation and $scheduleLineStation->getTime()->getDepartureTime()) {
				if ($return === null) $return = $scheduleLineStation;
			}
		});

		return $return;
	}

	/**
	 * @param string $direction
	 * @return ScheduleLineStation|null
	 */
	public function getLastScheduleLineStation()
	{
		/** @var ScheduleLineStation $return */
		$return = null;

		$this->getLine()->getLineStations($this->getDirection())->map(function (LineStation $lineStation) use (&$return) {
			$scheduleLineStation = $this->getScheduleLineStationsForLineStation($lineStation);
			if ($scheduleLineStation and $scheduleLineStation->getTime()->getDepartureTime()) {
				$return = $scheduleLineStation;
			}
		});

		return $return;
	}

	/**
	 * @return LanguageString
	 */
	public function getPrivateNote()
	{
		return $this->privateNote;
	}

	/**
	 * @param Language $language
	 * @param string $privateNote
	 */
	public function setPrivateNote(Language $language, $privateNote)
	{
		$this->privateNote = clone $this->getPrivateNote()->setString($language, $privateNote);
	}

	/**
	 * @return LanguageString
	 */
	public function getPublicNote()
	{
		return $this->publicNote;
	}

	/**
	 * @param Language $language
	 * @param string $publicNote
	 */
	public function setPublicNote(Language $language, $publicNote)
	{
		$this->publicNote = clone $this->getPublicNote()->setString($language, $publicNote);
	}

	/**
	 * @return bool
	 */
	public function isSeatsWithoutNumbers()
	{
		return $this->seatsWithoutNumbers;
	}

	/**
	 * @param bool $seatsWithoutNumbers
	 */
	public function setSeatsWithoutNumbers($seatsWithoutNumbers)
	{
		$this->seatsWithoutNumbers = $seatsWithoutNumbers;
	}

	/**
	 * @return bool
	 */
	public function isPayOnlineDisabled(): bool
	{
		return $this->payOnlineDisabled;
	}

	/**
	 * @param bool $payOnlineDisabled
	 */
	public function setPayOnlineDisabled(bool $payOnlineDisabled): void
	{
		$this->payOnlineDisabled = $payOnlineDisabled;
	}

}
