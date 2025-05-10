<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 11.09.17
 * Time: 12:15
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTariffEastExpress extends ExternalTariff
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