<?php

namespace App\Scrapers;

use Facebook\WebDriver\WebDriverBy;
use Carbon\Carbon;

class UshuaiaScraper
{
    // 1. Összegyűjti a linkeket a naptárból
    public function getEventLinks($driver): array
    {
        $lastHeight = $driver->executeScript("return document.body.scrollHeight");
        $attempts = 0;
        
        while ($attempts < 6) { 
            $driver->executeScript("window.scrollTo(0, document.body.scrollHeight);");
            sleep(2); 
            $newHeight = $driver->executeScript("return document.body.scrollHeight");
            if ($newHeight == $lastHeight) break; 
            $lastHeight = $newHeight;
            $attempts++;
        }

        $links = [];
        $elements = $driver->findElements(WebDriverBy::cssSelector('a'));
        
        foreach ($elements as $element) {
            $href = $element->getAttribute('href');
            if ($href && str_contains($href, '/club/events/')) {
                if (!str_starts_with($href, 'http')) {
                    $href = 'https://www.theushuaiaexperience.com' . $href;
                }
                $links[] = $href;
            }
        }
        
        return array_values(array_unique($links)); 
    }

    // 2. Kinyeri az adatokat egyenesen a HTML-ből (DOM)
    public function extractEventDetails($driver): ?array
    {
        try {
            $url = $driver->getCurrentURL();
            
            // --- 1. DÁTUM KINYERÉSE ---
            $startTime = null;
            if (preg_match('/on-(\d{4}-\d{2}-\d{2})/', $url, $matches)) {
                $startTime = Carbon::parse($matches[1] . ' 17:00:00')->format('Y-m-d H:i:s');
            } elseif (preg_match('/(\d{4}-\d{2}-\d{2})/', $url, $matches)) {
                $startTime = Carbon::parse($matches[1] . ' 17:00:00')->format('Y-m-d H:i:s');
            }
            if (!$startTime) return null; 

            // --- 2. CÍM KINYERÉSE ---
            $titleElements = $driver->findElements(WebDriverBy::cssSelector('h1'));
            $title = count($titleElements) > 0 ? trim($titleElements[0]->getText()) : $driver->getTitle();
            $title = str_replace([' | Ushuaïa Ibiza', ' - Ushuaïa Ibiza', 'Tickets', 'Buy'], '', $title);

            // --- 3. READ MORE GOMB MEGNYOMÁSA (JavaScript-tel) ---
            // A legbiztosabb mód: a JS megkeresi a gombot és rákattint, így a DOM frissül.
            try {
                $driver->executeScript("
                    let elements = document.querySelectorAll('button, a, span');
                    for(let el of elements) {
                        if(el.innerText && el.innerText.toUpperCase().trim() === 'READ MORE') {
                            el.click();
                            break;
                        }
                    }
                ");
                sleep(1); // Várunk picit, hogy az Alpine.js kinyissa a panelt
            } catch (\Exception $e) {}

            // --- 4. LEÍRÁS KINYERÉSE ---
            $description = 'Official event at Ushuaïa Ibiza.';
            try {
                // Ezzel a JS megoldással a teljes '.prose' blokk szövegét megkapjuk egyben.
                $descText = $driver->executeScript("
                    let proseElement = document.querySelector('.prose');
                    if (proseElement) {
                        return proseElement.innerText || proseElement.textContent;
                    }
                    return '';
                ");
                
                if (!empty(trim($descText))) {
                    $description = trim($descText);
                }
            } catch (\Exception $e) {}

            // --- 5. A VALÓDI POSZTER KÉP KINYERÉSE ---
            $image = null;
            try {
                $images = $driver->findElements(WebDriverBy::cssSelector('img'));
                foreach ($images as $img) {
                    $src = $img->getAttribute('src');
                    // Átugorjuk a logókat és ikonokat, az első nagy kép kell nekünk
                    if ($src && !str_contains(strtolower($src), 'logo') && !str_contains(strtolower($src), 'icon') && !str_ends_with(strtolower($src), 'svg')) {
                        $image = $src;
                        break; 
                    }
                }
            } catch (\Exception $e) {}

            // --- STÍLUS MEGHATÁROZÁSA ---
            $genre = 'EDM';
            $textLower = strtolower($title . ' ' . $description);
            
            if (str_contains($textLower, 'techno') || str_contains($textLower, 'ants')) $genre = 'Techno';
            elseif (str_contains($textLower, 'house') || str_contains($textLower, 'defected') || str_contains($textLower, 'calvin harris') || str_contains($textLower, 'david guetta')) $genre = 'House';
            elseif (str_contains($textLower, 'trance') || str_contains($textLower, 'armin')) $genre = 'Trance';

            return [
                'title' => trim(strip_tags($title)),
                'description' => trim(strip_tags($description)),
                'image_url' => $image,
                'start_time' => $startTime,
                'genre' => $genre,
                'age_limit' => 18, 
                'ticket_url' => $url
            ];

        } catch (\Exception $e) {
            return null;
        }
    }
}