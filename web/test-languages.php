<?php
echo "<h1>🔍 Language Database Test</h1>";

try {
    require __DIR__.'/../app/autoload.php';
    require_once __DIR__.'/../app/AppKernel.php';
    
    $kernel = new AppKernel('dev', true);
    $kernel->boot();
    $container = $kernel->getContainer();
    
    $em = $container->get('doctrine.orm.entity_manager');
    
    echo "<h2>Step 1: Check Languages Table</h2>";
    
    // Check if languages table exists and has data
    $languages = $em->getRepository('AppBundle:Language')->findAll();
    
    echo "<p>Found " . count($languages) . " languages in database</p>";
    
    if (empty($languages)) {
        echo "<h2>Step 2: Creating Default Languages</h2>";
        
        // Create default languages
        $languageData = [
            ['id' => 'cs', 'name' => 'Čeština'],
            ['id' => 'en', 'name' => 'English'],
            ['id' => 'ru', 'name' => 'Русский'],
            ['id' => 'bg', 'name' => 'Български'],
            ['id' => 'uk', 'name' => 'Українська']
        ];
        
        foreach ($languageData as $data) {
            $language = new \AppBundle\Entity\Language();
            $language->setId($data['id']);
            $language->setName($data['name']);
            
            $em->persist($language);
            echo "<p>✅ Created language: {$data['name']} ({$data['id']})</p>";
        }
        
        $em->flush();
        echo "<p>✅ All languages saved to database</p>";
        
    } else {
        echo "<h2>Step 2: Existing Languages</h2>";
        foreach ($languages as $language) {
            echo "<p>✅ {$language->getName()} ({$language->getId()})</p>";
        }
    }
    
    echo "<h2>Step 3: Test Language Service</h2>";
    
    $languageService = $container->get('service.language');
    $allLanguages = $languageService->getAllLanguages();
    echo "<p>Language service found " . count($allLanguages) . " languages</p>";
    
    if (!empty($allLanguages)) {
        $currentLanguage = $languageService->getCurrentLanguage();
        echo "<p>✅ Current language: {$currentLanguage->getName()} ({$currentLanguage->getId()})</p>";
    }
    
    echo "<h2>✅ Language Test Complete</h2>";
    
} catch (\Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?> 