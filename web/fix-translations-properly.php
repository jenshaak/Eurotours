<?php
// Properly fix English translations using the correct approach
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;
use AppBundle\VO\LanguageString;

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();

// Boot the kernel and get container
$kernel->boot();
$container = $kernel->getContainer();

// Set the request on the container
$container->get('request_stack')->push($request);

echo "<h1>Fix English Translations - Proper Approach</h1>";

try {
    $em = $container->get('doctrine.orm.entity_manager');
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    echo "<p>English Language ID: " . $englishLanguage->getId() . "</p>";
    echo "<p>Czech Language ID: " . $czechLanguage->getId() . "</p>";
    
    // Define English translations for cities that need them
    $englishTranslations = [
        // Czech cities
        'Praha' => 'Prague',
        'Brno' => 'Brno',
        'Ostrava' => 'Ostrava',
        'Plzeň' => 'Pilsen',
        
        // German cities
        'Munchen' => 'Munich',
        'Koln' => 'Cologne',
        'Nurnberg' => 'Nuremberg',
        'Dusseldorf' => 'Düsseldorf',
        'Wurzburg' => 'Würzburg',
        
        // Italian cities
        'Venezia' => 'Venice',
        'Firenze' => 'Florence',
        'Roma' => 'Rome',
        'Napoli' => 'Naples',
        'Milano' => 'Milan',
        'Torino' => 'Turin',
        
        // Polish cities
        'Warszawa' => 'Warsaw',
        'Krakow' => 'Kraków',
        'Lodz' => 'Łódź',
        'Wroclaw' => 'Wrocław',
        'Poznan' => 'Poznań',
        'Gdansk' => 'Gdańsk',
        
        // Ukrainian cities
        'Kiev' => 'Kyiv',
        'Lvov' => 'Lviv',
        'Kharkiv' => 'Kharkiv',
        'Odesa' => 'Odesa',
        'Dnipro' => 'Dnipro',
        
        // Russian cities
        'Moskva' => 'Moscow',
        'St. Peterburg' => 'Saint Petersburg',
        'Kaliningrad' => 'Kaliningrad',
        
        // Other cities
        'Kobenhavn' => 'Copenhagen',
        'Arhus' => 'Aarhus',
        'Beograd' => 'Belgrade',
        'Bucuresti' => 'Bucharest',
        'Bratislava' => 'Bratislava',
        'Sevilla' => 'Seville'
    ];
    
    // Get all cities
    $cities = $cityService->findAllCities();
    $updatedCount = 0;
    $totalCount = count($cities);
    
    echo "<h2>Processing $totalCount cities with proper approach...</h2>";
    
    foreach ($cities as $city) {
        $currentCzechName = $city->getName()->getString($czechLanguage);
        
        // Check if we have an English translation for this city
        if (isset($englishTranslations[$currentCzechName])) {
            $englishName = $englishTranslations[$currentCzechName];
            
            echo "<p>Processing: <strong>$currentCzechName</strong> → <strong>$englishName</strong></p>";
            
            // Create a new LanguageString object with both languages
            $newLanguageString = new LanguageString();
            $newLanguageString->setString($czechLanguage, $currentCzechName);
            $newLanguageString->setString($englishLanguage, $englishName);
            
            // Replace the entire name object
            $reflection = new ReflectionClass($city);
            $nameProperty = $reflection->getProperty('name');
            $nameProperty->setAccessible(true);
            $nameProperty->setValue($city, $newLanguageString);
            
            // Mark the entity as dirty
            $em->persist($city);
            
            $updatedCount++;
            
            // Flush every 10 updates to ensure they're saved
            if ($updatedCount % 10 == 0) {
                $em->flush();
                echo "<p><em>✅ Flushed $updatedCount updates to database...</em></p>";
            }
        }
    }
    
    // Final flush for any remaining updates
    $em->flush();
    
    echo "<h2>✅ English Translation Update Complete!</h2>";
    echo "<p><strong>Cities updated:</strong> $updatedCount</p>";
    echo "<p><strong>Total cities processed:</strong> $totalCount</p>";
    
    // Clear entity manager to force fresh data from database
    $em->clear();
    
    // Verification test with fresh data
    echo "<h2>Verification Test (Fresh from Database):</h2>";
    $testCities = ['Praha', 'Warszawa', 'Moskva', 'Roma', 'Venezia'];
    
    foreach ($testCities as $testCityName) {
        // Find city fresh from database
        $cityRepo = $em->getRepository('AppBundle:City');
        $cities = $cityRepo->findAll();
        
        foreach ($cities as $city) {
            if ($city->getName()->getString($czechLanguage) === $testCityName) {
                $englishName = $city->getName()->getString($englishLanguage);
                $czechName = $city->getName()->getString($czechLanguage);
                
                echo "<p><strong>$testCityName:</strong></p>";
                echo "<p>  Czech: '$czechName'</p>";
                echo "<p>  English: '$englishName'</p>";
                
                if (isset($englishTranslations[$testCityName]) && $englishName === $englishTranslations[$testCityName]) {
                    echo "<p>  ✅ <span style='color: green;'>Translation correct!</span></p>";
                } else {
                    echo "<p>  ❌ <span style='color: red;'>Translation incorrect!</span></p>";
                }
                break;
            }
        }
    }
    
    echo "<p><a href='/'>Go to Homepage</a> to test the search form with English names!</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 