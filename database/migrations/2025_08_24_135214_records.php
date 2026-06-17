<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->id("record_id");
            $table->integer("staff_id");
            $table->string('typeincident')->nullable();
            $table->datetime('datetimeoccurence')->nullable();
            $table->string('barangay')->nullable();
            $table->string('specificlocation')->nullable();
            $table->text('detaileddesc')->nullable();
            $table->integer('involvedinjured')->nullable();
            $table->integer('involveddead')->nullable();
            $table->string('filesubmitted')->nullable();
            $table->string('status')->nullable();
            $table->string('process_status')->nullable();

            $table->integer('affectedfamilies')->nullable();
            $table->integer('individuals')->nullable();
            $table->integer('evacuationfamilies')->nullable();
            $table->integer('evacuationindividuals')->nullable();
            $table->string('remarks')->nullable();
            $table->string('clearingoperations')->nullable();

            $table->integer('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->string('description')->nullable();
            $table->string('propertyno')->nullable();
            $table->date('dateacquired')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('typeOfRecord')->nullable();
            $table->string('cause')->nullable();
            $table->string('remarksAdmin')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
