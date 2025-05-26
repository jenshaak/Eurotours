<?php
// Direct database test to check what's stored
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

echo "<h1>Direct Database Test</h1>";

try {
    $em = $container->get('doctrine.orm.entity_manager');
    $connection = $em->getConnection();
    
    // Check what's actually stored in the cities table
    echo "<h2>Direct Database Query - Cities Table:</h2>";
    
    $result = $connection->executeQuery("SELECT city_id, name FROM cities WHERE city_id IN (921, 774, 615, 521, 163) ORDER BY city_id");
    
    echo "<table border='1'>";
    echo "<tr><th>City ID</th><th>Name (Serialized)</th><th>Unserialized</th></tr>";
    
    while ($row = $result->fetchAssociative()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['city_id']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($row['name'], 0, 100)) . "...</td>";
        
        // Try to unserialize the name
        try {
            $nameObject = unserialize($row['name']);
            if ($nameObject && is_object($nameObject)) {
                echo "<td>Object type: " . get_class($nameObject) . "</td>";
            } else {
                echo "<td>Failed to unserialize or not an object</td>";
            }
        } catch (Exception $e) {
            echo "<td>Error: " . htmlspecialchars($e->getMessage()) . "</td>";
        }
        
        echo "</tr>";
    }
    echo "</table>";
    
    // Test manual update of one city
    echo "<h2>Manual Update Test:</h2>";
    
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    // Get Praha (ID 921)
    $praha = $cityService->getCity(921);
    if ($praha) {
        echo "<p>Before manual update:</p>";
        echo "<p>Czech: " . $praha->getName()->getString($czechLanguage) . "</p>";
        echo "<p>English: " . $praha->getName()->getString($englishLanguage) . "</p>";
        
        // Manually set English name
        $praha->getName()->setString($englishLanguage, 'Prague (Manual Test)');
        
        // Save using entity manager directly
        $em->persist($praha);
        $em->flush();
        
        echo "<p>After manual update and flush:</p>";
        echo "<p>Czech: " . $praha->getName()->getString($czechLanguage) . "</p>";
        echo "<p>English: " . $praha->getName()->getString($englishLanguage) . "</p>";
        
        // Check database again
        $result2 = $connection->executeQuery("SELECT name FROM cities WHERE city_id = 921");
        $row2 = $result2->fetchAssociative();
        echo "<p>Raw database value after update: " . htmlspecialchars(substr($row2['name'], 0, 200)) . "...</p>";
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 