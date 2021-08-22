<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('user_name',20)->unique();
            $table->string('user_pin')->unique();
            $table->string('avatar')->nullable();
            $table->string('email')->unique();
            $table->enum('is_email_verified',['Y','N'])->default('N');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('user_role', ['admin', 'user'])->default('user'); 
            $table->rememberToken();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
