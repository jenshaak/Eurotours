<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 24.09.18
 * Time: 23:42
 */

namespace AppBundle\Exceptions;


class EcolinesSeatBusyException extends \Exception
{
	const ERROR_CODE = "SEAT_BUSY";
}
