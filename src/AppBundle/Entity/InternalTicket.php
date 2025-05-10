<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 05.03.18
 * Time: 15:03
 */

namespace AppBundle\Entity;


use AppBundle\VO\PriceCurrency;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="internal_tickets")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InternalTicketRepository")
 */
class InternalTicket implements TicketInterface
{
	/**
	 * @var int
	 * @ORM\Id()
	 * @ORM\Column(name="internal_ticket_id", type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	private $datetimeCreated;

	/**
	 * @var Order
	 * @ORM\ManyToOne(targetEntity="Order", inversedBy="internalTickets")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
	 */
	private $order;

	/**
	 * @var Route
	 * @ORM\ManyToOne(targetEntity="Route")
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="route_id", nullable=false)
	 */
	private $route;

	/**
	 * @var Schedule|null
	 * @ORM\ManyToOne(targetEntity="Schedule")
	 * @ORM\JoinColumn(name="schedule_id", referencedColumnName="schedule_id", nullable=null)
	 */
	private $schedule = null;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="date_day", type="date", nullable=false)
	 */
	private $dateDay;

	/**
	 * @var RouteTariff
	 * @ORM\ManyToOne(targetEntity="RouteTariff", inversedBy="internalTickets")
	 * @ORM\JoinColumn(name="route_tariff_id", referencedColumnName="route_tariff_id", nullable=false)
	 */
	private $routeTariff;

	/**
	 * @var OrderPerson
	 * @ORM\ManyToOne(targetEntity="OrderPerson", inversedBy="internalTickets")
	 * @ORM\JoinColumn(name="order_person_id", referencedColumnName="order_person_id", nullable=false)
	 */
	private $orderPerson;

	/**
	 * @var string|null
	 * @ORM\Column(name="file", type="string", length=512, nullable=true)
	 */
	private $file;

	/**
	 * @var int|null
	 * @ORM\Column(name="number", type="integer", nullable=true)
	 */
	private $number;

	/**
	 * @var int|null
	 * @ORM\Column(name="position_x", type="integer", nullable=true)
	 */
	private $positionX;

	/**
	 * @var int|null
	 * @ORM\Column(name="position_y", type="integer", nullable=true)
	 */
	private $positionY;

	/**
	 * @var Carrier|null
	 * @ORM\ManyToOne(targetEntity="Carrier")
	 * @ORM\JoinColumn(name="carrier_id", referencedColumnName="carrier_id", nullable=true)
	 * # TODO: Po doplneni dat tohle udelat NOT NULL
	 */
	private $carrier;

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
	 * @var \DateTime
	 * @ORM\Column(name="date_month_billed", type="date", nullable=true)
	 */
	private $dateMonthBilled;

	/**
	 * @var BookInternal
	 * @ORM\OneToOne(targetEntity="AppBundle\Entity\BookInternal", mappedBy="internalTicket")
	 */
	private $book;

	/**
	 * @var float
	 * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private $price;

	/**
	 * @var float
	 * @ORM\Column(name="price_include_surcharge", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private $priceIncludeSurcharge;

	/**
	 * @ORM\Column(name="seller_fee", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private ?float $sellerFee = null;

	/**
	 * @var string
	 * @ORM\Column(name="currency", type="string", length=3, nullable=true)
	 */
	private $currency;

	/**
	 * @var ExternalTicket|null
	 * @ORM\OneToOne(targetEntity="AppBundle\Entity\ExternalTicket", mappedBy="alternativeInternalTicket")
	 */
	private $alternativeExternalTicket = null;

	/** @ORM\Column(name="paid_by_bank", type="boolean", nullable=false) */
	private bool $paidByBank = false;

	/** @ORM\Column(name="datetime_paid_by_bank", type="datetime", nullable=true) */
	private ?DateTime $datetimePaidByBank = null;

	/** @ORM\Column(name="note_paid_by_bank", type="text", nullable=true) */
	private ?string $notePaidByBank = null;

	/**
	 * Just for generating ticket with price or without in `InternalTicketGeneratorService`
	 */
	public bool $generateWithPrice = true;

	public function __construct()
	{
		$this->datetimeCreated = new \DateTime;
	}

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return InternalTicket
	 */
	public static function create(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		$ticket = new InternalTicket;
		$ticket->setRoute($route);
		$ticket->setRouteTariff($routeTariff);
		$ticket->setOrderPerson($orderPerson);
		$ticket->setCurrency($route->getCurrency());
		$orderPerson->getInternalTickets()->add($ticket);
		$orderPerson->getOrder()->getInternalTickets()->add($ticket);
		$ticket->setOrder($orderPerson->getOrder());
		$ticket->setCarrier($route->getCarrier());
		return $ticket;
	}

	public function setBySeat(Seat $seat)
	{
		$this->setPositionX($seat->getPositionX());
		$this->setPositionY($seat->getPositionY());
		$this->setNumber($seat->getNumber());
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
		$this->setDateDay($route->getDatetimeDeparture());
		$this->setSchedule($route->getSchedule());
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
	 * @return int
	 */
	public function getNumber()
	{
		return $this->number;
	}

	/**
	 * @param int $number
	 */
	public function setNumber($number)
	{
		$this->number = $number;
	}

	/**
	 * @return int
	 */
	public function getPositionX()
	{
		return $this->positionX;
	}

	/**
	 * @param int $positionX
	 */
	public function setPositionX($positionX)
	{
		$this->positionX = $positionX;
	}

	/**
	 * @return int
	 */
	public function getPositionY()
	{
		return $this->positionY;
	}

	/**
	 * @param int $positionY
	 */
	public function setPositionY($positionY)
	{
		$this->positionY = $positionY;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateDay()
	{
		return $this->dateDay;
	}

	/**
	 * @param \DateTime $dateDay
	 */
	public function setDateDay($dateDay)
	{
		$this->dateDay = $dateDay;
	}

	/**
	 * @return Schedule|null
	 */
	public function getSchedule()
	{
		return $this->schedule;
	}

	/**
	 * @param Schedule|null $schedule
	 */
	public function setSchedule($schedule)
	{
		$this->schedule = $schedule;
	}

	/**
	 * @return string
	 */
	public function getContentType()
	{
		return "application/pdf";
	}

	/**
	 * @return Carrier|null
	 */
	public function getCarrier()
	{
		return $this->carrier;
	}

	/**
	 * @param Carrier|null $carrier
	 */
	public function setCarrier($carrier)
	{
		$this->carrier = $carrier;
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
	public function isTwoWayTicket()
	{
		return $this->getOrderPerson()->getRouteTariffBack() !== null;
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
	 * @return BookInternal
	 */
	public function getBook()
	{
		return $this->book;
	}

	/**
	 * @param BookInternal $book
	 */
	public function setBook($book)
	{
		$this->book = $book;
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
	public function isFirstWayForInReturnTicketTheSameCarrier()
	{
		return $this->getOrder()->getRouteBack()
			and $this->getRoute() === $this->getOrder()->getRouteThere()
			and $this->getOrder()->getRouteThere()->getCarrier() === $this->getOrder()->getRouteBack()->getCarrier();
	}

	/**
	 * @return bool
	 */
	public function isSecondWayForInReturnTicketTheSameCarrier()
	{
		return $this->getOrder()->getRouteBack()
			and $this->getRoute() === $this->getOrder()->getRouteBack()
			and $this->getOrder()->getRouteThere()->getCarrier() === $this->getOrder()->getRouteBack()->getCarrier();
	}

	public function isFirstWayForInReturnTicketNotTheSameCarrier()
	{
		return $this->getOrder()->getRouteBack()
			and $this->getRoute() === $this->getOrder()->getRouteThere()
			and $this->getOrder()->getRouteThere()->getCarrier() !== $this->getOrder()->getRouteBack()->getCarrier();
	}

	/**
	 * @return bool
	 */
	public function isSecondWayForInReturnTicketNotTheSameCarrier()
	{
		return $this->getOrder()->getRouteBack()
			and $this->getRoute() === $this->getOrder()->getRouteBack()
			and $this->getOrder()->getRouteThere()->getCarrier() !== $this->getOrder()->getRouteBack()->getCarrier();
	}

	/**
	 * @return PriceCurrency
	 */
	public function getPriceCurrency()
	{
		return PriceCurrency::create($this->price, $this->getCurrency());
	}

	/**
	 * @param float $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return PriceCurrency
	 */
	public function getPriceCurrencyIncludeSurcharge()
	{
		return PriceCurrency::create($this->priceIncludeSurcharge, $this->getCurrency());
	}

	/**
	 * @param float $priceIncludeSurcharge
	 */
	public function setPriceIncludeSurcharge($priceIncludeSurcharge)
	{
		$this->priceIncludeSurcharge = $priceIncludeSurcharge;
	}

	public function getSellerFee(): ?float
	{
		return $this->sellerFee;
	}

	public function setSellerFee(?float $sellerFee): InternalTicket
	{
		$this->sellerFee = $sellerFee;
		return $this;
	}

	public function getSellerFeeCurrency(): PriceCurrency
	{
		return PriceCurrency::create($this->getSellerFee(), $this->getCurrency());
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

	public function countPrices()
	{
		$this->setCurrency($this->getOrder()->getCurrency());

		$orderPersonRouteTariff = $this->getOrder()->getOrderPersonRouteTariff(
			$this->getOrderPerson(), $this->getRouteTariff()
		);

		if ($orderPersonRouteTariff->getChangedPrice() !== null) {
			$this->setPrice($orderPersonRouteTariff->getChangedPrice());
			$this->setPriceIncludeSurcharge($orderPersonRouteTariff->getChangedPrice());
		} else {
			if ($this->isFirstWayForInReturnTicketTheSameCarrier()) {
				if ($this->getBook()) {
					$this->setPrice(array_reduce($this->getOrder()->getBooks()->getValues(), function ($sum, Book $book) {
						if ($book->getOrderPersonRouteTariff()->getOrderPerson() === $this->getOrderPerson()) {
							$sum += $book->getPriceCurrency()->getPrice();
						}
						return $sum;
					}, 0));

					$this->setPriceIncludeSurcharge(array_reduce($this->getOrder()->getBooks()->getValues(), function ($sum, Book $book) {
						if ($book->getOrderPersonRouteTariff()->getOrderPerson() === $this->getOrderPerson()) {
							$sum += $book->getPriceCurrencyIncludeSurcharge()->getPrice();
						}
						return $sum;
					}, 0));
				} else {
					$this->setPrice($this->getOrderPerson()->getSumPrice()->getPrice());
					$this->setPriceIncludeSurcharge($this->getOrderPerson()->getSumPriceIncludeSurcharge()->getPrice());
				}
			} else {
				if ($this->getBook()) {
					$this->setPrice($this->getBook()->getPriceCurrency()->getPrice());
					$this->setPriceIncludeSurcharge($this->getBook()->getPriceCurrencyIncludeSurcharge()->getPrice());
				} else {
					$this->setPrice($this->getRouteTariff()->getPrice());
					$this->setPriceIncludeSurcharge($this->getRouteTariff()->getPriceIncludeSurcharge());
				}
			}
		}

		if($orderPersonRouteTariff->getSellerFee() !== null) {
			$this->setSellerFee($orderPersonRouteTariff->getSellerFee());
		}
	}

	/**
	 * @return string|null
	 */
	public function getCarrierTitle()
	{
		return null;
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
	 * @return ExternalTicket|null
	 */
	public function getAlternativeExternalTicket(): ?ExternalTicket
	{
		return $this->alternativeExternalTicket;
	}

	/**
	 * @param ExternalTicket|null $alternativeExternalTicket
	 */
	public function setAlternativeExternalTicket(?ExternalTicket $alternativeExternalTicket): void
	{
		$this->alternativeExternalTicket = $alternativeExternalTicket;
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
