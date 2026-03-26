<?php

namespace App\Scrapers;

use Carbon\Carbon;
use Illuminate\Support\Str;

class GreenValleyScraper
{
    // 1. Összegyűjti egyből az ADATOKAT, mert nincsenek aloldal linkek
    public function getEventLinks($driver): array
    {
        // Lefuttatunk egy JS kódot a böngészőben, ami megkeresi az esemény-kártyákat
        $script = "
            let events = [];
            let cards = document.querySelectorAll('div');
            for (let card of cards) {
                let text = card.innerText || '';
                // Kártya azonosítása: van benne dátum vonal (|), 'ingressos' és ésszerű a mérete
                if (text.includes('|') && text.toLowerCase().includes('ingressos') && text.length < 500) {
                    // Duplikációk szűrése a szöveg eleje alapján
                    if (!events.some(e => text.includes(e.raw_text.substring(0, 30)))) {
                        let img = card.querySelector('img');
                        events.push({
                            raw_text: text,
                            image_url: img ? img.src : ''
                        });
                    }
                }
            }
            return events;
        ";

        // Végrehajtjuk a JS-t a Seleniumon keresztül
        $scrapedCards = $driver->executeScript($script);
        $extractedEvents = [];

        // Portugál hónapok fordítása
        $months = [
            'janeiro' => '01', 'fevereiro' => '02', 'março' => '03', 'marco' => '03',
            'abril' => '04', 'maio' => '05', 'junho' => '06', 'julho' => '07',
            'agosto' => '08', 'setembro' => '09', 'outubro' => '10', 'novembro' => '11', 'dezembro' => '12'
        ];

        $currentYear = date('Y'); // Az aktuális év

        foreach ($scrapedCards as $card) {
            $rawText = $card['raw_text'];
            
            // Szétbontjuk sorokra a kártya szövegét
            $lines = array_values(array_filter(array_map('trim', explode("\n", $rawText))));
            if (count($lines) < 2) continue;

            $dateStr = $lines[0]; // pl. "02 de Maio | 22h"
            $description = $lines[1]; // pl. "Pré-estreia do novo show do Alok..."
            
            // --- DÁTUM ÉS IDŐPONT KINYERÉSE ---
            $startTime = null;
            if (preg_match('/(\d{1,2})\s+de\s+([a-zA-Zç]+)\s*\|\s*(\d{1,2})h/i', $dateStr, $matches)) {
                $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                $monthName = strtolower($matches[2]);
                $hour = str_pad($matches[3], 2, '0', STR_PAD_LEFT);
                
                $month = $months[$monthName] ?? '01';
                
                // Formázás: YYYY-MM-DD HH:00:00
                $startTime = Carbon::createFromFormat('Y-m-d H:i', "$currentYear-$month-$day $hour:00")->format('Y-m-d H:i:s');
            } else {
                continue; // Ha nem tudjuk értelmezni a dátumot, eldobjuk (nem buli kártya)
            }

            // --- CÍM GENERÁLÁSA ---
            $title = $description;
            if (mb_strlen($description) > 40) {
                $title = mb_substr($description, 0, 40) . '...';
            }

            // Mivel a képről nem tudjuk leolvasni a nagy betűket, okosítjuk a címet a leírás alapján
            if (str_contains(strtolower($description), 'alok')) $title = 'Alok @ Green Valley';
            if (str_contains(strtolower($description), 'boris brejcha')) $title = 'Boris Brejcha @ Green Valley';

            // --- MŰFAJ ---
            $genre = 'EDM';
            $descLower = strtolower($description);
            if (str_contains($descLower, 'techno') || str_contains($descLower, 'boris brejcha')) $genre = 'Techno';
            elseif (str_contains($descLower, 'house') || str_contains($descLower, 'alok')) $genre = 'House';

            // --- FAKE TICKET URL GENERÁLÁS (Fontos az adatbázis frissítéshez!) ---
            // Mivel nincs igazi aloldal URL, generálunk egy egyedit a dátumból, hogy az Event::updateOrCreate jól működjön
            $uniqueFakeUrl = 'https://greenvalleybr.com/agenda#event-' . Str::slug($dateStr . '-' . $title);

            // Egyből az adatokat adjuk vissza link helyett!
            $extractedEvents[] = [
                'title' => $title,
                'description' => $description,
                'start_time' => $startTime,
                'image_url' => $card['image_url'],
                'ticket_url' => $uniqueFakeUrl,
                'genre' => $genre,
                'age_limit' => 18
            ];
        }

        return $extractedEvents;
    }

    // 2. Kinyeri az adatokat
    public function extractEventDetails($driver): ?array
    {
        // Ezt a függvényt a Green Valley esetében most már nem hívja meg a rendszer, 
        // de benne hagyjuk, hogy az architektúra és az esetleges Interface ne dőljön össze.
        return null;
    }
}