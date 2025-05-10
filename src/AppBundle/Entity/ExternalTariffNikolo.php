<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 16.04.18
 * Time: 13:11
 */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTariffNikolo extends ExternalTariff
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
