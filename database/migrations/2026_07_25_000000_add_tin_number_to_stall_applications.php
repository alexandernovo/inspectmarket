<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stall_applications', function (Blueprint $table) {
            $table->string('tin_number', 15)->nullable()->after('business_owner');
            $table->index('tin_number');
        });
    }

    public function down(): void
    {
        Schema::table('stall_applications', function (Blueprint $table) {
            $table->dropIndex(['tin_number']);
            $table->dropColumn('tin_number');
        });
    }
};
