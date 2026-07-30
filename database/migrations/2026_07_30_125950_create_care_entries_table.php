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
        Schema::create('care_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_journal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('care_task_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->string('type', 40);
            $table->string('subtype', 80)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('timezone', 64);
            $table->string('status', 32)->default('completed');
            $table->string('source_type', 32)->default('owner');
            $table->string('source_name', 120);
            $table->string('verification_status', 40)->default('person-reported');
            $table->string('author_key', 80);
            $table->string('author_name', 120);
            $table->string('title', 180);
            $table->text('notes')->nullable();
            $table->text('measurements')->nullable();
            $table->text('context')->nullable();
            $table->decimal('quantity_value', 12, 3)->nullable();
            $table->string('quantity_unit', 40)->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedInteger('distance_meters')->nullable();
            $table->string('appetite', 32)->nullable();
            $table->string('intensity', 32)->nullable();
            $table->boolean('is_unusual')->default(false);
            $table->string('privacy', 32)->default('private');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by_key', 80)->nullable();
            $table->timestamps();

            $table->index(['care_journal_id', 'type', 'started_at']);
            $table->index(['care_journal_id', 'is_unusual', 'started_at']);
            $table->index(['care_journal_id', 'status', 'started_at']);
            $table->index(['source_type', 'verification_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_entries');
    }
};
