<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTicketTransTempo extends ExternalTicket implements ExternalTicketHtmlBodyInterface
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
	protected $bookingId;

	/**
	 * @var string
	 * @ORM\Column(name="hash", type="string", length=255, nullable=true)
	 */
	protected $hash;

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketTransTempo
	 */
	public static function create(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		return parent::createFromChildren(new self, $route, $routeTariff, $orderPerson);
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
	public function getBookingId(): string
	{
		return $this->bookingId;
	}

	/**
	 * @param string $bookingId
	 */
	public function setBookingId(string $bookingId): void
	{
		$this->bookingId = $bookingId;
	}

	/**
	 * @return string
	 */
	public function getHash(): string
	{
		return $this->hash;
	}

	/**
	 * @param string $hash
	 */
	public function setHash(string $hash): void
	{
		$this->hash = $hash;
	}
}
