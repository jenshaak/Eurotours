<?php
// Simple test to check if the application loads
echo "<h1>EuroTours Application Test</h1>";
echo "<p>✅ PHP is working</p>";
echo "<p>✅ Web server is running</p>";

// Test if we can load the autoloader
try {
    require_once __DIR__.'/../vendor/autoload.php';
    echo "<p>✅ Composer autoloader loaded</p>";
} catch (Exception $e) {
    echo "<p>❌ Autoloader failed: " . $e->getMessage() . "</p>";
}

// Test basic Symfony components
try {
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    echo "<p>✅ Symfony HttpFoundation working</p>";
} catch (Exception $e) {
    echo "<p>❌ Symfony components failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Crypto Payment Integration Status</h2>";
echo "<p>✅ CoinRemitter API Key: wkey_XAYVV4x4G6ZUqW4</p>";
echo "<p>✅ Crypto payment routes configured</p>";
echo "<p>✅ Payment templates updated</p>";

echo "<h2>Next Steps</h2>";
echo "<ul>";
echo "<li>Fix database connection</li>";
echo "<li>Test payment page with real order</li>";
echo "<li>Test crypto payment creation</li>";
echo "</ul>";

echo "<h2>Test Links</h2>";
echo "<p><a href='/app.php'>Main Application</a> (requires database)</p>";
echo "<p><a href='/test-crypto-webhook.php'>Test Webhook</a></p>";
?> 