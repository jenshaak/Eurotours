<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 07.11.17
 * Time: 17:26
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @deprecated
 */
class ExternalTariffEurolines extends ExternalTariff
{
	/**
	 * @var string
	 * @ORM\Column(name="uid", type="string", length=255, nullable=true)
	 */
	protected $uid;

	/**
	 * @return string
	 */
	public function getUid()
	{
		return $this->uid;
	}

	/**
	 * @param string $uid
	 */
	public function setUid($uid)
	{
		$this->uid = $uid;
	}

}
