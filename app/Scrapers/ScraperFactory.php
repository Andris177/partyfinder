<?php

namespace App\Scrapers;

class ScraperFactory
{
    public static function make(string $clubDriver): ScraperInterface
    {
        switch (strtolower($clubDriver)) {
            case 'bootshaus':
                return new BootshausScraper();
            
            // Ide fogjuk beírni a többi klubot később:
            // case 'pacha':
            //     return new PachaScraper();

            default:
                throw new \Exception("❌ Nincs még megírva a kaparó ehhez a klubhoz: " . $clubDriver);
        }
    }
}