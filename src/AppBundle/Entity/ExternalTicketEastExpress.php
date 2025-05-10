<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.09.17
 * Time: 20:16
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTicketEastExpress extends ExternalTicket implements ExternalTicketHtmlBodyInterface
{
	/**
	 * @var string
	 * @ORM\Column(name="html_body", type="text", nullable=true)
	 */
	protected $htmlBody;

	/**
	 * @param Route $route
	 * @param RouteTariff $routeTariff
	 * @param OrderPerson $orderPerson
	 * @return ExternalTicketEastExpress
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

}
