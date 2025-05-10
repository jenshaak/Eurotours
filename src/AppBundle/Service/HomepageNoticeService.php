<?php


namespace AppBundle\Service;


use AppBundle\VO\HomepageNotice;

class HomepageNoticeService
{
	const KEY_HOMEPAGE_NOTICE = "HomepageNotice";

	/**
	 * @var KeyValueService
	 */
	private $keyValueService;

	public function __construct(KeyValueService $keyValueService)
	{
		$this->keyValueService = $keyValueService;
	}

	public function save(HomepageNotice $homepageNotice)
	{
		$this->keyValueService->set(self::KEY_HOMEPAGE_NOTICE, serialize($homepageNotice));
	}

	/**
	 * @return HomepageNotice
	 */
	public function load()
	{
		$keyValue = $this->keyValueService->get(self::KEY_HOMEPAGE_NOTICE);
		return $keyValue->getValue() ? unserialize($keyValue->getValue()) : new HomepageNotice;
	}
}
