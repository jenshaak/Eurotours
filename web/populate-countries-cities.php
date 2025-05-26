<?php
// Script to populate countries and cities from the provided list
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

echo "<h1>Populating Countries and Cities</h1>";

try {
    $em = $container->get('doctrine.orm.entity_manager');
    $languageService = $container->get('service.language');
    $countryService = $container->get('service.country');
    $cityService = $container->get('service.city');
    
    $englishLanguage = $languageService->getEnglish();
    $czechLanguage = $languageService->getCzech();
    
    // Define the countries and cities data
    $countriesData = [
        'England' => ['Dover', 'London', 'Bradford', 'Leicester', 'Sheffield', 'Rotherham', 'Peterborough', 'Birmingham', 'Derby', 'Nottingham', 'Manchester', 'Chatham', 'Luton', 'Corby'],
        'Belgium' => ['Liege', 'Brussel', 'Antwerpen', 'Gent', 'Bruges'],
        'Belorussia' => ['Grodno', 'Lida', 'Minsk', 'Brest', 'Baranovichy', 'Orsha', 'Vitebsk'],
        'Bulgaria' => ['Sofia', 'Varna', 'Pazardzhik', 'Plovdiv', 'Stara Zagora', 'Ruse', 'Shumen', 'Razgrad', 'Burgas', 'Sozopol', 'Primorsko', 'Kiten', 'Ahtopol', 'Sinemorec', 'Carevo', 'Nesebar', 'Slanchev Briag', 'Zlatni piasaci', 'Balchik', 'Sliven', 'Dobrich', 'Haskovo', 'Chernomorec', 'Lozenec', 'Pomorie', 'Ravda', 'Sveti Vlas', 'Obzor', 'Albena', 'Kavarna', 'Šabla', 'Veliko Tarnovo', 'Aytos', 'Karnobat', 'Dimitrovgrad'],
        'Denmark' => ['Rodby', 'Nykobing', 'Kobenhavn', 'Tapernoje', 'Ringsted', 'Slagelse', 'Nyborg', 'Odense', 'Vejle', 'Horsens', 'Arhus', 'Aalborg'],
        'Estonia' => ['Parnu', 'Tallinn', 'Valga', 'Tartu', 'Narva', 'Kohtla Jarve'],
        'Finland' => ['Turku', 'Helsinki'],
        'France' => ['Chambery', 'Toulon', 'Bordeaux', 'Lille', 'Perpignan', 'Strasbourg', 'Dijon', 'Chalon-S-Saone', 'Lyon', 'Grenoble', 'Valence', 'Avignon', 'Aix-en-Provence', 'Marseille', 'Nimes', 'Montpellier', 'Nancy', 'Reims', 'Paris', 'Orleans', 'Tours', 'Angers', 'Nantes', 'Rouen', 'Caen', 'Rennes', 'Metz', 'Mulhouse', 'Belfort', 'Besancon', 'Saint Etienne', 'Clermond-Ferand', 'Nice', 'Annemasse', 'Annecy', 'Beziers', 'Narbonne', 'Carcassonne', 'Toulouse', 'Tarbes', 'Pau', 'Orthez', 'Bayonne', 'Le Cannet', 'Puget Sur Argents', 'Givors', 'Ussel', 'Tulle', 'Brive', 'Perigueux', 'Poitiers', 'Swindon', 'Bristol', 'Newport', 'Saint Gaudens', 'Le Havre', 'Chessy', 'Le Mans', 'Saint-Brieuc', 'Limoges', 'Brive-La-Gaillarde', 'Bapaume', 'Beaune', 'Beausoleil', 'Beauval', 'Bellegarde-sur-Valserine', 'Bergerac', 'Biarritz', 'Blois', 'Bollene', 'Cannes'],
        'Netherlands' => ['Lisse', 'Eindhoven', 'Arnhem', 'Utrecht', 'Amsterdam', 'Den Haag', 'Rotterdam', 'Venlo', 'Breda', 'Enschede', 'Nijmegen', 'Bergen op Zoom', 'Borger', 'Maastricht'],
        'Croatia' => ['Rijeka', 'Opatija', 'Rovinj', 'Pula', 'Crikvenica', 'Malinska', 'Krk', 'Baška', 'Karlovac', 'Plitvička Jezera', 'Zagreb', 'Zadar', 'Šibenik', 'Primošten', 'Trogir', 'Split', 'Omiš', 'Makarska', 'Tučepi', 'Podgora', 'Drvenik', 'Gradac', 'Ploče', 'Dubrovnik', 'Bibinje', 'Sukosan', 'Sveti Petar na Moru', 'Sveti Filip i Jakov', 'Biograd n. M.', 'Pakostane', 'Drage', 'Pirovac', 'Tribunj', 'Vodice', 'Brodarica', 'Zaboric', 'Bilo', 'Dolac', 'Rogoznica', 'Podstrana', 'Jesenice', 'Dugi Rat', 'Duce', 'Stanici', 'Ruskamen', 'Mimice', 'Pisak', 'Brela', 'Baska Voda', 'Promajna', 'Drasnice', 'Igrane', 'Zivogosce', 'Zaostrog', 'Podaca', 'Brist', 'Slano', 'Barban', 'Bedenica', 'Benkovac', 'Poreč'],
        'Italy' => ['Genova', 'Udine', 'Mestre', 'Venezia', 'Padova', 'Bologna', 'Firenze', 'Pisa', 'Livorno', 'Roma', 'Napoli', 'Bolzano', 'Trento', 'Verona', 'Brescia', 'Milano', 'Lugano', 'Como', 'Torino', 'Parma', 'Rimini', 'Pesaro', 'Ancona', 'Civitanova Marche', 'Porto San Giorgio', 'Porto DAscoli', 'Giulianova', 'Teramo', 'Val Vomano', 'LAquila', 'Avezzano', 'Sora', 'Cassino', 'Caserta', 'Bardonecchia', 'Bari', 'Barletta', 'Battipaglia', 'Bellaria Igea Marina', 'Belluno', 'Benevento', 'Bergamo', 'Bisceglie', 'Bitonto', 'Borca di Cadore', 'Pescara', 'Perugia', 'Ravenna', 'Riccione', 'San Benedetto del Tronto', 'Trieste'],
        'Lithuania' => ['Marijampole', 'Kaunas', 'Vilnius', 'Panevezys', 'Zarasai', 'Utena', 'Klaipeda', 'Palanga', 'Siauliai'],
        'Latvia' => ['Riga', 'Liepaja', 'Jelgava', 'Rezekne', 'Valmiera', 'Daugavpils', 'Bauska'],
        'Luxembourg' => ['Lucemburg'],
        'Hungary' => ['Györ', 'Budapest', 'Szeged'],
        'Macedonia' => ['Skopje', 'Kumanovo'],
        'Moldova' => ['Balti', 'Kishinev', 'Cimislia', 'Comrat', 'Ceadir-Lunga', 'Orhei', 'Singerei', 'Riscani', 'Leuseni', 'Tiraspol', 'Bender', 'Hincesti', 'Congaz'],
        'Germany' => ['Regensburg', 'Munchen', 'Augsburg', 'Gunzburg', 'Ulm', 'Stuttgart', 'Dresden', 'Berlin', 'Nurnberg', 'Frankfurt', 'Koln', 'Aachen', 'Hamburg', 'Hannover', 'Leipzig', 'Rostock', 'Karlsruhe', 'Freiburg', 'Heidelberg', 'Ingolstadt', 'Mannheim', 'Saarbrücken', 'Magdeburg', 'Wolfsburg', 'Aschaffenburg', 'Bremen', 'Mainz', 'Koblenz', 'Bonn', 'Dusseldorf', 'Wurzburg', 'Kassel', 'Dortmund', 'Gottingen', 'Essen', 'Solingen', 'Halle', 'Braunschweig', 'Memmingen', 'Lindau', 'Chemnitz', 'Gera', 'Jena', 'Weimar', 'Erfurt', 'Eisenach', 'Wiebaden', 'Erlangen', 'Lubbenau', 'Krausnick (Tropical Islands)', 'Potsdam', 'Oldenburg', 'Warnemunde', 'Bad Oeynhausen', 'Osnabruck', 'Bielefeld', 'Marienberg', 'Zschopau', 'Wehretal-Oetmannshausen', 'Hessisch Lichtenau', 'Bochum', 'Marktredwitz', 'Bischofsgrun', 'Bayreuth', 'Bamberg', 'Schweinfurt', 'Flensburg', 'Ludwigshafen', 'Heilbronn/Neckarsulm', 'Leverkusen', 'Hollfeld', 'Tubingen', 'Kornwestheim', 'Gotha', 'Salzgitter', 'Hildesheim', 'Amberg', 'Ansbach', 'Aalen', 'Bad Urach', 'Bansin', 'Barsinghausen', 'Bastogne', 'Bautzen', 'Beelen', 'Bergen auf Rügen', 'Berchtesgaden', 'Bergen (Celle)', 'Bensersiel', 'Bernau am Chiemsee', 'Bernburg (Saale)', 'Beverstedt', 'Bestwig', 'Biberach (Riß)', 'Binz', 'Bischofswiesen', 'Bischofgrun', 'Bispingen', 'Bitburg', 'Blankenburg', 'Bocholt', 'Born (Darß)', 'Borken', 'Duisburg', 'Munster', 'Offenburg', 'Paderborn', 'Passau', 'Pforzheim', 'Villach', 'Hof'],
        'Norway' => ['Sarpsborg', 'Oslo', 'Moss'],
        'Poland' => ['Wroclaw', 'Lodz', 'Warszawa', 'Bialystok', 'Suwalki', 'Ostrow Mazowiecka', 'Augustow', 'Krakow', 'Czestochowa', 'Gdynia', 'Gdansk', 'Przemysl', 'Gorzow Wielkopolski', 'Ostrow Wielkopolski', 'Poznan', 'Leszno', 'Katowice', 'Opole', 'Klodzko', 'Kielce', 'Gliwice', 'Bydgoszcz', 'Torun', 'Radom', 'Lublin', 'Tarnow', 'Rzeszow', 'Grudziad', 'Zdunska Wola', 'Kepno', 'Jaroslaw', 'Przeworsk', 'Debica', 'Nysa', 'Kalisz', 'Sieradz', 'Swiebodzin', 'Zielona Gora', 'Žary', 'Žagan', 'Polkowice', 'Swiebodzice', 'Dzierzoniow', 'Zabkowice Slaskie', 'Lubin', 'Jelenia Gora', 'Bochnia', 'Brzesko', 'Pilzno', 'Ropczyce', 'Lancut', 'Jedrzejow', 'Skarzysko Kamienna', 'Pulawy', 'Sieradz', 'Lubliniec', 'Radomsko', 'Lowicz', 'Lomza', 'Grajewo', 'Gniezno', 'Limanowa', 'Nowy Sacz', 'Gorlice', 'Sochaczew', 'Jaslo', 'Krosno', 'Sanok', 'Legnica', 'Szczecin', 'Bogatynia', 'Zgorzelec', 'Boleslawiec', 'Gorzyczki', 'Korczowa', 'Krakovets', 'Bielsko-Biala'],
        'Austria' => ['Wien', 'Salzburg', 'Innsbruck', 'Linz', 'Graz', 'Bludenz', 'Bregenz'],
        'Romania' => ['Arad', 'Timisoara', 'Lugoj', 'Deva', 'Sebes', 'Sibiu', 'Fagaras', 'Brasov', 'Ploiesti', 'Bucuresti', 'Adjud', 'Barlad', 'Husi', 'Salonta', 'Zalau', 'Carei', 'Satu Mare', 'Baia Mare', 'Sighetul Marmatei', 'Faget', 'Resita', 'Caransebes', 'Baile Herculane', 'Orsova', 'Turnu Severin', 'Lipova', 'Hunedoara', 'Petrosani', 'Bumbesti Jiu', 'Tg. Jiu', 'Rovinari', 'Filiasi', 'Craiova', 'Bals', 'Slatina', 'Orastie', 'Alba Iulia', 'Teius', 'Aiud', 'Turda', 'Dej', 'Bistrita', 'Talmaciu', 'Ramnicu Valcea', 'Targoviste', 'Medias', 'Sighisoara', 'Targu Mures', 'Sfantul Gheorghe', 'Targu Secuiesc', 'Onesti', 'Bacau', 'Buhusi', 'Piatra Neamt', 'Targu Neamt', 'Suceava', 'Botosani', 'Buzau', 'Ramnicu Sarat', 'Focsani', 'Tecuci', 'Vaslui', 'Iasi', 'Oltenita', 'Calarasi', 'Slobozia', 'Braila', 'Galati', 'Fetesti', 'Constanta', 'Tulcea', 'Pitesti', 'Oradea', 'Huedin', 'Cluj Napoca', 'Torda', 'Marosludas', 'Radnot', 'Sovata', 'Parajd', 'Odorheiu Secuiesc', 'Miercurea Ciuc', 'Mizil', 'Medgidia', 'Cernavoda', 'Urziceni', 'Falticeni', 'Pascani', 'Roman', 'Horezu', 'Hateg', 'Otelu Rosu', 'Sinicolau Mare', 'Toplita', 'Reghin', 'Herculane', 'Sannicolau Mare', 'Sinaia', 'Giurgiu', 'Vatra Dornei', 'Gura Humorului', 'Beclean', 'Nadlac'],
        'Russia' => ['Moskva', 'St. Peterburg', 'Ostrov', 'Pskov', 'Luga', 'Kaliningrad', 'Novgorod'],
        'Greece' => ['Thessaloniki', 'Athens', 'Katerini', 'Larissa', 'Lamia', 'Piraeus'],
        'Slovakia' => ['Bratislava', 'Hanušovce n. T.', 'Štrba', 'Makov', 'Trenčín', 'Dubnica nad Váhom', 'Považská Bystrica', 'Kraľovany', 'Ružomberok', 'Poprad', 'Prešov', 'Vranov nad Topľou', 'Stražské', 'Michalovce', 'Humenné', 'Snina', 'Spišska Nová ves', 'Levoča', 'Košice', 'Liptovský Mikuláš', 'Žilina', 'Rožňava', 'Štítnik', 'Jelšava', 'Revúca', 'Muráň', 'Tisovec', 'Brezno', 'Banská Bystrica', 'Zvolen', 'Žiar n. Hronom', 'Handlová', 'Prievidza', 'Bánovce n. B.', 'Sabinov', 'Lipany', 'Ľubotin', 'Stará Ľubovňa', 'Podolinec', 'Kežmarok', 'Sečovce', 'Rimavská Sobota', 'Lučenec', 'Nitra', 'Trnava', 'Sereď', 'Zlaté Moravce', 'Nová Baňa', 'Žarnovica', 'Nové Mesto n. V.', 'Piešťany', 'Topolčany', 'Partizánské', 'Hažín nad Cirochou', 'Kamenica nad Cirochou', 'Modrá nad Cirochou', 'Dlhé nad Cirochou', 'Belá nad Cirochou', 'Vrútky', 'Bardějov', 'Svidnik', 'Stropkov', 'Giraltovce', 'Martin', 'Sobrance'],
        'Serbia' => ['Novi Sad', 'Beograd', 'Subotica'],
        'Spain' => ['Figueres', 'Girona', 'Lloret de Mar', 'Barcelona', 'Tarragona', 'Salou', 'Alcalá de Chivert', 'Castellón', 'Valencia', 'Benidorm', 'Alicante', 'Murcia', 'Lleida', 'Zaragoza', 'Madrid', 'Irun', 'San Sebastian', 'Bilbao', 'Burgos', 'Valladolid', 'Tordesillas', 'Lorca', 'Puerto Lumbreras', 'Baza', 'Guadix', 'Granada', 'Malaga', 'Lerida', 'Bailen', 'Jaen', 'Cordoba', 'Lucena', 'Bejar', 'Plasencia', 'Caceres', 'Merida', 'Zafra', 'Sevilla', 'Jerez', 'Cadiz', 'Vejer', 'Tarifa', 'Algeciras', 'Benavente', 'Blanes'],
        'Sweden' => ['Malmö', 'Lund', 'Jonköping', 'Linköping', 'Stockholm', 'Uppsala', 'Helsingborg', 'Halmstad', 'Varberg', 'Goteborg', 'Uddevalla', 'Norrkoping', 'Nykoping', 'Sodertalje', 'Ljungby', 'Vasteras', 'Orebro', 'Varnamo', 'Landskrona', 'Vaxjo', 'Boras'],
        'Switzerland' => ['St. Gallen', 'Zurich', 'Bern', 'Lausanne', 'Geneve', 'Luzern', 'Basel', 'Fribourg', 'Bulle', 'Bellinzona', 'Winterthur'],
        'Turkey' => ['Istanbul'],
        'Ukraine' => ['Lvov', 'Ternopil', 'Chmelnickij', 'Vinnica', 'Uman', 'Kropyvnitskyi', 'Dnipro', 'Zaporozje', 'Doneck', 'Boryspil', 'Pyriatyn', 'Lubny', 'Poltava', 'Kharkiv', 'Zolochiv', 'Terebovlya', 'Chortkiv', 'Kamianets Podilskiy', 'Stryj', 'Dolina', 'Kaluš', 'Ivano Frankivsk', 'Nadvirna', 'Deljatin', 'Rachiv', 'Odesa', 'Nikolaev', 'Cherson', 'Rivne', 'Zitomir', 'Kiev', 'Čerkasy', 'Luck', 'Kovel', 'Užhorod', 'Mukačevo', 'Iršava', 'Chust', 'Tjačiv', 'Vinohradovo', 'Bolechiv', 'Černovcy', 'Kolomyja', 'Sniatyn', 'Mizhgirya', 'Rohatin', 'Kryvyj Rih', 'Gorodenka', 'Brody', 'Sarny', 'Korosten', 'Oleksandriia', 'Pyatikhatky', 'Krasnohrad', 'Beregovo', 'Vylok', 'Bushtyno', 'Drahovo', 'Yaremche', 'Yasinya', 'Dubno', 'Bohorodcany', 'Lanchyn', 'Velykyi Bychkiv', 'Svaljava', 'Nizhni Vorota', 'Pervomaisk', 'Truskavets', 'Drohobych', 'Zalishchyky', 'Dubove', 'Nova Kachovka', 'Berdansk', 'Mariupol', 'Melitopol', 'Kosiv', 'Tovste', 'Sambir', 'Kremenchuk', 'Novovolinsk', 'Vladimir Volynskiy', 'Kamianske', 'Tlumach', 'Letychiv', 'Nemyriv', 'Haisyn', 'Koblevo', 'Pryluky', 'Romny', 'Nedryhailiv', 'Sumy', 'Yuzhnoukrainsk', 'Voznesensk', 'Chop', 'Novohrad-Volynskyi', 'Stebnyk', 'Busk', 'Chmilnik', 'Volochisk', 'Kryve Ozero', 'Pereiaslav', 'Zolotonosha', 'Smila', 'Chernihiv', 'Zhashkiv', 'Golovanivsk', 'Talne', 'Zvenigorodka', 'Shpola', 'Nova Odesa', 'Synevyr', 'Aleksandrivka', 'Khorol', 'Shepetivka', 'Bila Tserkva', 'Skvyra', 'Popilnya', 'Silce', 'Hotyn', 'Kagarlik', 'Korsun Shevchenkivsky', 'Horodyshche', 'Volovets', 'Solotvyno', 'Izmajil', 'Sarata', 'Tatarbunary', 'Buchach', 'Oleksandriia', 'Nyzhni Vorota', 'Teresva', 'Skole', 'Slavjansk', 'Pokrovsk', 'Kramatorsk', 'Zvyahel', 'Vyžnycja', 'Storozhynets', 'Chutove', 'Zabolotiv', 'Lokhvytsia', 'Zboriv', 'Valky', 'Burshtyn'],
        'Czech Republic' => ['Ostrava', 'Frýdek-Místek', 'Brno', 'Praha', 'Nový Jičín', 'Olomouc', 'Plzeň', 'Hradec Králové', 'Ústí nad Labem', 'Znojmo', 'Karlovy Vary', 'Jičín', 'Mladá Boleslav', 'Bílá', 'České Budějovice', 'Příbram', 'Dobříš', 'Uherský Brod', 'Uherské Hradiště', 'Valašské Meziříčí', 'Liberec', 'Prostějov', 'Kolin', 'Jihlava', 'Humpolec', 'Cheb', 'Nachod', 'Český Krumlov', 'Jablonec n. Nisou', 'Železný Brod', 'Turnov', 'Chomutov', 'Louny', 'Slany', 'Benesov', 'Rokycany', 'Pardubice', 'Pisek', 'Vernovice', 'Kladno', 'Podebrady', 'Vysoké Mýto', 'Teplice', 'Břeclav'],
        'Slovenia' => ['Maribor', 'Bled', 'Ljubljana'],
        'Bosna a Hercegovina' => ['Sarajevo']
    ];
    
    $countryId = 1;
    $cityId = 1;
    
    foreach ($countriesData as $countryName => $cities) {
        echo "<h2>Processing Country: $countryName</h2>";
        
        // Create country
        $country = new Country();
        $country->setId($countryId);
        $country->getName()->setString($englishLanguage, $countryName);
        $country->getName()->setString($czechLanguage, $countryName); // Using same name for Czech for now
        
        $countryService->saveCountry($country);
        echo "<p>✅ Created country: $countryName (ID: $countryId)</p>";
        
        // Create cities for this country
        $cityCount = 0;
        foreach ($cities as $cityName) {
            $city = new City();
            $city->setId($cityId);
            $city->setCountry($country);
            $city->getName()->setString($englishLanguage, $cityName);
            $city->getName()->setString($czechLanguage, $cityName); // Using same name for Czech for now
            $city->setDeleted(false); // Set as not deleted (active)
            
            $cityService->saveCity($city);
            $cityCount++;
            $cityId++;
        }
        
        echo "<p>✅ Created $cityCount cities for $countryName</p>";
        $countryId++;
    }
    
    echo "<h2>✅ Population Complete!</h2>";
    echo "<p><strong>Total Countries:</strong> " . ($countryId - 1) . "</p>";
    echo "<p><strong>Total Cities:</strong> " . ($cityId - 1) . "</p>";
    echo "<p><a href='/'>Go to Homepage</a> to test the search form!</p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error occurred:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} 