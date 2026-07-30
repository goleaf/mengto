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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('expert_profile_id')->constrained()->cascadeOnDelete();
            $table->string('status', 40)->default('scheduled')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->text('private_notes')->nullable();
            $table->text('client_summary')->nullable();
            $table->json('action_plan')->nullable();
            $table->text('referral_summary')->nullable();
            $table->timestamp('follow_up_until')->nullable()->index();
            $table->timestamp('summary_confirmed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['expert_profile_id', 'status', 'follow_up_until'],
                'consultations_profile_status_followup_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
