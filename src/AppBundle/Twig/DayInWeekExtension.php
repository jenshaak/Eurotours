<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 02.05.18
 * Time: 22:24
 */

namespace AppBundle\Twig;


class DayInWeekExtension extends \Twig_Extension
{
	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter("dayInWeek", [$this, "dayInWeek"])
		];
	}

	public function getName()
	{
		return "dayInWeek";
	}

	/**
	 * @param $dayInWeek
	 * @return mixed
	 */
	public function dayInWeek($dayInWeek)
	{
		$daysInWeek = [
			1 => "pondělí",
			2 => "úterý",
			3 => "středa",
			4 => "čtvrtek",
			5 => "pátek",
			6 => "sobota",
			7 => "neděle"
		];

		return $daysInWeek[$dayInWeek];
	}
}
