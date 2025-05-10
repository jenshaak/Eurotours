<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 04.05.18
 * Time: 13:37
 */

namespace AppBundle\Service;


class DateTimeService
{
	/**
	 * @param \DateTime $datetime
	 * @return bool
	 */
	public function isWorkingTime(\DateTime $datetime)
	{
		$hour = $datetime->format("H");
		return $hour >= 8 and $hour < 20;
	}
}
