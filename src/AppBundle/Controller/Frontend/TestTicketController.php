<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 08.07.18
 * Time: 11:07
 */

namespace AppBundle\Controller\Frontend;


use AppBundle\Service\InternalTicketGeneratorService;
use AppBundle\Service\InternalTicketService;
use AppBundle\Service\UploadService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route(service="controller.backend.testTicket")
 */
class TestTicketController
{
	/**
	 * @var InternalTicketGeneratorService
	 */
	private $internalTicketGeneratorService;
	/**
	 * @var InternalTicketService
	 */
	private $internalTicketService;
	/**
	 * @var UploadService
	 */
	private $uploadService;

	public function __construct(InternalTicketGeneratorService $internalTicketGeneratorService,
	                            InternalTicketService $internalTicketService,
	                            UploadService $uploadService)
	{
		$this->internalTicketGeneratorService = $internalTicketGeneratorService;
		$this->internalTicketService = $internalTicketService;
		$this->uploadService = $uploadService;
	}

	/**
	 * @Route(path="/test")
	 * @return Response
	 * @throws \Twig\Error\Error
	 */
	public function testAction()
	{
		$internalTicket = $this->internalTicketService->getInternalTicket(2282);

		/*var_dump(
			!$internalTicket->getOrder()->getRouteBack()->getSchedule()->getLine()->isSeatsWithoutNumbers()
			and
			$internalTicket->getOrderPerson()->getInternalTicketForRoute($internalTicket->getOrder()->getRouteBack())
			and
			$internalTicket->getOrderPerson()->getInternalTicketForRoute($internalTicket->getOrder()->getRouteBack())->getNumber()
		);

exit;*/
		#$internalTicket = $this->internalTicketService->getInternalTicket(1514); # one way
		#$internalTicket = $this->internalTicketService->getInternalTicket(1518); # two way (stejny dopravce)
		#$internalTicket = $this->internalTicketService->getInternalTicket(1522); # two way (jiny dopravce)


		#$internalTicket = $this->internalTicketService->getInternalTicket(605); # two way
		#$internalTicket = $this->internalTicketService->getInternalTicket(134); # one way
		#$internalTicket = $this->internalTicketService->getInternalTicket(23); # open
		$this->internalTicketGeneratorService->generateTicket($internalTicket);

		return BinaryFileResponse::create($this->uploadService->getWebDir() . $internalTicket->getFile());
	}

}
