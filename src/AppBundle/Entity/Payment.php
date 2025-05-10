<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 26.06.17
 * Time: 15:33
 */

namespace AppBundle\Entity;
use AppBundle\VO\PriceCurrency;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PaymentRepository")
 * @ORM\Table(name="payments")
 */
class Payment
{
	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="payment_id", type="string", length=255)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var int
	 * @ORM\Column(name="ident", type="bigint", nullable=false, unique=true)
	 */
	private $ident;

	/**
	 * @var Order
	 * @ORM\OneToOne(targetEntity="Order", mappedBy="payment")
	 */
	private $order;

	/**
	 * @var int
	 * @ORM\Column(name="price", type="integer", nullable=false)
	 */
	private $price;

	/**
	 * @var string
	 * @ORM\Column(name="url", type="string", length=1024, nullable=false)
	 */
	private $url;

	/**
	 * @var bool
	 * @ORM\Column(name="paid", type="boolean", nullable=false)
	 */
	private $paid = false;

	/**
	 * @var bool
	 * @ORM\Column(name="cancelled", type="boolean", nullable=false)
	 */
	private $cancelled = false;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	private $datetimeCreated;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_paid", type="datetime", nullable=true)
	 */
	private $datetimePaid;

	/**
	 * @var string
	 * @ORM\Column(name="currency", type="string", length=3, nullable=false)
	 */
	private $currency;

	/**
	 * @var bool
	 * @ORM\Column(name="dummy", type="boolean", nullable=false)
	 */
	private $dummy = false;

	/**
	 * @var string|null
	 * @ORM\Column(name="email", type="string", length=255, nullable=true)
	 */
	private $email;

	/**
	 * @var string|null
	 * @ORM\Column(name="note", type="text", nullable=true)
	 */
	private $note;

	/**
	 * @var \DateTime|null
	 * @ORM\Column(name="datetime_expire", type="datetime", nullable=true)
	 */
	private $datetimeExpire = null;

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
	 * @return int
	 */
	public function getIdent()
	{
		return $this->ident;
	}

	/**
	 * @param int $ident
	 */
	public function setIdent($ident)
	{
		$this->ident = $ident;
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

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
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
	 * @return boolean
	 */
	public function isCancelled()
	{
		return $this->cancelled;
	}

	/**
	 * @param boolean $cancelled
	 */
	public function setCancelled($cancelled)
	{
		$this->cancelled = $cancelled;
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
	 * @return \DateTime
	 */
	public function getDatetimePaid()
	{
		return $this->datetimePaid;
	}

	/**
	 * @param \DateTime $datetimePaid
	 */
	public function setDatetimePaid($datetimePaid)
	{
		$this->datetimePaid = $datetimePaid;
	}

	/**
	 * @return string
	 */
	public function getCurrency(): string
	{
		return $this->currency;
	}

	/**
	 * @param string $currency
	 */
	public function setCurrency(string $currency)
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
	 * @return bool
	 */
	public function isDummy()
	{
		return $this->dummy;
	}

	/**
	 * @param bool $dummy
	 */
	public function setDummy($dummy)
	{
		$this->dummy = $dummy;
	}

	/**
	 * @return string|null
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string|null $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * @return string|null
	 */
	public function getNote()
	{
		return $this->note;
	}

	/**
	 * @param string|null $note
	 */
	public function setNote($note)
	{
		$this->note = $note;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDatetimeExpire(): ?\DateTime
	{
		return $this->datetimeExpire;
	}

	/**
	 * @param \DateTime|null $datetimeExpire
	 */
	public function setDatetimeExpire(?\DateTime $datetimeExpire): void
	{
		$this->datetimeExpire = $datetimeExpire;
	}
}
