<?php
echo "<h1>🔍 Translation Fix Test</h1>";

try {
    require __DIR__.'/../app/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';
    
    $kernel = new AppKernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    echo "<h2>Step 1: Create Request with Session</h2>";
    
    // Create request and set session (simulating real request)
    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    if ($container->has('session')) {
        $session = $container->get('session');
        $request->setSession($session);
        echo "✅ Session set on request<br>";
    }
    
    echo "<h2>Step 2: Simulate LanguageListener</h2>";
    
    // Simulate what LanguageListener does
    if ($request->hasSession()) {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }
        
        $languageBag = $session->getBag(\AppBundle\VO\LanguageBag::class);
        if ($languageBag->getLanguage() === null) {
            $languageBag->setLanguageId("cs");
            echo "✅ Language bag set to 'cs'<br>";
        }
        
        $locale = $languageBag->getLanguage();
        $request->setLocale($locale);
        echo "✅ Request locale set to: <strong>$locale</strong><br>";
        
        // Set translator locale (this is what our fix does)
        if ($container->has('translator')) {
            $translator = $container->get('translator');
            $translator->setLocale($locale);
            echo "✅ Translator locale set to: <strong>$locale</strong><br>";
        }
    }
    
    echo "<h2>Step 3: Test Translation</h2>";
    
    if ($container->has('translator')) {
        $translator = $container->get('translator');
        
        $testKey = 'homepage.claim.subtitle4';
        $translated = $translator->trans($testKey);
        echo "Translation of '$testKey': <strong>$translated</strong><br>";
        
        if ($translated !== $testKey) {
            echo "✅ <span style='color: green;'>Translation is working! Key was translated.</span><br>";
        } else {
            echo "❌ <span style='color: red;'>Translation failed - still showing key.</span><br>";
        }
        
        echo "Current translator locale: <strong>" . $translator->getLocale() . "</strong><br>";
    }
    
    echo "<h2>Step 4: Test Language Service</h2>";
    
    if ($container->has('service.language')) {
        $languageService = $container->get('service.language');
        
        try {
            $currentLanguage = $languageService->getCurrentLanguage();
            echo "Current language from service: <strong>" . $currentLanguage->getId() . "</strong> (" . $currentLanguage->getName() . ")<br>";
            
            if ($currentLanguage->getId() === 'cs') {
                echo "✅ <span style='color: green;'>Language service correctly defaults to Czech!</span><br>";
            } else {
                echo "❌ <span style='color: red;'>Language service still not defaulting to Czech.</span><br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Error getting current language: " . $e->getMessage() . "<br>";
        }
    }
    
    echo "<h2>✅ Translation Fix Test Complete</h2>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
} 