<?php


namespace AppBundle\Exceptions;


class RegabusConnectorException extends \Exception
{
	/** @var object */
	private $error;

	public function __construct($error)
	{
		parent::__construct("[" . $error->type . "] " . $error->code . ": " . $error->name);
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
