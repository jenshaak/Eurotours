<?php
// Debug version of app.php with detailed error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

umask(0000);

echo "<h1>🔍 Debug App - Detailed Error Tracking</h1>";
echo "<p>Starting application bootstrap...</p>";
flush();

try {
    echo "<p>Step 1: Loading autoloader...</p>";
    flush();
    $loader = require __DIR__.'/../app/autoload.php';
    echo "<p>✅ Autoloader loaded</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ Autoloader failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    echo "<p>Step 2: Loading AppKernel...</p>";
    flush();
    require_once __DIR__.'/../app/AppKernel.php';
    echo "<p>✅ AppKernel loaded</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ AppKernel failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    echo "<p>Step 3: Setting timezone...</p>";
    flush();
    date_default_timezone_set("Europe/Prague");
    echo "<p>✅ Timezone set</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ Timezone failed: " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>Step 4: Setting memory and session limits...</p>";
    flush();
    ini_set("memory_limit", "1G");
    ini_set("session.gc_maxlifetime", 60*60*24*7);
    ini_set("session.cookie_lifetime", 60*60*24*7);
    echo "<p>✅ Limits set</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ Limits failed: " . $e->getMessage() . "</p>";
    exit;
}

try {
    echo "<p>Step 5: Enabling debug mode...</p>";
    flush();
    Debug::enable();
    echo "<p>✅ Debug enabled</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ Debug enable failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    echo "<p>Step 6: Creating kernel instance...</p>";
    flush();
    $kernel = new AppKernel('dev', true);
    echo "<p>✅ Kernel created</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ Kernel creation failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    echo "<p>Step 7: Loading class cache...</p>";
    flush();
    $kernel->loadClassCache();
    echo "<p>✅ Class cache loaded</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ Class cache failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    echo "<p>Step 8: Creating request...</p>";
    flush();
    $request = Request::createFromGlobals();
    echo "<p>✅ Request created</p>";
    flush();
} catch (Exception $e) {
    echo "<p>❌ Request creation failed: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

try {
    echo "<p>Step 9: Handling request (this is where it might fail)...</p>";
    flush();
    $response = $kernel->handle($request);
    echo "<p>✅ Request handled successfully!</p>";
    flush();
    
    echo "<p>Step 10: Sending response...</p>";
    flush();
    $response->send();
    
    echo "<p>Step 11: Terminating kernel...</p>";
    flush();
    $kernel->terminate($request, $response);
    
    echo "<p>🎉 Application completed successfully!</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Request handling failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Error Class:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Error File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre><strong>Stack Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
    
    if ($e->getPrevious()) {
        echo "<h3>Previous Exception:</h3>";
        echo "<p>" . $e->getPrevious()->getMessage() . "</p>";
        echo "<pre>" . $e->getPrevious()->getTraceAsString() . "</pre>";
    }
    exit;
} catch (Error $e) {
    echo "<p>❌ Fatal error: " . $e->getMessage() . "</p>";
    echo "<p><strong>Error File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre><strong>Stack Trace:</strong>\n" . $e->getTraceAsString() . "</pre>";
    exit;
}
?> 