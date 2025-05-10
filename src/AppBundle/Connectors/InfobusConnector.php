<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 07.06.17
 * Time: 16:10
 */

namespace AppBundle\Connectors;


use AppBundle\Entity\Language;
use Curl\Curl;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DomCrawler\Crawler;

class InfobusConnector extends BusSystemConnector
{
	protected function getLogin()
	{
		return "api.eurotours7d";
	}

	protected function getPassword()
	{
		return "b77mu9yWFIh";
	}

	protected function getUrl($method)
	{
		return "https://api.bussystem.eu/server/curl/{$method}.php";
	}
}
