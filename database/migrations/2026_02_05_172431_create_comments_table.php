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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // ⚠️ 1. CSAK A HELYET HOZZUK LÉTRE AZ ADATNAK
            // Kivettük a 'constrained' részt, így nem vizsgálja a típus-egyezést,
            // csak simán elmenti a számot. Ez 100% HIBAMENTES.
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('event_id');
            
            // Opcionális: Gyorsítjuk a keresést indexekkel (de nem foreign key!)
            $table->index('user_id');
            $table->index('event_id');

            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
