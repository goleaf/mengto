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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('availability_slot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('reference')->unique();
            $table->uuid('idempotency_key')->unique();
            $table->string('client_key', 80)->index();
            $table->string('client_name', 120);
            $table->string('pet_key', 80)->nullable();
            $table->string('pet_name', 100);
            $table->string('pet_species', 80);
            $table->string('pet_age_label', 80)->nullable();
            $table->string('format', 60);
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at');
            $table->string('timezone', 64)->default('Europe/Vilnius');
            $table->string('location_label', 180)->nullable();
            $table->string('status', 40)->default('pending')->index();
            $table->json('questionnaire');
            $table->json('documents')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_status', 40)->default('not-required')->index();
            $table->boolean('terms_accepted');
            $table->boolean('data_consent');
            $table->boolean('recording_consent')->default(false);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 180)->nullable();
            $table->timestamp('reschedule_proposed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['expert_profile_id', 'status', 'starts_at', 'id'],
                'bookings_expert_status_start_idx',
            );
            $table->index(
                ['client_key', 'status', 'starts_at', 'id'],
                'bookings_client_status_start_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
