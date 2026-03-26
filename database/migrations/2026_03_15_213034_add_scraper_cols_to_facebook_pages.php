<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_pages', function (Blueprint $table) {
            // Hozzáadjuk a webes scrapeléshez szükséges oszlopokat
            $table->string('events_url')->nullable()->after('url');
            $table->string('scraper_driver')->default('facebook')->after('events_url');
        });
    }

    public function down(): void
    {
        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->dropColumn(['events_url', 'scraper_driver']);
        });
    }
};