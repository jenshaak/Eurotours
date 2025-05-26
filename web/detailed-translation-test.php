<?php
// Detailed translation test
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

echo "<h1>Detailed Translation Test</h1>";

try {
    $em = $container->get('doctrine.orm.entity_manager');
    $connection = $em->getConnection();
    
    // Check specific cities that should have English translations
    $testCities = [
        921 => ['original' => 'Praha', 'expected' => 'Prague'],
        774 => ['original' => 'Lvov', 'expected' => 'Lviv'],
        615 => ['original' => 'Moskva', 'expected' => 'Moscow'],
        521 => ['original' => 'Bucuresti', 'expected' => 'Bucharest'],
        163 => ['original' => 'Nijmegen', 'expected' => 'Nijmegen'] // This one shouldn't have changed
    ];
    
    echo "<h2>Detailed Analysis of Serialized Data:</h2>";
    
    foreach ($testCities as $cityId => $info) {
        echo "<h3>City ID: $cityId ({$info['original']} → {$info['expected']})</h3>";
        
        $result = $connection->executeQuery("SELECT name FROM cities WHERE city_id = ?", [$cityId]);
        $row = $result->fetchAssociative();
        
        if ($row) {
            $serializedData = $row['name'];
            echo "<p><strong>Raw serialized data:</strong></p>";
            echo "<pre>" . htmlspecialchars($serializedData) . "</pre>";
            
            // Unserialize and examine
            try {
                $nameObject = unserialize($serializedData);
                if ($nameObject && is_object($nameObject)) {
                    echo "<p><strong>Unserialized object type:</strong> " . get_class($nameObject) . "</p>";
                    
                    // Try to access the internal strings array using reflection
                    $reflection = new ReflectionClass($nameObject);
                    $properties = $reflection->getProperties();
                    
                    foreach ($properties as $property) {
                        $property->setAccessible(true);
                        $value = $property->getValue($nameObject);
                        echo "<p><strong>Property '{$property->getName()}':</strong> " . print_r($value, true) . "</p>";
                    }
                }
            } catch (Exception $e) {
                echo "<p>Error unserializing: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
        echo "<hr>";
    }
    
    // Now test using the service layer
    echo "<h2>Service Layer Test:</h2>";
    
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    foreach ($testCities as $cityId => $info) {
        $city = $cityService->getCity($cityId);
        if ($city) {
            $czechName = $city->getName()->getString($czechLanguage);
            $englishName = $city->getName()->getString($englishLanguage);
            
            echo "<p><strong>City ID $cityId:</strong></p>";
            echo "<p>  Czech: '$czechName'</p>";
            echo "<p>  English: '$englishName'</p>";
            echo "<p>  Expected English: '{$info['expected']}'</p>";
            
            if ($englishName === $info['expected']) {
                echo "<p>  ✅ <span style='color: green;'>Translation correct!</span></p>";
            } else {
                echo "<p>  ❌ <span style='color: red;'>Translation incorrect!</span></p>";
            }
        }
        echo "<hr>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 