<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 21.06.17
 * Time: 13:39
 */

namespace AppBundle\Twig;


use AppBundle\Entity\Language;
use AppBundle\Service\LanguageService;

class TimeAgoExtension extends \Twig_Extension
{
	/**
	 * @var LanguageService
	 */
	private $languageService;

	public function __construct(LanguageService $languageService)
	{
		$this->languageService = $languageService;
	}

	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter("timeAgo", [$this, "printTimeAgo"],  [ "is_safe" => [ "html" ]])
		];
	}

	public function getName()
	{
		return "timeAgo";
	}

	/**
	 * @param \DateTime $time
	 * @return string
	 */
	public function timeAgo(\DateTime $time)
	{
		if ($this->languageService->getCurrentLanguage()->getId() == Language::CS) {
			return $this->timeAgoCS($time);
		} elseif ($this->languageService->getCurrentLanguage()->getId() == Language::EN) {
			return $this->timeAgoEN($time);
		}

		return $this->timeAgoEN($time);
	}

	/**
	 * @param \DateTime $time
	 * @return string
	 */
	public function timeAgoCS(\DateTime $time)
	{
		if (!$time) {
			return FALSE;
		} elseif (is_numeric($time)) {
			$time = (int) $time;
		} elseif ($time instanceof \DateTime) {
			$time = $time->format('U');
		} else {
			$time = strtotime($time);
		}
		$delta = time() - $time;
		if ($delta < 0) {
			$delta = round(abs($delta) / 60);
			if ($delta == 0) return 'za okamžik';
			if ($delta == 1) return 'za minutu';
			if ($delta < 45) return 'za ' . $delta . ' ' . self::plural($delta, 'minuta', 'minuty', 'minut');
			if ($delta < 90) return 'za hodinu';
			if ($delta < 1440) return 'za ' . round($delta / 60) . ' ' . self::plural(round($delta / 60), 'hodina', 'hodiny', 'hodin');
			if ($delta < 2880) return 'zítra';
			if ($delta < 43200) return 'za ' . round($delta / 1440) . ' ' . self::plural(round($delta / 1440), 'den', 'dny', 'dní');
			if ($delta < 86400) return 'za měsíc';
			if ($delta < 525960) return 'za ' . round($delta / 43200) . ' ' . self::plural(round($delta / 43200), 'měsíc', 'měsíce', 'měsíců');
			if ($delta < 1051920) return 'za rok';
			return 'za ' . round($delta / 525960) . ' ' . self::plural(round($delta / 525960), 'rok', 'roky', 'let');
		}
		$delta = round($delta / 60);
		if ($delta == 0) return 'před okamžikem';
		if ($delta == 1) return 'před minutou';
		if ($delta < 45) return "před $delta minutami";
		if ($delta < 90) return 'před hodinou';
		if ($delta < 1440) return 'před ' . round($delta / 60) . ' hodinami';
		if ($delta < 2880) return 'včera';
		if ($delta < 43200) return 'před ' . round($delta / 1440) . ' dny';
		if ($delta < 86400) return 'před měsícem';
		if ($delta < 525960) return 'před ' . round($delta / 43200) . ' měsíci';
		if ($delta < 1051920) return 'před rokem';
		return 'před ' . round($delta / 525960) . ' lety';
	}

	/**
	 * @param \DateTime $ago
	 * @return string
	 */
	private function timeAgoEN(\DateTime $ago)
	{
		$now = new \DateTime;
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);

		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}

		$string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	/**
	 * @param \DateTime $time
	 * @return string
	 */
	public function printTimeAgo(\DateTime $time)
	{
		return "<span data-ago='" . $time->format("Y-m-d H:i:s") . "'>" . $this->timeAgo($time) . "</span>";
	}

	/**
	 * Plural: three forms, special cases for 1 and 2, 3, 4.
	 * (Slavic family: Slovak, Czech)
	 * @param  int
	 * @return mixed
	 */
	private static function plural($n)
	{
		$args = func_get_args();
		return $args[($n == 1) ? 1 : (($n >= 2 && $n <= 4) ? 2 : 3)];
	}
}