<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 10.04.18
 * Time: 16:44
 */

namespace AppBundle\Connectors;

class NikoloConnector extends BusSystemConnector
{
	/** @return null|string */
	protected function getLogin()
	{
		return "Eurotours";
	}

	/** @return null|string */
	protected function getPassword()
	{
		return "123456";
	}

	protected function getUrl($method)
	{
		return "https://api.nikolo.eu/server/curl/{$method}.php";
	}
}
