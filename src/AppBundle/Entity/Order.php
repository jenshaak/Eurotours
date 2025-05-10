<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.17
 * Time: 10:39
 */

namespace AppBundle\Entity;


use AppBundle\VO\PriceCurrency;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderRepository")
 * @ORM\Table(name="orders")
 * @ORM\HasLifecycleCallbacks()
 */
class Order
{
	const STATUS_DRAFT = "draft";
	const STATUS_RESERVATION = "reservation";
	const STATUS_ORDER = "order";
	const STATUS_WAITING_PAY = "waitingPay";
	const STATUS_EXPIRED = "expired";
	const STATUS_CANCELLED = "cancelled";
	const STATUS_PAID = "paid";
	const STATUS_SELECT_SEATS = "selectSeats";
	const STATUS_NO_SEATS = "noSeats";

	const ORDER_TYPE_ORDER = "order";
	const ORDER_TYPE_RESERVATION = "reservation";

	const PAID_BY_CASH = "cash";
	const PAID_BY_WIRE_TRANSFER = "wireTransfer";
	const PAID_BY_CARD = "card";

	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="order_id", type="string", length=255)
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
	 * @var Route
	 * @ORM\ManyToOne(targetEntity="Route")
	 * @ORM\JoinColumn(name="there_route_id", referencedColumnName="route_id", nullable=false)
	 */
	private $routeThere;

	/**
	 * @var Route|null
	 * @ORM\ManyToOne(targetEntity="Route")
	 * @ORM\JoinColumn(name="back_route_id", referencedColumnName="route_id", nullable=true)
	 */
	private $routeBack;

	/**
	 * @var string
	 * @ORM\Column(name="name", type="string", length=255, nullable=true)
	 */
	private $name;

	/**
	 * @var string
	 * @ORM\Column(name="email", type="string", length=255, nullable=true)
	 */
	private $email;

	/**
	 * @var string
	 * @ORM\Column(name="phone", type="string", length=24, nullable=true)
	 */
	private $phone;

	/**
	 * @var string
	 * @ORM\Column(name="note", type="text", nullable=true)
	 */
	private $note;

	/**
	 * @var integer
	 * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private $price;

	/** @ORM\Column(name="max_price", type="decimal", precision=10, scale=2, nullable=true) */
	private ?float $maxPrice = null;

	/**
	 * @ORM\Column(name="seller_fee", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private ?float $sellerFee = null;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	private $datetimeCreated;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_ordered", type="datetime", nullable=true)
	 */
	private $datetimeOrdered;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_sent_payment", type="datetime", nullable=true)
	 */
	private $datetimeSentPayment;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_paid", type="datetime", nullable=true)
	 */
	private $datetimePaid;

	/**
	 * @var OrderPerson[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="OrderPerson", mappedBy="order", cascade={"persist"})
	 */
	private $orderPersons;

	/**
	 * @var string
	 * @ORM\Column(name="status", type="string", nullable=false)
	 */
	private $status = self::STATUS_DRAFT;

	/**
	 * @var string
	 * @ORM\Column(name="order_type", type="string", nullable=true)
	 */
	private $orderType;

	/**
	 * @var string
	 * @ORM\Column(name="paid_by", type="string", length=32, nullable=true)
	 */
	private $paidBy;

	/**
	 * @var Payment
	 * @ORM\OneToOne(targetEntity="Payment", inversedBy="order")
	 * @ORM\JoinColumn(name="payment_id", referencedColumnName="payment_id", nullable=true)
	 */
	private $payment;

	/**
	 * @var string
	 * @ORM\Column(name="currency", type="string", length=3, nullable=false)
	 */
	private $currency;

	/**
	 * @var string
	 * @ORM\Column(name="ticket_currency", type="string", length=3, nullable=false)
	 */
	private $ticketCurrency;

	/**
	 * @var bool
	 * @ORM\Column(name="paid", type="boolean", nullable=false)
	 */
	private $paid = false;

	/**
	 * @var bool
	 * @ORM\Column(name="admin_no_seat", type="boolean", nullable=false)
	 */
	private $adminNoSeat = false;

	/**
	 * @var bool
	 * @ORM\Column(name="admin_free", type="boolean", nullable=false)
	 */
	private $adminFree = false;

	/**
	 * @var bool
	 * @ORM\Column(name="admin_change_price", type="boolean", nullable=false)
	 */
	private $adminChangePrice = false;

	/**
	 * @var ExternalTicket[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="ExternalTicket", mappedBy="order")
	 */
	private $externalTickets;

	/**
	 * @var InternalTicket[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="InternalTicket", mappedBy="order")
	 */
	private $internalTickets;

	/**
	 * @var Language
	 * @ORM\ManyToOne(targetEntity="Language")
	 * @ORM\JoinColumn(name="language_id", referencedColumnName="language_id", nullable=false)
	 */
	private $language;

	/**
	 * @var boolean
	 * @ORM\Column(name="deleted", type="boolean", nullable=false)
	 */
	private $deleted = false;

	/**
	 * @var OrderPersonRouteTariff[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="OrderPersonRouteTariff", mappedBy="order")
	 */
	private $orderPersonRouteTariffs;

	/**
	 * @var string
	 * @ORM\Column(name="ident", type="integer", nullable=true)
	 */
	private $ident;

	/**
	 * @var \DateTime|null
	 * @ORM\Column(name="datetime_reservation_day", type="date", nullable=true)
	 */
	private $dateReservationDay;

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
	 * @var Book[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="Book", mappedBy="order")
	 */
	private $books;

	/**
	 * @var string
	 * @ORM\Column(name="fulltext_string", type="text", nullable=false)
	 */
	private $fulltextString = "";

	/**
	 * @var boolean
	 * @ORM\Column(name="internal", type="boolean", nullable=false)
	 */
	private $internal = false;

	/**
	 * @var User|null
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
	 * @ORM\JoinColumn(name="seller_user_id", referencedColumnName="user_id", nullable=true)
	 */
	private $userSeller;

	/**
	 * @var User|null
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
	 * @ORM\JoinColumn(name="cashier_user_id", referencedColumnName="user_id", nullable=true)
	 */
	private $userCashier;

	/**
	 * @var bool
	 * @ORM\Column(name="seller_paid", type="boolean", nullable=true)
	 */
	private $sellerPaid = false;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_seller_paid", type="datetime", nullable=true)
	 */
	private $datetimeSellerPaid;

	/**
	 * @var string|null
	 * @ORM\Column(name="note_seller_paid", type="text", nullable=true)
	 */
	private $noteSellerPaid;

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
	 * @var User|null
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
	 * @ORM\JoinColumn(name="billed_user_id", referencedColumnName="user_id", nullable=true)
	 */
	private $userBilled;

	/**
	 * @var User|null
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
	 * @ORM\JoinColumn(name="cancelled_user_id", referencedColumnName="user_id", nullable=true)
	 */
	private $userCancelled;

	/** @ORM\Column(name="paid_by_bank", type="boolean", nullable=false) */
	private bool $paidByBank = false;

	/** @ORM\Column(name="datetime_paid_by_bank", type="datetime", nullable=true) */
	private ?DateTime $datetimePaidByBank = null;

	/** @ORM\Column(name="note_paid_by_bank", type="text", nullable=true) */
	private ?string $notePaidByBank = null;

	public function __construct()
	{
		$this->datetimeCreated = new \DateTime;
		$this->orderPersons = new ArrayCollection;
		$this->externalTickets = new ArrayCollection;
		$this->internalTickets = new ArrayCollection;
		$this->scheduleOwnSeatReservations = new ArrayCollection;
		$this->orderPersonRouteTariffs = new ArrayCollection;
		$this->books = new ArrayCollection;
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
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
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
	 * @return string
	 */
	public function getNote()
	{
		return $this->note;
	}

	/**
	 * @param string $note
	 */
	public function setNote($note)
	{
		$this->note = $note;
	}

	/**
	 * @return Route
	 */
	public function getRouteThere()
	{
		return $this->routeThere;
	}

	/**
	 * @param Route $routeThere
	 */
	public function setRouteThere($routeThere)
	{
		$this->routeThere = $routeThere;
	}

	/**
	 * @return Route|null
	 */
	public function getRouteBack()
	{
		return $this->routeBack;
	}

	/**
	 * @param Route $routeBack
	 */
	public function setRouteBack($routeBack)
	{
		$this->routeBack = $routeBack;
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

	public function getSellerFee(): ?float
	{
		return $this->sellerFee;
	}

	public function setSellerFee(?float $sellerFee): Order
	{
		$this->sellerFee = $sellerFee;
		return $this;
	}

	public function getSellerFeeCurrency(): PriceCurrency
	{
		return PriceCurrency::create($this->getSellerFee(), $this->getCurrency());
	}


	public function getPriceWithSellerFeeCurrency(): PriceCurrency
	{
		return PriceCurrency::create($this->price + ($this->sellerFee ?? 0), $this->getCurrency());
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
		if (preg_match("~eurotours~i", $name)) {
			$this->setInternal(true);
		}
	}

	/**
	 * @return OrderPerson
	 */
	public function createOrderPerson()
	{
		$orderPerson = new OrderPerson;
		$orderPerson->setOrder($this);
		$this->orderPersons->add($orderPerson);

		return $orderPerson;
	}

	/**
	 * @return OrderPerson[]|ArrayCollection
	 */
	public function getOrderPersons()
	{
		return $this->orderPersons;
	}

	/**
	 * @param OrderPerson[]|ArrayCollection $orderPersons
	 */
	public function setOrderPersons($orderPersons)
	{
		$this->orderPersons = $orderPersons;
	}

	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @return string
	 */
	public function getOrderType()
	{
		return $this->orderType;
	}

	/**
	 * @param string $orderType
	 */
	public function setOrderType($orderType)
	{
		$this->orderType = $orderType;
	}

	/**
	 * @param int $id
	 * @return OrderPerson|null
	 */
	public function getOrderPersonById($id)
	{
		$orderPerson = $this->getOrderPersons()->filter(function (OrderPerson $orderPerson) use ($id) {
			return $orderPerson->getId() == $id;
		});

		return !$orderPerson->isEmpty() ? $orderPerson->first() : null;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeOrdered()
	{
		return $this->datetimeOrdered;
	}

	/**
	 * @param \DateTime $datetimeOrdered
	 */
	public function setDatetimeOrdered($datetimeOrdered)
	{
		$this->datetimeOrdered = $datetimeOrdered;
	}

	/**
	 * @return Payment
	 */
	public function getPayment()
	{
		return $this->payment;
	}

	/**
	 * @param Payment $payment
	 */
	public function setPayment($payment)
	{
		$this->payment = $payment;
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
	 * @return boolean
	 */
	public function isPaid()
	{
		return $this->paid;
	}

	/**
	 * @param boolean $paid
	 */
	public function setPaid($paid)
	{
		$this->paid = $paid;
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
	 * @return Language
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param Language $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}

	/**
	 * @return bool
	 */
	public function isAdminNoSeat()
	{
		return $this->adminNoSeat;
	}

	/**
	 * @param bool $adminNoSeat
	 */
	public function setAdminNoSeat($adminNoSeat)
	{
		$this->adminNoSeat = $adminNoSeat;
	}

	/**
	 * @return bool
	 */
	public function isAdminFree()
	{
		return $this->adminFree;
	}

	/**
	 * @param bool $adminFree
	 */
	public function setAdminFree($adminFree)
	{
		$this->adminFree = $adminFree;
	}

	/**
	 * @return string
	 */
	public function getPaidBy()
	{
		return $this->paidBy;
	}

	/**
	 * @param string $paidBy
	 */
	public function setPaidBy($paidBy)
	{
		$this->paidBy = $paidBy;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeSentPayment()
	{
		return $this->datetimeSentPayment;
	}

	/**
	 * @param \DateTime $datetimeSentPayment
	 */
	public function setDatetimeSentPayment(\DateTime $datetimeSentPayment)
	{
		$this->datetimeSentPayment = $datetimeSentPayment;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimePaid()
	{
		return $this->datetimePaid;
	}

	/**
	 * @param \DateTime $datetimePaid
	 */
	public function setDatetimePaid(\DateTime $datetimePaid)
	{
		$this->datetimePaid = $datetimePaid;
	}

	/**
	 * @return bool
	 */
	public function isDeleted()
	{
		return $this->deleted;
	}

	/**
	 * @param bool $deleted
	 */
	public function setDeleted($deleted)
	{
		$this->deleted = $deleted;
	}

	/**
	 * @return bool
	 */
	public function isAdminChangePrice()
	{
		return $this->adminChangePrice;
	}

	/**
	 * @param bool $adminChangePrice
	 */
	public function setAdminChangePrice($adminChangePrice)
	{
		$this->adminChangePrice = $adminChangePrice;
	}

	/**
	 * @return InternalTicket[]|ArrayCollection
	 */
	public function getInternalTickets()
	{
		return $this->internalTickets;
	}

	/**
	 * @return InternalTicket[]|ArrayCollection
	 */
	public function getInternalTicketsWithoutDuplicities() {
		$buff = [];
		return $this->internalTickets->filter(function (InternalTicket $internalTicket) use (&$buff) {
			$key = $internalTicket->getCarrier()->getId() . ":" . $internalTicket->getOrderPerson()->getId();
			if (isset($buff[$key])) return false;
			$buff[$key] = true;
			return true;
		});
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
	 * @return ArrayCollection|TicketInterface[]|InternalTicket[]|ExternalTicket[]
	 */
	public function getTicketsWithoutDuplicities()
	{
		$tickets = new ArrayCollection;
		$this->getInternalTicketsWithoutDuplicities()->map(function (InternalTicket $ticket) use ($tickets) {
			if ($ticket->getAlternativeExternalTicket() instanceof ExternalTicket) return;
			$tickets->add($ticket);
		});
		$this->getExternalTickets()->map(function (ExternalTicket $ticket) use ($tickets) {
			$tickets->add($ticket);
		});
		return $tickets;
	}

	public function recountPrice()
	{
		$tickets = new ArrayCollection;
		$price = array_reduce($this->getBooks()->getValues(), function ($sum, BookInternal $book) use ($tickets) {
			if ($book->getInternalTicket() !== null) {
				$tickets->add($book->getInternalTicket());
			} else {
				$sum += $book->getPriceCurrencyIncludeSurcharge()->getPrice();
			}
			return $sum;
		}, 0);

		$price += array_reduce($tickets->getValues(), function ($sum, InternalTicket $ticket) {
			if (!$ticket->isSecondWayForInReturnTicketTheSameCarrier()) {
				$sum += $ticket->getPriceCurrencyIncludeSurcharge()->getPrice();
			}
			return $sum;
		}, 0);

		$this->setPrice($price);
	}

	/**
	 * @return OrderPersonRouteTariff[]|ArrayCollection
	 */
	public function getOrderPersonRouteTariffs()
	{
		return $this->orderPersonRouteTariffs;
	}

	/**
	 * @param OrderPerson $orderPerson
	 * @param RouteTariff $routeTariff
	 * @return OrderPersonRouteTariff|null
	 */
	public function getOrderPersonRouteTariff(OrderPerson $orderPerson, RouteTariff $routeTariff)
	{
		$return = $this->getOrderPersonRouteTariffs()->filter(function (OrderPersonRouteTariff $orderPersonRouteTariff) use ($orderPerson, $routeTariff) {
			return $orderPersonRouteTariff->getOrderPerson() === $orderPerson and $orderPersonRouteTariff->getRouteTariff() === $routeTariff;
		});

		return $return->isEmpty() ? null : $return->first();
	}

	/**
	 * @param OrderPersonRouteTariff[]|ArrayCollection $orderPersonRouteTariffs
	 */
	public function setOrderPersonRouteTariffs($orderPersonRouteTariffs)
	{
		$this->orderPersonRouteTariffs = $orderPersonRouteTariffs;
	}

	/**
	 * @return ArrayCollection|Route[]
	 */
	public function getRoutes()
	{
		$routers = new ArrayCollection;
		if ($this->getRouteThere()) {
			$routers->add($this->getRouteThere());
		}

		if ($this->getRouteBack()) {
			$routers->add($this->getRouteBack());
		}

		return $routers;
	}

	/**
	 * @return string
	 */
	public function getIdent()
	{
		return $this->ident;
	}

	/**
	 * @param string $ident
	 */
	public function setIdent($ident)
	{
		$this->ident = $ident;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDateReservationDay()
	{
		return $this->dateReservationDay;
	}

	/**
	 * @param \DateTime|null $dateReservationDay
	 */
	public function setDateReservationDay($dateReservationDay)
	{
		$this->dateReservationDay = $dateReservationDay;
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
	 * @return Book[]|ArrayCollection
	 */
	public function getBooks()
	{
		return $this->books;
	}

	/**
	 * @param Book[]|ArrayCollection $books
	 */
	public function setBooks($books)
	{
		$this->books = $books;
	}

	/**
	 * @return string
	 */
	public function getFulltextString()
	{
		return $this->fulltextString;
	}

	/**
	 * @param string $fulltextString
	 */
	public function setFulltextString($fulltextString)
	{
		$this->fulltextString = $fulltextString;
	}

	/**
	 * @ORM\PrePersist()
	 * @ORM\PreUpdate()
	 */
	public function generateFulltextString()
	{
		$this->setFulltextString(
			implode(" ", $this->getOrderPersons()->map(function (OrderPerson $orderPerson) {
				return $orderPerson->getName() . " " . $orderPerson->getPhone();
			})->toArray()) .
			" " . $this->getName() .
			" " . $this->getEmail() .
			" " . $this->getPhone() .
			implode(" ", $this->getRoutes()->map(function (Route $route) {
				return implode(" ", $route->getFromCity()->getName()->getAllLanguagesStrings()) .
					" " . implode(" ", $route->getToCity()->getName()->getAllLanguagesStrings());
			})->toArray())
		);
	}

	/**
	 * @return bool
	 */
	public function isInternal()
	{
		return $this->internal;
	}

	/**
	 * @param bool $internal
	 */
	public function setInternal($internal)
	{
		$this->internal = $internal;
	}

	/**
	 * @return User|null
	 */
	public function getUserSeller()
	{
		return $this->userSeller;
	}

	/**
	 * @param User|null $userSeller
	 */
	public function setUserSeller($userSeller)
	{
		$this->userSeller = $userSeller;
	}

	/**
	 * @return \DateTime
	 */
	public function getDatetimeSellerPaid()
	{
		return $this->datetimeSellerPaid;
	}

	/**
	 * @param \DateTime $datetimeSellerPaid
	 */
	public function setDatetimeSellerPaid($datetimeSellerPaid)
	{
		$this->datetimeSellerPaid = $datetimeSellerPaid;
	}

	/**
	 * @return string|null
	 */
	public function getNoteSellerPaid()
	{
		return $this->noteSellerPaid;
	}

	/**
	 * @param string|null $noteSellerPaid
	 */
	public function setNoteSellerPaid($noteSellerPaid)
	{
		$this->noteSellerPaid = $noteSellerPaid;
	}

	/**
	 * @return bool
	 */
	public function isSellerPaid()
	{
		return $this->sellerPaid;
	}

	/**
	 * @param bool $sellerPaid
	 */
	public function setSellerPaid($sellerPaid)
	{
		$this->sellerPaid = $sellerPaid;
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
	 * @return string
	 */
	public function getTicketCurrency()
	{
		return $this->ticketCurrency;
	}

	/**
	 * @param string $ticketCurrency
	 */
	public function setTicketCurrency($ticketCurrency)
	{
		$this->ticketCurrency = $ticketCurrency;
	}

	/**
	 * @return Carrier[]|ArrayCollection
	 */
	public function getCarriers()
	{
		return $this->getRoutes()->map(function (Route $route) {
			return $route->getCarrier();
		});
	}

	/**
	 * @return User|null
	 */
	public function getUserCashier(): ?User
	{
		return $this->userCashier;
	}

	/**
	 * @param User|null $userCashier
	 */
	public function setUserCashier(?User $userCashier): void
	{
		$this->userCashier = $userCashier;
	}

	/**
	 * @return User|null
	 */
	public function getUserBilled(): ?User
	{
		return $this->userBilled;
	}

	/**
	 * @param User|null $userBilled
	 */
	public function setUserBilled(?User $userBilled): void
	{
		$this->userBilled = $userBilled;
	}

	/**
	 * @return User|null
	 */
	public function getUserCancelled(): ?User
	{
		return $this->userCancelled;
	}

	/**
	 * @param User|null $userCancelled
	 */
	public function setUserCancelled(?User $userCancelled): void
	{
		$this->userCancelled = $userCancelled;
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
