<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

umask(0000);

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';

date_default_timezone_set("Europe/Prague");

ini_set("memory_limit", "1G");
ini_set("session.gc_maxlifetime", 60*60*24*7);
ini_set("session.cookie_lifetime", 60*60*24*7);

if (
	(isset($_SERVER['SYMFONY_ENV']) and $_SERVER['SYMFONY_ENV'] === "dev")
	||
	(isset($_SERVER['SERVER_SOFTWARE']) and preg_match("~Symfony Local Server~", $_SERVER['SERVER_SOFTWARE']))
	||
	(($_ENV['SYMFONY_ENV'] ?? null) === "dev")
) {
	Debug::enable();
	$kernel = new AppKernel('dev', true);
	$kernel->loadClassCache();
} else {
	include_once __DIR__.'/../var/bootstrap.php.cache';
	$kernel = new AppKernel('prod', false);
	$kernel->loadClassCache();
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

$kernel->terminate($request, $response);
