<?php
// Test Prague as default "from" city
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

echo "<h1>Prague Default Test</h1>";

try {
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    // Set language to English
    $languageService->setCurrentLanguage($englishLanguage);
    
    echo "<h2>Testing Prague City:</h2>";
    
    // Test getPragueCity method
    $pragueCity = $cityService->getPragueCity();
    if ($pragueCity) {
        echo "<p>✅ Prague found via getPragueCity():</p>";
        echo "<p>  ID: " . $pragueCity->getId() . "</p>";
        echo "<p>  Czech name: '" . $pragueCity->getName()->getString($czechLanguage) . "'</p>";
        echo "<p>  English name: '" . $pragueCity->getName()->getString($englishLanguage) . "'</p>";
    } else {
        echo "<p>❌ Prague not found via getPragueCity()</p>";
    }
    
    echo "<h2>Testing Search Form Widget:</h2>";
    
    // Get the search form widget
    $searchFormWidget = $container->get('widget.frontend.searchForm');
    $searchFormHtml = $searchFormWidget->fetch();
    
    // Check if Prague is selected by default
    if (strpos($searchFormHtml, 'value="41" selected') !== false) {
        echo "<p>✅ Prague (ID 41) is selected by default in the search form!</p>";
    } else {
        echo "<p>❌ Prague is not selected by default in the search form.</p>";
    }
    
    // Check for Prague in the form
    if (strpos($searchFormHtml, 'Prague') !== false) {
        echo "<p>✅ 'Prague' text found in search form</p>";
    } else {
        echo "<p>❌ 'Prague' text not found in search form</p>";
    }
    
    if (strpos($searchFormHtml, 'Praha') !== false) {
        echo "<p>✅ 'Praha' text found in search form</p>";
    } else {
        echo "<p>❌ 'Praha' text not found in search form</p>";
    }
    
    echo "<h2>Search Form HTML Sample (first 1500 chars):</h2>";
    echo "<pre>" . htmlspecialchars(substr($searchFormHtml, 0, 1500)) . "...</pre>";
    
    echo "<h2>Looking for 'selected' in form:</h2>";
    preg_match_all('/value="(\d+)"[^>]*selected/', $searchFormHtml, $matches);
    if (!empty($matches[1])) {
        echo "<p>Selected city IDs found: " . implode(', ', $matches[1]) . "</p>";
        
        foreach ($matches[1] as $cityId) {
            $city = $cityService->getCity($cityId);
            if ($city) {
                echo "<p>  City ID $cityId: " . $city->getName()->getString($englishLanguage) . "</p>";
            }
        }
    } else {
        echo "<p>No selected cities found in form</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 