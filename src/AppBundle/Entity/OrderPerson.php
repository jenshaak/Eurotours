<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 19.06.17
 * Time: 17:38
 */

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderPersonRepository")
 * @ORM\Table(name="orders_persons")
 */
class OrderPerson
{
	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="order_person_id", type="string", length=255)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var Order
	 * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderPersons")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
	 */
	private $order;

	/**
	 * @var RouteTariff
	 * @ORM\ManyToOne(targetEntity="RouteTariff")
	 * @ORM\JoinColumn(name="route_tariff_there_id", referencedColumnName="route_tariff_id", nullable=false)
	 */
	private $routeTariffThere;

	/**
	 * @var RouteTariff
	 * @ORM\ManyToOne(targetEntity="RouteTariff")
	 * @ORM\JoinColumn(name="route_tariff_back_id", referencedColumnName="route_tariff_id", nullable=true)
	 */
	private $routeTariffBack;

	/**
	 * @var string
	 * @ORM\Column(name="name", type="string", length=255, nullable=true)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(name="phone", type="string", length=255, nullable=true)
	 */
	private $phone;

	/**
	 * @var ExternalTicket[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="ExternalTicket", mappedBy="orderPerson")
	 */
	private $externalTickets;

	/**
	 * @var InternalTicket[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="InternalTicket", mappedBy="orderPerson")
	 */
	private $internalTickets;

	/**
	 * @var \DateTime|null
	 * @ORM\Column(name="date_birth", type="date", nullable=true)
	 */
	private $dateBirth;

	/** @ORM\Column(name="document_number", type="string", length=255, nullable=true) */
	private ?string $documentNumber = null;

	public function __construct()
	{
		$this->externalTickets = new ArrayCollection;
		$this->internalTickets = new ArrayCollection;
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
	 * @return Order
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @param Order $order
	 */
	public function setOrder($order)
	{
		$this->order = $order;
	}

	/**
	 * @return RouteTariff
	 */
	public function getRouteTariffThere()
	{
		return $this->routeTariffThere;
	}

	/**
	 * @param RouteTariff $routeTariffThere
	 */
	public function setRouteTariffThere($routeTariffThere)
	{
		$this->routeTariffThere = $routeTariffThere;
	}

	/**
	 * @return RouteTariff
	 */
	public function getRouteTariffBack()
	{
		return $this->routeTariffBack;
	}

	/**
	 * @param RouteTariff $routeTariffBack
	 */
	public function setRouteTariffBack($routeTariffBack)
	{
		$this->routeTariffBack = $routeTariffBack;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param string $phone
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
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
	 * @return ExternalTicket
	 */
	public function getExternalTicketForRoute(Route $route)
	{
		return $this->getExternalTickets()->filter(function (ExternalTicket $externalTicket) use ($route) {
			return $route === $externalTicket->getRoute();
		})->first();
	}

	/**
	 * @return InternalTicket
	 */
	public function getInternalTicketForRoute(Route $route)
	{
		return $this->getInternalTickets()->filter(function (InternalTicket $internalTicket) use ($route) {
			return $route === $internalTicket->getRoute();
		})->first();
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
	 * @return \DateTime|null
	 */
	public function getDateBirth()
	{
		return $this->dateBirth;
	}

	/**
	 * @param \DateTime|null $dateBirth
	 */
	public function setDateBirth($dateBirth)
	{
		$this->dateBirth = $dateBirth;
	}

	public function getDocumentNumber(): ?string
	{
		return $this->documentNumber;
	}

	public function setDocumentNumber(?string $documentNumber): void
	{
		$this->documentNumber = $documentNumber;
	}

	/**
	 * @return \AppBundle\VO\PriceCurrency|float
	 */
	public function getSumPrice()
	{
		$price = $this->getRouteTariffThere()->getPriceCurrency();
		if ($this->getRouteTariffBack() !== null and $this->getRouteTariffBack()->getPriceCurrency()) {
			$price->addPrice($this->getRouteTariffBack()->getPriceCurrency()->getPrice());
		}
		return $price;
	}

	/**
	 * @return \AppBundle\VO\PriceCurrency|float
	 */
	public function getSumPriceIncludeSurcharge()
	{
		$price = $this->getRouteTariffThere()->getPriceCurrencyIncludeSurcharge();
		if ($this->getRouteTariffBack() !== null and $this->getRouteTariffBack()->getPriceCurrencyIncludeSurcharge()) {
			$price->addPrice($this->getRouteTariffBack()->getPriceCurrencyIncludeSurcharge()->getPrice());
		}
		return $price;
	}

	/**
	 * @return Route
	 */
	public function getRouteThere()
	{
		return $this->getRouteTariffThere()->getRoute();
	}

	/**
	 * @return Route|null
	 */
	public function getRouteBack()
	{
		return $this->getRouteTariffBack()->getRoute();
	}

	/**
	 * @return InternalTicket|null
	 */
	public function getInternalTicketThere()
	{
		return $this->getInternalTicketForRoute($this->getRouteThere());
	}

	/**
	 * @return InternalTicket|null
	 */
	public function getInternalTicketBack()
	{
		return $this->getInternalTicketForRoute($this->getRouteBack());
	}

}
