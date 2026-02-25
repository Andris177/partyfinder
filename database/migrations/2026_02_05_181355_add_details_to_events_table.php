<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Csak akkor adjuk hozzá, ha még NINCS ott
            if (!Schema::hasColumn('events', 'genre')) {
                $table->string('genre')->nullable()->after('title');
            }
            if (!Schema::hasColumn('events', 'age_limit')) {
                $table->integer('age_limit')->default(0)->after('genre');
            }
            if (!Schema::hasColumn('events', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('description');
            }
            if (!Schema::hasColumn('events', 'ticket_url')) {
                $table->string('ticket_url')->nullable()->after('facebook_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            //
        });
    }
};
