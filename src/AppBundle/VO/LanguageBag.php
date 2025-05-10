<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.04.17
 * Time: 14:03
 */

namespace AppBundle\VO;

use AppBundle\Entity\Language;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;

class LanguageBag extends AttributeBag
{
	const KEY_LANGUAGE = "language";

	/** @var string */
	private $language;

	public function initialize(array &$attributes)
	{
		parent::initialize($attributes);

		if ($this->has(self::KEY_LANGUAGE)) {
			$this->language = $this->get(self::KEY_LANGUAGE);
		}
	}

	public function getName()
	{
		return get_class($this);
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param Language $language
	 * @return $this
	 */
	public function setLanguage(Language $language)
	{
		$this->set(self::KEY_LANGUAGE, $this->language = $language->getId());

		return $this;
	}

	/**
	 * @param string $languageId
	 * @return $this
	 */
	public function setLanguageId($languageId)
	{
		$this->set(self::KEY_LANGUAGE, $this->language = $languageId);

		return $this;
	}

}
