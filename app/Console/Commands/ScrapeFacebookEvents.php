<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\FacebookPage;
use App\Models\Location;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys; 

class ScrapeFacebookEvents extends Command
{
    protected $signature = 'events:scrape';
    protected $description = 'V82 - Stabil Verzió + Kényszerített Koordináta';

    public function handle()
    {
        $this->info("📈 V82 - Stabil Robot indítása...");

        $c_user = env('FB_COOKIE_USER');
        $xs = env('FB_COOKIE_XS');

        if (!$c_user || !$xs) {
            $this->error("❌ HIBA: Nincs süti az .env fájlban!");
            return;
        }

        try {
            $options = (new ChromeOptions())->addArguments([
                '--disable-gpu', 
                '--window-size=1920,1080', 
                '--no-sandbox',
                '--disable-blink-features=AutomationControlled', 
                '--lang=hu-HU',
                '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36'
            ]);

            $driver = RemoteWebDriver::create('http://localhost:9515', DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options));

            $driver->get('https://www.facebook.com/');
            $driver->manage()->addCookie(['name' => 'c_user', 'value' => $c_user, 'domain' => '.facebook.com', 'path' => '/']);
            $driver->manage()->addCookie(['name' => 'xs', 'value' => $xs, 'domain' => '.facebook.com', 'path' => '/']);
            $driver->get('https://www.facebook.com/'); 
            sleep(3);

            $pages = FacebookPage::with('city')->where('is_active', true)->get();

            foreach ($pages as $page) {
                $cityName = $page->city->name ?? '';
                $this->info("🔍 " . $page->name . " (" . ($cityName ?: 'Nincs város') . ") feldolgozása...");

                // --- 📍 HELYSZÍN LÉTREHOZÁSA ÉS AZONNALI KOORDINÁTA KÉNYSZERÍTÉS ---
                $location = Location::firstOrCreate(
                    ['name' => $page->name],
                    [
                        'city_id' => $page->city_id,
                        'country_id' => $page->city->country_id ?? 1,
                        'address' => $cityName,
                        'slug' => Str::slug($page->name),
                        'lat' => 0,
                        'lng' => 0
                    ]
                );

                // ITT A JAVÍTÁS: Ha nincs koordináta, akkor keressük meg "Klub Név + Város" alapján AZONNAL!
                // Nem várunk az eseményekre, mert az bizonytalan.
                if (floatval($location->lat) == 0) {
                    $this->line("   📍 Nincs koordináta! Keresés: '{$page->name}, {$cityName}'");
                    
                    // Próbálkozás 1: Klub neve + Város (Pl: Praterdome Vienna)
                    $coords = $this->getCoordinatesFromAddress($page->name . ', ' . $cityName);
                    
                    // Próbálkozás 2: Csak a klub neve (Pl: Praterdome)
                    if (!$coords) {
                        $coords = $this->getCoordinatesFromAddress($page->name);
                    }

                    // Próbálkozás 3: Végső esetben a Város (Pl: Vienna) - hogy ne 0 legyen!
                    if (!$coords) {
                        $coords = $this->getCoordinatesFromAddress($cityName);
                    }

                    if ($coords) {
                        $location->lat = $coords['lat'];
                        $location->lng = $coords['lng'];
                        // Ha még csak a város volt a címe, akkor legalább írjuk be a klub nevét címnek ideiglenesen
                        if ($location->address == $cityName) {
                            $location->address = $page->name . ', ' . $cityName;
                        }
                        $location->save();
                        $this->info("      ✅ Koordináta mentve: {$coords['lat']}, {$coords['lng']}");
                    }
                }
                // ---------------------------------------------------------------------
                
                $path = parse_url($page->url, PHP_URL_PATH);
                $slug = trim($path, '/');
                $slug = str_replace(['/events', 'upcoming_hosted_events'], '', $slug);
                $url = "https://www.facebook.com/{$slug}/upcoming_hosted_events";

                $driver->get($url);
                sleep(5); 
                
                // --- LISTA GÖRGETÉS ---
                $this->info("   🐛 Lista betöltése...");
                $driver->executeScript("document.body.style.zoom = '25%'");
                sleep(2);
                $previousCount = 0; $noChangeCounter = 0;
                for ($i = 1; $i <= 15; $i++) {
                    $elements = $driver->findElements(WebDriverBy::cssSelector('a[href*="/events/"]'));
                    $currentCount = count($elements);
                    if ($currentCount > 0) {
                        $lastElement = end($elements);
                        try { $driver->executeScript("arguments[0].scrollIntoView({behavior: 'smooth', block: 'center'});", [$lastElement]); } catch (\Exception $e) {}
                    } else {
                        $driver->executeScript("window.scrollTo(0, document.body.scrollHeight);");
                    }
                    sleep(3); 
                    if ($currentCount == $previousCount) {
                        $noChangeCounter++;
                        try { $driver->getKeyboard()->sendKeys(WebDriverKeys::PAGE_DOWN); } catch(\Exception $e){}
                        sleep(1);
                        try { $driver->getKeyboard()->sendKeys(WebDriverKeys::END); } catch(\Exception $e){}
                        if ($noChangeCounter >= 5) { break; }
                    } else {
                        $noChangeCounter = 0; 
                    }
                    $previousCount = $currentCount;
                }
                $driver->executeScript("document.body.style.zoom = '100%'");
                sleep(2);
                
                $elements = $driver->findElements(WebDriverBy::cssSelector('a[href*="/events/"]'));
                $eventLinks = [];
                foreach ($elements as $element) {
                    try {
                        $href = $element->getAttribute('href');
                        if (preg_match('/events\/(\d+)/', $href, $matches)) {
                            $eventLinks[] = "https://www.facebook.com/events/" . $matches[1];
                        }
                    } catch (\Exception $e) { continue; }
                }
                $eventLinks = array_unique($eventLinks);
                $this->info("   🧐 Összesen találtam " . count($eventLinks) . " eseményt.");

                foreach ($eventLinks as $link) {
                    preg_match('/events\/(\d+)/', $link, $matches);
                    $eventId = $matches[1];
                    
                    $driver->get($link);
                    
                    $attempts = 0;
                    $maxAttempts = 5; 
                    $ticketUrl = null;
                    $interested = 0;
                    $fullText = "";
                    $bodyText = "";
                    $title = "";
                    $jsonDate = null;

                    while ($attempts < $maxAttempts) {
                        $attempts++;
                        sleep(2); 

                        try {
                            $jsonDate = $driver->executeScript("
                                let scripts = document.querySelectorAll('script[type=\"application/ld+json\"]');
                                for(let s of scripts) {
                                    try {
                                        let data = JSON.parse(s.innerText);
                                        if (Array.isArray(data)) data = data[0];
                                        if(data['@type'] === 'Event' && data.startDate) {
                                            return data.startDate;
                                        }
                                    } catch(e){}
                                }
                                return null;
                            ");
                        } catch (\Exception $e) {}

                        if ($jsonDate) break;

                        try {
                            $driver->executeScript("
                                let buttons = document.querySelectorAll('div[role=\"button\"], span[role=\"button\"], div.x1i10hfl');
                                for (let btn of buttons) {
                                    if (btn.innerText.includes('Továbbiak') || btn.innerText.includes('See more')) btn.click();
                                }
                            ");
                        } catch (\Exception $e) {}
                        
                        $driver->executeScript('window.scrollBy(0, 500);');
                    }

                    // ADATOK
                    try {
                        $mainElement = $driver->findElement(WebDriverBy::cssSelector('[role="main"]'));
                        $fullText = $mainElement->getText();
                    } catch (\Exception $e) {
                        $fullText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
                    }


                    // --- 📍 PONTOS CÍM KINYERÉS A SZÖVEGBŐL (A TE EREDETI LOGIKÁD) ---
                    // Ez továbbra is hasznos, mert ha a Facebook kiírja az utcanevet, akkor frissítjük a címet!
                    
                    $extractedAddress = null;
                    $lines = explode("\n", str_replace("\r", "", $fullText));
                    $cityNameLower = mb_strtolower($page->city->name ?? '');

                    foreach ($lines as $line) {
                        $line = trim($line);
                        $lineLower = mb_strtolower($line);
                        
                        // Ha a sor tartalmazza a város nevét és van benne vessző
                        if ($cityNameLower && str_contains($lineLower, $cityNameLower) && str_contains($line, ',') && mb_strlen($line) < 100) {
                            if (!str_contains($lineLower, 'eseménye') && !str_contains($lineLower, 'event by') && !str_contains($lineLower, 'facebookon')) {
                                $parts = explode(',', $line);
                                $cleanStreet = trim($parts[0]);
                                if (mb_strlen($cleanStreet) > 3) {
                                    $extractedAddress = $cleanStreet; 
                                    break; 
                                }
                            }
                        }
                    }

                    if ($extractedAddress) {
                        // Ha találtunk pontosabb címet (utcanevet), akkor frissítjük az adatbázist!
                        $location->address = $extractedAddress; 
                        
                        // És ha eddig nem volt jó koordináta (vagy csak város szintű), akkor most pontosítunk!
                        $searchQuery = $extractedAddress . ', ' . $cityName;
                        $coords = $this->getCoordinatesFromAddress($searchQuery);
                        
                        if ($coords) {
                            $location->lat = $coords['lat'];
                            $location->lng = $coords['lng'];
                            $this->line("      📍 Cím pontosítva a szövegből: '$extractedAddress' (GPS frissítve)");
                        }
                        $location->save(); 
                    }
                    // -------------------------------------------------------------


                    try {
                        $bodyText = $driver->findElement(WebDriverBy::tagName('body'))->getText();
                    } catch (\Exception $e) {
                        $bodyText = $fullText;
                    }
                    
                    $title = $driver->getTitle();
                    $title = str_replace([' | Facebook', 'Events'], '', $title);
                    $title = preg_replace('/^\(\d+\)\s*/', '', $title);

                    // --- LEÍRÁS TISZTÍTÁSA ---
                    $description = "Részletek az eseménynél.";
                    $startMarkers = ['Nyilvános · Bárki a Facebookon vagy azon kívül', 'Nyilvános', '· Bárki a Facebookon vagy azon kívül'];
                    foreach ($startMarkers as $marker) {
                        if (str_contains($fullText, $marker)) {
                            $parts = explode($marker, $fullText);
                            if (count($parts) > 1) {
                                $rawDesc = $parts[1]; 
                                $endMarkers = ["Kevesebb jelenjen meg", "Show less", "Szervezők", "Jegyek", "Vendéglista", "Az eseményről", "Helyszín", "Vendégek"];
                                foreach ($endMarkers as $endMarker) {
                                    $subParts = explode($endMarker, $rawDesc);
                                    if (count($subParts) > 1) $rawDesc = $subParts[0]; 
                                }
                                $description = trim($rawDesc);
                                break;
                            }
                        }
                    }
                    $description = preg_replace('/^.*?Bárki a Facebookon vagy azon kívül.*?$/m', '', $description);
                    $description = ltrim($description, " .·\n\r\t"); 
                    if (strlen($description) < 10) $description = "Részletek az eseménynél.";

                    // KÉP (Javítva, hogy ne legyen hiba, ha nincs kép)
                    $imageUrl = null;
                    try {
                        $images = $driver->findElements(WebDriverBy::tagName('img'));
                        foreach ($images as $img) {
                            try {
                                $src = $img->getAttribute('src');
                                $width = (int)$driver->executeScript("return arguments[0].naturalWidth", [$img]);
                                if ($width > 500 && str_contains($src, 'scontent')) {
                                    $imageUrl = html_entity_decode($src);
                                    break; 
                                }
                            } catch (\Exception $e) { continue; }
                        }
                    } catch (\Exception $e) {}

                    // --- STATISZTIKA ---
                    $parseNumber = function($str) {
                        $str = mb_strtoupper(trim($str));
                        $multiplier = 1;
                        if (str_contains($str, 'E') || str_contains($str, 'K')) {
                            $multiplier = 1000;
                            $str = str_replace(['E', 'K'], '', $str);
                        } elseif (str_contains($str, 'M')) {
                            $multiplier = 1000000;
                            $str = str_replace('M', '', $str);
                        }
                        $str = str_replace(',', '.', $str);
                        $str = preg_replace('/[^\d.]/', '', $str);
                        return (int)(floatval($str) * $multiplier);
                    };

                    if (preg_match('/([\d,.]+\s*[EKM]?)\s*ember\s*válaszolt/iu', $bodyText, $m)) {
                        $interested = $parseNumber($m[1]);
                    } else {
                        if (preg_match('/([\d,.]+\s*[EKM]?)\s*érdeklődő/iu', $bodyText, $m)) {
                            $interested = $parseNumber($m[1]);
                        }
                    }

                    // JEGYEK
                    if (!$ticketUrl) {
                        $blackList = ['facebook.com', 'messenger.com', 'whatsapp.com', 'maps.google', 'goo.gl/maps', 'waze.com', 'youtube.com', 'youtu.be', 'instagram.com', 'tiktok.com', 'support.google'];
                        try {
                            $allLinks = $driver->findElements(WebDriverBy::tagName('a'));
                            foreach ($allLinks as $l) {
                                $href = $l->getAttribute('href');
                                if (!$href) continue;
                                if (str_contains($href, 'facebook.com/l.php')) {
                                    $parsed = parse_url($href);
                                    parse_str($parsed['query'] ?? '', $q);
                                    if (isset($q['u'])) $href = urldecode($q['u']);
                                }
                                $isClean = true;
                                foreach($blackList as $b) { if (str_contains($href, $b)) $isClean = false; }
                                if ($isClean && str_contains($href, 'http')) {
                                    $ticketUrl = $href;
                                    break; 
                                }
                            }
                        } catch (\Exception $e) {}
                    }

                    // DÁTUM
                    $startTime = null;
                    if ($jsonDate) {
                        try {
                            $startTime = Carbon::parse($jsonDate)->setTimezone('Europe/Budapest');
                        } catch (\Exception $e) {}
                    }

                    if (!$startTime) {
                        $cleanTitle = mb_strtolower(str_replace(['|', 'I ', 'l '], '.', $title));
                        if (preg_match('/(?<!\d)(\d{1,2})\s*[.\/]\s*(\d{1,2})(?!\d)/', $cleanTitle, $m)) {
                            $m1 = (int)$m[1]; $m2 = (int)$m[2];
                            if ($m1 >= 1 && $m1 <= 12 && $m2 >= 1 && $m2 <= 31) {
                                $startTime = Carbon::create(2026, $m1, $m2, 19, 0, 0, 'Europe/Budapest');
                            }
                        }
                        if (!$startTime) {
                            $cleanedFullText = str_replace('|', "\n", $fullText);
                            $lines = explode("\n", $cleanedFullText);
                            $monthMap = ['jan'=>1,'feb'=>2,'febr'=>2,'már'=>3,'mar'=>3,'márc'=>3,'ápr'=>4,'apr'=>4,'máj'=>5,'maj'=>5,'jún'=>6,'jun'=>6,'júl'=>7,'jul'=>7,'aug'=>8,'szep'=>9,'sep'=>9,'sze'=>9,'szept'=>9,'okt'=>10,'nov'=>11,'dec'=>12];

                            foreach ($lines as $index => $line) {
                                if ($index > 50) break;
                                $cleanLine = mb_strtolower(trim($line));

                                if (preg_match('/(202\d)\.\s*([a-zA-Záéíóöőúüű]+)\s*(\d{1,2})\./u', $cleanLine, $m)) {
                                    $y = (int)$m[1]; $monthName = $m[2]; $d = (int)$m[3];
                                    $mon = 0;
                                    foreach ($monthMap as $key => $val) { if (str_starts_with($monthName, $key)) { $mon = $val; break; } }
                                    if ($mon > 0) {
                                        $startTime = Carbon::create($y, $mon, $d, 19, 0, 0, 'Europe/Budapest');
                                        if (preg_match('/(\d{1,2}):(\d{2})/', $cleanLine, $tm)) { $startTime->setTime((int)$tm[1], (int)$tm[2]); }
                                        break;
                                    }
                                }
                                if (preg_match('/^(ma|holnap)/iu', $cleanLine, $m)) {
                                    $startTime = (mb_strtolower($m[1]) == 'ma') ? Carbon::today() : Carbon::tomorrow();
                                    $startTime->setTime(19, 0);
                                    if (preg_match('/(\d{1,2}):(\d{2})/', $cleanLine, $tm)) { $startTime->setTime((int)$tm[1], (int)$tm[2]); }
                                    break;
                                }
                                if (preg_match('/(e hét|jövő hét)\s+(hétfő|kedd|szerda|csütörtök|péntek|szombat|vasárnap)/iu', $cleanLine, $m)) {
                                    $dayMap = ['hétfő'=>'monday','kedd'=>'tuesday','szerda'=>'wednesday','csütörtök'=>'thursday','péntek'=>'friday','szombat'=>'saturday','vasárnap'=>'sunday'];
                                    $startTime = ($m[1] == 'e hét') ? Carbon::parse("this " . $dayMap[$m[2]]) : Carbon::parse("next " . $dayMap[$m[2]]);
                                    $startTime->setTime(19, 0);
                                    if (preg_match('/(\d{1,2}):(\d{2})/', $cleanLine, $tm)) { $startTime->setTime((int)$tm[1], (int)$tm[2]); }
                                    break;
                                }
                                foreach ($monthMap as $key => $val) {
                                    if (preg_match('/' . $key . '[a-z]*\.?\s*(\d{1,2})/', $cleanLine, $m)) {
                                        if (!str_contains($cleanLine, '202')) { 
                                            $startTime = Carbon::create(2026, $val, (int)$m[1], 19, 0, 0, 'Europe/Budapest');
                                            if (preg_match('/(\d{1,2}):(\d{2})/', $cleanLine, $tm)) { $startTime->setTime((int)$tm[1], (int)$tm[2]); }
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (!$startTime) {
                        $startTime = Carbon::now()->addDays(30);
                    }

                    // MENTÉS
                    Event::updateOrCreate(
                        ['facebook_event_id' => $eventId],
                        [
                            'title' => $title,
                            'description' => $description,
                            'start_time' => $startTime,
                            'location_id' => $location->id,
                            'facebook_url' => $link,
                            // JAVÍTVA: Ha null, akkor legyen üres string, hogy ne szálljon el a kód
                            'image_url' => $imageUrl ?? '', 
                            'ticket_url' => $ticketUrl,
                            'interested_count' => $interested,
                            'created_by' => 1,
                            'genre' => 'Egyéb',
                        ]
                    );

                    $this->info("       ✅ SIKER: $title");
                }
                
            }
            $driver->quit();
            $this->info("🏁 KÉSZ!");
        } catch (\Exception $e) {
            $this->error("❌ HIBA: " . $e->getMessage());
        }
    }

    private function getCoordinatesFromAddress($address)
    {
        try {
            sleep(1); 
            $response = Http::withHeaders([
                'User-Agent' => 'PartyFinderApp/1.0 (admin@example.com)'
            ])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1
            ]);

            $data = $response->json();

            if (!empty($data) && isset($data[0])) {
                return [
                    'lat' => $data[0]['lat'],
                    'lng' => $data[0]['lon']
                ];
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}