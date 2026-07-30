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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expert_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_key', 80)->index();
            $table->string('actor_role', 80);
            $table->string('action', 100)->index();
            $table->string('target_type', 120);
            $table->string('target_id', 120)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(
                ['expert_profile_id', 'created_at', 'id'],
                'audit_logs_profile_created_idx',
            );
            $table->index(
                ['booking_id', 'created_at', 'id'],
                'audit_logs_booking_created_idx',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
