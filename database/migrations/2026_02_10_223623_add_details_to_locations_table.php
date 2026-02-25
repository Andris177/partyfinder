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
    Schema::table('locations', function (Blueprint $table) {
        // Ellenőrizzük, és ha nincs, hozzáadjuk a hiányzókat
        if (!Schema::hasColumn('locations', 'slug')) {
            $table->string('slug')->nullable()->after('name');
        }
        if (!Schema::hasColumn('locations', 'lat')) {
            $table->decimal('lat', 10, 7)->nullable()->default(0)->after('slug');
        }
        if (!Schema::hasColumn('locations', 'lng')) {
            $table->decimal('lng', 10, 7)->nullable()->default(0)->after('lat');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
};
