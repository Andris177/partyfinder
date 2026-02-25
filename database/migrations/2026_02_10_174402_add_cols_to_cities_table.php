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
    Schema::table('cities', function (Blueprint $table) {
        // Csak akkor adja hozzá, ha még nincs ott (biztonsági ellenőrzés)
        if (!Schema::hasColumn('cities', 'slug')) {
            $table->string('slug')->nullable();
        }
        if (!Schema::hasColumn('cities', 'lat')) {
            $table->decimal('lat', 10, 8)->nullable()->default(0);
        }
        if (!Schema::hasColumn('cities', 'lng')) {
            $table->decimal('lng', 11, 8)->nullable()->default(0);
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            //
        });
    }
};
