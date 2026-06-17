<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('username')->nullable();
            $table->string('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('phone_num')->nullable();
            $table->string('status')->nullable();
            $table->string('password')->nullable();
            $table->string('usertype')->nullable();
            $table->string('profile')->nullable();
            $table->string('background')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
