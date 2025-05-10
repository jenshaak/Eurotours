<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.01.18
 * Time: 10:57
 */

namespace AppBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BookStudentAgency extends Book
{
	/**
	 * @var string
	 * @ORM\Column(name="ticket_identifier", type="string", length=255, nullable=true)
	 */
	protected $ticketIdentifier;

	/**
	 * @var string
	 * @ORM\Column(name="account_code", type="string", length=255, nullable=true)
	 */
	protected $accountCode;

	/**
	 * @var ExternalTicketStudentAgency
	 * @ORM\OneToOne(targetEntity="ExternalTicket")
	 * @ORM\JoinColumn(name="external_ticket_id", referencedColumnName="external_ticket_id", nullable=true)
	 */
	protected $externalTicket;

	/**
	 * @return string
	 */
	public function getTicketIdentifier()
	{
		return $this->ticketIdentifier;
	}

	/**
	 * @param string $ticketIdentifier
	 */
	public function setTicketIdentifier($ticketIdentifier)
	{
		$this->ticketIdentifier = $ticketIdentifier;
	}

	/**
	 * @return string
	 */
	public function getAccountCode()
	{
		return $this->accountCode;
	}

	/**
	 * @param string $accountCode
	 */
	public function setAccountCode($accountCode)
	{
		$this->accountCode = $accountCode;
	}

	/**
	 * @return ExternalTicketStudentAgency
	 */
	public function getExternalTicket()
	{
		return $this->externalTicket;
	}

	/**
	 * @param ExternalTicketStudentAgency $externalTicket
	 */
	public function setExternalTicket($externalTicket)
	{
		$this->externalTicket = $externalTicket;
	}

}
