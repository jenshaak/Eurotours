<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class BookLikeBus extends Book
{
	/** @ORM\Column(name="external_order_id", type="integer", nullable=true) */
	private ?int $externalOrderId = null;

	public function getExternalOrderId(): ?int
	{
		return $this->externalOrderId;
	}

	public function setExternalOrderId(?int $externalOrderId): void
	{
		$this->externalOrderId = $externalOrderId;
	}
}
