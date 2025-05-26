<?php
echo "<h1>🔍 Translation System Debug</h1>";

try {
    require __DIR__.'/../app/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';
    
    $kernel = new AppKernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    echo "<h2>Step 1: Check Translator Service</h2>";
    
    if ($container->has('translator')) {
        $translator = $container->get('translator');
        echo "✅ Translator service exists: " . get_class($translator) . "<br>";
        
        echo "<h2>Step 2: Test Translation</h2>";
        
        // Test basic translation
        $testKey = 'homepage.claim.subtitle4';
        $translated = $translator->trans($testKey);
        echo "Translation of '$testKey': <strong>$translated</strong><br>";
        
        // Test with specific locale
        $translatedCs = $translator->trans($testKey, [], null, 'cs');
        echo "Translation with 'cs' locale: <strong>$translatedCs</strong><br>";
        
        $translatedEn = $translator->trans($testKey, [], null, 'en');
        echo "Translation with 'en' locale: <strong>$translatedEn</strong><br>";
        
        echo "<h2>Step 3: Check Current Locale</h2>";
        
        // Check current locale
        if ($container->has('request_stack')) {
            $requestStack = $container->get('request_stack');
            $request = $requestStack->getCurrentRequest();
            if ($request) {
                echo "Current request locale: <strong>" . $request->getLocale() . "</strong><br>";
            } else {
                echo "❌ No current request<br>";
            }
        }
        
        // Check translator locale
        echo "Translator locale: <strong>" . $translator->getLocale() . "</strong><br>";
        
        echo "<h2>Step 4: Check Language Service</h2>";
        
        if ($container->has('service.language')) {
            $languageService = $container->get('service.language');
            echo "✅ Language service exists<br>";
            
            try {
                $currentLanguage = $languageService->getCurrentLanguage();
                echo "Current language from service: <strong>" . $currentLanguage->getId() . "</strong> (" . $currentLanguage->getName() . ")<br>";
                
                // Try setting the translator locale to match
                $translator->setLocale($currentLanguage->getId());
                echo "Set translator locale to: <strong>" . $currentLanguage->getId() . "</strong><br>";
                
                // Test translation again
                $translatedAfter = $translator->trans($testKey);
                echo "Translation after setting locale: <strong>$translatedAfter</strong><br>";
                
            } catch (Exception $e) {
                echo "❌ Error getting current language: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "❌ Language service not found<br>";
        }
        
        echo "<h2>Step 5: Check Translation Files</h2>";
        
        // Check if translation files are loaded
        $catalogue = $translator->getCatalogue('cs');
        if ($catalogue->has($testKey)) {
            echo "✅ Translation key '$testKey' found in 'cs' catalogue<br>";
            echo "Value: <strong>" . $catalogue->get($testKey) . "</strong><br>";
        } else {
            echo "❌ Translation key '$testKey' NOT found in 'cs' catalogue<br>";
        }
        
    } else {
        echo "❌ Translator service not found<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
} 