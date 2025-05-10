<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 01.11.17
 * Time: 11:21
 */

namespace AppBundle\Controller\Backend;

use AppBundle\Service\DateFormatService;
use AppBundle\Service\InternalTicketGeneratorService;
use AppBundle\Service\SimpleInternalTicketGeneratorService;
use AppBundle\Service\UploadService;
use AppBundle\VO\SimpleInternalTicket;
use AppBundle\Widget\Backend\MenuWidget;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route(service="controller.backend.ticket")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class TicketController
{
	const PARAM_THERE_FROM = "thereFrom";
	const PARAM_THERE_DATE_DEPARTURE = "thereDateDeparture";
	const PARAM_THERE_TIME_DEPARTURE = "thereTimeDeparture";
	const PARAM_THERE_TO = "thereTo";
	const PARAM_THERE_DATE_ARRIVAL = "thereDateArrival";
	const PARAM_THERE_TIME_ARRIVAL = "thereTimeArrival";
	const PARAM_THERE_CARRIER = "thereCarrier";
	const PARAM_THERE_PRICE = "therePrice";

	const PARAM_BACK_FROM = "backFrom";
	const PARAM_BACK_DATE_DEPARTURE = "backDateDeparture";
	const PARAM_BACK_TIME_DEPARTURE = "backTimeDeparture";
	const PARAM_BACK_TO = "backTo";
	const PARAM_BACK_DATE_ARRIVAL = "backDateArrival";
	const PARAM_BACK_TIME_ARRIVAL = "backTimeArrival";
	const PARAM_BACK_CARRIER = "backCarrier";
	const PARAM_BACK_PRICE = "backPrice";

	const PARAM_NAME = "name";
	const PARAM_TYPE = "type";

	const PARAM_TYPE_ONE_WAY = "oneway";
	const PARAM_TYPE_RETURN = "return";

	/**
	 * @var MenuWidget
	 */
	private $menuWidget;
	/**
	 * @var Container
	 */
	private $container;
	/**
	 * @var DateFormatService
	 */
	private $dateFormatService;
	/**
	 * @var SimpleInternalTicketGeneratorService
	 */
	private $simpleInternalTicketGeneratorService;
	/**
	 * @var UploadService
	 */
	private $uploadService;

	public function __construct(MenuWidget $menuWidget,
	                            ContainerInterface $container,
	                            DateFormatService $dateFormatService,
								SimpleInternalTicketGeneratorService $simpleInternalTicketGeneratorService,
								UploadService $uploadService)
	{
		$this->menuWidget = $menuWidget;
		$this->container = $container;
		$this->dateFormatService = $dateFormatService;
		$this->simpleInternalTicketGeneratorService = $simpleInternalTicketGeneratorService;
		$this->uploadService = $uploadService;
	}

	/**
	 * @Route(path="/backend/tickets", name="backend_tickets", methods={"GET"})
	 * @Template()
	 */
	public function ticketsAction()
	{
		$this->menuWidget->setActive(MenuWidget::ACTIVE_TICKETS);

		return [];
	}

	/**
	 * @Route(path="/backend/ticket/generator", name="backend_ticket_generator", methods={"POST"})
	 * @return BinaryFileResponse
	 */
	public function ticketGeneratorAction(Request $request)
	{
		$thereTicket = new SimpleInternalTicket;
		$thereTicket->setIdent(time());
		$thereTicket->setName($request->request->get(self::PARAM_NAME));
		$thereTicket->setCarrier($request->request->get(self::PARAM_THERE_CARRIER));
		$thereTicket->setFrom($request->request->get(self::PARAM_THERE_FROM));
		$thereTicket->setTo($request->request->get(self::PARAM_THERE_TO));
		$thereTicket->setPrice($request->request->get(self::PARAM_THERE_PRICE));
		$thereTicket->setDatetimeDeparture($this->dateFormatService->dateTimeParse(
			$request->request->get(self::PARAM_THERE_DATE_DEPARTURE)
			. " " .
			$request->request->get(self::PARAM_THERE_TIME_DEPARTURE)
		));
		$thereTicket->setDatetimeArrival($this->dateFormatService->dateTimeParse(
			$request->request->get(self::PARAM_THERE_DATE_ARRIVAL)
			. " " .
			$request->request->get(self::PARAM_THERE_TIME_ARRIVAL)
		));

		/** @var SimpleInternalTicket|null $backTicket */
		$backTicket = null;
		if ($request->request->get(self::PARAM_TYPE) == self::PARAM_TYPE_RETURN) {
			$backTicket = new SimpleInternalTicket;
			$backTicket->setIdent($thereTicket->getIdent());
			$backTicket->setName($request->request->get(self::PARAM_NAME));
			$backTicket->setCarrier($request->request->get(self::PARAM_BACK_CARRIER));
			$backTicket->setFrom($request->request->get(self::PARAM_THERE_FROM));
			$backTicket->setTo($request->request->get(self::PARAM_THERE_TO));
			$backTicket->setPrice($request->request->get(self::PARAM_THERE_PRICE));
			$backTicket->setDatetimeDeparture($this->dateFormatService->dateTimeParse(
				$request->request->get(self::PARAM_THERE_DATE_DEPARTURE)
				. " " .
				$request->request->get(self::PARAM_THERE_TIME_DEPARTURE)
			));
			$backTicket->setDatetimeArrival($this->dateFormatService->dateTimeParse(
				$request->request->get(self::PARAM_THERE_DATE_ARRIVAL)
				. " " .
				$request->request->get(self::PARAM_THERE_TIME_ARRIVAL)
			));
		}

		$this->simpleInternalTicketGeneratorService->generateTicket($thereTicket);

		return BinaryFileResponse::create($this->uploadService->getWebDir() . $thereTicket->getFile());
	}
}
