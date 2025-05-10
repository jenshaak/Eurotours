<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 25.05.17
 * Time: 11:20
 */

namespace AppBundle\Repository;


use AppBundle\Entity\Order;
use AppBundle\VO\OrderFilter;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class OrderRepository extends EntityRepository
{
	public function save(Order $order)
	{
		$this->getEntityManager()->persist($order);
		$this->getEntityManager()->flush($order);
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function createSearchQuery()
	{
		$query = $this->createQueryBuilder("o");
		$query->select("o");
		$query->leftJoin("o.routeThere", "routeThere");
		$query->leftJoin("o.routeBack", "routeBack");
		return $query;
	}

	/**
	 * @param OrderFilter $filter
	 * @return Order[]
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function findOrders(OrderFilter $filter): array
	{
		$query = $this->createSearchQuery();
		$query = $this->applyFilter($query, $filter);

		$results = $query->getQuery()->getResult();
		$filter->setFoundedOrders(
			(int) $query->select("COUNT(DISTINCT o.id)")->setFirstResult(0)->getQuery()->getSingleScalarResult()
		);

		return $results;
	}

	private function applyFilter(QueryBuilder $query, OrderFilter $filter): QueryBuilder
	{
		if ($filter->isOnlyNonCancelled()) {
			$query->andWhere("(o.cancelled = :cancelled)")->setParameter("cancelled", false);
		}

		if ($filter->getLimit()) {
			$query->setMaxResults($filter->getLimit());
		}

		if ($filter->getOffset()) {
			$query->setFirstResult($filter->getOffset());
		}

		if ($filter->getColorType() === OrderFilter::COLOR_TYPE_PAID_CARD) {
			$query->andWhere("o.paid = :paid")->setParameter("paid", true);
			$query->andWhere("o.paidBy = :paidBy")->setParameter("paidBy", Order::PAID_BY_CARD);

		} elseif ($filter->getColorType() === OrderFilter::COLOR_TYPE_WEB_PAID_WIRE_CASH) {
			$query->andWhere("o.internal = :internal")->setParameter("internal", false);
			$query->andWhere("(o.paidBy = :paidByCash OR o.paidBy = :paidByWire)")
				->setParameter("paidByCash", Order::PAID_BY_CASH)
				->setParameter("paidByWire", Order::PAID_BY_WIRE_TRANSFER);

		} elseif ($filter->getColorType() === OrderFilter::COLOR_TYPE_WEB_RESERVATION) {
			$query->andWhere("o.internal = :internal")->setParameter("internal", false);
			$query->andWhere("o.orderType = :orderType")->setParameter("orderType", Order::ORDER_TYPE_RESERVATION);

			$query->andWhere("o.paid = :paid")->setParameter("paid", false);
		} elseif ($filter->getColorType() === OrderFilter::COLOR_TYPE_INTERNAL_PAID) {
			$query->andWhere("o.internal = :internal")->setParameter("internal", true);
			$query->andWhere("o.paid = :paid")->setParameter("paid", true);

		} elseif ($filter->getColorType() === OrderFilter::COLOR_TYPE_INTERNAL_RESERVATION) {
			$query->andWhere("o.internal = :internal")->setParameter("internal", true);
			$query->andWhere("o.orderType = :orderType")->setParameter("orderType", Order::ORDER_TYPE_RESERVATION);
			$query->andWhere("o.paid = :paid")->setParameter("paid", false);
		}

		if ($filter->getUserSeller()) {
			$query->andWhere("o.userSeller = :userSeller")->setParameter("userSeller", $filter->getUserSeller());
		}

		if ($filter->getUserCashier()) {
			$query->andWhere("o.userCashier = :userCashier")->setParameter("userCashier", $filter->getUserCashier());
		}

		if ($filter->getUserCancelled()) {
			$query->andWhere("o.userCancelled = :userCancelled")->setParameter("userCancelled", $filter->getUserCancelled());
		}

		if ($filter->getStatus()) {
			$query->andWhere("o.status = :status")->setParameter("status", $filter->getStatus());
		}

		if ($filter->getSellerPaid() !== null) {
			$query->andWhere("o.sellerPaid = :sellerPaid")->setParameter("sellerPaid", $filter->getSellerPaid());
		}

		if ($filter->getBilled() !== null) {
			$query->andWhere("o.billed = :billed")->setParameter("billed", $filter->getBilled());
		}

		if ($filter->getMonthOrder()) {
			$query->andWhere("o.datetimeOrdered >= :monthOrderFrom")->setParameter("monthOrderFrom", clone $filter->getMonthOrder());
			$query->andWhere("o.datetimeOrdered < :monthOrderTo")->setParameter("monthOrderTo", (clone $filter->getMonthOrder())->modify("+1 month"));
		}

		if ($filter->getMonthPaid()) {
			$query->andWhere("o.datetimePaid >= :monthPaidFrom")->setParameter("monthPaidFrom", clone $filter->getMonthPaid());
			$query->andWhere("o.datetimePaid < :monthPaidTo")->setParameter("monthPaidTo", (clone $filter->getMonthPaid())->modify("+1 month"));
		}

		if ($filter->getDateRangeOrder()) {
			$query->andWhere("DATE(o.datetimeOrdered) >= DATE(:dateRangeOrderFrom)")->setParameter("dateRangeOrderFrom", $filter->getDateRangeOrder()->getFrom());
			$query->andWhere("DATE(o.datetimeOrdered) <= DATE(:dateRangeOrderTo)")->setParameter("dateRangeOrderTo", $filter->getDateRangeOrder()->getTo());
		}

		if ($filter->getDateRangeBilled()) {
			$buff = $this->getEntityManager()->getConnection()->prepare("SELECT order_id FROM internal_tickets WHERE DATE(datetime_billed) >= DATE(?) AND DATE(datetime_billed) <= DATE(?)");
			$buff->bindValue(1, $filter->getDateRangeBilled()->getFrom()->format("Y-m-d"));
			$buff->bindValue(2, $filter->getDateRangeBilled()->getTo()->format("Y-m-d"));
			$buff->execute();
			$query->andWhere("o.id IN (:ids)")->setParameter("ids", array_map(function ($i) { return $i['order_id']; }, $buff->fetchAll()));
		}

		if ($filter->getDateRangeDeparture()) {
			$query->andWhere("DATE(routeThere.datetimeDeparture) >= DATE(:dateRangeDepartureFrom)")->setParameter("dateRangeDepartureFrom", $filter->getDateRangeDeparture()->getFrom());
			$query->andWhere("DATE(routeThere.datetimeDeparture) <= DATE(:dateRangeDepartureTo)")->setParameter("dateRangeDepartureTo", $filter->getDateRangeDeparture()->getTo());
		}

		if ($filter->getDateRangePaid()) {
			$query->andWhere("DATE(o.datetimePaid) >= DATE(:dateRangePaidFrom)")->setParameter("dateRangePaidFrom", $filter->getDateRangePaid()->getFrom());
			$query->andWhere("DATE(o.datetimePaid) <= DATE(:dateRangePaidTo)")->setParameter("dateRangePaidTo", $filter->getDateRangePaid()->getTo());
		}

		if ($filter->getMonthBilled()) {
			$buff = $this->getEntityManager()->getConnection()->prepare("SELECT order_id FROM internal_tickets WHERE date_month_billed = ?");
			$buff->bindValue(1, $filter->getMonthBilled()->format("Y-m-d"));
			$buff->execute();
			$query->andWhere("o.id IN (:ids)")->setParameter("ids", array_map(function ($i) { return $i['order_id']; }, $buff->fetchAll()));
		}

		if ($filter->isOnlyExpired()) {
			$query->andWhere("o.dateReservationDay < DATE(:dateReservationDay)")->setParameter("dateReservationDay", new \DateTime);
		}

		if ($filter->getMonthDeparture()) {
			$query->andWhere("routeThere.datetimeDeparture >= :monthDepartureFrom")->setParameter("monthDepartureFrom", clone $filter->getMonthDeparture());
			$query->andWhere("routeThere.datetimeDeparture < :monthDepartureTo")->setParameter("monthDepartureTo", (clone $filter->getMonthDeparture())->modify("+1 month"));
		}

		if ($filter->getCarrier()) {
			$query->andWhere("(routeThere.carrier = :carrier OR routeBack.carrier = :carrier)");
			$query->setParameter("carrier", $filter->getCarrier());
		}

		if ($filter->getFulltextSearch()) {
			$query->andWhere("o.fulltextString LIKE :fulltextSearch")
				->setParameter("fulltextSearch", "%" . $filter->getFulltextSearch() . "%");
		}

		$query->andWhere("o.deleted = :deleted")->setParameter("deleted", false);

		if ($filter->getOrderBy() === OrderFilter::ORDER_BY_DATETIME_CREATED) {
			$query->orderBy("o.datetimeCreated", $filter->getOrderByDirection());
		} elseif ($filter->getOrderBy() === OrderFilter::ORDER_BY_DATETIME_DEPARTURE) {
			$query->orderBy("routeThere.datetimeDeparture", $filter->getOrderByDirection());
		} elseif ($filter->getOrderBy() === OrderFilter::ORDER_BY_DATETIME_PAID) {
			$query->orderBy("o.datetimePaid", $filter->getOrderByDirection());
		}

		if ($filter->isOnlyCancelled()) {
			$query->andWhere("o.cancelled = 1");
		}

		if ($filter->isOnlyNonDraft()) {
			$query->andWhere("(o.status <> :statusDraft and o.status <> :selectSeats)")
				->setParameter("statusDraft", Order::STATUS_DRAFT)
				->setParameter("selectSeats", Order::STATUS_SELECT_SEATS);
		}

		return $query;
	}

	/**
	 * @param int $ident
	 * @return Order|null
	 */
	public function getOrderByIdent($ident)
	{
		/** @var Order|null $order */
		$order = $this->findOneBy([ "ident" => $ident ]);
		return $order;
	}

	private function getSummarizeQuery(): QueryBuilder
	{
		$query = $this->createQueryBuilder("o");
		$query->select("o.currency, sum(o.price) as priceSum, sum(o.sellerFee) as sellerFeeSum");
		$query->leftJoin("o.routeThere", "routeThere");
		$query->leftJoin("o.routeBack", "routeBack");

		return $query;
	}

	/**
	 * @param OrderFilter $filter
	 * @return float|int|mixed|string
	 */
	public function summarizeOrders(OrderFilter $filter)
	{
		$query = $this->getSummarizeQuery();
		$query = $this->applyFilter($query, $filter);

		$query->groupBy('o.currency');
		$query->orderBy('o.currency', 'asc');

		return $query->getQuery()->getResult();
	}
}
