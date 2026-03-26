<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Http;

class ScrapeClubWebsites extends Command
{
    protected $signature = 'clubs:scrape {club?}';
    protected $description = 'Hibrid Web Scraper (JSON-LD + Mesterlövész modulok)';

    public function handle()
    {
        $targetClub = $this->argument('club');

        if ($targetClub) {
            $this->info("🚀 Scraper indítása KIZÁRÓLAG erre a klubra: {$targetClub}...");
        } else {
            $this->info("🚀 Hibrid Web Scraper elindítva AZ ÖSSZES klubra...");
        }

        $clubs = [
            [
                'name' => 'Bootshaus',
                'url' => 'https://bootshaus.tv/events',
                'module' => new \App\Scrapers\BootshausScraper()
            ],
            [
                'name' => 'Pacha Ibiza',
                'url' => 'https://pacha.com/events',
                'module' => new \App\Scrapers\PachaScraper()
            ],
            [
                'name' => 'Hi Ibiza', 
                'url' => 'https://www.hiibiza.com/events-calendar', 
                'module' => new \App\Scrapers\HiIbizaScraper()
            ],
            [
                'name' => 'Green Valley', 
                'url' => 'https://greenvalleybr.com/', 
                'module' => new \App\Scrapers\GreenValleyScraper()
            ],
            // --- AZ ÚJ IBIZAI KLUB ---
            [
                'name' => 'Ushuaia Ibiza', 
                'url' => 'https://www.theushuaiaexperience.com/en/club/calendar', 
                'module' => new \App\Scrapers\UshuaiaScraper()
            ],
            [
                'name' => 'The Warehouse Project', 
                'url' => 'https://www.thewarehouseproject.com/calendar', 
                'module' => new \App\Scrapers\WHPScraper()
            ],
            [
                'name' => 'Echostage', 
                'url' => 'https://echostage.com/all-events/', 
                'module' => new \App\Scrapers\EchostageScraper()
            ],
            [
                'name' => 'Savaya', 
                'url' => 'https://www.savaya.com/event-calendar', 
                'module' => new \App\Scrapers\SavayaScraper()
            ],
            [
                'name' => 'Laroc Club', 
                'url' => 'https://www.laroc.club/valinhos/en/', 
                'module' => new \App\Scrapers\LarocScraper()
            ],
            [
                'name' => 'Illuzion', 
                'url' => 'https://www.illuzionphuket.com/events/', 
                'module' => new \App\Scrapers\IlluzionScraper()
            ],
            [
                'name' => 'Noa Beach Club', 
                'url' => 'https://www.noa-zrce.com/en/club', 
                'module' => new \App\Scrapers\NoaScraper()
            ]
        ];

        try {
            $options = (new ChromeOptions())->addArguments([
                '--disable-gpu', 
                '--window-size=1920,1080', 
                '--no-sandbox',
                '--disable-blink-features=AutomationControlled', 
                '--lang=hu-HU',
                '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/125.0.0.0 Safari/537.36'
            ]);

            $driver = RemoteWebDriver::create('http://localhost:9515', DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $options));

            foreach ($clubs as $club) {
                $clubName = $club['name'];

                if ($targetClub && stripos($clubName, $targetClub) === false) {
                    continue; 
                }

                $this->info("🔍 {$clubName} feldolgozása...");

                $location = Location::where('name', $clubName)->first();
                if (!$location) {
                    $this->warn("⚠️ A(z) {$clubName} nincs az adatbázisban! Ugrom...");
                    continue;
                }

                if (floatval($location->lat) == 0) {
                    $coords = $this->getCoordinatesFromAddress($clubName . ', ' . ($location->city->name ?? ''));
                    if ($coords) {
                        $location->update(['lat' => $coords['lat'], 'lng' => $coords['lng']]);
                    }
                }

                $driver->get($club['url']);
                sleep(4); 
                
                $scraperModule = $club['module'];
                $eventLinks = $scraperModule->getEventLinks($driver);
                
                $this->info("   🧐 Összesen találtam " . count($eventLinks) . " eseményt/linket.");

                foreach ($eventLinks as $item) {
                    
                    // 🔴 ÚJ LOGIKA: Ha a scraper egyből az adatot adja vissza, nem töltünk be új oldalt
                    if (is_array($item)) {
                        $eventData = $item;
                    } else {
                        // Régi logika a többi klubnak (aloldal betöltése)
                        $driver->get($item);
                        sleep(3);
                        $eventData = $scraperModule->extractEventDetails($driver);
                    }

                    if ($eventData && !empty($eventData['title']) && !empty($eventData['start_time'])) {
                        Event::updateOrCreate(
                            ['ticket_url' => $eventData['ticket_url'] ?? (is_string($item) ? $item : null)], 
                            [
                                'title' => $eventData['title'],
                                'description' => $eventData['description'] ?? 'Részletek az oldalon.',
                                'start_time' => $eventData['start_time'],
                                'location_id' => $location->id,
                                'image_url' => $eventData['image_url'] ?? '',
                                'created_by' => 1,
                                'genre' => $eventData['genre'] ?? 'Egyéb',
                                'age_limit' => $eventData['age_limit'] ?? 0,
                                'facebook_url' => null,
                                'facebook_event_id' => null,
                                'facebook_attending_count' => 0,
                                'facebook_interested_count' => 0,
                            ]
                        );
                        $this->info("       ✅ SIKER: " . $eventData['title']);
                    }
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
            $response = Http::withHeaders(['User-Agent' => 'PartyFinderApp/1.0'])->get('https://nominatim.openstreetmap.org/search', [
                'q' => $address, 'format' => 'json', 'limit' => 1
            ]);
            $data = $response->json();
            if (!empty($data) && isset($data[0])) return ['lat' => $data[0]['lat'], 'lng' => $data[0]['lon']];
        } catch (\Exception $e) { return null; }
        return null;
    }
}