<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 14.11.17
 * Time: 18:16
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @deprecated
 */
class ExternalTicketEurolines extends ExternalTicket
{
	/**
	 * @var string|resource
	 * @ORM\Column(name="pdf_body", type="blob", nullable=true)
	 */
	protected $pdfBody;

	/**
	 * @var string
	 * @ORM\Column(name="ticket_number", type="string", length=255, nullable=true)
	 */
	protected $ticketIdent;

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketEurolines
	 */
	public static function create(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		return parent::createFromChildren(new self, $route, $routeTariff, $orderPerson);
	}

	/**
	 * @return resource
	 */
	public function getPdfBody()
	{
		return $this->pdfBody;
	}

	/**
	 * @param string $pdfBody
	 */
	public function setPdfBody(string $pdfBody)
	{
		$this->pdfBody = $pdfBody;
	}

	/**
	 * @return string
	 */
	public function getTicketIdent(): string
	{
		return $this->ticketIdent;
	}

	/**
	 * @param string $ticketIdent
	 */
	public function setTicketIdent(string $ticketIdent)
	{
		$this->ticketIdent = $ticketIdent;
	}

}
