<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 12.05.17
 * Time: 16:21
 */

namespace AppBundle\Entity;


use AppBundle\VO\LanguageString;
use AppBundle\VO\PriceCurrency;
use AppBundle\VO\ScheduleTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RouteRepository")
 * @ORM\Table(name="routes")
 */
class Route
{
	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="route_id", type="string", length=64)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var Search
	 * @ORM\ManyToOne(targetEntity="Search")
	 * @ORM\JoinColumn(name="search_id", referencedColumnName="search_id", nullable=false)
	 */
	private $search;

	/**
	 * @var Schedule
	 * @ORM\ManyToOne(targetEntity="Schedule")
	 * @ORM\JoinColumn(name="schedule_id", referencedColumnName="schedule_id", nullable=true)
	 */
	private $schedule;

	/**
	 * @var SearchExternal
	 * @ORM\ManyToOne(targetEntity="SearchExternal")
	 * @ORM\JoinColumn(name="search_external_id", referencedColumnName="search_external_id", nullable=true)
	 */
	private $searchExternal;

	/**
	 * @var string
	 * @ORM\Column(name="external_ident", type="string", length=1024, nullable=true)
	 */
	private $externalIdent;

	/**
	 * @var object|null
	 * @ORM\Column(name="external_object", type="object", nullable=false)
	 */
	private $externalObject;

	/**
	 * @var City
	 * @ORM\ManyToOne(targetEntity="City")
	 * @ORM\JoinColumn(name="from_city_id", referencedColumnName="city_id", nullable=true)
	 */
	private $fromCity;

	/**
	 * @var City
	 * @ORM\ManyToOne(targetEntity="City")
	 * @ORM\JoinColumn(name="to_city_id", referencedColumnName="city_id", nullable=true)
	 */
	private $toCity;

	/**
	 * @var Station
	 * @ORM\ManyToOne(targetEntity="Station")
	 * @ORM\JoinColumn(name="from_station_id", referencedColumnName="station_id", nullable=true)
	 */
	private $fromStation;

	/**
	 * @var Station
	 * @ORM\ManyToOne(targetEntity="Station")
	 * @ORM\JoinColumn(name="to_station_id", referencedColumnName="station_id", nullable=true)
	 */
	private $toStation;

	/**
	 * @var ExternalStation
	 * @ORM\ManyToOne(targetEntity="ExternalStation")
	 * @ORM\JoinColumn(name="from_external_station_id", referencedColumnName="external_station_id", nullable=true)
	 */
	private $fromExternalStation;

	/**
	 * @var ExternalStation
	 * @ORM\ManyToOne(targetEntity="ExternalStation")
	 * @ORM\JoinColumn(name="to_external_station_id", referencedColumnName="external_station_id", nullable=true)
	 */
	private $toExternalStation;

	/**
	 * @var ExternalCity
	 * @ORM\ManyToOne(targetEntity="ExternalCity")
	 * @ORM\JoinColumn(name="from_external_city_id", referencedColumnName="external_city_id", nullable=true)
	 */
	private $fromExternalCity;

	/**
	 * @var ExternalCity
	 * @ORM\ManyToOne(targetEntity="ExternalCity")
	 * @ORM\JoinColumn(name="to_external_city_id", referencedColumnName="external_city_id", nullable=true)
	 */
	private $toExternalCity;

	/**
	 * @var ScheduleTime
	 * @ORM\Column(name="schedule_time_departure", type="object", nullable=true)
	 */
	private $scheduleTimeDeparture;

	/**
	 * @var ScheduleTime
	 * @ORM\Column(name="schedule_time_arrival", type="object", nullable=true)
	 */
	private $scheduleTimeArrival;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_departure", type="datetime", nullable=true)
	 */
	private $datetimeDeparture;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_arrival", type="datetime", nullable=true)
	 */
	private $datetimeArrival;

	/**
	 * @var integer
	 * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private $price;

	/**
	 * @ORM\Column(name="max_price", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private ?float $maxPrice = null;

	/**
	 * @var bool
	 * @ORM\Column(name="variable_price", type="boolean", nullable=false)
	 */
	private $variablePrice = false;

	/**
	 * @var Line
	 * @ORM\ManyToOne(targetEntity="Line")
	 * @ORM\JoinColumn(name="line_id", referencedColumnName="line_id", nullable=true)
	 */
	private $line;

	/**
	 * @var Carrier
	 * @ORM\ManyToOne(targetEntity="Carrier")
	 * @ORM\JoinColumn(name="carrier_id", referencedColumnName="carrier_id", nullable=true)
	 */
	private $carrier;

	/**
	 * @var string|null
	 * @ORM\Column(name="carrier_title", type="string", length=90, nullable=true)
	 */
	private $carrierTitle = null;

	/**
	 * @var string
	 * @ORM\Column(name="direction", type="string", length=32, nullable=false)
	 */
	private $direction = LineStation::DIRECTION_THERE;

	/**
	 * @var RouteTariff[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="RouteTariff", mappedBy="route", cascade={"persist"})
	 */
	private $routeTariffs;

	/**
	 * @var string
	 * @ORM\Column(name="currency", type="string", length=3, nullable=false)
	 */
	private $currency;

	/**
	 * @var ExternalTicket[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="ExternalTicket", mappedBy="route")
	 */
	private $externalTickets;

	/**
	 * @var bool
	 * @ORM\Column(name="is_open", type="boolean", nullable=false)
	 */
	private $isOpen = false;

	/**
	 * @var null|bool
	 * @ORM\Column(name="is_direct", type="boolean", nullable=true)
	 */
	private $isDirect = null;

	/**
	 * @var Book
	 * @ORM\OneToOne(targetEntity="Book")
	 * @ORM\JoinColumn(name="book_id", referencedColumnName="book_id", nullable=true)
	 */
	private $book;

	/**
	 * @var Book[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="AppBundle\Entity\Book", mappedBy="route")
	 */
	private $books;

	/**
	 * @var ScheduleLineStation|null
	 * @ORM\ManyToOne(targetEntity="ScheduleLineStation")
	 * @ORM\JoinColumn(name="from_schedule_list_station_id", referencedColumnName="schedule_line_station_id", nullable=true)
	 */
	private $fromScheduleLineStation;

	/**
	 * @var ScheduleLineStation|null
	 * @ORM\ManyToOne(targetEntity="ScheduleLineStation")
	 * @ORM\JoinColumn(name="to_schedule_list_station_id", referencedColumnName="schedule_line_station_id", nullable=true)
	 */
	private $toScheduleLineStation;

	/**
	 * @var OrderPersonRouteTariff[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="OrderPersonRouteTariff", mappedBy="route")
	 */
	private $orderPersonRouteTariffs;

	public function __construct()
	{
		$this->scheduleTimeArrival = new ScheduleTime;
		$this->scheduleTimeDeparture = new ScheduleTime;
		$this->routeTariffs = new ArrayCollection;
		$this->externalTickets = new ArrayCollection;
		$this->orderPersonRouteTariffs = new ArrayCollection;
		$this->externalObject = null;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * @return City
	 */
	public function getFromCity()
	{
		return $this->fromCity;
	}

	/**
	 * @param City $fromCity
	 */
	public function setFromCity($fromCity)
	{
		$this->fromCity = $fromCity;
	}

	/**
	 * @return City
	 */
	public function getToCity()
	{
		return $this->toCity;
	}

	/**
	 * @param City $toCity
	 */
	public function setToCity($toCity)
	{
		$this->toCity = $toCity;
	}

	/**
	 * @return Station
	 */
	public function getFromStation()
	{
		return $this->fromStation;
	}

	/**
	 * @param Station $fromStation
	 */
	public function setFromStation($fromStation)
	{
		$this->fromStation = $fromStation;
	}

	/**
	 * @return Station
	 */
	public function getToStation()
	{
		return $this->toStation;
	}

	/**
	 * @param Station $toStation
	 */
	public function setToStation($toStation)
	{
		$this->toStation = $toStation;
	}

	/**
	 * @return ExternalStation
	 */
	public function getFromExternalStation()
	{
		return $this->fromExternalStation;
	}

	/**
	 * @param ExternalStation $fromExternalStation
	 */
	public function setFromExternalStation($fromExternalStation)
	{
		$this->fromExternalStation = $fromExternalStation;
	}

	/**
	 * @return ExternalStation
	 */
	public function getToExternalStation()
	{
		return $this->toExternalStation;
	}

	/**
	 * @param ExternalStation $toExternalStation
	 */
	public function setToExternalStation($toExternalStation)
	{
		$this->toExternalStation = $toExternalStation;
	}

	/**
	 * @return ScheduleTime
	 */
	public function getScheduleTimeDeparture()
	{
		return $this->scheduleTimeDeparture;
	}

	/**
	 * @param ScheduleTime $scheduleTimeDeparture
	 */
	public function setScheduleTimeDeparture($scheduleTimeDeparture)
	{
		$this->scheduleTimeDeparture = $scheduleTimeDeparture;
	}

	/**
	 * @return ScheduleTime
	 */
	public function getScheduleTimeArrival()
	{
		return $this->scheduleTimeArrival;
	}

	/**
	 * @param ScheduleTime $scheduleTimeArrival
	 */
	public function setScheduleTimeArrival($scheduleTimeArrival)
	{
		$this->scheduleTimeArrival = $scheduleTimeArrival;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeDeparture()
	{
		return $this->datetimeDeparture;
	}

	/**
	 * @param \DateTime $datetimeDeparture
	 */
	public function setDatetimeDeparture($datetimeDeparture)
	{
		$this->datetimeDeparture = $datetimeDeparture;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeArrival()
	{
		return $this->datetimeArrival;
	}

	/**
	 * @param \DateTime $datetimeArrival
	 */
	public function setDatetimeArrival($datetimeArrival)
	{
		$this->datetimeArrival = $datetimeArrival;
	}

	/**
	 * @return int
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param int $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	public function getMaxPrice(): ?float
	{
		return $this->maxPrice;
	}

	public function setMaxPrice(?float $maxPrice): void
	{
		$this->maxPrice = $maxPrice;
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
	 * @return Search
	 */
	public function getSearch()
	{
		return $this->search;
	}

	/**
	 * @param Search $search
	 */
	public function setSearch($search)
	{
		$this->search = $search;
	}

	/**
	 * @return SearchExternal
	 */
	public function getSearchExternal()
	{
		return $this->searchExternal;
	}

	/**
	 * @param SearchExternal $searchExternal
	 */
	public function setSearchExternal($searchExternal)
	{
		$this->searchExternal = $searchExternal;
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
	 * @return boolean
	 */
	public function isVariablePrice()
	{
		return $this->variablePrice;
	}

	/**
	 * @param boolean $variablePrice
	 */
	public function setVariablePrice($variablePrice)
	{
		$this->variablePrice = $variablePrice;
	}

	/**
	 * @return ExternalCity
	 */
	public function getFromExternalCity()
	{
		return $this->fromExternalCity;
	}

	/**
	 * @param ExternalCity $fromExternalCity
	 */
	public function setFromExternalCity($fromExternalCity)
	{
		$this->fromExternalCity = $fromExternalCity;
	}

	/**
	 * @return ExternalCity
	 */
	public function getToExternalCity()
	{
		return $this->toExternalCity;
	}

	/**
	 * @param ExternalCity $toExternalCity
	 */
	public function setToExternalCity($toExternalCity)
	{
		$this->toExternalCity = $toExternalCity;
	}

	/**
	 * @return string
	 */
	public function getExternalIdent()
	{
		return $this->externalIdent;
	}

	/**
	 * @param string $externalIdent
	 */
	public function setExternalIdent($externalIdent)
	{
		$this->externalIdent = $externalIdent;
	}

	/**
	 * @param RouteTariff[]|ArrayCollection $routeTariffs
	 */
	public function setRouteTariffs($routeTariffs)
	{
		$this->routeTariffs = $routeTariffs;
	}

	/**
	 * @return RouteTariff[]|ArrayCollection
	 */
	public function getRouteTariffs()
	{
		return $this->routeTariffs;
	}

	/**
	 * @param Tariff $tariff
	 * @return RouteTariff|null
	 */
	public function getRouteTariffForTariff(Tariff $tariff)
	{
		$routeTariff = $this->routeTariffs->filter(function (RouteTariff $rt) use ($tariff) {
			return $rt->getTariff() === $tariff;
		});

		return $routeTariff->isEmpty() ? null : $routeTariff->first();
	}

	/**
	 * @return ArrayCollection|Tariff[]
	 */
	public function getTariffs()
	{
		return $this->routeTariffs->map(function (RouteTariff $routeTariff) {
			return $routeTariff->getTariff();
		});
	}

	/**
	 * @return LanguageString
	 */
	public function getFromStationName()
	{
		if ($this->getFromExternalStation()) {
			return $this->getFromExternalStation()->getName();
		} elseif ($this->getFromStation()) {
			return $this->getFromStation()->getName();
		} else {
			return new LanguageString();
		}
	}

	/**
	 * @return LanguageString
	 */
	public function getToStationName()
	{
		if ($this->getToExternalStation()) {
			return $this->getToExternalStation()->getName();
		} elseif ($this->getToStation()) {
			return $this->getToStation()->getName();
		} else {
			return new LanguageString();
		}
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
	 * @return PriceCurrency
	 */
	public function getPriceCurrencyIncludeSurcharge()
	{
		return PriceCurrency::create($this->getPriceIncludeSurcharge(), $this->getCurrency());
	}

	public function getMaxPriceCurrencyIncludeSurcharge(): PriceCurrency
	{
		return PriceCurrency::create($this->getMaxPriceIncludeSurcharge(), $this->getCurrency());
	}

	/**
	 * @return PriceCurrency
	 */
	public function getPriceCurrency()
	{
		return PriceCurrency::create($this->getPrice(), $this->getCurrency());
	}

	public function getMaxPriceCurrency()
	{
		return PriceCurrency::create($this->getMaxPrice(), $this->getCurrency());
	}


	/**
	 * @return ExternalTicket[]|ArrayCollection
	 */
	public function getExternalTickets()
	{
		return $this->externalTickets;
	}

	/**
	 * @param ExternalTicket[]|ArrayCollection $externalTickets
	 */
	public function setExternalTickets($externalTickets)
	{
		$this->externalTickets = $externalTickets;
	}

	/**
	 * @return boolean
	 */
	public function isIsOpen()
	{
		return $this->isOpen;
	}

	/**
	 * @param boolean $isOpen
	 */
	public function setIsOpen($isOpen)
	{
		$this->isOpen = $isOpen;
	}

	/**
	 * @return bool|null
	 */
	public function getIsDirect()
	{
		return $this->isDirect;
	}

	/**
	 * @param bool|null $isDirect
	 */
	public function setIsDirect($isDirect)
	{
		$this->isDirect = $isDirect;
	}

	/**
	 * @return LanguageString|null
	 */
	public function getSimpleFromCityName()
	{
		if ($this->getFromCity() instanceof City) {
			return $this->getFromCity()->getName();
		} elseif ($this->getFromExternalCity() instanceof ExternalCity) {
			return $this->getFromExternalCity()->getName();
		}

		return null;
	}

	/**
	 * @return LanguageString|null
	 */
	public function getSimpleToCityName()
	{
		if ($this->getToCity() instanceof City) {
			return $this->getToCity()->getName();
		} elseif ($this->getToExternalCity() instanceof ExternalCity) {
			return $this->getToExternalCity()->getName();
		}

		return null;
	}

	/**
	 * @return OrderPersonRouteTariff[]|ArrayCollection
	 */
	public function getOrderPersonRouteTariffs()
	{
		return $this->orderPersonRouteTariffs;
	}

	/**
	 * @param OrderPersonRouteTariff[]|ArrayCollection $orderPersonRouteTariffs
	 */
	public function setOrderPersonRouteTariffs($orderPersonRouteTariffs)
	{
		$this->orderPersonRouteTariffs = $orderPersonRouteTariffs;
	}

	/**
	 * @return Book
	 */
	public function getBook()
	{
		return $this->book;
	}

	/**
	 * @param Book $book
	 */
	public function setBook(Book $book)
	{
		$this->book = $book;
	}

	/**
	 * @param Route $route
	 * @return bool
	 */
	public function isCollidingWithRoute(Route $route)
	{
		if ($route->getLine() !== $this->getLine()) return false;
		if ($route->getSchedule() !== $this->getSchedule()) return false;

		$firstFrom = $route->getFromScheduleLineStation()->getLineStation()->getWeight();
		$firstTo = $route->getToScheduleLineStation()->getLineStation()->getWeight();

		$secondFrom = $this->getFromScheduleLineStation()->getLineStation()->getWeight();
		$secondTo = $this->getToScheduleLineStation()->getLineStation()->getWeight();

		if ($firstFrom <= $secondFrom and $firstTo > $secondFrom) {
			return true;
		}

		if ($secondFrom <= $firstFrom and $secondTo > $firstFrom) {
			return true;
		}

		return false;
	}

	/**
	 * @return ScheduleLineStation|null
	 */
	public function getFromScheduleLineStation()
	{
		return $this->fromScheduleLineStation;
	}

	/**
	 * @param ScheduleLineStation|null $fromScheduleLineStation
	 */
	public function setFromScheduleLineStation($fromScheduleLineStation)
	{
		$this->fromScheduleLineStation = $fromScheduleLineStation;
	}

	/**
	 * @return ScheduleLineStation|null
	 */
	public function getToScheduleLineStation()
	{
		return $this->toScheduleLineStation;
	}

	/**
	 * @param ScheduleLineStation|null $toScheduleLineStation
	 */
	public function setToScheduleLineStation($toScheduleLineStation)
	{
		$this->toScheduleLineStation = $toScheduleLineStation;
	}

	/**
	 * @return object|null
	 */
	public function getExternalObject()
	{
		return $this->externalObject;
	}

	/**
	 * @param object|null $externalObject
	 */
	public function setExternalObject($externalObject)
	{
		$this->externalObject = $externalObject;
	}

	/**
	 * @return float
	 */
	public function getPriceIncludeSurcharge()
	{
		$surcharge = $this->getCarrier()->getSurcharge();
		return ceil($this->getPrice() * (1+($surcharge/100)));
	}

	/**
	 * @return float
	 */
	public function getMaxPriceIncludeSurcharge()
	{
		$surcharge = $this->getCarrier()->getSurcharge();
		return ceil($this->getMaxPrice() * (1+($surcharge/100)));
	}

	/**
	 * @return int
	 */
	public function modifyDays()
	{
		$from = new \DateTime($this->getDatetimeDeparture()->format("Y-m-d 12:00:00"));
		$to = new \DateTime($this->getDatetimeArrival()->format("Y-m-d 12:00:00"));
		$diff = $from->diff($to);
		return $diff->days;
	}

	/**
	 * @return Book[]|ArrayCollection
	 */
	public function getBooks()
	{
		return $this->books;
	}

	/**
	 * @return string|null
	 */
	public function getCarrierTitle(): ?string
	{
		return $this->carrierTitle;
	}

	/**
	 * @param string|null $carrierTitle
	 */
	public function setCarrierTitle(?string $carrierTitle): void
	{
		$this->carrierTitle = $carrierTitle;
	}

	public function getOrderInSearch(): int
	{
		$order = (int) $this->getDatetimeDeparture()->format("YmdHi") . 000;

		if ($this->getCarrier()->getCode() == "Inf") $order += 50;
		if (!$this->getLine()) $order += 10;

		return $order;
	}

}
