<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stalls', function (Blueprint $table) {
            $table->id();
            $table->string('section', 30);
            $table->unsignedInteger('stall_number');
            $table->decimal('monthly_rate', 10, 2)->default(0);
            $table->string('status', 20)->default('AVAILABLE');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['section', 'stall_number']);
            $table->index(['section', 'status']);
        });

        Schema::create('stall_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_number')->unique();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('stall_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name');
            $table->string('business_category');
            $table->string('business_address');
            $table->string('preferred_section', 30);
            $table->unsignedInteger('preferred_stall_number')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->text('remarks')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('stall_application_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('period_month');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method', 30)->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->string('receipt_path')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('livestock_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('livestock_type', 20);
            $table->string('owner_name');
            $table->string('address');
            $table->string('contact_number', 30);
            $table->dateTime('scheduled_at');
            $table->string('status', 20)->default('PENDING');
            $table->unsignedInteger('animal_count')->default(1);
            $table->string('inspection_result', 30)->nullable();
            $table->text('findings')->nullable();
            $table->timestamp('inspected_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['livestock_type', 'status']);
            $table->index(['inspector_id', 'scheduled_at']);
        });

        Schema::create('cash_ticket_collections', function (Blueprint $table) {
            $table->id();
            $table->string('collection_number')->unique();
            $table->foreignId('collector_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stall_section', 30);
            $table->unsignedInteger('ticket_quantity');
            $table->decimal('amount', 12, 2);
            $table->date('collection_date');
            $table->string('status', 20)->default('DRAFT');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['collection_date', 'status']);
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 30)->default('OTHERS');
            $table->string('title');
            $table->text('content');
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->index(['is_published', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('cash_ticket_collections');
        Schema::dropIfExists('livestock_inspections');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('stall_applications');
        Schema::dropIfExists('stalls');
    }
};
