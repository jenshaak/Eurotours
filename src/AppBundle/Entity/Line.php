<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 12:09
 */

namespace AppBundle\Entity;
use AppBundle\Service\TariffService;
use AppBundle\VO\LanguageString;
use AppBundle\VO\LinePeriod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LineRepository")
 * @ORM\Table(name="line")
 */
class Line
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="line_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var string
	 * @ORM\Column(name="code", type="string", length=16, nullable=true)
	 */
	private $code;

	/**
	 * @var LineStation[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="LineStation", mappedBy="line", orphanRemoval=true, cascade={"persist"})
	 * @ORM\OrderBy({ "weight" = "ASC" })
	 */
	private $lineStations;

	/**
	 * @var Carrier
	 * @ORM\ManyToOne(targetEntity="Carrier", inversedBy="lines")
	 * @ORM\JoinColumn(name="carrier_id", referencedColumnName="carrier_id", nullable=false)
	 */
	private $carrier;

	/**
	 * @var Schedule[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Schedule", mappedBy="line", orphanRemoval=true, cascade={"persist"})
	 * @ORM\OrderBy({ "weight" = "ASC" })
	 */
	private $schedules;

	/**
	 * @var Tariff[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Tariff", mappedBy="line", cascade={"persist"})
	 */
	private $tariffs;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	/**
	 * @var boolean
	 * @ORM\Column(name="hidden", type="boolean", nullable=false)
	 */
	private $hidden = false;

	/**
	 * @var boolean
	 * @ORM\Column(name="open_possible", type="boolean", nullable=false)
	 */
	private $openPossible = false;

	/**
	 * @var boolean
	 * @ORM\Column(name="pay_online", type="boolean", nullable=false)
	 */
	private $payOnline = false;

	/**
	 * @var null
	 * @ORM\Column(name="allow_order_days", type="integer", nullable=true)
	 * @deprecated
	 */
	private $allowOrderDays = null;

	/**
	 * @var null
	 * @ORM\Column(name="allow_order_hours_there", type="integer", nullable=true)
	 */
	private $allowOrderHoursThere = null;

	/**
	 * @var null
	 * @ORM\Column(name="allow_order_hours_back", type="integer", nullable=true)
	 */
	private $allowOrderHoursBack = null;

	/**
	 * @var bool
	 * @ORM\Column(name="admin_free_possible", type="boolean", nullable=false)
	 */
	private $adminFreePossible = false;

	/**
	 * @var bool
	 * @ORM\Column(name="back_way_only_as_return_pay_possible", type="boolean", nullable=false)
	 */
	private $backWayOnlyAsReturnPayPossible = false;

	/**
	 * @var bool
	 * @deprecated
	 * @ORM\Column(name="seats_without_numbers", type="boolean", nullable=false)
	 */
	private $seatsWithoutNumbers = false;

	/**
	 * @var bool
	 * @ORM\Column(name="seats_without_numbers_there", type="boolean", nullable=false)
	 */
	private $seatsWithoutNumbersThere = false;

	/**
	 * @var bool
	 * @ORM\Column(name="seats_without_numbers_back", type="boolean", nullable=false)
	 */
	private $seatsWithoutNumbersBack = false;

	/**
	 * @var bool
	 * @ORM\Column(name="internal_ticket", type="boolean", nullable=false)
	 */
	private $generateInternalTicket = false;

	/**
	 * @var ArrayCollection|User[]
	 * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User")
	 * @ORM\JoinTable(
	 *     name="lines_sellers",
	 *     joinColumns={@ORM\JoinColumn(name="line_id", referencedColumnName="line_id")},
	 *     inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="user_id")}
	 * )
	 */
	private $sellers;

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
	 * @var LinePeriod|null
	 * @ORM\Column(name="line_period", type="object", nullable=false)
	 */
	private $linePeriod = null;

	public function __construct()
	{
		$this->lineStations = new ArrayCollection;
		$this->schedules = new ArrayCollection;
		$this->tariffs = new ArrayCollection;
		$this->sellers = new ArrayCollection;
		$this->privateNote = new LanguageString;
		$this->publicNote = new LanguageString;
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
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @return LineStation[]|ArrayCollection
	 */
	public function getLineStations($direction = null)
	{
		if ($direction) {
			return $this->lineStations->filter(function (LineStation $lineStation) use ($direction) {
				return $lineStation->getDirection() == $direction;
			});
		} else {
			return $this->lineStations;
		}
	}

	/**
	 * @return LineStation[]|ArrayCollection
	 */
	public function getActiveLineStations($direction = null)
	{
		if ($direction) {
			return $this->lineStations->filter(function (LineStation $lineStation) use ($direction) {
				return !$lineStation->isDeleted() and $lineStation->getDirection() == $direction;
			});
		} else {
			return $this->lineStations->filter(function (LineStation $lineStation) use ($direction) {
				return !$lineStation->isDeleted();
			});
		}
	}

	/**
	 * @param LineStation[]|ArrayCollection $lineStations
	 */
	public function setLineStations($lineStations)
	{
		$this->lineStations = $lineStations;
	}

	/**
	 * @param LineStation $lineStation
	 */
	public function addLineStation(LineStation $lineStation)
	{
		$this->lineStations->add($lineStation);
		$lineStation->setLine($this);
	}

	/**
	 * @return Schedule[]|ArrayCollection
	 */
	public function getSchedules($direction = null)
	{
		if ($direction) {
			return $this->schedules->filter(function (Schedule $schedule) use ($direction) {
				return $schedule->getDirection() == $direction;
			});
		} else {
			return $this->schedules;
		}
	}

	/**
	 * @return Schedule[]|ArrayCollection
	 */
	public function getActiveSchedules($direction = null)
	{
		if ($direction) {
			return $this->schedules->filter(function (Schedule $schedule) use ($direction) {
				return !$schedule->isDeleted() and $schedule->getDirection() == $direction;
			});
		} else {
			return $this->schedules->filter(function (Schedule $schedule) {
				return !$schedule->isDeleted();
			});
		}
	}

	/**
	 * @param Schedule[]|ArrayCollection $schedules
	 */
	public function setSchedules($schedules)
	{
		$this->schedules = $schedules;
	}

	/**
	 * @return Carrier
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}

	/**
	 * @param Carrier $carrier
	 */
	public function setCarrier($carrier)
	{
		$this->carrier = $carrier;
	}

	/**
	 * @return Tariff[]|ArrayCollection
	 */
	public function getTariffs()
	{
		return $this->tariffs->filter(function (Tariff $tariff) { return !$tariff->isDeleted(); });
	}

	/**
	 * @return Tariff[]|ArrayCollection
	 */
	public function getPercentTariffs()
	{
		return $this->getTariffs()->filter(function (Tariff $tariff) {
			return $tariff->getType() === Tariff::TYPE_PERCENT;
		});
	}

	/**
	 * @return LineStation|null
	 */
	public function getFirstLineStation()
	{
		$firstLineStation = $this->getActiveLineStations(LineStation::DIRECTION_THERE)->first();
		return $firstLineStation ? $firstLineStation : null;
	}

	/**
	 * @return LineStation|null
	 */
	public function getLastLineStation()
	{
		$lastLineStation = $this->getActiveLineStations(LineStation::DIRECTION_THERE)->last();
		return $lastLineStation ? $lastLineStation : null;
	}

	/**
	 * @param int $id
	 * @return LineStation
	 */
	public function getLineStationById($id)
	{
		return $this->getActiveLineStations()->filter(function (LineStation $lineStation) use ($id) {
			return $lineStation->getId() == $id;
		})->first();
	}

	/**
	 * @param int $id
	 * @return Schedule
	 */
	public function getScheduleById($id)
	{
		return $this->getSchedules()->filter(function (Schedule $schedule) use ($id) {
			return $schedule->getId() == $id;
		})->first();
	}

	/**
	 * @param Station $station
	 * @param string $direction
	 * @return LineStation
	 */
	public function getLineStationByStation(Station $station, $direction)
	{
		return $this->getActiveLineStations($direction)->filter(function (LineStation $lineStation) use ($station) {
			return $lineStation->getStation() === $station;
		})->first();
	}

	/**
	 * @param LineStation $lineStation
	 * @return LineStation
	 */
	public function getOppositeLineStation(LineStation $lineStation)
	{
		$station = $lineStation->getStation();
		if ($lineStation->getDirection() == LineStation::DIRECTION_THERE) {
			$direction = LineStation::DIRECTION_BACK;
		} else {
			$direction = LineStation::DIRECTION_THERE;
		}

		return $this->getActiveLineStations($direction)->filter(function (LineStation $lineStation) use ($station) {
			return $lineStation->getStation() === $station;
		})->first();
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
	 * @return boolean
	 */
	public function isHidden()
	{
		return $this->hidden;
	}

	/**
	 * @param boolean $hidden
	 */
	public function setHidden($hidden)
	{
		$this->hidden = $hidden;
	}

	/**
	 * @param Station $station
	 * @return bool
	 */
	public function containsStation(Station $station)
	{
		return $this->getActiveLineStations()->exists(function ($k, LineStation $lineStation) use ($station) {
			return $lineStation->getStation() == $station;
		});
	}

	/**
	 * @param City $city
	 * @return bool
	 */
	public function containsCity(City $city)
	{
		return $this->getActiveLineStations()->exists(function ($k, LineStation $lineStation) use ($city) {
			return $lineStation->getCity() == $city;
		});
	}

	/**
	 * @return boolean
	 */
	public function isOpenPossible()
	{
		return $this->openPossible;
	}

	/**
	 * @param boolean $openPossible
	 */
	public function setOpenPossible($openPossible)
	{
		$this->openPossible = $openPossible;
	}

	/**
	 * @return boolean
	 */
	public function isPayOnline()
	{
		return $this->payOnline;
	}

	/**
	 * @param boolean $payOnline
	 */
	public function setPayOnline($payOnline)
	{
		$this->payOnline = $payOnline;
	}

	/**
	 * @param string $direction
	 * @return null
	 */
	public function getAllowOrderDays($direction)
	{
		if ($direction === LineStation::DIRECTION_THERE) {
			return $this->allowOrderHoursThere;
		} else {
			return $this->allowOrderHoursBack;
		}
	}

	/**
	 * @return null
	 */
	public function getAllowOrderHoursThere()
	{
		return $this->allowOrderHoursThere;
	}

	/**
	 * @param null $allowOrderHoursThere
	 */
	public function setAllowOrderHoursThere($allowOrderHoursThere)
	{
		$this->allowOrderHoursThere = $allowOrderHoursThere;
	}

	/**
	 * @return null
	 */
	public function getAllowOrderHoursBack()
	{
		return $this->allowOrderHoursBack;
	}

	/**
	 * @param null $allowOrderHoursBack
	 */
	public function setAllowOrderHoursBack($allowOrderHoursBack)
	{
		$this->allowOrderHoursBack = $allowOrderHoursBack;
	}

	function __clone()
	{
		$this->setId(null);
	}

	/**
	 * @param Tariff[]|ArrayCollection $tariffs
	 */
	public function setTariffs($tariffs)
	{
		$this->tariffs = $tariffs;
	}

	/**
	 * @return bool
	 */
	public function isAdminFreePossible()
	{
		return $this->adminFreePossible;
	}

	/**
	 * @param bool $adminFreePossible
	 */
	public function setAdminFreePossible($adminFreePossible)
	{
		$this->adminFreePossible = $adminFreePossible;
	}

	/**
	 * @return bool
	 */
	public function isBackWayOnlyAsReturnPayPossible()
	{
		return $this->backWayOnlyAsReturnPayPossible;
	}

	/**
	 * @param bool $backWayOnlyAsReturnPayPossible
	 */
	public function setBackWayOnlyAsReturnPayPossible($backWayOnlyAsReturnPayPossible)
	{
		$this->backWayOnlyAsReturnPayPossible = $backWayOnlyAsReturnPayPossible;
	}

	/**
	 * @return bool
	 */
	public function isSeatsWithoutNumbers($direction)
	{
		if ($direction === LineStation::DIRECTION_THERE) {
			return $this->isSeatsWithoutNumbersThere();
		} else {
			return $this->isSeatsWithoutNumbersBack();
		}
	}

	/**
	 * @return bool
	 */
	public function isGenerateInternalTicket()
	{
		return $this->generateInternalTicket;
	}

	/**
	 * @param bool $generateInternalTicket
	 */
	public function setGenerateInternalTicket($generateInternalTicket)
	{
		$this->generateInternalTicket = $generateInternalTicket;
	}

	/**
	 * @return User[]|ArrayCollection
	 */
	public function getSellers()
	{
		return $this->sellers;
	}

	/**
	 * @param User[]|ArrayCollection $sellers
	 */
	public function setSellers($sellers)
	{
		$this->sellers = $sellers;
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
	public function isSeatsWithoutNumbersThere()
	{
		return $this->seatsWithoutNumbersThere;
	}

	/**
	 * @param bool $seatsWithoutNumbersThere
	 */
	public function setSeatsWithoutNumbersThere($seatsWithoutNumbersThere)
	{
		$this->seatsWithoutNumbersThere = $seatsWithoutNumbersThere;
	}

	/**
	 * @return bool
	 */
	public function isSeatsWithoutNumbersBack()
	{
		return $this->seatsWithoutNumbersBack;
	}

	/**
	 * @param bool $seatsWithoutNumbersBack
	 */
	public function setSeatsWithoutNumbersBack($seatsWithoutNumbersBack)
	{
		$this->seatsWithoutNumbersBack = $seatsWithoutNumbersBack;
	}

	/**
	 * @return LinePeriod|null
	 */
	public function getLinePeriod()
	{
		return $this->linePeriod;
	}

	/**
	 * @param LinePeriod|null $linePeriod
	 */
	public function setLinePeriod($linePeriod)
	{
		$this->linePeriod = $linePeriod;
	}

}
