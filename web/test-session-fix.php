<?php
echo "<h1>🔍 Session Fix Debug Test</h1>";

try {
    echo "<h2>Step 1: Basic Setup</h2>";
    require __DIR__.'/../app/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';
    echo "✅ Autoloader and AppKernel loaded<br>";
    
    echo "<h2>Step 2: Kernel Boot</h2>";
    $kernel = new AppKernel('dev', true);
    $kernel->boot();
    echo "✅ Kernel booted<br>";
    
    echo "<h2>Step 3: Container Access</h2>";
    $container = $kernel->getContainer();
    echo "✅ Container retrieved<br>";
    
    echo "<h2>Step 4: Session Service Check</h2>";
    if ($container->has('session')) {
        echo "✅ Session service exists in container<br>";
        
        $session = $container->get('session');
        echo "✅ Session service retrieved: " . get_class($session) . "<br>";
        
        // Test if we can get the language bag
        try {
            $languageBag = $session->getBag(\AppBundle\VO\LanguageBag::class);
            echo "✅ Language bag available: " . get_class($languageBag) . "<br>";
        } catch (Exception $e) {
            echo "❌ Language bag not available: " . $e->getMessage() . "<br>";
        }
        
        try {
            $currencyBag = $session->getBag(\AppBundle\VO\CurrencyBag::class);
            echo "✅ Currency bag available: " . get_class($currencyBag) . "<br>";
        } catch (Exception $e) {
            echo "❌ Currency bag not available: " . $e->getMessage() . "<br>";
        }
        
    } else {
        echo "❌ Session service NOT found in container<br>";
        echo "Available services: <br>";
        foreach ($container->getServiceIds() as $serviceId) {
            if (strpos($serviceId, 'session') !== false) {
                echo "- $serviceId<br>";
            }
        }
    }
    
    echo "<h2>Step 5: Request Creation and Session Assignment</h2>";
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    echo "✅ Request created<br>";
    
    if ($container->has('session')) {
        $session = $container->get('session');
        $request->setSession($session);
        echo "✅ Session set on request<br>";
        
        if ($request->hasSession()) {
            echo "✅ Request now has session support<br>";
            
            $requestSession = $request->getSession();
            echo "✅ Session retrieved from request: " . get_class($requestSession) . "<br>";
            
            // Test language bag
            try {
                $languageBag = $requestSession->getBag(\AppBundle\VO\LanguageBag::class);
                echo "✅ Language bag retrieved: " . get_class($languageBag) . "<br>";
                
                if ($languageBag->getLanguage() === null) {
                    $languageBag->setLanguageId("cs");
                    echo "✅ Default language set to 'cs'<br>";
                } else {
                    echo "✅ Language already set: " . $languageBag->getLanguage() . "<br>";
                }
                
            } catch (Exception $e) {
                echo "❌ Language bag error: " . $e->getMessage() . "<br>";
            }
            
        } else {
            echo "❌ Request still has NO session support after setting<br>";
        }
    }
    
    echo "<h2>Step 6: Test Request Handling</h2>";
    try {
        // Don't actually handle the request, just test if we can
        echo "✅ Ready to handle request with session support<br>";
        
        // Test the LanguageListener logic
        if ($request->hasSession()) {
            $session = $request->getSession();
            
            if (!$session->isStarted()) {
                $session->start();
                echo "✅ Session started<br>";
            } else {
                echo "✅ Session already started<br>";
            }
            
            echo "✅ Session ID: " . $session->getId() . "<br>";
        }
        
    } catch (Exception $e) {
        echo "❌ Request handling test failed: " . $e->getMessage() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>🎯 Test Complete</h2>";
?> 