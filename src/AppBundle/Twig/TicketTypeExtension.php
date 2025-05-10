<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-03-22
 * Time: 13:56
 */

namespace AppBundle\Twig;


use AppBundle\Entity\ExternalTicket;
use AppBundle\Entity\InternalTicket;

class TicketTypeExtension extends \Twig_Extension
{
	public function getTests()
	{
		return [
			new \Twig_SimpleTest("internalTicket", function ($ticket) { return $ticket instanceof InternalTicket; }),
			new \Twig_SimpleTest("externalTicket", function ($ticket) { return $ticket instanceof ExternalTicket; }),
		];
	}

	public function getName()
	{
		return "ticketType";
	}

}
