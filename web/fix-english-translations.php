<?php
// Fixed script to properly set English translations for cities
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();

// Boot the kernel and get container
$kernel->boot();
$container = $kernel->getContainer();

// Set the request on the container
$container->get('request_stack')->push($request);

echo "<h1>Fixing English City Translations</h1>";

try {
    $languageService = $container->get('service.language');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    // Define English translations for cities that need them
    $englishTranslations = [
        // Czech cities
        'Praha' => 'Prague',
        'Brno' => 'Brno',
        'Ostrava' => 'Ostrava',
        'Plzeň' => 'Pilsen',
        'České Budějovice' => 'České Budějovice',
        'Olomouc' => 'Olomouc',
        'Liberec' => 'Liberec',
        'Hradec Králové' => 'Hradec Králové',
        'Ústí nad Labem' => 'Ústí nad Labem',
        'Pardubice' => 'Pardubice',
        'Jihlava' => 'Jihlava',
        'Karlovy Vary' => 'Karlovy Vary',
        'Mladá Boleslav' => 'Mladá Boleslav',
        'Prostějov' => 'Prostějov',
        'Kolin' => 'Kolín',
        'Humpolec' => 'Humpolec',
        'Cheb' => 'Cheb',
        'Nachod' => 'Náchod',
        'Český Krumlov' => 'Český Krumlov',
        'Železný Brod' => 'Železný Brod',
        'Turnov' => 'Turnov',
        'Chomutov' => 'Chomutov',
        'Louny' => 'Louny',
        'Slany' => 'Slaný',
        'Benesov' => 'Benešov',
        'Rokycany' => 'Rokycany',
        'Vernovice' => 'Vernovice',
        'Kladno' => 'Kladno',
        'Podebrady' => 'Poděbrady',
        'Vysoké Mýto' => 'Vysoké Mýto',
        'Dobříš' => 'Dobříš',
        'Teplice' => 'Teplice',
        'Břeclav' => 'Břeclav',
        'Uherský Brod' => 'Uherský Brod',
        'Uherské Hradiště' => 'Uherské Hradiště',
        'Valašské Meziříčí' => 'Valašské Meziříčí',
        'Příbram' => 'Příbram',
        'Znojmo' => 'Znojmo',
        
        // German cities
        'Munchen' => 'Munich',
        'Koln' => 'Cologne',
        'Nurnberg' => 'Nuremberg',
        'Munster' => 'Münster',
        'Saarbrücken' => 'Saarbrücken',
        'Dusseldorf' => 'Düsseldorf',
        'Wurzburg' => 'Würzburg',
        'Gottingen' => 'Göttingen',
        'Wiebaden' => 'Wiesbaden',
        'Tubingen' => 'Tübingen',
        
        // French cities
        'Chalon-S-Saone' => 'Chalon-sur-Saône',
        'Clermond-Ferand' => 'Clermont-Ferrand',
        'Aix-en-Provence' => 'Aix-en-Provence',
        'Saint Etienne' => 'Saint-Étienne',
        'Bellegarde-sur-Valserine' => 'Bellegarde-sur-Valserine',
        'Brive-La-Gaillarde' => 'Brive-la-Gaillarde',
        'Saint-Brieuc' => 'Saint-Brieuc',
        'Saint Gaudens' => 'Saint-Gaudens',
        
        // Spanish cities
        'Alcalá de Chivert' => 'Alcalá de Chivert',
        'Castellón' => 'Castellón',
        'San Sebastian' => 'San Sebastián',
        'Tordesillas' => 'Tordesillas',
        'Puerto Lumbreras' => 'Puerto Lumbreras',
        'Plasencia' => 'Plasencia',
        'Sevilla' => 'Seville',
        'Algeciras' => 'Algeciras',
        
        // Italian cities
        'Venezia' => 'Venice',
        'Firenze' => 'Florence',
        'Roma' => 'Rome',
        'Napoli' => 'Naples',
        'Milano' => 'Milan',
        'Torino' => 'Turin',
        'Civitanova Marche' => 'Civitanova Marche',
        'Porto San Giorgio' => 'Porto San Giorgio',
        'Porto DAscoli' => 'Porto d\'Ascoli',
        'Val Vomano' => 'Val Vomano',
        'LAquila' => 'L\'Aquila',
        'Bellaria Igea Marina' => 'Bellaria-Igea Marina',
        'Borca di Cadore' => 'Borca di Cadore',
        'San Benedetto del Tronto' => 'San Benedetto del Tronto',
        
        // Polish cities
        'Warszawa' => 'Warsaw',
        'Krakow' => 'Kraków',
        'Lodz' => 'Łódź',
        'Wroclaw' => 'Wrocław',
        'Poznan' => 'Poznań',
        'Gdansk' => 'Gdańsk',
        'Szczecin' => 'Szczecin',
        'Bydgoszcz' => 'Bydgoszcz',
        'Lublin' => 'Lublin',
        'Katowice' => 'Katowice',
        'Bialystok' => 'Białystok',
        'Gdynia' => 'Gdynia',
        'Czestochowa' => 'Częstochowa',
        'Radom' => 'Radom',
        'Torun' => 'Toruń',
        'Rzeszow' => 'Rzeszów',
        'Kielce' => 'Kielce',
        'Gliwice' => 'Gliwice',
        'Bielsko-Biala' => 'Bielsko-Biała',
        
        // Ukrainian cities
        'Kiev' => 'Kyiv',
        'Lvov' => 'Lviv',
        'Kharkiv' => 'Kharkiv',
        'Odesa' => 'Odesa',
        'Dnipro' => 'Dnipro',
        'Kryvyj Rih' => 'Kryvyi Rih',
        'Mariupol' => 'Mariupol',
        'Kremenchuk' => 'Kremenchuk',
        'Kamianske' => 'Kamianske',
        'Ivano Frankivsk' => 'Ivano-Frankivsk',
        'Ternopil' => 'Ternopil',
        'Bila Tserkva' => 'Bila Tserkva',
        'Poltava' => 'Poltava',
        'Cherson' => 'Kherson',
        'Rivne' => 'Rivne',
        'Užhorod' => 'Uzhhorod',
        'Mukačevo' => 'Mukachevo',
        'Černovcy' => 'Chernivtsi',
        'Kolomyja' => 'Kolomyia',
        'Chernihiv' => 'Chernihiv',
        'Sumy' => 'Sumy',
        
        // Russian cities
        'Moskva' => 'Moscow',
        'St. Peterburg' => 'Saint Petersburg',
        'Kaliningrad' => 'Kaliningrad',
        'Novgorod' => 'Novgorod',
        
        // Other cities that need translation
        'Kobenhavn' => 'Copenhagen',
        'Arhus' => 'Aarhus',
        'Malmö' => 'Malmö',
        'Bern' => 'Bern',
        'Luzern' => 'Lucerne',
        'Basel' => 'Basel',
        'Beograd' => 'Belgrade',
        'Bucuresti' => 'Bucharest',
        'Bratislava' => 'Bratislava',
        'Ljubljana' => 'Ljubljana',
        'Sarajevo' => 'Sarajevo'
    ];
    
    // Get all cities
    $cities = $cityService->findAllCities();
    $updatedCount = 0;
    $totalCount = count($cities);
    
    echo "<h2>Processing $totalCount cities...</h2>";
    
    foreach ($cities as $city) {
        $currentCzechName = $city->getName()->getString($czechLanguage);
        
        // Check if we have an English translation for this city
        if (isset($englishTranslations[$currentCzechName])) {
            $englishName = $englishTranslations[$currentCzechName];
            
            // Set the English translation
            $city->getName()->setString($englishLanguage, $englishName);
            $cityService->saveCity($city);
            
            $updatedCount++;
            echo "<p>✅ Updated: <strong>$currentCzechName</strong> → <strong>$englishName</strong></p>";
            
            // Flush every 50 updates to avoid memory issues
            if ($updatedCount % 50 == 0) {
                echo "<p><em>Flushed $updatedCount updates...</em></p>";
            }
        }
    }
    
    echo "<h2>✅ English Translation Update Complete!</h2>";
    echo "<p><strong>Cities with English translations:</strong> $updatedCount</p>";
    echo "<p><strong>Total cities processed:</strong> $totalCount</p>";
    
    // Test a few cities to verify the update worked
    echo "<h2>Verification Test:</h2>";
    $testCities = ['Praha', 'Warszawa', 'Moskva', 'Roma', 'Venezia'];
    
    foreach ($testCities as $testCityName) {
        foreach ($cities as $city) {
            if ($city->getName()->getString($czechLanguage) === $testCityName) {
                $englishName = $city->getName()->getString($englishLanguage);
                echo "<p><strong>$testCityName:</strong> English = '$englishName'</p>";
                break;
            }
        }
    }
    
    echo "<p><a href='/'>Go to Homepage</a> to test the search form with English names!</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 