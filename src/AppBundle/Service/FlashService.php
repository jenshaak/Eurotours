<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 06.06.18
 * Time: 12:17
 */

namespace AppBundle\Service;


use Symfony\Component\HttpFoundation\Session\Session;

class FlashService
{
	const ERROR = "error";
	const NOTICE = "notice";
	const WARNING = "warning";

	/**
	 * @var Session
	 */
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function addNotice($message)
	{
		$this->session->getFlashBag()->add(self::NOTICE, $message);
	}

	public function addError($message)
	{
		$this->session->getFlashBag()->add(self::ERROR, $message);
	}

	public function addWarning($message)
	{
		$this->session->getFlashBag()->add(self::WARNING, $message);
	}

	/**
	 * @return bool
	 */
	public function existsError()
	{
		return $this->session->getFlashBag()->has(self::ERROR);
	}

	/**
	 * @return bool
	 */
	public function existsNotice()
	{
		return $this->session->getFlashBag()->has(self::NOTICE);
	}
}
