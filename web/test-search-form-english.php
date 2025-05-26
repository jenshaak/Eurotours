<?php
// Test search form with English language
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

echo "<h1>Search Form English Test</h1>";

try {
    $languageService = $container->get('service.language');
    $englishLanguage = $languageService->getEnglish();
    
    // Set language to English
    $languageService->setCurrentLanguage($englishLanguage);
    
    echo "<p>Current language set to: <strong>" . $languageService->getCurrentLanguage()->getId() . "</strong></p>";
    
    // Get the search form widget
    $searchFormWidget = $container->get('widget.frontend.searchForm');
    $searchFormHtml = $searchFormWidget->fetch();
    
    echo "<h2>Testing for English City Names in Search Form:</h2>";
    
    // Check for specific English translations
    $englishCities = [
        'Prague' => 'Praha',
        'Warsaw' => 'Warszawa', 
        'Moscow' => 'Moskva',
        'Rome' => 'Roma',
        'Venice' => 'Venezia',
        'Munich' => 'Munchen',
        'Cologne' => 'Koln',
        'Copenhagen' => 'Kobenhavn',
        'Belgrade' => 'Beograd',
        'Bucharest' => 'Bucuresti'
    ];
    
    $foundEnglish = [];
    $foundOriginal = [];
    
    foreach ($englishCities as $english => $original) {
        if (strpos($searchFormHtml, $english) !== false) {
            $foundEnglish[] = $english;
        }
        if (strpos($searchFormHtml, $original) !== false) {
            $foundOriginal[] = $original;
        }
    }
    
    echo "<p><strong>English city names found:</strong> " . implode(', ', $foundEnglish) . "</p>";
    echo "<p><strong>Original city names found:</strong> " . implode(', ', $foundOriginal) . "</p>";
    
    if (count($foundEnglish) > 0) {
        echo "<p>✅ <span style='color: green;'>SUCCESS! English city names are appearing in the search form!</span></p>";
        echo "<p>Found " . count($foundEnglish) . " English city names out of " . count($englishCities) . " tested.</p>";
    } else {
        echo "<p>❌ <span style='color: red;'>No English city names found in search form.</span></p>";
    }
    
    if (count($foundOriginal) > 0) {
        echo "<p>⚠️ <span style='color: orange;'>Warning: Still found " . count($foundOriginal) . " original city names. This might indicate mixed language display.</span></p>";
    }
    
    echo "<h2>Sample of Search Form HTML (first 2000 chars):</h2>";
    echo "<pre>" . htmlspecialchars(substr($searchFormHtml, 0, 2000)) . "...</pre>";
    
    echo "<p><a href='/' target='_blank'>Open Homepage</a> to test the search form manually!</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 