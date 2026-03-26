<?php

namespace App\Scrapers;

use Facebook\WebDriver\WebDriverBy;

class BootshausScraper implements ScraperInterface
{
    public function getEventLinks($driver): array
    {
        $links = [];
        $elements = $driver->findElements(WebDriverBy::tagName('a'));
        
        foreach ($elements as $element) {
            try {
                $href = $element->getAttribute('href');
                
                if ($href) {
                    // Keresünk minden olyan linket, amiben benne van az "/events/" 
                    // DE kizárjuk magát a főoldalt és az archívumot
                    if (str_contains($href, '/events/') && !preg_match('/\/events\/?$/', $href) && !str_contains($href, 'archive')) {
                        
                        // Ha a link relatív (nincs előtte a http), elé tesszük a domaint
                        if (!str_starts_with($href, 'http')) {
                            $href = 'https://bootshaus.tv' . $href;
                        }
                        
                        $links[] = $href;
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return array_unique($links); // Duplikációk kiszűrése
    }

    public function extractEventDetails($driver): ?array
    {
        try {
            // A látható szöveget (innerText) szedjük ki, ami TÖKÉLETESEN megőrzi a \n sortöréseket!
            $data = $driver->executeScript("
                var title = document.querySelector('meta[property=\"og:title\"]') ? document.querySelector('meta[property=\"og:title\"]').content : '';
                var img = document.querySelector('meta[property=\"og:image\"]') ? document.querySelector('meta[property=\"og:image\"]').content : '';
                
                // Megkeressük a legnagyobb szöveges dobozt, és a vizuális szövegét kérjük el (ez meghagyja a bekezdéseket!)
                var descBox = document.querySelector('.ticket-description') || document.querySelector('.event-description') || document.querySelector('.page-wrapper') || document.body;
                
                return {title: title, image: img, descText: descBox.innerText};
            ");

            if (empty($data['title'])) return null;

            $bodyText = $data['descText'];

            // 1. KORHATÁR (Most már mindent elkap!)
            $ageLimit = 0;
            if (preg_match('/(18\s*Jahre|18\s*years|18\+|ab\s*18|admission\s*18)/i', $bodyText)) $ageLimit = 18;
            elseif (preg_match('/(16\s*Jahre|16\s*years|16\+|ab\s*16|admission\s*16)/i', $bodyText)) $ageLimit = 16;
            elseif (preg_match('/(21\s*Jahre|21\s*years|21\+|ab\s*21)/i', $bodyText)) $ageLimit = 21;

            // 2. MŰFAJ (Minden új stílus benne van)
            $genre = 'Egyéb';
            $genreKeywords = [
                'Techno' => ['techno', 'schranz', 'hard techno', 'peak time'],
                'House' => ['house', 'tech house', 'deep house'],
                'Hardstyle' => ['hardstyle', 'rawstyle', 'frenchcore', 'hardcore', 'hardbrake'],
                'Drum & Bass' => ['drum & bass', 'dnb', 'liquicity', 'neurofunk'],
                'EDM' => ['edm', 'big room', 'electro', 'mainstage'],
                'Trance' => ['trance', 'psytrance', 'progressive'],
                'Retro' => ['90s', '2000s', 'classic rave', 'retro']
            ];
            foreach ($genreKeywords as $g => $keywords) {
                foreach ($keywords as $kw) {
                    if (stripos($bodyText, $kw) !== false) {
                        $genre = $g; break 2;
                    }
                }
            }

            // 3. DÁTUM KERESŐ
            $startTime = null;
            $lines = explode("\n", $bodyText);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                if (preg_match('/(sale|pre-sale|start|ticket|waiting list|einlass)/i', $line)) continue;

                if (preg_match('/(\d{2})\.(\d{2})\.(\d{2,4})/', $line, $matches)) {
                    $day = $matches[1]; $month = $matches[2];
                    $year = strlen($matches[3]) == 2 ? '20' . $matches[3] : $matches[3];
                    $hour = 22; $min = 0;
                    if (preg_match('/(\d{2}):(\d{2})/', $line, $tMatches)) { $hour = $tMatches[1]; $min = $tMatches[2]; }
                    $startTime = \Carbon\Carbon::create($year, $month, $day, $hour, $min, 0, 'Europe/Berlin'); break; 
                }

                $months = ['Jan'=>1, 'Feb'=>2, 'Mar'=>3, 'Apr'=>4, 'May'=>5, 'Jun'=>6, 'Jul'=>7, 'Aug'=>8, 'Sep'=>9, 'Oct'=>10, 'Nov'=>11, 'Dec'=>12];
                if (preg_match('/(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\s+(\d{1,2})/i', $line, $eMatches)) {
                    $mName = ucfirst(substr($eMatches[1], 0, 3));
                    if (isset($months[$mName])) {
                        $year = date('Y'); if (preg_match('/20\d{2}/', $line, $yMatches)) $year = $yMatches[0];
                        $hour = 22; $min = 0;
                        if (preg_match('/(\d{2}):(\d{2})/', $line, $tMatches)) { $hour = $tMatches[1]; $min = $tMatches[2]; }
                        $startTime = \Carbon\Carbon::create($year, $months[$mName], $eMatches[2], $hour, $min, 0, 'Europe/Berlin'); break;
                    }
                }
            }

            $lines = explode("\n", $bodyText);
            $finalLines = [];

            // Ezeket a szavakat, ha önmagukban állnak a sorban, azonnal kidobjuk!
            $menuKeywords = [
                'NEWS', 'EVENTS', 'GALLERY', 'FAQ', 'MERCH-SHOP', 'APP', 'TICKET-SHOP', 
                'FESTIVALS', 'BOOTSHAUS MUSIC', 'JOBS', 'DETAILS', 'GENRES', 'TICKETS',
                'LOCATION', 'CONTACT', 'SITEMAP', 'STARTSEITE', 'ARTISTS', 'BEGIN', 'END'
            ];

            // Ha ezek közül BÁRMELYIKET meglátja a robot, azonnal leállítja az olvasást (Levágja a láblécet)
            $footerKeywords = [
                'TABLE BOOKING', 'PRESSE', 'PARTNER', 'INFLUENCER', 'PRIVACY', 'IMPRINT', 
                'LANGUAGE', 'SOCIAL MEDIA', 'TEL +49', 'FAX +49', 'E-MAIL INFO'
            ];

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if (empty($trimmed)) continue;
                
                $upperLine = strtoupper($trimmed);

                // 1. Ha a "TABLE BOOKING" vagy más lábléc jön, azonnal megállítjuk az olvasást!
                $hitFooter = false;
                foreach ($footerKeywords as $fw) {
                    if (str_contains($upperLine, $fw)) {
                        $hitFooter = true;
                        break;
                    }
                }
                if ($hitFooter) break; // Kilépünk a ciklusból, a szöveg alja kuka!

                // 2. Kidobjuk a pontosan megegyező menüszavakat (pl. "NEWS", "EVENTS")
                if (in_array($upperLine, $menuKeywords)) continue;

                // 3. Kidobjuk a fejlécben lévő széttöredezett dátum/idő morzsákat (pl. 21, MAR, 22:00, 2026, 10, OCT)
                if (preg_match('/^(\d{1,2}|\d{4}|JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC|\d{1,2}:\d{2})$/i', $upperLine)) continue;

                // 4. Kidobjuk a felesleges címismétléseket (pl. "EVENTS BOOTSHAUS PRES...")
                if (str_starts_with($upperLine, 'EVENTS ') || str_starts_with($upperLine, 'BOOTSHAUS ') || str_starts_with($upperLine, 'BLACKLIST ')) {
                    if (strlen($trimmed) < 50) continue; // Csak a rövid, menü-szerű címeket dobjuk el
                }

                // Ha átment az összes fenti szűrőn, akkor ez egy igazi mondat, elmentjük!
                $finalLines[] = $trimmed;
            }

            // Összefűzzük, és ha túl hosszú, biztonságosan vágjuk
            $cleanDescription = implode("\n\n", $finalLines);
            if (mb_strlen($cleanDescription, 'UTF-8') > 1500) {
                $cleanDescription = mb_substr($cleanDescription, 0, 1500, 'UTF-8') . '...';
            }

            if (!$startTime) return null;

            return [
                'title' => $data['title'],
                'description' => $cleanDescription ?: 'No description available.',
                'image_url' => $data['image'],
                'start_time' => $startTime,
                'genre' => $genre,
                'age_limit' => $ageLimit,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}