<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacebookPagesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facebook_pages')) {
            Schema::create('facebook_pages', function (Blueprint $table) {
                $table->id();
                $table->string('name'); 
                $table->string('url');
                
                // 🔴 JAVÍTÁS ITT:
                // unsignedBigInteger helyett unsignedInteger-t használunk!
                // Ez azért kell, mert a 'cities' táblád valószínűleg régebbi típusú ID-t használ.
                $table->unsignedInteger('city_id');
                
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_scraped_at')->nullable();
                $table->timestamps();

                // Most már össze fogja tudni kötni:
                $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('facebook_pages');
    }
}
