<?php

namespace App\Scrapers;

use Facebook\WebDriver\WebDriverBy;
use Carbon\Carbon;

class WHPScraper
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
            if ($href && (str_contains($href, '/event/') || str_contains($href, '/calendar/'))) {
                if (!str_starts_with($href, 'http')) {
                    $href = 'https://www.thewarehouseproject.com' . $href;
                }
                $links[] = $href;
            }
        }
        
        return array_values(array_unique($links)); 
    }

    // 2. Kinyeri az adatokat
    public function extractEventDetails($driver): ?array
    {
        try {
            $url = $driver->getCurrentURL();
            
            // --- CÍM KINYERÉSE ---
            $titleElements = $driver->findElements(WebDriverBy::cssSelector('h1'));
            $title = count($titleElements) > 0 ? trim($titleElements[0]->getText()) : $driver->getTitle();
            $title = str_replace([' | The Warehouse Project', 'Tickets', 'Buy'], '', $title);

            // --- ALAP ADATOK (Meta fallback) ---
            $description = 'WHP Manchester Event.';
            $image = null;
            $startTimeStr = null;

            try {
                $metaDesc = $driver->findElements(WebDriverBy::cssSelector('meta[name="description"], meta[property="og:description"]'));
                if (count($metaDesc) > 0) $description = $metaDesc[0]->getAttribute('content');
                
                $metaImage = $driver->findElements(WebDriverBy::cssSelector('meta[property="og:image"]'));
                if (count($metaImage) > 0) $image = $metaImage[0]->getAttribute('content');
            } catch (\Exception $e) {}

            // --- JSON-LD KERESÉSE ---
            try {
                $scripts = $driver->findElements(WebDriverBy::cssSelector('script[type="application/ld+json"]'));
                foreach ($scripts as $script) {
                    $json = json_decode($script->getAttribute('innerHTML'), true);
                    
                    // Rekurzív keresés az Event típusra
                    $eventData = null;
                    if (isset($json['@type']) && str_contains(is_array($json['@type']) ? implode(',', $json['@type']) : $json['@type'], 'Event')) {
                        $eventData = $json;
                    } elseif (is_array($json)) {
                        foreach ($json as $item) {
                            if (is_array($item) && isset($item['@type']) && str_contains(is_array($item['@type']) ? implode(',', $item['@type']) : $item['@type'], 'Event')) {
                                $eventData = $item;
                                break;
                            }
                        }
                    }

                    if ($eventData) {
                        $title = $eventData['name'] ?? $title;
                        $description = $eventData['description'] ?? $description;
                        $image = is_array($eventData['image'] ?? null) ? ($eventData['image'][0] ?? $image) : ($eventData['image'] ?? $image);
                        $startTimeStr = $eventData['startDate'] ?? $startTimeStr;
                        break;
                    }
                }
            } catch (\Exception $e) {}

            // --- DÁTUM FELDOLGOZÁSA ---
            $startTime = null;
            if ($startTimeStr) {
                $startTime = Carbon::parse($startTimeStr)->setTimezone('Europe/London')->format('Y-m-d H:i:s');
            } elseif (preg_match('/(\d{4}[-\/]\d{2}[-\/]\d{2})/', $url, $matches)) {
                $startTime = Carbon::parse($matches[1] . ' 22:00:00')->format('Y-m-d H:i:s');
            }
            if (!$startTime) return null; 

            // --- STÍLUS MEGHATÁROZÁSA ---
            $genre = 'Techno'; // WHP alapból Techno/House fókuszú
            $textLower = strtolower($title . ' ' . $description);
            if (str_contains($textLower, 'house') || str_contains($textLower, 'disco')) $genre = 'House';
            elseif (str_contains($textLower, 'dnb') || str_contains($textLower, 'drum and bass') || str_contains($textLower, 'chase')) $genre = 'Drum & Bass';

            return [
                'title' => trim(strip_tags($title)),
                'description' => trim(strip_tags($description)),
                'image_url' => $image,
                'start_time' => $startTime,
                'genre' => $genre,
                'age_limit' => 18, 
                'ticket_url' => $url
            ];

        } catch (\Exception $e) { return null; }
    }
}