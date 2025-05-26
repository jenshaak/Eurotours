<?php
// Test file to check countries and cities data
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

echo "<h1>Countries and Cities Debug</h1>";

try {
    // Get the same services that the SearchFormWidget uses
    $countryService = $container->get('service.country');
    $cityService = $container->get('service.city');
    
    echo "<h2>Country Service Test:</h2>";
    $countries = $countryService->findAllCountries();
    echo "<p>Found " . count($countries) . " countries</p>";
    
    foreach ($countries as $country) {
        $languageService = $container->get('service.language');
        echo "<h3>Country: " . $country->getName()->getString($languageService->getCurrentLanguage()) . "</h3>";
        echo "<p>Cities count: " . count($country->getCities()) . "</p>";
        echo "<p>Active cities count: " . count($country->getActiveCities()) . "</p>";
        
        if (count($country->getCities()) > 0) {
            echo "<h4>All Cities:</h4>";
            foreach ($country->getCities() as $city) {
                echo "<p>- " . $city->getName()->getString($languageService->getCurrentLanguage()) . " (ID: " . $city->getId() . ", Active: " . (!$city->isDeleted() ? 'Yes' : 'No') . ")</p>";
            }
        }
        
        if (count($country->getActiveCities()) > 0) {
            echo "<h4>Active Cities:</h4>";
            foreach ($country->getActiveCities() as $city) {
                echo "<p>- " . $city->getName()->getString($languageService->getCurrentLanguage()) . " (ID: " . $city->getId() . ")</p>";
            }
        }
    }
    
    echo "<h2>Prague City Test:</h2>";
    $pragueCity = $cityService->getPragueCity();
    if ($pragueCity) {
        echo "<p>Prague found: " . $pragueCity->getName()->getString($languageService->getCurrentLanguage()) . " (ID: " . $pragueCity->getId() . ")</p>";
    } else {
        echo "<p>Prague city not found!</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 