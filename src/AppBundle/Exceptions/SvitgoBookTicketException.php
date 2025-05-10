<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.05.18
 * Time: 20:13
 */

namespace AppBundle\Exceptions;


class SvitgoBookTicketException extends \Exception
{
	/**
	 * NikoloBookTicketException constructor.
	 */
	public function __construct($code, $request)
	{
		parent::__construct("Svitgo book ticket error '{$code}'. " . print_r($request, true));
	}
}
