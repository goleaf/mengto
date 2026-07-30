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
        Schema::create('device_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smart_device_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_pet_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pet_profile_key', 80)->nullable();
            $table->string('pet_name', 120)->nullable();
            $table->string('external_event_id', 160)->nullable();
            $table->string('metric_type', 64);
            $table->decimal('numeric_value', 18, 6)->nullable();
            $table->string('text_value', 255)->nullable();
            $table->string('unit', 40)->nullable();
            $table->timestamp('recorded_at');
            $table->string('timezone', 64);
            $table->decimal('accuracy_meters', 10, 2)->nullable();
            $table->string('confidence', 24)->default('unknown');
            $table->string('verification_status', 40)->default('device-unverified');
            $table->text('original_payload')->nullable();
            $table->text('processed_payload')->nullable();
            $table->boolean('is_stale')->default(false);
            $table->foreignId('care_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('medical_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('weight_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['smart_device_id', 'external_event_id']);
            $table->index(['smart_device_id', 'metric_type', 'recorded_at']);
            $table->index(['pet_profile_key', 'metric_type', 'recorded_at']);
            $table->index(['verification_status', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_readings');
    }
};
