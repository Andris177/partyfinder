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
            $table->unsignedInteger('facebook_interested_count')->default(0)->after('facebook_event_id');
            $table->unsignedInteger('facebook_attending_count')->default(0)->after('facebook_interested_count');

            $table->unsignedInteger('local_interested_count')->default(0)->after('facebook_attending_count');
            $table->unsignedInteger('local_attending_count')->default(0)->after('local_interested_count');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'facebook_interested_count',
                'facebook_attending_count',
                'local_interested_count',
                'local_attending_count'
            ]);
        });
    }
};
