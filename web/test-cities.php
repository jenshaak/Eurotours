<?php
// Test file to check cities and tables
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

echo "<h1>Database Tables and Cities Debug</h1>";

try {
    $em = $container->get('doctrine.orm.entity_manager');
    $connection = $em->getConnection();
    
    // Show all tables
    echo "<h2>All Tables:</h2>";
    $tables = $connection->getSchemaManager()->listTableNames();
    echo "<pre>";
    foreach ($tables as $table) {
        echo "- $table\n";
    }
    echo "</pre>";
    
    // Check if cities table exists and has data
    if (in_array('cities', $tables)) {
        echo "<h2>Cities Table Data:</h2>";
        $stmt = $connection->query('SELECT * FROM cities LIMIT 10');
        $cities = $stmt->fetchAll();
        echo "<pre>" . print_r($cities, true) . "</pre>";
    } else {
        echo "<h2>Cities table does not exist!</h2>";
        
        // Check for similar table names
        echo "<h3>Looking for similar tables:</h3>";
        foreach ($tables as $table) {
            if (strpos(strtolower($table), 'city') !== false || strpos(strtolower($table), 'place') !== false || strpos(strtolower($table), 'location') !== false) {
                echo "<p>Found similar table: <strong>$table</strong></p>";
                
                // Show structure
                $stmt = $connection->query("DESCRIBE $table");
                $structure = $stmt->fetchAll();
                echo "<pre>" . print_r($structure, true) . "</pre>";
                
                // Show sample data
                $stmt = $connection->query("SELECT * FROM $table LIMIT 5");
                $data = $stmt->fetchAll();
                echo "<pre>" . print_r($data, true) . "</pre>";
            }
        }
    }
    
} catch (Exception $e) {
    echo "<h2>Error:</h2>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 