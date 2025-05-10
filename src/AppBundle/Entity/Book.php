<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.01.18
 * Time: 10:53
 */

namespace AppBundle\Entity;
use AppBundle\VO\PriceCurrency;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\BookRepository")
 * @ORM\Table(name="books")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap(
 *     {
 *         "internal" = "BookInternal",
 *         "student_agency" = "BookStudentAgency",
 *         "nikolo" = "BookNikolo",
 *         "infobus" = "BookInfobus",
 *         "ecolines" = "BookEcolines",
 *         "regabus" = "BookRegabus",
 *         "blabla" = "BookBlabla",
 *         "trans_tempo" = "BookTransTempo",
 *         "likebus" = "BookLikeBus"
 *     }
 * )
 */
abstract class Book
{
	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="book_id", type="string", length=64)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	protected $id;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	protected $datetimeCreated;

	/**
	 * @var Order
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Order", inversedBy="books")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=true)
	 */
	protected $order;

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
	 * @var OrderPersonRouteTariff
	 * @ORM\OneToOne(targetEntity="AppBundle\Entity\OrderPersonRouteTariff", mappedBy="book")
	 */
	private $orderPersonRouteTariff;

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
	 * @var Route|null
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Route", inversedBy="books")
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="route_id", nullable=true)
	 */
	private $route;

	public function __construct()
	{
		$this->datetimeCreated = new \DateTime;
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
	 * @return \DateTime
	 */
	public function getDatetimeCreated()
	{
		return $this->datetimeCreated;
	}

	/**
	 * @param \DateTime $datetimeCreated
	 */
	public function setDatetimeCreated(\DateTime $datetimeCreated)
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
	 * @return bool
	 */
	public function isStillValid()
	{
		if ($this->isCancelled()) return false;

		if ($this->getOrder()->getDateReservationDay()) {
			$datetimeLimit = new \DateTime($this->getOrder()->getDateReservationDay()->format("Y-m-d 23:59:59"));
			return $datetimeLimit > new \DateTime;
		}

		return true;
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
	 * @return OrderPersonRouteTariff
	 */
	public function getOrderPersonRouteTariff()
	{
		return $this->orderPersonRouteTariff;
	}

	/**
	 * @param OrderPersonRouteTariff $orderPersonRouteTariff
	 */
	public function setOrderPersonRouteTariff($orderPersonRouteTariff)
	{
		$this->orderPersonRouteTariff = $orderPersonRouteTariff;
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

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return $this->getOrder()->getCurrency();
	}

	public function countPrices()
	{
		if ($this->getOrderPersonRouteTariff()->getChangedPrice() !== null) {
			$this->setPrice($this->getOrderPersonRouteTariff()->getChangedPrice());
			$this->setPriceIncludeSurcharge($this->getOrderPersonRouteTariff()->getChangedPrice());
		} else {
			$this->setPrice($this->getOrderPersonRouteTariff()->getRouteTariff()->getPrice());
			$this->setPriceIncludeSurcharge($this->getOrderPersonRouteTariff()->getRouteTariff()->getPriceIncludeSurcharge());
		}
	}

	/**
	 * @return Route|null
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * @param Route|null $route
	 */
	public function setRoute($route)
	{
		$this->route = $route;
		$route->getBooks()->add($this);
	}

}
