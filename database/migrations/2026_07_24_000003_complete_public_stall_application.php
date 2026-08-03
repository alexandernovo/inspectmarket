<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stall_applications', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->change();
            $table->string('request_source', 20)->default('TENANT')->after('tenant_id');
            $table->string('email')->nullable()->after('contact_number');
            $table->string('sex', 20)->nullable()->after('civil_status');
            $table->string('business_nature')->nullable()->after('business_category');
            $table->string('trade_name')->nullable()->after('business_nature');
            $table->date('permit_issued_at')->nullable()->after('trade_name');
            $table->string('other_business')->nullable()->after('permit_issued_at');
        });
    }

    public function down(): void
    {
        Schema::table('stall_applications', function (Blueprint $table) {
            $table->dropColumn([
                'request_source',
                'email',
                'sex',
                'business_nature',
                'trade_name',
                'permit_issued_at',
                'other_business',
            ]);
            $table->foreignId('tenant_id')->nullable(false)->change();
        });
    }
};
