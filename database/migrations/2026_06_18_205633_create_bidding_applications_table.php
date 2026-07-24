<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidding_applications', function (Blueprint $table) {
            $table->id();

            $table->string('name_owner');
            $table->string('tin')->nullable();

            $table->text('address');

            $table->string('cellphone_no')->nullable();

            $table->string('business_type')->nullable();

            $table->string('nature_business')->nullable();

            $table->string('category')->nullable();

            $table->string('business_trade_name')->nullable();

            $table->string('stall_applied')->nullable();

            $table->string('alternative_stall')->nullable();

            $table->string('other_business')->nullable();

            $table->date('date_filed')->nullable();

            $table->string('received_by')->nullable();

            $table->text('remarks')->nullable();

            $table->string('signature_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bidding_applications');
    }
};


// php artisan migrate:refresh --path=database/migrations/2026_06_18_205633_create_bidding_applications_table.php