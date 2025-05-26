<?php
// Test file to debug search form widget issues
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();

// Boot the kernel and get container
$kernel->boot();
$container = $kernel->getContainer();

// Set the request on the container
$container->get('request_stack')->push($request);

// Get the search form widget
$searchFormWidget = $container->get('widget.frontend.searchForm');

echo "<h1>Search Form Widget Debug</h1>";

try {
    $widgetHtml = $searchFormWidget->fetch('home');
    echo "<h2>Widget HTML:</h2>";
    echo "<pre>" . htmlspecialchars($widgetHtml) . "</pre>";
    
    echo "<h2>Rendered Widget:</h2>";
    echo $widgetHtml;
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 