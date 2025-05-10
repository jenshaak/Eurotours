<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.09.17
 * Time: 20:13
 */

namespace AppBundle\Entity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ExternalTicketRepository")
 * @ORM\Table(name="external_tickets")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *         "student_agency" = "ExternalTicketStudentAgency",
 *         "east_express" = "ExternalTicketEastExpress",
 *         "eurolines" = "ExternalTicketEurolines",
 *         "nikolo" = "ExternalTicketNikolo",
 *         "regabus" = "ExternalTicketRegabus",
 *         "blabla" = "ExternalTicketBlabla",
 *         "ecolines" = "ExternalTicketEcolines",
 *         "trans_tempo" = "ExternalTicketTransTempo",
 *         "bus_system" = "ExternalTicketBusSystem"
 *     }
 * )
 */
abstract class ExternalTicket implements TicketInterface
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="external_ticket_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	protected $datetimeCreated;

	/**
	 * @var Order
	 * @ORM\ManyToOne(targetEntity="Order", inversedBy="externalTickets")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
	 */
	protected $order;

	/**
	 * @var Route
	 * @ORM\ManyToOne(targetEntity="Route", inversedBy="externalTickets")
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="route_id", nullable=false)
	 */
	protected $route;

	/**
	 * @var RouteTariff
	 * @ORM\ManyToOne(targetEntity="RouteTariff", inversedBy="externalTickets")
	 * @ORM\JoinColumn(name="route_tariff_id", referencedColumnName="route_tariff_id", nullable=false)
	 */
	protected $routeTariff;

	/**
	 * @var OrderPerson
	 * @ORM\ManyToOne(targetEntity="OrderPerson", inversedBy="externalTickets")
	 * @ORM\JoinColumn(name="order_person_id", referencedColumnName="order_person_id", nullable=false)
	 */
	protected $orderPerson;

	/**
	 * @var string
	 * @ORM\Column(name="file", type="string", length=512, nullable=true)
	 */
	protected $file;

	/**
	 * @var string
	 * @ORM\Column(name="content_type", type="string", length=64, nullable=true)
	 */
	protected $contentType;

	/**
	 * @var boolean
	 * @ORM\Column(name="cancelled", type="boolean", nullable=false)
	 */
	private $cancelled = false;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_cancelled", type="datetime", nullable=true)
	 */
	private $datetimeCancelled;

	/**
	 * @var boolean
	 * @ORM\Column(name="billed", type="boolean", nullable=false)
	 */
	private $billed = false;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_billed", type="datetime", nullable=true)
	 */
	private $datetimeBilled;

	/**
	 * @var string|null
	 * @ORM\Column(name="note_billed", type="text", nullable=true)
	 */
	private $noteBilled;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="date_month_billed", type="date", nullable=true)
	 */
	private $dateMonthBilled;

	/**
	 * @var InternalTicket|null
	 * @ORM\OneToOne(targetEntity="AppBundle\Entity\InternalTicket")
	 * @ORM\JoinColumn(name="alternative_internal_ticket_id", referencedColumnName="internal_ticket_id", nullable=true)
	 */
	private $alternativeInternalTicket = null;

	/** @ORM\Column(name="paid_by_bank", type="boolean", nullable=false) */
	private bool $paidByBank = false;

	/** @ORM\Column(name="datetime_paid_by_bank", type="datetime", nullable=true) */
	private ?DateTime $datetimePaidByBank = null;

	/** @ORM\Column(name="note_paid_by_bank", type="text", nullable=true) */
	private ?string $notePaidByBank = null;

	public function __construct()
	{
		$this->datetimeCreated = new \DateTime;
	}

	/**
	 * @param ExternalTicket $externalTicket
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicket
	 */
	public static function createFromChildren(ExternalTicket $externalTicket, Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		$externalTicket->setRoute($route);
		$externalTicket->setRouteTariff($routeTariff);
		$externalTicket->setOrderPerson($orderPerson);
		$externalTicket->setOrder($orderPerson->getOrder());
		return $externalTicket;
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
	 * @return \DateTime
	 */
	public function getDatetimeCreated()
	{
		return $this->datetimeCreated;
	}

	/**
	 * @param \DateTime $datetimeCreated
	 */
	public function setDatetimeCreated($datetimeCreated)
	{
		$this->datetimeCreated = $datetimeCreated;
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
		$route->getExternalTickets()->add($this);
	}

	/**
	 * @return RouteTariff
	 */
	public function getRouteTariff()
	{
		return $this->routeTariff;
	}

	/**
	 * @param RouteTariff $routeTariff
	 */
	public function setRouteTariff($routeTariff)
	{
		$this->routeTariff = $routeTariff;
		$routeTariff->getExternalTickets()->add($this);
	}

	/**
	 * @return OrderPerson
	 */
	public function getOrderPerson()
	{
		return $this->orderPerson;
	}

	/**
	 * @param OrderPerson $orderPerson
	 */
	public function setOrderPerson($orderPerson)
	{
		$this->orderPerson = $orderPerson;
		$orderPerson->getExternalTickets()->add($this);
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
		$order->getExternalTickets()->add($this);
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @param string $file
	 */
	public function setFile($file)
	{
		$this->file = $file;
	}

	/**
	 * @return string
	 */
	public function getContentType()
	{
		return $this->contentType;
	}

	/**
	 * @param string $contentType
	 */
	public function setContentType($contentType)
	{
		$this->contentType = $contentType;
	}

	/**
	 * @return bool
	 */
	public function isCancelled()
	{
		return $this->cancelled;
	}

	/**
	 * @param bool $cancelled
	 */
	public function setCancelled($cancelled)
	{
		$this->cancelled = $cancelled;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeCancelled()
	{
		return $this->datetimeCancelled;
	}

	/**
	 * @param \DateTime $datetimeCancelled
	 */
	public function setDatetimeCancelled($datetimeCancelled)
	{
		$this->datetimeCancelled = $datetimeCancelled;
	}

	/**
	 * @return bool
	 */
	public function isBilled()
	{
		return $this->billed;
	}

	/**
	 * @param bool $billed
	 */
	public function setBilled($billed)
	{
		$this->billed = $billed;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeBilled()
	{
		return $this->datetimeBilled;
	}

	/**
	 * @param \DateTime $datetimeBilled
	 */
	public function setDatetimeBilled($datetimeBilled)
	{
		$this->datetimeBilled = $datetimeBilled;
	}

	/**
	 * @return string|null
	 */
	public function getNoteBilled()
	{
		return $this->noteBilled;
	}

	/**
	 * @param string|null $noteBilled
	 */
	public function setNoteBilled($noteBilled)
	{
		$this->noteBilled = $noteBilled;
	}

	/**
	 * @return Carrier
	 */
	public function getCarrier()
	{
		return $this->getRoute()->getCarrier();
	}

	/**
	 * @return string|null
	 */
	public function getCarrierTitle()
	{
		return $this->getRoute()->getCarrierTitle();
	}

	/**
	 * @return \DateTime
	 */
	public function getDateMonthBilled()
	{
		return $this->dateMonthBilled;
	}

	/**
	 * @param \DateTime $dateMonthBilled
	 */
	public function setDateMonthBilled($dateMonthBilled)
	{
		$this->dateMonthBilled = $dateMonthBilled;
	}

	/**
	 * @return InternalTicket|null
	 */
	public function getAlternativeInternalTicket(): ?InternalTicket
	{
		return $this->alternativeInternalTicket;
	}

	/**
	 * @param InternalTicket|null $alternativeInternalTicket
	 */
	public function setAlternativeInternalTicket(?InternalTicket $alternativeInternalTicket): void
	{
		$this->alternativeInternalTicket = $alternativeInternalTicket;
	}

	public function isPaidByBank(): bool
	{
		return $this->paidByBank;
	}

	public function setPaidByBank(bool $paidByBank): void
	{
		$this->paidByBank = $paidByBank;
	}

	public function getDatetimePaidByBank(): ?DateTime
	{
		return $this->datetimePaidByBank;
	}

	public function setDatetimePaidByBank(?DateTime $datetimePaidByBank): void
	{
		$this->datetimePaidByBank = $datetimePaidByBank;
	}

	public function getNotePaidByBank(): ?string
	{
		return $this->notePaidByBank;
	}

	public function setNotePaidByBank(?string $notePaidByBank): void
	{
		$this->notePaidByBank = $notePaidByBank;
	}
}
