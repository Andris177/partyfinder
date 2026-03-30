<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('email'); // pl: 'google' vagy 'apple'
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('password')->nullable()->change(); // Jelszó nem kötelező a social loginnál
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id']);
            $table->string('password')->nullable(false)->change();
        });
    }
};