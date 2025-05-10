<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTariffBlabla extends ExternalTariff
{
	/**
	 * @var integer
	 * @ORM\Column(name="percent", type="smallint", nullable=true)
	 */
	private $percent;

	/**
	 * @return int
	 */
	public function getPercent()
	{
		return $this->percent;
	}

	/**
	 * @param int $percent
	 */
	public function setPercent($percent)
	{
		$this->percent = $percent;
	}
}

