<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 2019-04-05
 * Time: 11:09
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ExternalTariffRegabus extends ExternalTariff
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
