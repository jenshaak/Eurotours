<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.05.17
 * Time: 17:03
 */

namespace AppBundle\Entity;

use AppBundle\VO\PriceCurrency;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RouteTariffRepository")
 * @ORM\Table(name="routes_tariffs")
 */
class RouteTariff
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="route_tariff_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var Route
	 * @ORM\ManyToOne(targetEntity="Route", inversedBy="routeTariffs")
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="route_id", nullable=true)
	 */
	private $route;

	/**
	 * @var Tariff
	 * @ORM\ManyToOne(targetEntity="Tariff")
	 * @ORM\JoinColumn(name="tariff_id", referencedColumnName="tariff_id", nullable=true)
	 */
	private $tariff;

	/**
	 * @var ExternalTariff
	 * @ORM\ManyToOne(targetEntity="ExternalTariff")
	 * @ORM\JoinColumn(name="external_tariff_id", referencedColumnName="external_tariff_id", nullable=true)
	 */
	private $externalTariff;

	/**
	 * @var string
	 * @ORM\Column(name="external_booking_ident", type="text", nullable=true)
	 */
	private $externalBookingIdent;

	/**
	 * @var int
	 * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private $price;

	/** @ORM\Column(name="max_price", type="decimal", precision=10, scale=2, nullable=true) */
	private ?float $maxPrice = null;

	/**
	 * @var int
	 * @ORM\Column(name="original_price", type="integer", nullable=true)
	 */
	private $originalPrice;

	/**
	 * @var int
	 * @ORM\Column(name="person_limit", type="integer", nullable=true)
	 */
	private $personLimit;

	/**
	 * @var Fare
	 * @ORM\ManyToOne(targetEntity="Fare")
	 * @ORM\JoinColumn(name="fare_id", referencedColumnName="fare_id", nullable=true)
	 */
	private $fare;

	/**
	 * @var string
	 * @ORM\Column(name="currency", type="string", length=3, nullable=true)
	 */
	private $currency;

	/**
	 * @var ExternalTicket[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="ExternalTicket", mappedBy="routeTariff")
	 */
	private $externalTickets;

	/**
	 * @var InternalTicket[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="InternalTicket", mappedBy="routeTariff")
	 */
	private $internalTickets;

	/**
	 * @param Route $route
	 * @param Tariff $tariff
	 * @param Fare $fare
	 * @return RouteTariff
	 */
	public static function createForTariff(Route $route, Tariff $tariff, Fare $fare = null)
	{
		$routeTariff = new RouteTariff;
		$routeTariff->setRoute($route);
		$routeTariff->setFare($fare);
		$route->getRouteTariffs()->add($routeTariff);
		$routeTariff->setTariff($tariff);
		return $routeTariff;
	}

	/**
	 * @param Route $route
	 * @param ExternalTariff $externalTariff
	 * @param string $currency
	 * @return RouteTariff
	 */
	public static function createForExternalTariff(Route $route, ExternalTariff $externalTariff, $currency)
	{
		$routeTariff = new RouteTariff;
		$routeTariff->setRoute($route);
		$routeTariff->setCurrency($currency);
		$route->getRouteTariffs()->add($routeTariff);
		$routeTariff->setExternalTariff($externalTariff);
		return $routeTariff;
	}

	public function __construct()
	{
		$this->externalTickets = new ArrayCollection;
		$this->internalTickets = new ArrayCollection;
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
	 * @return Route
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * @param Route $route
	 */
	public function setRoute($route)
	{
		$this->route = $route;
	}

	/**
	 * @return Tariff
	 */
	public function getTariff()
	{
		return $this->tariff;
	}

	/**
	 * @param Tariff $tariff
	 */
	public function setTariff($tariff)
	{
		$this->tariff = $tariff;
	}

	/**
	 * @return ExternalTariff
	 */
	public function getExternalTariff()
	{
		return $this->externalTariff;
	}

	/**
	 * @param ExternalTariff $externalTariff
	 */
	public function setExternalTariff($externalTariff)
	{
		$this->externalTariff = $externalTariff;
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param float $price
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
	 * @return int
	 */
	public function getOriginalPrice()
	{
		return $this->originalPrice;
	}

	/**
	 * @param int $originalPrice
	 */
	public function setOriginalPrice($originalPrice)
	{
		$this->originalPrice = $originalPrice;
	}

	/**
	 * @return int
	 */
	public function getPersonLimit()
	{
		return $this->personLimit;
	}

	/**
	 * @param int $personLimit
	 */
	public function setPersonLimit($personLimit)
	{
		$this->personLimit = $personLimit;
	}

	/**
	 * @param RouteTariff $routeTariff
	 * @return bool
	 */
	public function isTheSameTariff(RouteTariff $routeTariff)
	{
		return $routeTariff->getExternalTariff() == $this->getExternalTariff()
		and $routeTariff->getTariff() == $this->getTariff();
	}

	/**
	 * @return Fare
	 */
	public function getFare()
	{
		return $this->fare;
	}

	/**
	 * @param Fare $fare
	 */
	public function setFare($fare)
	{
		$this->fare = $fare;
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
	public function getPriceCurrency()
	{
		return PriceCurrency::create($this->getPrice(), $this->getCurrency());
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
	 * @return string
	 */
	public function getExternalBookingIdent()
	{
		return $this->externalBookingIdent;
	}

	/**
	 * @param string $externalBookingIdent
	 */
	public function setExternalBookingIdent($externalBookingIdent)
	{
		$this->externalBookingIdent = $externalBookingIdent;
	}

	/**
	 * @return InternalTicket[]|ArrayCollection
	 */
	public function getInternalTickets()
	{
		return $this->internalTickets;
	}

	/**
	 * @param InternalTicket[]|ArrayCollection $internalTickets
	 */
	public function setInternalTickets($internalTickets)
	{
		$this->internalTickets = $internalTickets;
	}

	/**
	 * @return ArrayCollection|TicketInterface[]
	 */
	public function getTickets()
	{
		$tickets = new ArrayCollection;
		$this->getInternalTickets()->map(function (InternalTicket $ticket) use ($tickets) {
			$tickets->add($ticket);
		});
		$this->getExternalTickets()->map(function (ExternalTicket $ticket) use ($tickets) {
			$tickets->add($ticket);
		});
		return $tickets;
	}

	/**
	 * @return float
	 */
	public function getPriceIncludeSurcharge()
	{
		$surcharge = $this->getRoute()->getCarrier()->getSurcharge();
		return ceil($this->getPrice() * (1+($surcharge/100)));
	}

	public function getMaxPriceIncludeSurcharge()
	{
		$surcharge = $this->getRoute()->getCarrier()->getSurcharge();
		return ceil($this->getMaxPrice() * (1+($surcharge/100)));
	}
}
