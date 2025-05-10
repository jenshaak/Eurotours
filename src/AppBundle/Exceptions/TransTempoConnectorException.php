<?php


namespace AppBundle\Exceptions;


class TransTempoConnectorException extends \Exception
{
	/** @var object */
	private $error;

	public function __construct($error)
	{
		parent::__construct($error->code . ": " . $error->message);
		$this->error = $error;
	}

	/**
	 * @return object
	 */
	public function getError()
	{
		return $this->error;
	}
}
