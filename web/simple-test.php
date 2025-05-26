<?php
echo "<h1>✅ PHP is Working!</h1>";
echo "<p>Server time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP version: " . phpversion() . "</p>";
echo "<p>Memory usage: " . memory_get_usage(true) . " bytes</p>";

// Test if we can include files
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    echo "<p>✅ Composer autoload file exists</p>";
} else {
    echo "<p>❌ Composer autoload file missing</p>";
}

echo "<h2>🔗 Test Links</h2>";
echo "<a href='/simple-test.php'>Reload this page</a><br>";
echo "<a href='/test-app.php'>Test App (might hang)</a><br>";
echo "<a href='/app.php'>Main App (might hang)</a>";
?> 