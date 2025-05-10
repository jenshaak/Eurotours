<?php
/**
 * Created by PhpStorm.
 * User: pix
 * Date: 13.09.17
 * Time: 14:31
 */

namespace AppBundle\Service;


use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class WebDriverService
{
	private $seleniumUrl;

	public function __construct($seleniumUrl)
	{
		$this->seleniumUrl = $seleniumUrl;
	}

	/**
	 * @return RemoteWebDriver
	 */
	public function createWebDriver()
	{
		return RemoteWebDriver::create($this->seleniumUrl, DesiredCapabilities::firefox());
	}
}
