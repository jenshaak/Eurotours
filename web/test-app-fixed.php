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
$isDevEnvironment = true;

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
	$request = Request::createFromGlobals();
	$response = $kernel->handle($request);
	$response->send();
	$kernel->terminate($request, $response);
} catch (\Exception $e) {
	// Show detailed error information
	echo "<h1>🔧 Application Test Results</h1>";
	echo "<h2>❌ Error Occurred</h2>";
	echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
	echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
	echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
	echo "<h3>Stack Trace:</h3>";
	echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
	
	echo "<h2>🔍 Debugging Information</h2>";
	echo "<p>This test helps identify what's still causing issues with the main application.</p>";
} 