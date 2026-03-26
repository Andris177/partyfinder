<?php

namespace App\Scrapers;

use Facebook\WebDriver\WebDriverBy;
use Carbon\Carbon;

class LarocScraper
{
    // 1. Összegyűjti a linkeket
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
            // Célba vesszük a belső és a külső (brazil) jegylinkeket is
            if ($href && (str_contains($href, '/event') || str_contains($href, 'ingresse.com') || str_contains($href, 'sympla.com.br'))) {
                if (!str_starts_with($href, 'http')) {
                    $href = 'https://www.laroc.club' . $href;
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
            
            $titleElements = $driver->findElements(WebDriverBy::cssSelector('h1, h2'));
            $title = count($titleElements) > 0 ? trim($titleElements[0]->getText()) : $driver->getTitle();
            $title = str_replace([' | Laroc Club', 'Tickets', 'Ingressos'], '', $title);

            $description = 'Sunset event at Laroc Club Valinhos.';
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
                $startTime = Carbon::parse($startTimeStr)->setTimezone('America/Sao_Paulo')->format('Y-m-d H:i:s');
            } elseif (preg_match('/(\d{4}[-\/]\d{2}[-\/]\d{2})/', $url, $matches)) {
                $startTime = Carbon::parse($matches[1] . ' 16:00:00')->format('Y-m-d H:i:s'); // Laroc naplementés klub, 16:00 körül nyit
            }
            if (!$startTime) return null; 

            $genre = 'EDM';
            $textLower = strtolower($title . ' ' . $description);
            if (str_contains($textLower, 'techno') || str_contains($textLower, 'boris')) $genre = 'Techno';
            elseif (str_contains($textLower, 'house') || str_contains($textLower, 'vintage culture')) $genre = 'House';

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