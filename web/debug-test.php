<?php
// Debug test to find where the application hangs
set_time_limit(30); // Prevent infinite hanging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug Test - Step by Step</h1>";
flush(); // Force output

echo "<p>Step 1: Basic PHP ✅</p>";
flush();

try {
    echo "<p>Step 2: Loading autoloader...</p>";
    flush();
    require_once __DIR__.'/../vendor/autoload.php';
    echo "<p>Step 2: Autoloader loaded ✅</p>";
    flush();
} catch (Exception $e) {
    echo "<p>Step 2: Autoloader failed ❌ - " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>Step 3: Testing Symfony Request...</p>";
    flush();
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    echo "<p>Step 3: Symfony Request created ✅</p>";
    flush();
} catch (Exception $e) {
    echo "<p>Step 3: Symfony Request failed ❌ - " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>Step 4: Loading AppKernel...</p>";
    flush();
    require_once __DIR__.'/../app/AppKernel.php';
    echo "<p>Step 4: AppKernel class loaded ✅</p>";
    flush();
} catch (Exception $e) {
    echo "<p>Step 4: AppKernel failed ❌ - " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>Step 5: Creating kernel instance...</p>";
    flush();
    $kernel = new AppKernel('dev', true);
    echo "<p>Step 5: Kernel instance created ✅</p>";
    flush();
} catch (Exception $e) {
    echo "<p>Step 5: Kernel creation failed ❌ - " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>Step 6: Booting kernel (this might hang)...</p>";
    flush();
    $kernel->boot();
    echo "<p>Step 6: Kernel booted ✅</p>";
    flush();
} catch (Exception $e) {
    echo "<p>Step 6: Kernel boot failed ❌ - " . $e->getMessage() . "</p>";
    exit;
}

echo "<p>🎉 All steps completed successfully!</p>";
?> 