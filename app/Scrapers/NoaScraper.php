<?php

namespace App\Scrapers;

use Facebook\WebDriver\WebDriverBy;
use Carbon\Carbon;

class NoaScraper
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
        
        // 🎯 A titkos fegyver: Mivel a lapozóban el vannak rejtve a gombok, 
        // egy JavaScript-tel kiszedjük az összes létező linket a DOM-ból, 
        // függetlenül attól, hogy látszanak-e éppen a képernyőn!
        $extractedUrls = $driver->executeScript("
            return Array.from(document.querySelectorAll('a'))
                        .map(a => a.href)
                        .filter(href => href.includes('/event/') || href.includes('/festival/') || href.includes('buy-tickets'));
        ");

        if (is_array($extractedUrls)) {
            foreach ($extractedUrls as $href) {
                if (!str_starts_with($href, 'http')) {
                    $href = 'https://www.noa-zrce.com' . $href;
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
            $title = str_replace([' | Noa Beach Club', 'Tickets', 'Festival'], '', $title);

            $description = 'Epic party at Noa Beach Club Zrce.';
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
                $startTime = Carbon::parse($startTimeStr)->setTimezone('Europe/Zagreb')->format('Y-m-d H:i:s');
            } elseif (preg_match('/(\d{4}[-\/]\d{2}[-\/]\d{2})/', $url, $matches)) {
                $startTime = Carbon::parse($matches[1] . ' 16:00:00')->format('Y-m-d H:i:s'); // Noa Zrce délután kezdi a bulikat
            }
            if (!$startTime) return null; 

            $genre = 'EDM';
            $textLower = strtolower($title . ' ' . $description);
            if (str_contains($textLower, 'techno')) $genre = 'Techno';
            elseif (str_contains($textLower, 'house')) $genre = 'House';
            elseif (str_contains($textLower, 'hardstyle') || str_contains($textLower, 'circus maximus')) $genre = 'Hardstyle';

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