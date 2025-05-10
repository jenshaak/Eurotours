<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTicketBusSystem extends ExternalTicket
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
	protected $orderIdent;

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketBusSystem
	 */
	public static function create(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		/** @var ExternalTicketBusSystem $externalTicket */
		$externalTicket = parent::createFromChildren(new self, $route, $routeTariff, $orderPerson);
		return $externalTicket;
	}

	/**
	 * @return resource|string
	 */
	public function getPdfBody()
	{
		return $this->pdfBody;
	}

	/**
	 * @param resource|string $pdfBody
	 */
	public function setPdfBody($pdfBody): void
	{
		$this->pdfBody = $pdfBody;
	}

	/**
	 * @return string
	 */
	public function getOrderIdent(): string
	{
		return $this->orderIdent;
	}

	/**
	 * @param string $orderIdent
	 */
	public function setOrderIdent(string $orderIdent): void
	{
		$this->orderIdent = $orderIdent;
	}
}
