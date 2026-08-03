<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::table('stall_applications', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->string('civil_status', 30)->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('business_owner')->nullable();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('or_number')->nullable();
            $table->decimal('shortage_amount', 12, 2)->default(0);
        });

        Schema::table('livestock_inspections', function (Blueprint $table) {
            $table->string('breed')->nullable();
            $table->string('sex', 20)->nullable();
            $table->string('animal_age')->nullable();
            $table->decimal('live_weight', 10, 2)->nullable();
            $table->decimal('carcass_weight', 10, 2)->nullable();
            $table->string('source_location')->nullable();
            $table->string('purpose')->nullable();
            $table->json('ante_mortem_findings')->nullable();
            $table->json('post_mortem_findings')->nullable();
            $table->string('certificate_number')->nullable()->unique();
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
        });

        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone_num', 30);
            $table->string('code', 6);
            $table->string('purpose', 30);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->index(['phone_num', 'purpose', 'code']);
        });

        Schema::create('stall_application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stall_application_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('cash_ticket_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_number')->unique();
            $table->foreignId('collector_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->string('stall_section', 30);
            $table->unsignedInteger('ticket_start');
            $table->unsignedInteger('ticket_end');
            $table->unsignedInteger('ticket_quantity');
            $table->date('assigned_date');
            $table->string('status', 20)->default('ASSIGNED');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['collector_id', 'assigned_date']);
        });

        Schema::table('cash_ticket_collections', function (Blueprint $table) {
            $table->foreignId('cash_ticket_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('ticket_start')->nullable();
            $table->unsignedInteger('ticket_end')->nullable();
            $table->decimal('shortage_amount', 12, 2)->default(0);
        });

        Schema::create('market_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['sender_id', 'recipient_id', 'created_at']);
        });

        Schema::create('market_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('contact_number', 30);
            $table->text('message');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('group', 40)->default('GENERAL');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('market_notifications');
        Schema::dropIfExists('market_messages');

        Schem::table('cash_ticket_collections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_ticket_assignment_id');
            $table->dropColumn(['ticket_start', 'ticket_end', 'shortage_amount']);
        });

        Schema::dropIfExists('cash_ticket_assignments');
        Schema::dropIfExists('stall_application_documents');
        Schema::dropIfExists('verification_codes');

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name']);
        });

        Schema::table('livestock_inspections', function (Blueprint $table) {
            $table->dropColumn([
                'breed',
                'sex',
                'animal_age',
                'live_weight',
                'carcass_weight',
                'source_location',
                'purpose',
                'ante_mortem_findings',
                'post_mortem_findings',
                'certificate_number',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['or_number', 'shortage_amount']);
        });

        Schema::table('stall_applications', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'civil_status', 'contact_number', 'business_owner']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone_verified_at', 'last_login_at']);
        });
    }
};
