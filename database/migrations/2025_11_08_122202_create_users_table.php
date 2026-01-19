<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->enum('role', ['admin','user'])->default('user');
            $table->boolean('notification_enabled')->default(true);
            $table->string('avatar_url')->nullable();
            $table->timestamp('email_verified_at')->nullable();

            $table->rememberToken();   // ✅ ezt hozzáadtam
            $table->timestamps();       // ✅ és ezt is!
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
