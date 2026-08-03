<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livestock_inspections', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->change();
            $table->string('email')->nullable()->after('contact_number');
            $table->string('request_source', 20)->default('TENANT')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('livestock_inspections', function (Blueprint $table) {
            $table->dropColumn(['email', 'request_source']);
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }
};
