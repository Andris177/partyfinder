<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventTypeLinksTable extends Migration
{
    public function up()
    {
        Schema::create('event_type_links', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('type_id');
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('event_types')->onDelete('cascade');
            $table->unique(['event_id','type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_type_links');
    }
}
