<?php
// Debug script to check city translations in database
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

echo "<h1>Debug City Translations</h1>";

try {
    $em = $container->get('doctrine.orm.entity_manager');
    $languageService = $container->get('service.language');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    echo "<h2>Language Objects:</h2>";
    echo "<p>English Language ID: " . $englishLanguage->getId() . "</p>";
    echo "<p>Czech Language ID: " . $czechLanguage->getId() . "</p>";
    
    // Check a few specific cities directly from database
    $testCityNames = ['Praha', 'Warszawa', 'Moskva', 'Roma', 'Venezia'];
    
    echo "<h2>Direct Database Query for City Translations:</h2>";
    
    foreach ($testCityNames as $cityName) {
        echo "<h3>City: $cityName</h3>";
        
        // Find the city
        $city = $em->getRepository('AppBundle:City')->createQueryBuilder('c')
            ->join('c.name', 'n')
            ->join('n.strings', 's')
            ->where('s.string = :cityName')
            ->setParameter('cityName', $cityName)
            ->getQuery()
            ->getOneOrNullResult();
            
        if ($city) {
            echo "<p>Found city with ID: " . $city->getId() . "</p>";
            
            // Get the LanguageString object
            $nameObject = $city->getName();
            echo "<p>LanguageString class: " . get_class($nameObject) . "</p>";
            
            // Try to get all strings
            $allStrings = $nameObject->getAllLanguagesStrings();
            echo "<p>All language strings: " . print_r($allStrings, true) . "</p>";
            
            // Try specific languages
            $czechName = $nameObject->getString($czechLanguage);
            $englishName = $nameObject->getString($englishLanguage);
            
            echo "<p>Czech name: '$czechName'</p>";
            echo "<p>English name: '$englishName'</p>";
            
            // Check if they're the same
            if ($czechName === $englishName) {
                echo "<p>❌ <span style='color: red;'>Names are identical - English translation not set!</span></p>";
            } else {
                echo "<p>✅ <span style='color: green;'>Different names found!</span></p>";
            }
        } else {
            echo "<p>❌ City not found!</p>";
        }
        echo "<hr>";
    }
    
    echo "<h2>Check LanguageString Table Structure:</h2>";
    
    // Let's check the actual database structure
    $connection = $em->getConnection();
    
    // Check if language_strings table exists and its structure
    try {
        $result = $connection->executeQuery("DESCRIBE language_strings");
        echo "<h3>language_strings table structure:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetchAssociative()) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p>Error checking table structure: " . $e->getMessage() . "</p>";
    }
    
    // Check some actual data
    try {
        $result = $connection->executeQuery("SELECT * FROM language_strings WHERE language_string_id IN (SELECT name_id FROM cities WHERE city_id IN (921, 774, 615)) LIMIT 10");
        echo "<h3>Sample language_strings data:</h3>";
        echo "<table border='1'>";
        echo "<tr><th>language_string_id</th><th>language_id</th><th>string</th></tr>";
        while ($row = $result->fetchAssociative()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['language_string_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['language_id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['string']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } catch (Exception $e) {
        echo "<p>Error checking language strings data: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 