<?php
// Simple debug script to check city names
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

echo "<h1>Simple City Names Debug</h1>";

try {
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    echo "<h2>Language IDs:</h2>";
    echo "<p>English: " . $englishLanguage->getId() . "</p>";
    echo "<p>Czech: " . $czechLanguage->getId() . "</p>";
    
    // Get a few specific cities by ID
    $testCityIds = [921, 774, 615, 521, 163]; // Praha, Lvov, Moskva, Bucuresti, Venezia
    
    echo "<h2>Testing Specific Cities:</h2>";
    
    foreach ($testCityIds as $cityId) {
        $city = $cityService->getCity($cityId);
        if ($city) {
            echo "<h3>City ID: $cityId</h3>";
            
            $czechName = $city->getName()->getString($czechLanguage);
            $englishName = $city->getName()->getString($englishLanguage);
            
            echo "<p>Czech: '$czechName'</p>";
            echo "<p>English: '$englishName'</p>";
            
            if ($czechName === $englishName) {
                echo "<p>❌ <span style='color: red;'>Same name - no English translation</span></p>";
            } else {
                echo "<p>✅ <span style='color: green;'>Different names - English translation exists</span></p>";
            }
            
            // Check all language strings
            $allStrings = $city->getName()->getAllLanguagesStrings();
            echo "<p>All strings: " . implode(', ', $allStrings) . "</p>";
            
        } else {
            echo "<p>City ID $cityId not found</p>";
        }
        echo "<hr>";
    }
    
    echo "<h2>Test Manual Translation Setting:</h2>";
    
    // Try to manually set a translation for one city
    $testCity = $cityService->getCity(921); // Praha
    if ($testCity) {
        echo "<p>Before update:</p>";
        echo "<p>Czech: " . $testCity->getName()->getString($czechLanguage) . "</p>";
        echo "<p>English: " . $testCity->getName()->getString($englishLanguage) . "</p>";
        
        // Set English name
        $testCity->getName()->setString($englishLanguage, 'Prague');
        $cityService->saveCity($testCity);
        
        echo "<p>After update:</p>";
        echo "<p>Czech: " . $testCity->getName()->getString($czechLanguage) . "</p>";
        echo "<p>English: " . $testCity->getName()->getString($englishLanguage) . "</p>";
        
        if ($testCity->getName()->getString($englishLanguage) === 'Prague') {
            echo "<p>✅ <span style='color: green;'>Manual update worked!</span></p>";
        } else {
            echo "<p>❌ <span style='color: red;'>Manual update failed!</span></p>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 