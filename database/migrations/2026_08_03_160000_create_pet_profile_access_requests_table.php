<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pet_profile_access_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('request_key', 80)->unique();
            $table->foreignId('pet_profile_id')
                ->constrained('pet_profiles')
                ->restrictOnDelete();
            $table->foreignId('requester_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('requester_actor_key_snapshot', 120);
            $table->string('request_type', 40);
            $table->string('requested_role', 40);
            $table->string('status', 24)->default('pending');
            $table->text('evidence_summary');
            $table->timestamp('temporary_access_ends_at')->nullable();
            $table->string('active_key', 64)->nullable()->unique();
            $table->string('submission_key', 64)->unique();
            $table->string('decision_key', 64)->nullable()->unique();
            $table->foreignId('reviewed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('granted_manager_id')
                ->nullable()
                ->constrained('pet_profile_managers')
                ->nullOnDelete();
            $table->text('resolution_note')->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->timestamps();

            $table->index(
                ['pet_profile_id', 'status', 'created_at', 'id'],
                'pet_access_requests_profile_status_idx',
            );
            $table->index(
                ['requester_user_id', 'status', 'created_at', 'id'],
                'pet_access_requests_requester_status_idx',
            );
            $table->index('reviewed_by_user_id', 'pet_access_requests_reviewer_idx');
            $table->index('granted_manager_id', 'pet_access_requests_manager_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_profile_access_requests');
    }
};
