<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 28.05.18
 * Time: 20:30
 */

namespace AppBundle\Exceptions;


class SvitgoBuyBookTicketException extends \Exception
{
	public function __construct($code, $request)
	{
		parent::__construct("Svitgo buy book ticket error '{$code}'. " . print_r($request, true));
	}
}
