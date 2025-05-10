<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.09.17
 * Time: 20:15
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTicketStudentAgency extends ExternalTicket
{
	/**
	 * @var string|resource
	 * @ORM\Column(name="image_body", type="blob", nullable=true)
	 */
	protected $imageBody;

	/**
	 * @var string
	 * @ORM\Column(name="ticket_number", type="string", length=255, nullable=true)
	 */
	protected $ticketIdent;

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketStudentAgency
	 */
	public static function create(Route $route, RouteTariff $routeTariff, OrderPerson $orderPerson)
	{
		return parent::createFromChildren(new self, $route, $routeTariff, $orderPerson);
	}

	/**
	 * @return resource|string
	 */
	public function getImageBody()
	{
		return $this->imageBody;
	}

	/**
	 * @param resource|string $imageBody
	 */
	public function setImageBody($imageBody)
	{
		$this->imageBody = $imageBody;
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
