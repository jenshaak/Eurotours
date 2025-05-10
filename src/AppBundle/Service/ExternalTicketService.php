<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.09.17
 * Time: 20:17
 */

namespace AppBundle\Service;


use AppBundle\Entity\ExternalTicket;
use AppBundle\Repository\ExternalTicketRepository;

class ExternalTicketService
{
	/**
	 * @var ExternalTicketRepository
	 */
	private $externalTicketRepository;

	public function __construct(ExternalTicketRepository $externalTicketRepository)
	{
		$this->externalTicketRepository = $externalTicketRepository;
	}

	/**
	 * @param ExternalTicket $externalTicket
	 */
	public function saveExternalTicket(ExternalTicket $externalTicket)
	{
		$this->externalTicketRepository->save($externalTicket);
	}
}