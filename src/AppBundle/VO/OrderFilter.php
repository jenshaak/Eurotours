<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 21.06.17
 * Time: 12:45
 */

namespace AppBundle\VO;


use AppBundle\Entity\Carrier;
use AppBundle\Entity\City;
use AppBundle\Entity\User;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderFilter
{
	const ORDER_BY_DATETIME_CREATED = "datetimeCreated";
	const ORDER_BY_DATETIME_PAID = "datetimePaid";
	const ORDER_BY_DATETIME_DEPARTURE = "datetimeDeparture";
	const ORDER_BY_DIRECTION_DESC = "DESC";
	const ORDER_BY_DIRECTION_ASC = "ASC";

	const COLOR_TYPE_PAID_CARD = "paid-card";
	const COLOR_TYPE_WEB_PAID_WIRE_CASH = "web-paid-wire-cash";
	const COLOR_TYPE_WEB_RESERVATION = "web-reservation";
	const COLOR_TYPE_INTERNAL_PAID = "internal-paid";
	const COLOR_TYPE_INTERNAL_RESERVATION = "internal-reservation";

	/** @var int */
	private $limit = null;

	/** @var int */
	private $offset = 0;

	/** @var bool */
	private $onlyNonDraft = false;

	/** @var int|null */
	private $foundedOrders = null;

	/** @var string */
	private $orderBy = self::ORDER_BY_DATETIME_CREATED;

	/** @var string */
	private $orderByDirection = self::ORDER_BY_DIRECTION_DESC;

	/** @var City|null */
	private $fromCity;

	/** @var City|null */
	private $toCity;

	/** @var Carrier|null */
	private $carrier;

	/** @var boolean */
	private $onlyExpired = false;

	/**
	 * @var User|null
	 */
	private $userSeller;

	/**
	 * @var User|null
	 */
	private $userCancelled;

	/**
	 * @var User|null
	 */
	private $userCashier;

	/**
	 * @var string|null
	 */
	private $fulltextSearch;

	/**
	 * @var null|boolean
	 */
	private $sellerPaid = null;

	/**
	 * @var null|boolean
	 */
	private $billed;

	/**
	 * @var null|string
	 */
	private $colorType;

	/**
	 * @var \DateTime|null
	 */
	private $monthDeparture;

	/**
	 * @var \DateTime|null
	 */
	private $monthOrder;

	/**
	 * @var \DateTime|null
	 */
	private $monthPaid;

	/**
	 * @var boolean
	 */
	private $onlyNonCancelled = false;

	/** @var string|null */
	private $status;

	/** @var \DateTime|null */
	private $monthBilled;

	private ?DateRange $dateRangeOrder = null;
	private ?DateRange $dateRangeDeparture = null;
	private ?DateRange $dateRangeBilled = null;
	private ?DateRange $dateRangePaid = null;

	private bool $onlyCancelled = false;

	/**
	 * @return boolean
	 */
	public function isOnlyNonDraft()
	{
		return $this->onlyNonDraft;
	}

	/**
	 * @param boolean $onlyNonDraft
	 */
	public function setOnlyNonDraft($onlyNonDraft)
	{
		$this->onlyNonDraft = $onlyNonDraft;
	}

	/**
	 * @return int
	 */
	public function getNextOffset()
	{
		return $this->getOffset() + $this->getLimit();
	}

	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit($limit)
	{
		$this->limit = $limit;
	}

	/**
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @param int $offset
	 */
	public function setOffset($offset)
	{
		$this->offset = $offset;
	}

	/**
	 * @return int|null
	 */
	public function getFoundedOrders()
	{
		return $this->foundedOrders;
	}

	/**
	 * @param int|null $foundedOrders
	 */
	public function setFoundedOrders($foundedOrders)
	{
		$this->foundedOrders = $foundedOrders;
	}

	/**
	 * @return string
	 */
	public function getOrderBy()
	{
		return $this->orderBy;
	}

	/**
	 * @param string $orderBy
	 */
	public function setOrderBy($orderBy)
	{
		$this->orderBy = $orderBy;
	}

	/**
	 * @return City|null
	 */
	public function getFromCity()
	{
		return $this->fromCity;
	}

	/**
	 * @param City|null $fromCity
	 */
	public function setFromCity($fromCity)
	{
		$this->fromCity = $fromCity;
	}

	/**
	 * @return City|null
	 */
	public function getToCity()
	{
		return $this->toCity;
	}

	/**
	 * @param City|null $toCity
	 */
	public function setToCity($toCity)
	{
		$this->toCity = $toCity;
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
	 * @return null|string
	 */
	public function getFulltextSearch()
	{
		return $this->fulltextSearch;
	}

	/**
	 * @param null|string $fulltextSearch
	 */
	public function setFulltextSearch($fulltextSearch)
	{
		$this->fulltextSearch = $fulltextSearch;
	}

	/**
	 * @return string
	 */
	public function getOrderByDirection()
	{
		return $this->orderByDirection;
	}

	/**
	 * @param string $orderByDirection
	 */
	public function setOrderByDirection($orderByDirection)
	{
		$this->orderByDirection = $orderByDirection;
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
	 * @return bool|null
	 */
	public function getSellerPaid()
	{
		return $this->sellerPaid;
	}

	/**
	 * @param bool|null $sellerPaid
	 */
	public function setSellerPaid($sellerPaid)
	{
		$this->sellerPaid = $sellerPaid;
	}

	/**
	 * @return string|null
	 */
	public function getColorType()
	{
		return $this->colorType;
	}

	/**
	 * @param string|null $colorType
	 */
	public function setColorType($colorType)
	{
		$this->colorType = $colorType;
	}

	/**
	 * @return bool|null
	 */
	public function getBilled()
	{
		return $this->billed;
	}

	/**
	 * @param bool|null $billed
	 */
	public function setBilled($billed)
	{
		$this->billed = $billed;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getMonthDeparture()
	{
		return $this->monthDeparture;
	}

	/**
	 * @param \DateTime|null $monthDeparture
	 */
	public function setMonthDeparture($monthDeparture)
	{
		$this->monthDeparture = $monthDeparture;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getMonthOrder()
	{
		return $this->monthOrder;
	}

	/**
	 * @param \DateTime|null $monthOrder
	 */
	public function setMonthOrder($monthOrder)
	{
		$this->monthOrder = $monthOrder;
	}

	/**
	 * @return bool
	 */
	public function isOnlyExpired()
	{
		return $this->onlyExpired;
	}

	/**
	 * @param bool $onlyExpired
	 */
	public function setOnlyExpired($onlyExpired)
	{
		$this->onlyExpired = $onlyExpired;
	}

	/**
	 * @return bool
	 */
	public function isOnlyNonCancelled()
	{
		return $this->onlyNonCancelled;
	}

	/**
	 * @param bool $onlyNonCancelled
	 */
	public function setOnlyNonCancelled($onlyNonCancelled)
	{
		$this->onlyNonCancelled = $onlyNonCancelled;
	}

	/**
	 * @return string|null
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param string|null $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getMonthBilled()
	{
		return $this->monthBilled;
	}

	/**
	 * @param \DateTime|null $monthBilled
	 */
	public function setMonthBilled($monthBilled)
	{
		$this->monthBilled = $monthBilled;
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
	 * @return DateRange|null
	 */
	public function getDateRangeOrder(): ?DateRange
	{
		return $this->dateRangeOrder;
	}

	/**
	 * @param DateRange|null $dateRangeOrder
	 */
	public function setDateRangeOrder(?DateRange $dateRangeOrder): void
	{
		$this->dateRangeOrder = $dateRangeOrder;
	}

	/**
	 * @return DateRange|null
	 */
	public function getDateRangeDeparture(): ?DateRange
	{
		return $this->dateRangeDeparture;
	}

	/**
	 * @param DateRange|null $dateRangeDeparture
	 */
	public function setDateRangeDeparture(?DateRange $dateRangeDeparture): void
	{
		$this->dateRangeDeparture = $dateRangeDeparture;
	}

	/**
	 * @return DateRange|null
	 */
	public function getDateRangeBilled(): ?DateRange
	{
		return $this->dateRangeBilled;
	}

	/**
	 * @param DateRange|null $dateRangeBilled
	 */
	public function setDateRangeBilled(?DateRange $dateRangeBilled): void
	{
		$this->dateRangeBilled = $dateRangeBilled;
	}

	/**
	 * @return DateRange|null
	 */
	public function getDateRangePaid(): ?DateRange
	{
		return $this->dateRangePaid;
	}

	/**
	 * @param DateRange|null $dateRangePaid
	 */
	public function setDateRangePaid(?DateRange $dateRangePaid): void
	{
		$this->dateRangePaid = $dateRangePaid;
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

	/**
	 * @return \DateTime|null
	 */
	public function getMonthPaid(): ?\DateTime
	{
		return $this->monthPaid;
	}

	/**
	 * @param \DateTime|null $monthPaid
	 */
	public function setMonthPaid(?\DateTime $monthPaid): void
	{
		$this->monthPaid = $monthPaid;
	}

	public function isOnlyCancelled(): bool
	{
		return $this->onlyCancelled;
	}

	public function setOnlyCancelled(bool $onlyCancelled): void
	{
		$this->onlyCancelled = $onlyCancelled;
	}
}
