<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 18:15
 */

namespace AppBundle\Controller\Backend;
use AppBundle\Entity\BookInternal;
use AppBundle\Entity\InternalTicket;
use AppBundle\Service\BookService;
use AppBundle\Service\CarrierService;
use AppBundle\Service\InternalTicketService;
use AppBundle\Service\UserService;
use AppBundle\VO\SeatsSoldLineVO;
use AppBundle\Widget\Backend\MenuWidget;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.seatsSold")
 * @Security("has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_CARRIER')")
 */
class SeatsSoldController
{
	const PARAM_CARRIER = "carrier";

	/**
	 * @var UserService
	 */
	private $userService;
	/**
	 * @var CarrierService
	 */
	private $carrierService;
	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var BookService
	 */
	private $bookService;

	public function __construct(UserService $userService,
	                            CarrierService $carrierService,
	                            MenuWidget $menuWidget,
	                            InternalTicketService $internalTicketService,
	                            BookService $bookService)
	{
		$this->userService = $userService;
		$this->carrierService = $carrierService;
		$this->menuWidget = $menuWidget;
		$this->internalTicketService = $internalTicketService;
		$this->bookService = $bookService;

		$this->menuWidget->setActive(MenuWidget::ACTIVE_SEATS_SOLD);
	}

	/**
	 * @Route(path="/backend/seatsSold", name="backend_seats_sold")
	 * @Template()
	 * @return array
	 */
	public function seatsSoldAction(Request $request)
	{
		$carrier = $this->getCurrentCarrier($request);

		/** @var ArrayCollection|SeatsSoldLineVO[] $seatsSoldLines */
		$seatsSoldLines = new ArrayCollection;
		if ($carrier) {
			array_map(function (InternalTicket $ticket) use ($seatsSoldLines) {
				if ($ticket->getOrder()->isDeleted()) return;
				$schedule = $ticket->getSchedule();
				$line = $schedule->getLine();
				$key = $schedule->getId() . "-" . $ticket->getDateDay()->format("Ymd");
				if (!$seatsSoldLines->containsKey($key)) {
					$seatsSoldLine = new SeatsSoldLineVO;
					$seatsSoldLine->setFromCity($schedule->getFirstScheduleLineStation()->getLineStation()->getCity());
					$seatsSoldLine->setToCity($schedule->getLastScheduleLineStation()->getLineStation()->getCity());
					$seatsSoldLine->setFromTime($schedule->getFirstScheduleLineStation()->getTime()->getDepartureTime());
					$seatsSoldLine->setToTime($schedule->getLastScheduleLineStation()->getTime()->getDepartureTime());
					$seatsSoldLine->setLine($line);
					$seatsSoldLine->setDateDay($ticket->getDateDay());
					$seatsSoldLines->set($key, $seatsSoldLine);
				}

				$seatsSoldLines->get($key)->addTicket($ticket);
			}, $this->internalTicketService->findInternalTicketsForCarrier(
				$carrier, new \DateTime("midnight"), new \DateTime("+3 month"), true
			));

			array_map(function (BookInternal $book) use ($seatsSoldLines) {
				if ($book->getOrder()->isDeleted()) return;
				if ($book->getInternalTicket() !== null) return;
				$schedule = $book->getSeatsPlan()->getSchedule();
				$line = $schedule->getLine();
				$key = $schedule->getId() . "-" . $book->getDateDay()->format("Ymd");
				if (!$seatsSoldLines->containsKey($key)) {
					$seatsSoldLine = new SeatsSoldLineVO;
					$seatsSoldLine->setFromCity($schedule->getFirstScheduleLineStation()->getLineStation()->getCity());
					$seatsSoldLine->setToCity($schedule->getLastScheduleLineStation()->getLineStation()->getCity());
					$seatsSoldLine->setFromTime($schedule->getFirstScheduleLineStation()->getTime()->getDepartureTime());
					$seatsSoldLine->setToTime($schedule->getLastScheduleLineStation()->getTime()->getDepartureTime());
					$seatsSoldLine->setLine($line);
					$seatsSoldLine->setDateDay($book->getDateDay());
					$seatsSoldLines->set($key, $seatsSoldLine);
				}

				$seatsSoldLines->get($key)->addBook($book);
			}, $this->bookService->findInternalBooksForCarrier(
				$carrier, new \DateTime("midnight"), new \DateTime("+3 month"), true
			));
		}

		$seatsSoldLines = $seatsSoldLines->getValues();
		/** @var SeatsSoldLineVO[] $seatsSoldLines */
		usort($seatsSoldLines, function (SeatsSoldLineVO $a, SeatsSoldLineVO $b) {
			return $a->getDateDay() < $b->getDateDay() ? -1 : +1;
		});

		return [
			"carrier" => $carrier,
			"carriers" => $this->carrierService->findAllCarriers(),
			"seatsSoldLines" => $seatsSoldLines
		];
	}

	/**
	 * @param Request $request
	 * @return \AppBundle\Entity\Carrier|null
	 */
	private function getCurrentCarrier(Request $request)
	{
		$currentUser = $this->userService->getCurrentUser();
		if ($this->userService->isCarrier()) {
			$carrier = $currentUser->getCarrier();
		} else {
			if ($request->query->has(self::PARAM_CARRIER)) {
				$carrier = $this->carrierService->getCarrier($request->query->getInt(self::PARAM_CARRIER));
			} else {
				$carrier = null;
			}
		}
		return $carrier;
	}
}
