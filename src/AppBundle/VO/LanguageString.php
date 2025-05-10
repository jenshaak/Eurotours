<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 12:14
 */

namespace AppBundle\VO;


use AppBundle\Entity\Language;

class LanguageString
{
	/**
	 * @var string[]
	 */
	private $strings = [];

	/**
	 * @param Language $language
	 * @return string
	 */
	public function getString(Language $language)
	{
		if (isset($this->strings[$language->getId()])) {
			return $this->strings[$language->getId()];

		} elseif (!empty($this->strings)) {
			return array_values($this->strings)[0];

		} else {
			return "";
		}
	}

	/**
	 * @param Language $language
	 * @param string $string
	 * @return $this
	 */
	public function setString(Language $language, $string)
	{
		$this->strings[$language->getId()] = $string;
		return $this;
	}

	/**
	 * @param string $string
	 * @return $this
	 */
	public function setStringForAllLanguages($string)
	{
		foreach ($this->strings as $k => $v) {
			$this->strings[$k] = $string;
		}
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getAllLanguagesStrings()
	{
		return array_values($this->strings);
	}

	/**
	 * @return string
	 */
	public function getAllLanguagesStringsInString()
	{
		return implode(" ", $this->getAllLanguagesStrings());
	}
}
