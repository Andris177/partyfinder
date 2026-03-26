<?php

namespace App\Scrapers;

use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;

class HiIbizaScraper
{
    // 1. Összegyűjti a linkeket (Show more nyomkodással a naptárból)
    public function getEventLinks($driver): array
    {
        $lastHeight = $driver->executeScript("return document.body.scrollHeight");
        $attempts = 0;
        
        while ($attempts < 15) { 
            $driver->executeScript("window.scrollTo(0, document.body.scrollHeight);");
            sleep(2); 

            try {
                // Keresi a "Load more" vagy "Show more" gombokat
                $buttons = $driver->findElements(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'more')] | //a[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'more')]"));
                $clicked = false;
                
                foreach ($buttons as $btn) {
                    if ($btn->isDisplayed()) {
                        $driver->executeScript("arguments[0].click();", [$btn]);
                        sleep(2);
                        $clicked = true;
                    }
                }
            } catch (\Exception $e) {}

            $newHeight = $driver->executeScript("return document.body.scrollHeight");
            if ($newHeight == $lastHeight && !$clicked) break; 
            
            $lastHeight = $newHeight;
            $attempts++;
        }

        $links = [];
        $elements = $driver->findElements(WebDriverBy::cssSelector('a'));
        foreach ($elements as $element) {
            $href = $element->getAttribute('href');
            // A Hï Ibiza linkjei általában így néznek ki: /events/2026/buli-neve
            if ($href && str_contains($href, '/events/202')) { 
                if (!str_starts_with($href, 'http')) $href = 'https://www.hiibiza.com' . $href;
                $links[] = $href;
            }
        }
        return array_unique($links); 
    }

    // 2. Kinyeri az adatokat a buli oldaláról
    public function extractEventDetails($driver): ?array
    {
        try {
            $url = $driver->getCurrentURL();
            
            $titleElements = $driver->findElements(WebDriverBy::cssSelector('h1'));
            $title = count($titleElements) > 0 ? trim($titleElements[0]->getText()) : 'Hï Ibiza Party';
            if (str_contains(strtoupper($title), 'SELLING FAST')) $title = $driver->getTitle();

            $image = null;
            $metaImages = $driver->findElements(WebDriverBy::cssSelector('meta[property="og:image"]'));
            if (count($metaImages) > 0) $image = $metaImages[0]->getAttribute('content');

            // Read More gomb kinyitása
            try {
                $readMoreBtns = $driver->findElements(WebDriverBy::xpath("//*[contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'read more')]"));
                foreach ($readMoreBtns as $btn) {
                    if ($btn->isDisplayed()) {
                        $driver->executeScript("arguments[0].click();", [$btn]);
                        sleep(1); 
                    }
                }
            } catch (\Exception $e) {}

            $bodyText = $driver->findElement(WebDriverBy::cssSelector('body'))->getText();

            // --- IDŐPONT KINYERÉSE ---
            $timeString = '23:30:00'; // A Hï Ibiza alapértelmezetten 23:30-kor nyit
            if (preg_match('/(\d{2}:\d{2})\s*-\s*(Close|End|\d{2}:\d{2})/i', $bodyText, $matches)) {
                $timeString = $matches[1] . ':00';
            } elseif (preg_match('/doors\s*(\d{2}:\d{2})/i', $bodyText, $matches)) {
                $timeString = $matches[1] . ':00';
            }

            $startTime = null;
            // 1. Megpróbáljuk a láthatatlan JSON-LD-ből kiszedni a hivatalos dátumot
            try {
                $scripts = $driver->findElements(WebDriverBy::cssSelector('script[type="application/ld+json"]'));
                foreach ($scripts as $script) {
                    $json = json_decode($script->getAttribute('innerHTML'), true);
                    if (is_array($json) && isset($json['startDate'])) {
                        $startTime = Carbon::parse($json['startDate'])->setTimezone('Europe/Madrid')->format('Y-m-d H:i:s');
                        break;
                    } elseif (is_array($json) && isset($json[0]['startDate'])) {
                        $startTime = Carbon::parse($json[0]['startDate'])->setTimezone('Europe/Madrid')->format('Y-m-d H:i:s');
                        break;
                    }
                }
            } catch (\Exception $e) {}

            // 2. Ha nincs JSON, megnézzük az URL-t (pl. hiibiza.com/events/2026/david-guetta/2026-06-05)
            if (!$startTime && preg_match('/(\d{4}-\d{2}-\d{2})/', $url, $urlDate)) {
                $startTime = Carbon::parse($urlDate[1] . ' ' . $timeString)->format('Y-m-d H:i:s');
            }

            // --- KORHATÁR ÉS STÍLUS ---
            $ageLimit = 18; 
            if (preg_match('/(21\+|over 21)/i', $bodyText)) $ageLimit = 21;

            $genre = 'House'; 
            $topText = strtolower(mb_substr($bodyText, 0, 1000)); 
            if (str_contains($topText, 'techno') || str_contains($topText, 'tale of us') || str_contains($topText, 'afterlife') || str_contains($topText, 'artbat')) $genre = 'Techno';
            elseif (str_contains($topText, 'trance') || str_contains($topText, 'armin van buuren')) $genre = 'Trance';
            elseif (str_contains($topText, 'edm') || str_contains($topText, 'david guetta') || str_contains($topText, 'morten')) $genre = 'EDM';
            elseif (str_contains($topText, 'reggaeton') || str_contains($topText, 'j balvin') || str_contains($topText, 'latin')) $genre = 'R&B';
            elseif (str_contains($topText, 'disco') || str_contains($topText, 'glitterbox')) $genre = 'Disco'; // A Hï híres a Glitterbox disco bulikról!

            // --- 🎯 LÉZERES LEÍRÁS KINYERÉSE ---
            $lines = explode("\n", $bodyText);
            $cleanDescription = '';

            foreach ($lines as $line) {
                $trimmed = trim($line);
                
                if (mb_strlen($trimmed, 'UTF-8') > 60) {
                    $upperLine = strtoupper($trimmed);
                    
                    // A Hï Ibiza speciális reklámszövegeinek szűrése
                    $isSpam = false;
                    $spamPhrases = ['COOKIES', 'VIP ZONE', 'TICKETS LEFT', 'VIP TABLES', 'COMPLIMENTARY DRINKS', 'EVENT SELLING FAST', 'TERMS AND CONDITIONS', 'PRIVACY POLICY', 'WORLD\'S #1 CLUB', 'HOW DO I GET TO'];
                    
                    foreach ($spamPhrases as $spam) {
                        if (str_contains($upperLine, $spam)) { $isSpam = true; break; }
                    }
                    if ($isSpam) continue;

                    if (str_contains(strtoupper($title), $upperLine) || str_contains($upperLine, strtoupper($title))) continue;

                    $cleanDescription = $trimmed;
                    break;
                }
            }

            if (empty(trim($cleanDescription))) {
                $cleanDescription = 'Official Hï Ibiza event. Get ready for an unforgettable night at the World\'s #1 Club! More info and VIP bookings on the official website.';
            }

            if (!$startTime) return null;

            return [
                'title' => $title,
                'description' => $cleanDescription,
                'image_url' => $image,
                'start_time' => $startTime,
                'genre' => $genre,
                'age_limit' => $ageLimit,
                'ticket_url' => $url
            ];

        } catch (\Exception $e) {
            return null;
        }
    }
}