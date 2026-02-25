<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('events', function (Blueprint $table) {
        // FONTOS: Oda kell írni, hogy nullable(), különben hibát dob üres mezőknél!
        $table->text('ticket_url')->nullable()->change();
    });
}

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Visszaállítás (ha kellene)
            $table->string('ticket_url')->nullable()->change();
        });
    }
};