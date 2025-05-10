<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-06-03
 * Time: 18:02
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ActivityRepository")
 * @ORM\Table(name="activities")
 */
class Activity
{
	const TYPE_NOTE = "Note";
	const TYPE_INTERNAL_TICKET_GENERATED = "InternalTicketGenerated";
	const TYPE_ORDER_PAID = "OrderPaid";
	const TYPE_ORDER_CREATED = "OrderCreated";
	const TYPE_DATETIME_RESERVATION_CHANGED = "DatetimeReservationChanged";
	const TYPE_PAYMENT_CREATED = "PaymentCreated";
	const TYPE_PAYMENT_SEND_EMAIL = "PaymentSendEmail";
	const TYPE_NEED_MANUAL_TICKET = "NeedManualTicket";
	const TYPE_EXPIRED_BOOK = "ExpiredBook";
	const TYPE_NO_SEATS = "NoSeats";
	const TYPE_ORDER_CANCEL = "OrderCancel";
	const TYPE_TICKET_CANCEL = "TicketCancel";
	const TYPE_TICKET_BILLED = "TicketBilled";
	const TYPE_TICKET_BILLED_CANCEL = "TicketBilledCancel";

	/**
	 * @var string
	 * @ORM\Id()
	 * @ORM\Column(name="activity_id", type="string", length=64)
	 * @ORM\GeneratedValue(strategy="UUID")
	 */
	private $id;

	/**
	 * @var Order
	 * @ORM\ManyToOne(targetEntity="Order")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", nullable=false)
	 */
	private $order;

	/**
	 * @var string|null
	 * @ORM\Column(name="content_text", type="string", length=1024, nullable=true)
	 */
	private $contentText;

	/**
	 * @var \DateTime
	 * @ORM\Column(name="datetime_created", type="datetime", nullable=false)
	 */
	private $datetimeCreated;

	/**
	 * @var User|null
	 * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=true)
	 */
	private $user;

	/**
	 * @var string
	 * @ORM\Column(name="type", type="string", length=64, nullable=false)
	 */
	private $type;

	/**
	 * @param Order $order
	 * @param string $type
	 * @return Activity
	 */
	public static function create(Order $order, $type)
	{
		$activity = new Activity;
		$activity->setType($type);
		$activity->setOrder($order);
		return $activity;
	}

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
	 * @return string|null
	 */
	public function getContentText()
	{
		return $this->contentText;
	}

	/**
	 * @param string|null $contentText
	 */
	public function setContentText($contentText)
	{
		$this->contentText = $contentText;
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
	 * @return User|null
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param User|null $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
}
