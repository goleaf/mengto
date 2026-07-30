<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medication_doses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medication_id')->constrained()->cascadeOnDelete();
            $table->uuid('idempotency_key')->unique();
            $table->timestamp('scheduled_for')->index();
            $table->timestamp('administered_at')->nullable()->index();
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('status', 40)->index();
            $table->string('dose_given', 120)->nullable();
            $table->string('administered_by_key', 80)->index();
            $table->string('administered_by_name', 120);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(
                ['medication_id', 'scheduled_for'],
                'medication_doses_medication_slot_unique',
            );
            $table->index(
                ['medical_record_id', 'scheduled_for', 'status'],
                'medication_doses_record_schedule_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medication_doses');
    }
};
