<?php

namespace App\Scrapers;

use Facebook\WebDriver\WebDriverBy;
use Carbon\Carbon;

class IlluzionScraper
{
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
            // A Thaiföldi klub linkjeinél az /events/ útvonalat keressük
            if ($href && str_contains($href, '/events/') && !str_ends_with($href, '/events/')) {
                if (!str_starts_with($href, 'http')) {
                    $href = 'https://www.illuzionphuket.com' . $href;
                }
                $links[] = $href;
            }
        }
        
        return array_values(array_unique($links)); 
    }

    public function extractEventDetails($driver): ?array
    {
        try {
            $url = $driver->getCurrentURL();
            
            $titleElements = $driver->findElements(WebDriverBy::cssSelector('h1'));
            $title = count($titleElements) > 0 ? trim($titleElements[0]->getText()) : $driver->getTitle();
            $title = str_replace([' | Illuzion Phuket', 'Tickets'], '', $title);

            $description = 'Unforgettable night at Illuzion Phuket.';
            $image = null;
            $startTimeStr = null;

            try {
                $metaDesc = $driver->findElements(WebDriverBy::cssSelector('meta[name="description"], meta[property="og:description"]'));
                if (count($metaDesc) > 0) $description = $metaDesc[0]->getAttribute('content');
                
                $metaImage = $driver->findElements(WebDriverBy::cssSelector('meta[property="og:image"]'));
                if (count($metaImage) > 0) $image = $metaImage[0]->getAttribute('content');
            } catch (\Exception $e) {}

            try {
                $scripts = $driver->findElements(WebDriverBy::cssSelector('script[type="application/ld+json"]'));
                foreach ($scripts as $script) {
                    $json = json_decode($script->getAttribute('innerHTML'), true);
                    
                    if (isset($json['@type']) && str_contains(is_array($json['@type']) ? implode(',', $json['@type']) : $json['@type'], 'Event')) {
                        $title = $json['name'] ?? $title;
                        $description = $json['description'] ?? $description;
                        $image = is_array($json['image'] ?? null) ? ($json['image'][0] ?? $image) : ($json['image'] ?? $image);
                        $startTimeStr = $json['startDate'] ?? $startTimeStr;
                        break;
                    }
                }
            } catch (\Exception $e) {}

            $startTime = null;
            if ($startTimeStr) {
                $startTime = Carbon::parse($startTimeStr)->setTimezone('Asia/Bangkok')->format('Y-m-d H:i:s');
            } elseif (preg_match('/(\d{4}[-\/]\d{2}[-\/]\d{2})/', $url, $matches)) {
                $startTime = Carbon::parse($matches[1] . ' 21:00:00')->format('Y-m-d H:i:s');
            }
            if (!$startTime) return null; 

            $genre = 'EDM';
            $textLower = strtolower($title . ' ' . $description);
            if (str_contains($textLower, 'techno')) $genre = 'Techno';
            elseif (str_contains($textLower, 'hip hop') || str_contains($textLower, 'central cee')) $genre = 'Hip Hop';
            elseif (str_contains($textLower, 'house')) $genre = 'House';

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