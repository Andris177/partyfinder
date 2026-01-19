<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('location_id');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('image_url')->nullable();
            $table->string('ticket_url')->nullable();
            $table->string('facebook_event_id', 100)->nullable();

            // ✅ Facebookról importált értékek
            $table->unsignedInteger('facebook_attending_count')->default(0);
            $table->unsignedInteger('facebook_interested_count')->default(0);

            // ✅ Az alkalmazásban gyűjtött értékek
            $table->integer('attending_count')->default(0);
            $table->integer('interested_count')->default(0);

            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('events');
    }
}
