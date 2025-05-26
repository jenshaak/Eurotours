<?php

echo "<h1>🔍 EuroTours Application Comprehensive Debug</h1>";

// Step 1: Basic PHP and autoloader test
echo "<h2>Step 1: Basic Setup</h2>";
try {
    echo "✅ PHP Version: " . PHP_VERSION . "<br>";
    echo "✅ Memory Limit: " . ini_get('memory_limit') . "<br>";
    echo "✅ Error Reporting: " . error_reporting() . "<br>";
    
    umask(0000);
    
    $loader = require __DIR__.'/../app/autoload.php';
    echo "✅ Autoloader loaded successfully<br>";
    
    require_once __DIR__.'/../app/AppKernel.php';
    echo "✅ AppKernel class loaded<br>";
    
} catch (Exception $e) {
    echo "❌ Basic setup failed: " . $e->getMessage() . "<br>";
    exit;
}

// Step 2: Kernel creation test
echo "<h2>Step 2: Kernel Creation</h2>";
try {
    $kernel = new AppKernel('dev', true);
    echo "✅ Kernel created successfully<br>";
    
    $kernel->loadClassCache();
    echo "✅ Class cache loaded<br>";
    
} catch (Exception $e) {
    echo "❌ Kernel creation failed: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Step 3: Kernel boot test
echo "<h2>Step 3: Kernel Boot</h2>";
try {
    $kernel->boot();
    echo "✅ Kernel booted successfully<br>";
    
    $container = $kernel->getContainer();
    echo "✅ Container available<br>";
    
} catch (Exception $e) {
    echo "❌ Kernel boot failed: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Step 4: Request creation test
echo "<h2>Step 4: Request Creation</h2>";
try {
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    echo "✅ Request created successfully<br>";
    echo "✅ Request URI: " . $request->getRequestUri() . "<br>";
    echo "✅ Request Method: " . $request->getMethod() . "<br>";
    
} catch (Exception $e) {
    echo "❌ Request creation failed: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    exit;
}

// Step 5: Session test
echo "<h2>Step 5: Session Test</h2>";
try {
    if ($request->hasSession()) {
        echo "✅ Request has session support<br>";
        $session = $request->getSession();
        
        if (!$session->isStarted()) {
            $session->start();
            echo "✅ Session started<br>";
        } else {
            echo "✅ Session already started<br>";
        }
        
        echo "✅ Session ID: " . $session->getId() . "<br>";
        
    } else {
        echo "⚠️ Request has no session support<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Session test failed: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Step 6: Language bag test
echo "<h2>Step 6: Language Bag Test</h2>";
try {
    if ($request->hasSession()) {
        $session = $request->getSession();
        $languageBag = $session->getBag(\AppBundle\VO\LanguageBag::class);
        echo "✅ Language bag retrieved<br>";
        
        if ($languageBag->getLanguage() === null) {
            $languageBag->setLanguageId("cs");
            echo "✅ Default language set to 'cs'<br>";
        } else {
            echo "✅ Language already set: " . $languageBag->getLanguage() . "<br>";
        }
        
        $request->setLocale($languageBag->getLanguage());
        echo "✅ Request locale set<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Language bag test failed: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Step 7: Request handling test
echo "<h2>Step 7: Request Handling Test</h2>";
try {
    // Start output buffering to capture any output
    ob_start();
    
    $response = $kernel->handle($request);
    
    // Get any captured output
    $capturedOutput = ob_get_clean();
    
    echo "✅ Request handled successfully<br>";
    echo "✅ Response status: " . $response->getStatusCode() . "<br>";
    echo "✅ Response content type: " . $response->headers->get('Content-Type') . "<br>";
    
    if ($capturedOutput) {
        echo "⚠️ Captured output during handling: <pre>" . htmlspecialchars($capturedOutput) . "</pre>";
    }
    
    // Show first 500 characters of response content
    $content = $response->getContent();
    if (strlen($content) > 500) {
        echo "✅ Response content (first 500 chars): <pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
    } else {
        echo "✅ Response content: <pre>" . htmlspecialchars($content) . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Request handling failed: " . $e->getMessage() . "<br>";
    echo "❌ Error file: " . $e->getFile() . ":" . $e->getLine() . "<br>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    
    // Check for previous exceptions
    $prev = $e->getPrevious();
    if ($prev) {
        echo "<h3>Previous Exception:</h3>";
        echo "❌ " . $prev->getMessage() . "<br>";
        echo "❌ File: " . $prev->getFile() . ":" . $prev->getLine() . "<br>";
        echo "<pre>" . $prev->getTraceAsString() . "</pre>";
    }
}

echo "<h2>🎯 Debug Complete</h2>";
echo "<p>This comprehensive test helps identify exactly where the application fails.</p>";
?> 