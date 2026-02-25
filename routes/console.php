<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // <--- EZT NE FELEJTSD EL!

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- ROBOT IDŐZÍTÉSE ---
// Minden nap éjfélkor (00:00) lefuttatja a scraper-t
Schedule::command('events:scrape')
        ->dailyAt('18:00')
        ->timezone('Europe/Budapest');