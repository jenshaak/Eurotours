#!/usr/bin/env php
<?php

use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;

require __DIR__.'/../vendor/autoload.php';


$host = "http://localhost:4444/wd/hub";
$driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
$driver->get("http://www.seznam.cz");
var_dump($driver->findElement(WebDriverBy::className("article__title"))->getText());