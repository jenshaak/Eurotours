<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 15.05.18
 * Time: 17:42
 */

namespace AppBundle\Entity;
use AppBundle\VO\PriceCurrency;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OrderPersonRouteTariffRepository")
 * @ORM\Table(name="orders_persons_route_tariffs")
 */
class OrderPersonRouteTariff
{
	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="order_person_route_tariff_id", type="string", length=255)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var OrderPerson
	 * @ORM\ManyToOne(targetEntity="OrderPerson")
	 * @ORM\JoinColumn(name="order_person_id", referencedColumnName="order_person_id", nullable=false)
	 */
	private $orderPerson;

	/**
	 * @var RouteTariff
	 * @ORM\ManyToOne(targetEntity="RouteTariff")
	 * @ORM\JoinColumn(name="route_tariff_id", referencedColumnName="route_tariff_id", nullable=false)
	 */
	private $routeTariff;

	/**
	 * @var Order
	 * @ORM\ManyToOne(targetEntity="Order", inversedBy="orderPersonRouteTariffs")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
	 */
	private $order;

	/**
	 * @var Route
	 * @ORM\ManyToOne(targetEntity="Route", inversedBy="orderPersonRouteTariffs")
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="route_id", nullable=false)
	 */
	private $route;

	/**
	 * @var Book
	 * @ORM\OneToOne(targetEntity="Book", inversedBy="orderPersonRouteTariff")
	 * @ORM\JoinColumn(name="book_id", referencedColumnName="book_id", nullable=true)
	 */
	private $book;

	/**
	 * @ORM\Column(name="price", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private ?float $changedPrice = null;

	/**
	 * @ORM\Column(name="seller_fee", type="decimal", precision=10, scale=2, nullable=true)
	 */
	private ?float $sellerFee = null;

	public static function create(OrderPerson $orderPerson, RouteTariff $routeTariff)
	{
		$orderPersonRouteTariff = new OrderPersonRouteTariff;
		$orderPersonRouteTariff->setOrderPerson($orderPerson);
		$orderPersonRouteTariff->setRouteTariff($routeTariff);
		$orderPersonRouteTariff->setOrder($orderPerson->getOrder());
		$orderPersonRouteTariff->setRoute($routeTariff->getRoute());
		return $orderPersonRouteTariff;
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
	 * @return RouteTariff
	 */
	public function getRouteTariff()
	{
		return $this->routeTariff;
	}

	/**
	 * @param RouteTariff $routeTariff
	 */
	public function setRouteTariff(RouteTariff $routeTariff)
	{
		$this->routeTariff = $routeTariff;
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
	public function setBook($book)
	{
		$this->book = $book;
	}

	public function getChangedPrice(): ?float
	{
		return $this->changedPrice;
	}

	public function setChangedPrice(?float $changedPrice)
	{
		$this->changedPrice = $changedPrice;
	}

	public function getSellerFee(): ?float
	{
		return $this->sellerFee;
	}

	public function getSellerFeeCurrency(): PriceCurrency
	{
		return PriceCurrency::create($this->sellerFee, $this->getOrder()->getCurrency());
	}

	public function setSellerFee(?float $sellerFee): OrderPersonRouteTariff
	{
		$this->sellerFee = $sellerFee;
		return $this;
	}
}
