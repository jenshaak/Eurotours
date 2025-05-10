<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.05.18
 * Time: 20:09
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTicketBlabla extends ExternalTicket implements ExternalTicketHtmlBodyInterface
{
	/**
	 * @var string
	 * @ORM\Column(name="html_body", type="text", nullable=true)
	 */
	protected $htmlBody;

	/**
	 * @var string
	 * @ORM\Column(name="ticket_number", type="string", length=255, nullable=true)
	 */
	protected $ticketIdent;

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketBlabla
	 */
	public static function create(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		/** @var ExternalTicketBlabla $externalTicket */
		$externalTicket = parent::createFromChildren(new self, $route, $routeTariff, $orderPerson);
		return $externalTicket;
	}

	/**
	 * @return string
	 */
	public function getHtmlBody()
	{
		return $this->htmlBody;
	}

	/**
	 * @param string $htmlBody
	 */
	public function setHtmlBody($htmlBody)
	{
		$this->htmlBody = $htmlBody;
	}

	/**
	 * @return string
	 */
	public function getTicketIdent()
	{
		return $this->ticketIdent;
	}

	/**
	 * @param string $ticketIdent
	 */
	public function setTicketIdent($ticketIdent)
	{
		$this->ticketIdent = $ticketIdent;
	}

}
