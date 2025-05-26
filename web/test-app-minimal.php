<?php
echo "<h1>🔍 Minimal App Test</h1>";

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

umask(0000);

try {
    echo "<p>Step 1: Loading dependencies...</p>";
    $loader = require __DIR__.'/../app/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';
    echo "<p>✅ Dependencies loaded</p>";

    echo "<p>Step 2: Setting up environment...</p>";
    date_default_timezone_set("Europe/Prague");
    ini_set("memory_limit", "1G");
    echo "<p>✅ Environment configured</p>";

    echo "<p>Step 3: Creating kernel...</p>";
    Debug::enable();
    $kernel = new AppKernel('dev', true);
    $kernel->loadClassCache();
    echo "<p>✅ Kernel created</p>";

    echo "<p>Step 4: Booting kernel...</p>";
    $kernel->boot();
    $container = $kernel->getContainer();
    echo "<p>✅ Kernel booted, container available</p>";

    echo "<p>Step 5: Creating request...</p>";
    $request = Request::createFromGlobals();
    echo "<p>✅ Request created</p>";

    echo "<p>Step 6: Setting session...</p>";
    if ($container->has('session')) {
        $session = $container->get('session');
        $request->setSession($session);
        echo "<p>✅ Session set on request</p>";
    } else {
        echo "<p>❌ No session service found</p>";
    }

    echo "<p>Step 7: Handling request...</p>";
    flush(); // Make sure we see output up to this point
    
    // Capture any output during request handling
    ob_start();
    $response = $kernel->handle($request);
    $capturedOutput = ob_get_clean();
    
    echo "<p>✅ Request handled successfully!</p>";
    echo "<p>Response status: " . $response->getStatusCode() . "</p>";
    echo "<p>Response headers: " . json_encode($response->headers->all()) . "</p>";
    
    if ($capturedOutput) {
        echo "<p>⚠️ Captured output: <pre>" . htmlspecialchars($capturedOutput) . "</pre></p>";
    }
    
    // Show first 1000 characters of response
    $content = $response->getContent();
    if (strlen($content) > 1000) {
        echo "<p>Response content (first 1000 chars): <pre>" . htmlspecialchars(substr($content, 0, 1000)) . "...</pre></p>";
    } else {
        echo "<p>Response content: <pre>" . htmlspecialchars($content) . "</pre></p>";
    }

} catch (\Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    
    // Check for previous exceptions
    $prev = $e->getPrevious();
    if ($prev) {
        echo "<h3>Previous Exception:</h3>";
        echo "<p><strong>Message:</strong> " . htmlspecialchars($prev->getMessage()) . "</p>";
        echo "<p><strong>File:</strong> " . $prev->getFile() . ":" . $prev->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($prev->getTraceAsString()) . "</pre>";
    }
}

echo "<h2>🎯 Test Complete</h2>";
?> 