<?php


namespace AppBundle\VO;


use AppBundle\Entity\Language;

class HomepageNotice
{
	/** @var boolean */
	private $show = false;

	/** @var LanguageString */
	private $content;

	public function __construct()
	{
		$this->content = new LanguageString;
	}

	/**
	 * @return LanguageString
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @param Language $language
	 * @param string $publicNote
	 */
	public function setContent(Language $language, $publicNote)
	{
		$this->content = clone $this->getContent()->setString($language, $publicNote);
	}

	/**
	 * @return bool
	 */
	public function isShow(): bool
	{
		return $this->show;
	}

	/**
	 * @param bool $show
	 */
	public function setShow(bool $show): void
	{
		$this->show = $show;
	}
}
