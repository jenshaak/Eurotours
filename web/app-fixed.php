<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

umask(0000);

// Ensure no output before this point
if (ob_get_level()) {
    ob_end_clean();
}

/**
 * @var Composer\Autoload\ClassLoader
 */
$loader = require __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

date_default_timezone_set("Europe/Prague");

ini_set("memory_limit", "1G");

// Determine environment
$isDevEnvironment = (
	(isset($_SERVER['SYMFONY_ENV']) and $_SERVER['SYMFONY_ENV'] === "dev")
	||
	(isset($_SERVER['SERVER_SOFTWARE']) and preg_match("~Symfony Local Server~", $_SERVER['SERVER_SOFTWARE']))
	||
	(($_ENV['SYMFONY_ENV'] ?? null) === "dev")
);

if ($isDevEnvironment) {
	Debug::enable();
	$kernel = new AppKernel('dev', true);
	$kernel->loadClassCache();
} else {
	include_once __DIR__.'/../var/bootstrap.php.cache';
	$kernel = new AppKernel('prod', false);
	$kernel->loadClassCache();
}

try {
	// Boot the kernel first to get the container
	$kernel->boot();
	$container = $kernel->getContainer();
	
	// Create request
	$request = Request::createFromGlobals();
	
	// Get the session service from the container and set it on the request
	if ($container->has('session')) {
		$session = $container->get('session');
		$request->setSession($session);
	}
	
	// Handle the request
	$response = $kernel->handle($request);
	$response->send();
	$kernel->terminate($request, $response);
	
} catch (\Exception $e) {
	// In development, show the error
	if ($isDevEnvironment) {
		throw $e;
	} else {
		// In production, log the error and show a generic error page
		error_log('Application error: ' . $e->getMessage());
		http_response_code(500);
		echo 'An error occurred. Please try again later.';
	}
} 