<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTariffLikeBus extends ExternalTariff
{
	/** @ORM\Column(name="percent", type="smallint", nullable=true) */
	private int $percent;

	public function getPercent(): int
	{
		return $this->percent;
	}

	public function setPercent(int $percent): void
	{
		$this->percent = $percent;
	}
}
