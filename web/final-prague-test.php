<?php
// Final comprehensive test for Prague default and English translations
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

echo "<h1>🎉 Final Prague Default & English Translation Test</h1>";

try {
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    // Set language to English
    $languageService->setCurrentLanguage($englishLanguage);
    
    echo "<h2>✅ Test 1: Prague City Service</h2>";
    
    $pragueCity = $cityService->getPragueCity();
    if ($pragueCity) {
        $czechName = $pragueCity->getName()->getString($czechLanguage);
        $englishName = $pragueCity->getName()->getString($englishLanguage);
        
        echo "<p>✅ <strong>Prague found via getPragueCity():</strong></p>";
        echo "<p>  ID: <strong>" . $pragueCity->getId() . "</strong></p>";
        echo "<p>  Czech name: '<strong>$czechName</strong>'</p>";
        echo "<p>  English name: '<strong>$englishName</strong>'</p>";
        echo "<p>  Country: " . $pragueCity->getCountry()->getName()->getString($englishLanguage) . "</p>";
        
        if ($pragueCity->getId() == 921 && $czechName == 'Praha' && $englishName == 'Prague') {
            echo "<p>✅ <span style='color: green;'>Prague service test PASSED!</span></p>";
        } else {
            echo "<p>❌ <span style='color: red;'>Prague service test FAILED!</span></p>";
        }
    } else {
        echo "<p>❌ Prague not found via getPragueCity()</p>";
    }
    
    echo "<h2>✅ Test 2: Search Form Default Selection</h2>";
    
    // Get the search form widget
    $searchFormWidget = $container->get('widget.frontend.searchForm');
    $searchFormHtml = $searchFormWidget->fetch();
    
    // Check if Prague is selected by default
    if (strpos($searchFormHtml, 'value="921" selected') !== false) {
        echo "<p>✅ <span style='color: green;'>Prague (ID 921) is selected by default in the search form!</span></p>";
    } else {
        echo "<p>❌ <span style='color: red;'>Prague is not selected by default in the search form.</span></p>";
    }
    
    echo "<h2>✅ Test 3: English City Names in Form</h2>";
    
    // Check for English city names
    $englishCities = ['Prague', 'Warsaw', 'Moscow', 'Rome', 'Venice', 'Munich', 'Cologne'];
    $foundEnglish = [];
    
    foreach ($englishCities as $cityName) {
        if (strpos($searchFormHtml, $cityName) !== false) {
            $foundEnglish[] = $cityName;
        }
    }
    
    echo "<p><strong>English city names found:</strong> " . implode(', ', $foundEnglish) . "</p>";
    echo "<p><strong>Score:</strong> " . count($foundEnglish) . "/" . count($englishCities) . "</p>";
    
    if (count($foundEnglish) >= 5) {
        echo "<p>✅ <span style='color: green;'>English translations test PASSED!</span></p>";
    } else {
        echo "<p>❌ <span style='color: red;'>English translations test FAILED!</span></p>";
    }
    
    echo "<h2>✅ Test 4: Form Structure Validation</h2>";
    
    // Check form structure
    $hasFromSelect = strpos($searchFormHtml, 'id="searchFrom"') !== false;
    $hasToSelect = strpos($searchFormHtml, 'id="searchTo"') !== false;
    $hasDateInput = strpos($searchFormHtml, 'id="searchDay"') !== false;
    $hasSubmitButton = strpos($searchFormHtml, 'type="submit"') !== false;
    
    echo "<p>From dropdown: " . ($hasFromSelect ? "✅" : "❌") . "</p>";
    echo "<p>To dropdown: " . ($hasToSelect ? "✅" : "❌") . "</p>";
    echo "<p>Date input: " . ($hasDateInput ? "✅" : "❌") . "</p>";
    echo "<p>Submit button: " . ($hasSubmitButton ? "✅" : "❌") . "</p>";
    
    if ($hasFromSelect && $hasToSelect && $hasDateInput && $hasSubmitButton) {
        echo "<p>✅ <span style='color: green;'>Form structure test PASSED!</span></p>";
    } else {
        echo "<p>❌ <span style='color: red;'>Form structure test FAILED!</span></p>";
    }
    
    echo "<h2>🎯 Overall Results</h2>";
    
    $allTestsPassed = ($pragueCity && $pragueCity->getId() == 921) && 
                     (strpos($searchFormHtml, 'value="921" selected') !== false) &&
                     (count($foundEnglish) >= 5) &&
                     ($hasFromSelect && $hasToSelect && $hasDateInput && $hasSubmitButton);
    
    if ($allTestsPassed) {
        echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>🎉 ALL TESTS PASSED!</h3>";
        echo "<p><strong>✅ Prague is now set as the default 'from' city</strong></p>";
        echo "<p><strong>✅ English city names are displaying correctly</strong></p>";
        echo "<p><strong>✅ Search form is fully functional</strong></p>";
        echo "<p><strong>✅ Users will see 'Prague' as the default starting point</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>❌ Some tests failed</h3>";
        echo "<p>Please check the individual test results above.</p>";
        echo "</div>";
    }
    
    echo "<p><a href='/' target='_blank' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Test the Live Application</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 