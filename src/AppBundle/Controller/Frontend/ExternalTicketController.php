<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 18.09.17
 * Time: 17:03
 */

namespace AppBundle\Controller\Frontend;


use AppBundle\Entity\ExternalTicket;
use AppBundle\Entity\ExternalTicketHtmlBodyInterface;
use AppBundle\Entity\Order;
use AppBundle\Service\ExternalTicketGeneratorService;
use AppBundle\Service\ExternalTicketService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route(service="controller.frontend.externalTicket")
 */
class ExternalTicketController
{
	/**
	 * @var ExternalTicketService
	 */
	private $externalTicketService;
	/**
	 * @var Container
	 */
	private $container;
	/**
	 * @var ExternalTicketGeneratorService
	 */
	private $externalTicketGeneratorService;

	public function __construct(ExternalTicketService $externalTicketService,
	                            ContainerInterface $container,
	                            ExternalTicketGeneratorService $externalTicketGeneratorService)
	{
		$this->externalTicketService = $externalTicketService;
		$this->container = $container;
		$this->externalTicketGeneratorService = $externalTicketGeneratorService;
	}

	/**
	 * @Route(path="/order/{order}/external-ticket/{externalTicket}", name="external_ticket")
	 * @param Order $order
	 * @param ExternalTicket $externalTicket
	 * @return Response
	 * @deprecated
	 */
	public function externalTicketAction(Order $order, ExternalTicket $externalTicket)
	{
		$this->externalTicketGeneratorService->generateFile($externalTicket);
		$this->externalTicketService->saveExternalTicket($externalTicket);

		return Response::create();
	}

	/**
	 * @Route(path="/secret/path/to/external/ticket/{externalTicket}", name="secret_external_ticket")
	 * @param ExternalTicket $externalTicket
	 * @return Response
	 * @deprecated
	 */
	public function secretExternalTicketAction(ExternalTicket $externalTicket)
	{
		if ($externalTicket instanceof ExternalTicketHtmlBodyInterface) {
			return Response::create($externalTicket->getHtmlBody());
		}

		throw new NotFoundHttpException;
	}
}
