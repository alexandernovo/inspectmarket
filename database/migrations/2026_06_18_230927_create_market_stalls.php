<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('market_stalls', function (Blueprint $table) {
            $table->id();

            $table->string('section'); // Fish, Pork, Poultry, Beef, Mixed
            $table->integer('stall_no');

            $table->string('status');

            $table->string('tenant_name')->nullable();
            $table->string('business_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('market_stalls');
    }
};

// php artisan migrate:refresh --path=database/migrations/2026_06_18_230927_create_market_stalls.php

