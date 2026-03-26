<?php

namespace App\Scrapers;

use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;

class PachaScraper
{
    // 1. Összegyűjti a bulik linkjeit (Show more nyomkodással)
    public function getEventLinks($driver): array
    {
        $lastHeight = $driver->executeScript("return document.body.scrollHeight");
        $attempts = 0;
        
        while ($attempts < 15) { 
            $driver->executeScript("window.scrollTo(0, document.body.scrollHeight);");
            sleep(2); 

            try {
                $buttons = $driver->findElements(WebDriverBy::xpath("//button[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'show more')] | //a[contains(translate(., 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'show more')]"));
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
            if ($href && str_contains($href, '/event/')) {
                if (!str_starts_with($href, 'http')) $href = 'https://pacha.com' . $href;
                $links[] = $href;
            }
        }
        return array_unique($links); 
    }

    // 2. Kinyeri az adatokat
    public function extractEventDetails($driver): ?array
    {
        try {
            $url = $driver->getCurrentURL();
            
            $titleElements = $driver->findElements(WebDriverBy::cssSelector('h1'));
            $title = count($titleElements) > 0 ? trim($titleElements[0]->getText()) : 'Pacha Ibiza Party';
            if (str_contains(strtoupper($title), 'EVENT SELLING FAST')) $title = $driver->getTitle();

            $image = null;
            $metaImages = $driver->findElements(WebDriverBy::cssSelector('meta[property="og:image"]'));
            if (count($metaImages) > 0) $image = $metaImages[0]->getAttribute('content');

            try {
                $readMoreBtns = $driver->findElements(WebDriverBy::xpath("//*[contains(translate(text(), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz'), 'read more')]"));
                foreach ($readMoreBtns as $btn) {
                    if ($btn->isDisplayed()) {
                        $driver->executeScript("arguments[0].click();", [$btn]);
                        sleep(1); 
                    }
                }
            } catch (\Exception $e) {}

            // A LÁTHATÓ SZÖVEG lekérése
            $bodyText = $driver->findElement(WebDriverBy::cssSelector('body'))->getText();

            // --- IDŐPONT KINYERÉSE ---
            $timeString = '23:59:00'; 
            if (preg_match('/\|\s*(\d{2}:\d{2})\s*-/', $bodyText, $matches)) {
                $timeString = $matches[1] . ':00'; 
            } elseif (preg_match('/(\d{2}:\d{2})\s*-\s*\d{2}:\d{2}/', $bodyText, $matches)) {
                $timeString = $matches[1] . ':00';
            }

            $startTime = null;
            if (preg_match('/(\d{2}-\d{2}-202[4-9])/', $url, $urlDate)) {
                $startTime = Carbon::parse($urlDate[1] . ' ' . $timeString)->format('Y-m-d H:i:s');
            }

            // --- KORHATÁR ÉS STÍLUS ---
            $ageLimit = 18; 
            if (preg_match('/(18\+|18 \+)/', $bodyText)) $ageLimit = 18;
            if (preg_match('/(21\+|21 \+|over 21)/i', $bodyText)) $ageLimit = 21;

            $genre = 'House'; 
            $topText = strtolower(mb_substr($bodyText, 0, 800)); 
            if (str_contains($topText, 'techno') || str_contains($topText, 'marco carola') || str_contains($topText, 'camelphat') || str_contains($topText, 'pawsa') || str_contains($topText, 'dennis cruz')) $genre = 'Techno';
            elseif (str_contains($topText, 'trance')) $genre = 'Trance';
            elseif (str_contains($topText, 'edm') || str_contains($topText, 'david guetta')) $genre = 'EDM';
            elseif (str_contains($topText, 'reggaeton') || str_contains($topText, 'j balvin')) $genre = 'R&B';

            // --- 🎯 LÉZERES LEÍRÁS KINYERÉSE ---
            $lines = explode("\n", $bodyText);
            $cleanDescription = '';

            foreach ($lines as $line) {
                $trimmed = trim($line);
                
                // Csak akkor foglalkozunk a sorral, ha az elég hosszú (tehát nem egy gomb felirata)
                if (mb_strlen($trimmed, 'UTF-8') > 60) {
                    $upperLine = strtoupper($trimmed);

                    // Ha szemét (Süti, Jegyek, Asztalfoglalás), megyünk tovább
                    $isSpam = false;
                    $spamPhrases = ['COOKIES', 'VIP ZONE', 'TICKETS LEFT', 'SAVE UP TO', 'RIGHTS RESERVED', 'COMPLIMENTARY DRINKS', 'EVENT SELLING FAST', 'TERMS AND CONDITIONS', 'PRIVACY POLICY'];
                    
                    foreach ($spamPhrases as $spam) {
                        if (str_contains($upperLine, $spam)) { $isSpam = true; break; }
                    }
                    if ($isSpam) continue;

                    // Ha a szöveg egyezik a címmel, kihagyjuk
                    if (str_contains(strtoupper($title), $upperLine) || str_contains($upperLine, strtoupper($title))) continue;

                    // Ha idáig eljutott, megtaláltuk a Tiszta Leírást! Eltesszük és kilépünk!
                    $cleanDescription = $trimmed;
                    break;
                }
            }

            if (empty(trim($cleanDescription))) {
                $cleanDescription = 'Official Pacha Ibiza event. Get ready for an unforgettable night! More info and VIP bookings on the official website.';
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