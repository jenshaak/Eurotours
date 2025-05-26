<?php
// Script to update cities with proper English names
require_once __DIR__.'/../app/autoload.php';
require_once __DIR__.'/../app/AppKernel.php';

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Country;
use AppBundle\Entity\City;

$kernel = new AppKernel('dev', true);
$request = Request::createFromGlobals();

// Boot the kernel and get container
$kernel->boot();
$container = $kernel->getContainer();

// Set the request on the container
$container->get('request_stack')->push($request);

echo "<h1>Updating City Names with English Translations</h1>";

try {
    $em = $container->get('doctrine.orm.entity_manager');
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
        'Zlín' => 'Zlín',
        'Havířov' => 'Havířov',
        'Kladno' => 'Kladno',
        'Most' => 'Most',
        'Opava' => 'Opava',
        'Frýdek-Místek' => 'Frýdek-Místek',
        'Karviná' => 'Karviná',
        'Jihlava' => 'Jihlava',
        'Teplice' => 'Teplice',
        'Děčín' => 'Děčín',
        'Karlovy Vary' => 'Karlovy Vary',
        'Jablonec nad Nisou' => 'Jablonec nad Nisou',
        'Mladá Boleslav' => 'Mladá Boleslav',
        'Prostějov' => 'Prostějov',
        'Přerov' => 'Přerov',
        'Česká Lípa' => 'Česká Lípa',
        'Třebíč' => 'Třebíč',
        'Třinec' => 'Třinec',
        'Tábor' => 'Tábor',
        'Znojmo' => 'Znojmo',
        'Příbram' => 'Příbram',
        'Cheb' => 'Cheb',
        'Trutnov' => 'Trutnov',
        'Orlová' => 'Orlová',
        'Písek' => 'Písek',
        'Kroměříž' => 'Kroměříž',
        'Vsetín' => 'Vsetín',
        'Valašské Meziříčí' => 'Valašské Meziříčí',
        'Uherské Hradiště' => 'Uherské Hradiště',
        'Uherský Brod' => 'Uherský Brod',
        'Český Krumlov' => 'Český Krumlov',
        'Břeclav' => 'Břeclav',
        'Kolin' => 'Kolín',
        'Humpolec' => 'Humpolec',
        'Nachod' => 'Náchod',
        'Železný Brod' => 'Železný Brod',
        'Turnov' => 'Turnov',
        'Chomutov' => 'Chomutov',
        'Louny' => 'Louny',
        'Slany' => 'Slaný',
        'Benesov' => 'Benešov',
        'Rokycany' => 'Rokycany',
        'Vernovice' => 'Vernovice',
        'Podebrady' => 'Poděbrady',
        'Vysoké Mýto' => 'Vysoké Mýto',
        'Dobříš' => 'Dobříš',
        
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
        'Málaga' => 'Málaga',
        'Córdoba' => 'Córdoba',
        'Plasencia' => 'Plasencia',
        'Cáceres' => 'Cáceres',
        'Mérida' => 'Mérida',
        'Sevilla' => 'Seville',
        'Cádiz' => 'Cádiz',
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
        'Zabrze' => 'Zabrze',
        'Olsztyn' => 'Olsztyn',
        'Bielsko-Biala' => 'Bielsko-Biała',
        
        // Ukrainian cities
        'Kiev' => 'Kyiv',
        'Lvov' => 'Lviv',
        'Kharkiv' => 'Kharkiv',
        'Odesa' => 'Odesa',
        'Dnipro' => 'Dnipro',
        'Zaporizhzhia' => 'Zaporizhzhia',
        'Kryvyj Rih' => 'Kryvyi Rih',
        'Mykolaiv' => 'Mykolaiv',
        'Mariupol' => 'Mariupol',
        'Luhansk' => 'Luhansk',
        'Vinnytsia' => 'Vinnytsia',
        'Chernihiv' => 'Chernihiv',
        'Cherkasy' => 'Cherkasy',
        'Zhytomyr' => 'Zhytomyr',
        'Sumy' => 'Sumy',
        'Khmelnytskyi' => 'Khmelnytskyi',
        'Chernivtsi' => 'Chernivtsi',
        'Rivne' => 'Rivne',
        'Kamianske' => 'Kamianske',
        'Kropyvnytskyi' => 'Kropyvnytskyi',
        'Ivano Frankivsk' => 'Ivano-Frankivsk',
        'Ternopil' => 'Ternopil',
        'Kremenchuk' => 'Kremenchuk',
        'Bila Tserkva' => 'Bila Tserkva',
        'Poltava' => 'Poltava',
        'Cherson' => 'Kherson',
        'Užhorod' => 'Uzhhorod',
        'Mukačevo' => 'Mukachevo',
        'Černovcy' => 'Chernivtsi',
        'Kolomyja' => 'Kolomyia',
        
        // Russian cities
        'Moskva' => 'Moscow',
        'St. Peterburg' => 'Saint Petersburg',
        'Kaliningrad' => 'Kaliningrad',
        'Novgorod' => 'Novgorod',
        
        // Other cities that need translation
        'Kobenhavn' => 'Copenhagen',
        'Arhus' => 'Aarhus',
        'Göteborg' => 'Gothenburg',
        'Malmö' => 'Malmö',
        'Zürich' => 'Zurich',
        'Genève' => 'Geneva',
        'Bern' => 'Bern',
        'Basel' => 'Basel',
        'Luzern' => 'Lucerne',
        'Athína' => 'Athens',
        'Thessaloníki' => 'Thessaloniki',
        'Beograd' => 'Belgrade',
        'Bucuresti' => 'Bucharest',
        'Bratislava' => 'Bratislava',
        'Ljubljana' => 'Ljubljana',
        'Sarajevo' => 'Sarajevo'
    ];
    
    // Get all cities
    $cities = $cityService->findAllCities();
    $updatedCount = 0;
    
    echo "<h2>Processing " . count($cities) . " cities...</h2>";
    
    foreach ($cities as $city) {
        $currentName = $city->getName()->getString($czechLanguage);
        
        // Check if we have an English translation for this city
        if (isset($englishTranslations[$currentName])) {
            $englishName = $englishTranslations[$currentName];
            $city->getName()->setString($englishLanguage, $englishName);
            $cityService->saveCity($city);
            $updatedCount++;
            echo "<p>✅ Updated: <strong>$currentName</strong> → <strong>$englishName</strong></p>";
        } else {
            // For cities without specific translations, keep the original name
            $city->getName()->setString($englishLanguage, $currentName);
            $cityService->saveCity($city);
        }
    }
    
    echo "<h2>✅ Update Complete!</h2>";
    echo "<p><strong>Cities with English translations:</strong> $updatedCount</p>";
    echo "<p><strong>Total cities processed:</strong> " . count($cities) . "</p>";
    echo "<p><a href='/'>Go to Homepage</a> to test the search form with English names!</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 