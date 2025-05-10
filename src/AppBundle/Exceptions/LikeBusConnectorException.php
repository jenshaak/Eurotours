<?php

namespace AppBundle\Exceptions;

use Exception;

class LikeBusConnectorException extends Exception
{
	private object $error;

	public function __construct(object $error)
	{
		parent::__construct($error->code . ": " . $error->message);

		$this->error = $error;
	}

	public function getError(): object
	{
		return $this->error;
	}
}
