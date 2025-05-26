<?php
// Test file to check English city names
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

echo "<h1>English City Names Test</h1>";

try {
    // Get services
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    echo "<h2>Current Language Settings:</h2>";
    $currentLanguage = $languageService->getCurrentLanguage();
    echo "<p>Current language: <strong>" . $currentLanguage->getId() . "</strong> (" . $currentLanguage->getName() . ")</p>";
    
    echo "<h2>Setting Language to English:</h2>";
    $languageService->setCurrentLanguage($englishLanguage);
    $currentLanguage = $languageService->getCurrentLanguage();
    echo "<p>New current language: <strong>" . $currentLanguage->getId() . "</strong> (" . $currentLanguage->getName() . ")</p>";
    
    echo "<h2>Testing Specific Cities with English Names:</h2>";
    
    // Test some specific cities that should have English translations
    $testCities = [
        'Praha' => 'Prague',
        'Warszawa' => 'Warsaw', 
        'Moskva' => 'Moscow',
        'Roma' => 'Rome',
        'Venezia' => 'Venice',
        'Munchen' => 'Munich',
        'Koln' => 'Cologne',
        'Kiev' => 'Kyiv',
        'Lvov' => 'Lviv',
        'Bucuresti' => 'Bucharest'
    ];
    
    $cities = $cityService->findAllCities();
    
    foreach ($testCities as $originalName => $expectedEnglish) {
        foreach ($cities as $city) {
            $czechName = $city->getName()->getString($czechLanguage);
            if ($czechName === $originalName) {
                $englishName = $city->getName()->getString($englishLanguage);
                echo "<p><strong>$originalName:</strong></p>";
                echo "<p>  Czech: $czechName</p>";
                echo "<p>  English: $englishName</p>";
                echo "<p>  Expected: $expectedEnglish</p>";
                if ($englishName === $expectedEnglish) {
                    echo "<p>  ✅ <span style='color: green;'>Correct English translation!</span></p>";
                } else {
                    echo "<p>  ❌ <span style='color: red;'>Translation mismatch!</span></p>";
                }
                echo "<hr>";
                break;
            }
        }
    }
    
    echo "<h2>Testing Search Form Widget with English Language:</h2>";
    
    // Test the search form widget with English language
    $searchFormWidget = $container->get('widget.frontend.searchForm');
    $searchFormHtml = $searchFormWidget->fetch();
    
    // Check if we can find some English city names in the HTML
    $englishCities = ['Prague', 'Warsaw', 'Moscow', 'Rome', 'Venice', 'Munich'];
    $foundEnglishCities = [];
    
    foreach ($englishCities as $englishCity) {
        if (strpos($searchFormHtml, $englishCity) !== false) {
            $foundEnglishCities[] = $englishCity;
        }
    }
    
    echo "<p>English cities found in search form: <strong>" . implode(', ', $foundEnglishCities) . "</strong></p>";
    
    if (count($foundEnglishCities) > 0) {
        echo "<p>✅ <span style='color: green;'>English city names are working in the search form!</span></p>";
    } else {
        echo "<p>❌ <span style='color: red;'>English city names not found in search form.</span></p>";
    }
    
    echo "<h2>Sample of Search Form HTML (first 1000 chars):</h2>";
    echo "<pre>" . htmlspecialchars(substr($searchFormHtml, 0, 1000)) . "...</pre>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 