<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 24.04.17
 * Time: 13:42
 */

namespace AppBundle\Entity;


use AppBundle\VO\Days;
use AppBundle\VO\LanguageString;
use AppBundle\VO\TemporaryPercentTariffRange;
use AppBundle\VO\TemporaryTariffRange;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TariffRepository")
 * @ORM\Table(name="tariffs")
 */
class Tariff
{
	const TYPE_PRICES = "prices";
	const TYPE_PERCENT = "percent";

	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="tariff_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var Carrier
	 * @ORM\ManyToOne(targetEntity="Carrier")
	 * @ORM\JoinColumn(name="carrier_id", referencedColumnName="carrier_id", nullable=false)
	 */
	private $carrier;

	/**
	 * @var Line
	 * @ORM\ManyToOne(targetEntity="Line", inversedBy="tariffs")
	 * @ORM\JoinColumn(name="line_id", referencedColumnName="line_id", nullable=true)
	 */
	private $line;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="name", type="object", nullable=false)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(name="old_price_table", type="string", nullable=true)
	 */
	private $oldPriceTable;

	/**
	 * @var string
	 * @ORM\Column(name="type", type="string", length=16, nullable=false)
	 */
	private $type = self::TYPE_PRICES;

	/**
	 * @var integer
	 * @ORM\Column(name="percent", type="smallint", nullable=true)
	 */
	private $percent;

	/**
	 * @var integer|null
	 * @ORM\Column(name="back_way_by_percent_discount", type="integer", nullable=true)
	 */
	private $backWayByPercentDiscount = null;

	/**
	 * @var Tariff|null
	 * @ORM\ManyToOne(targetEntity="Tariff", cascade={"persist"})
	 * @ORM\JoinColumn(name="percent_from_tariff_id", referencedColumnName="tariff_id", nullable=true)
	 */
	private $percentFromTariff;

	/**
	 * @var Tariff|null
	 * @ORM\ManyToOne(targetEntity="Tariff", inversedBy="temporaryTariffs")
	 * @ORM\JoinColumn(name="temporary_from_tariff_id", referencedColumnName="tariff_id", nullable=true)
	 */
	private $temporaryFromTariff;

	/**
	 * @var Tariff[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tariff", mappedBy="temporaryFromTariff")
	 */
	private $temporaryTariffs;

	/**
	 * @var Fare[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Fare", mappedBy="tariff", cascade={"persist"})
	 */
	private $fares;

	/**
	 * @var string
	 * @ORM\Column(name="currency", type="string", length=3, nullable=true)
	 */
	private $currency;

	/**
	 * @var Tariff
	 * @ORM\OneToOne(targetEntity="Tariff")
	 * @ORM\JoinColumn(name="other_currency_tariff_id", referencedColumnName="tariff_id", nullable=true)
	 */
	private $otherCurrencyTariff;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	/**
	 * @var null
	 * @ORM\Column(name="allow_days", type="integer", nullable=true)
	 */
	private $allowDays = null;

	/**
	 * @var LanguageString
	 * @ORM\Column(name="conditions", type="object", nullable=false)
	 */
	private $conditions;

	/**
	 * @var Days
	 * @ORM\Column(name="exclude_days", type="object", nullable=false)
	 */
	private $excludeDays;

	/**
	 * @var ArrayCollection|TemporaryTariffRange[]
	 * @ORM\Column(name="temporary_tariff_ranges", type="object", nullable=false)
	 */
	private $temporaryTariffRanges;

	/**
	 * @var ArrayCollection|TemporaryPercentTariffRange[]
	 * @ORM\Column(name="temporary_percent_tariff_ranges", type="object", nullable=false)
	 */
	private $temporaryPercentTariffRanges;

	public function __construct()
	{
		$this->name = new LanguageString;
		$this->conditions = new LanguageString;
		$this->excludeDays = new Days;
		$this->temporaryTariffs = new ArrayCollection;
		$this->temporaryTariffRanges = new ArrayCollection;
		$this->temporaryPercentTariffRanges = new ArrayCollection;
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
	 * @return string
	 */
	public function getOldPriceTable()
	{
		return $this->oldPriceTable;
	}

	/**
	 * @param string $oldPriceTable
	 */
	public function setOldPriceTable($oldPriceTable)
	{
		$this->oldPriceTable = $oldPriceTable;
	}

	/**
	 * @return Fare[]|ArrayCollection
	 */
	public function getFares()
	{
		return $this->fares;
	}

	/**
	 * @param Fare[]|ArrayCollection $fares
	 */
	public function setFares($fares)
	{
		$this->fares = $fares;
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
	 * @param LineStation $fromLineStation
	 * @param LineStation $toLineStation
	 * @return Fare|null
	 */
	public function getFareForLineStations(LineStation $fromLineStation, LineStation $toLineStation)
	{
		$fares = $this->getFares()->filter(function (Fare $fare) use ($fromLineStation, $toLineStation) {
			return $fare->getFromLineStation() === $fromLineStation and $fare->getToLineStation() === $toLineStation and !$fare->isDeleted();
		});

		return !$fares->isEmpty() ? $fares->first() : null;
	}

	/**
	 * @param LineStation $lineStation
	 * @return Fare[]|ArrayCollection
	 */
	public function getFaresForLineStation(LineStation $lineStation)
	{
		return $this->getFares()->filter(function (Fare $fare) use ($lineStation) {
			return $fare->getFromLineStation() === $lineStation or $fare->getToLineStation() === $lineStation and !$fare->isDeleted();
		});
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return Tariff|null
	 */
	public function getPercentFromTariff()
	{
		return $this->percentFromTariff;
	}

	/**
	 * @param Tariff|null $percentFromTariff
	 */
	public function setPercentFromTariff($percentFromTariff)
	{
		$this->percentFromTariff = $percentFromTariff;
	}

	/**
	 * @return int
	 */
	public function getPercent()
	{
		return $this->percent;
	}

	/**
	 * @param \DateTime $dateDay
	 * @param string $direction
	 * @return int
	 */
	public function getPercentForDay(\DateTime $dateDay, $direction)
	{
		if ($this->getTemporaryPercentTariffRanges() === null or $this->getTemporaryPercentTariffRanges()->isEmpty()) return $this->getPercent();

		$buff = $this->getTemporaryPercentTariffRanges()->filter(function (TemporaryPercentTariffRange $temporaryPercentTariffRange) use ($dateDay, $direction) {
			return $temporaryPercentTariffRange->getTariffRange()->isBetween($dateDay) and $temporaryPercentTariffRange->isForDirection($direction);
		});

		/** @var TemporaryPercentTariffRange|null $temporaryPercentTariffRange */
		$temporaryPercentTariffRange = !$buff->isEmpty() ? $buff->first() : null;

		return $temporaryPercentTariffRange ? $temporaryPercentTariffRange->getPercent() : $this->getPercent();
	}

	/**
	 * @param int $percent
	 */
	public function setPercent($percent)
	{
		$this->percent = $percent;
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
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}

	/**
	 * @return Tariff
	 */
	public function getOtherCurrencyTariff()
	{
		return $this->otherCurrencyTariff;
	}

	/**
	 * @return Tariff
	 */
	public function getOtherCurrencyTariffWithTemporary()
	{
		return $this->temporaryFromTariff
			? $this->temporaryFromTariff->getOtherCurrencyTariff()
			: $this->getOtherCurrencyTariff();
	}

	/**
	 * @param Tariff $otherCurrencyTariff
	 */
	public function setOtherCurrencyTariff($otherCurrencyTariff)
	{
		$this->otherCurrencyTariff = $otherCurrencyTariff;
	}

	/**
	 * @return null
	 */
	public function getAllowDays()
	{
		return $this->allowDays;
	}

	/**
	 * @param null $allowDays
	 */
	public function setAllowDays($allowDays)
	{
		$this->allowDays = $allowDays;
	}

	public function __clone()
	{
		$this->setId(null);
	}

	/**
	 * @return LanguageString
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * @param Language $language
	 * @param string $conditions
	 */
	public function setConditions(Language $language, $conditions)
	{
		$this->conditions = clone $this->getConditions()->setString($language, $conditions);
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
	public function setExcludeDays(Days $excludeDays)
	{
		$this->excludeDays = $excludeDays;
	}

	/**
	 * @return Tariff|null
	 */
	public function getTemporaryFromTariff()
	{
		return $this->temporaryFromTariff;
	}

	/**
	 * @param Tariff|null $temporaryFromTariff
	 */
	public function setTemporaryFromTariff($temporaryFromTariff)
	{
		$this->temporaryFromTariff = $temporaryFromTariff;
	}

	/**
	 * @return Tariff[]|ArrayCollection
	 */
	public function getTemporaryTariffs()
	{
		return $this->temporaryTariffs;
	}

	/**
	 * @param Tariff[]|ArrayCollection $temporaryTariffs
	 */
	public function setTemporaryTariffs($temporaryTariffs)
	{
		$this->temporaryTariffs = $temporaryTariffs;
	}

	/**
	 * @param \DateTime $dateDay
	 * @param string $direction
	 * @return bool
	 */
	public function isTemporaryTariffForDate(\DateTime $dateDay, $direction)
	{
		if ($this->getTemporaryTariffRanges() === null or $this->getTemporaryTariffRanges()->isEmpty()) return false;

		return $this->getTemporaryTariffRanges()->exists(function ($i, TemporaryTariffRange $temporaryTariffRange) use ($dateDay, $direction) {
			return $temporaryTariffRange->getTariffRange()->isBetween($dateDay) and $temporaryTariffRange->isForDirection($direction);
		});
	}

	/**
	 * @param \DateTime $dateDay
	 * @param string $direction
	 * @return Tariff|null
	 */
	public function getTemporaryTariffForDate(\DateTime $dateDay, $direction)
	{
		$temporaryTariff = $this->getTemporaryTariffs()->filter(function (Tariff $temporaryTariff) use ($dateDay, $direction) {
			return $temporaryTariff->isTemporaryTariffForDate($dateDay, $direction) and !$temporaryTariff->isDeleted();
		});

		return $temporaryTariff->isEmpty() ? null : $temporaryTariff->first();
	}

	public function __toString()
	{
		return $this->getName()->getAllLanguagesStringsInString();
	}

	/**
	 * @return array|int[]
	 */
	public function getTemporaryExcludeTariffs(\DateTime $dateDay, $direction)
	{
		$ranges = $this->getTemporaryTariffRanges()->filter(function (TemporaryTariffRange $temporaryTariffRange) use ($dateDay, $direction) {
			return $temporaryTariffRange->getTariffRange()->isBetween($dateDay) and $temporaryTariffRange->isForDirection($direction);
		});

		if ($ranges->isEmpty()) return [];
		/** @var TemporaryTariffRange $temporaryTariffRange */
		$temporaryTariffRange = $ranges->first();

		return array_map(function ($i) { return (int) $i; }, $temporaryTariffRange->getExcludeTariffs());
	}

	/**
	 * @return TemporaryTariffRange[]|ArrayCollection
	 */
	public function getTemporaryTariffRanges()
	{
		return $this->temporaryTariffRanges;
	}

	/**
	 * @param TemporaryTariffRange[]|ArrayCollection $temporaryTariffRanges
	 */
	public function setTemporaryTariffRanges($temporaryTariffRanges)
	{
		$this->temporaryTariffRanges = $temporaryTariffRanges;
	}

	/**
	 * @return TemporaryPercentTariffRange[]|ArrayCollection
	 */
	public function getTemporaryPercentTariffRanges()
	{
		return $this->temporaryPercentTariffRanges;
	}

	/**
	 * @param TemporaryPercentTariffRange[]|ArrayCollection $temporaryPercentTariffRanges
	 */
	public function setTemporaryPercentTariffRanges($temporaryPercentTariffRanges)
	{
		$this->temporaryPercentTariffRanges = $temporaryPercentTariffRanges;
	}

	/**
	 * @return int|null
	 */
	public function getBackWayByPercentDiscount(): ?int
	{
		return $this->backWayByPercentDiscount;
	}

	/**
	 * @param int|null $backWayByPercentDiscount
	 */
	public function setBackWayByPercentDiscount(?int $backWayByPercentDiscount): void
	{
		$this->backWayByPercentDiscount = $backWayByPercentDiscount;
	}

	public function getNumberForOrder(): int
	{
		if ($this->getPercentFromTariff()) {
			$number = $this->getPercentFromTariff()->getId() * 10000 + 1000;
		} elseif ($this->getTemporaryFromTariff()) {
			$number = $this->getTemporaryFromTariff()->getId() * 10000 + 1000;
		} else {
			$number = $this->getId() * 10000;
		}

		$number -= 100;
		if ($this->getPercent()) $number += $this->getPercent();

		return $number;
	}
}
