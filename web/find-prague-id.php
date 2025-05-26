<?php
// Find the correct ID for Prague/Praha
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

echo "<h1>Find Prague ID</h1>";

try {
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    echo "<h2>Searching for Prague/Praha in all cities:</h2>";
    
    $cities = $cityService->findAllCities();
    $pragueFound = false;
    
    foreach ($cities as $city) {
        $czechName = $city->getName()->getString($czechLanguage);
        $englishName = $city->getName()->getString($englishLanguage);
        
        // Look for Prague or Praha
        if (stripos($czechName, 'Praha') !== false || stripos($englishName, 'Prague') !== false) {
            echo "<p>✅ Found Prague:</p>";
            echo "<p>  ID: <strong>" . $city->getId() . "</strong></p>";
            echo "<p>  Czech name: '" . $czechName . "'</p>";
            echo "<p>  English name: '" . $englishName . "'</p>";
            echo "<p>  Country: " . $city->getCountry()->getName()->getString($englishLanguage) . "</p>";
            echo "<hr>";
            $pragueFound = true;
        }
    }
    
    if (!$pragueFound) {
        echo "<p>❌ No city with 'Praha' or 'Prague' found!</p>";
        
        echo "<h2>Let's check what city ID 41 actually is:</h2>";
        $city41 = $cityService->getCity(41);
        if ($city41) {
            echo "<p>City ID 41:</p>";
            echo "<p>  Czech name: '" . $city41->getName()->getString($czechLanguage) . "'</p>";
            echo "<p>  English name: '" . $city41->getName()->getString($englishLanguage) . "'</p>";
            echo "<p>  Country: " . $city41->getCountry()->getName()->getString($englishLanguage) . "</p>";
        }
        
        echo "<h2>Let's check some cities around ID 41:</h2>";
        for ($i = 35; $i <= 50; $i++) {
            $city = $cityService->getCity($i);
            if ($city) {
                $czechName = $city->getName()->getString($czechLanguage);
                $englishName = $city->getName()->getString($englishLanguage);
                echo "<p>ID $i: '$czechName' / '$englishName' (" . $city->getCountry()->getName()->getString($englishLanguage) . ")</p>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 