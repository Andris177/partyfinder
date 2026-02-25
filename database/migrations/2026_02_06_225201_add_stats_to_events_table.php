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
    Schema::table('events', function (Blueprint $table) {
        // Csak akkor adja hozzá, ha még NINCS ott
        if (!Schema::hasColumn('events', 'interested_count')) {
            $table->integer('interested_count')->default(0)->nullable();
        }
        
        if (!Schema::hasColumn('events', 'going_count')) {
            $table->integer('going_count')->default(0)->nullable();
        }
    });
}

public function down()
{
    Schema::table('events', function (Blueprint $table) {
        $table->dropColumn(['interested_count', 'going_count']);
    });
}
};
