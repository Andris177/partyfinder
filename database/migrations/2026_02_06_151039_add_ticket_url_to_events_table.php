<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Csak akkor hozza létre, ha MÉG NINCS ott
        if (!Schema::hasColumn('events', 'ticket_url')) {
            Schema::table('events', function (Blueprint $table) {
                $table->string('ticket_url')->nullable()->after('facebook_url');
            });
        }
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('ticket_url');
        });
    }
};
