<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- 1. FACEBOOK ROBOT IDŐZÍTÉSE ---
Schedule::command('events:scrape')
        ->dailyAt('18:00')
        ->timezone('Europe/Budapest');

// --- 2. WEBES KLUBOK (pl. Bootshaus) ROBOT IDŐZÍTÉSE ---
// 10 perccel később indítjuk, hogy a Facebookos biztosan befejezze a dolgát
Schedule::command('clubs:scrape')
        ->dailyAt('18:10')
        ->timezone('Europe/Budapest');

/* 💡 TESZTELÉSHEZ:
Ha azonnal látni akarod, hogy működik, vedd ki a fenti sorokat megjegyzésbe (//),
és használd ezeket egy perces csúszással:

Schedule::command('events:scrape')->everyMinute();
Schedule::command('clubs:scrape')->everyTwoMinutes();
*/