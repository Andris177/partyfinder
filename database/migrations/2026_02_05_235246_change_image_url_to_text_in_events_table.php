<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Megváltoztatjuk a típust TEXT-re, ami sokkal hosszabb lehet
            $table->text('image_url')->change();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Visszaállítás (ha kellene)
            $table->string('image_url')->nullable()->change();
        });
    }
};