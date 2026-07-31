<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_account_blocks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('block_key')->unique();
            $table->foreignId('blocker_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('blocked_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('source_actor_id')
                ->nullable()
                ->constrained('social_actors')
                ->nullOnDelete();
            $table->foreignId('target_actor_id')
                ->nullable()
                ->constrained('social_actors')
                ->nullOnDelete();
            $table->string('status', 24)->default('active');
            $table->string('scope', 40)->default('all-managed-profiles');
            $table->string('active_key', 64)->nullable()->unique();
            $table->string('idempotency_key', 190)->unique();
            $table->string('reason_code', 100)->nullable();
            $table->unsignedInteger('lock_version')->default(1);
            $table->foreignId('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('revoked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('blocked_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(
                ['blocker_user_id', 'status', 'blocked_at', 'id'],
                'social_account_blocks_outgoing_idx',
            );
            $table->index(
                ['blocked_user_id', 'status', 'blocked_at', 'id'],
                'social_account_blocks_incoming_idx',
            );
        });

        Schema::table('social_relationship_requests', function (Blueprint $table): void {
            $table->string('message_fingerprint', 64)->nullable()->after('message');
            $table->string('risk_level', 24)->default('normal')->after('message_fingerprint');
            $table->json('risk_signals')->nullable()->after('risk_level');
            $table->boolean('prevent_repeats')->default(false)->after('repeat_after');

            $table->index(
                ['created_by_user_id', 'sent_at', 'id'],
                'social_requests_account_window_idx',
            );
            $table->index(
                ['created_by_user_id', 'message_fingerprint', 'sent_at'],
                'social_requests_message_fingerprint_idx',
            );
            $table->index(
                ['created_by_user_id', 'relationship_type', 'prevent_repeats', 'repeat_after'],
                'social_requests_account_repeat_idx',
            );
        });

        Schema::table('social_relationship_events', function (Blueprint $table): void {
            $table->foreignId('social_account_block_id')
                ->nullable()
                ->after('social_relationship_request_id')
                ->constrained('social_account_blocks')
                ->nullOnDelete();
            $table->index('social_account_block_id', 'social_events_account_block_idx');
        });

        Schema::table('forum_reports', function (Blueprint $table): void {
            $table->string('idempotency_key', 190)->nullable()->unique()->after('deduplication_key');
        });
    }

    public function down(): void
    {
        Schema::table('forum_reports', function (Blueprint $table): void {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });

        Schema::table('social_relationship_events', function (Blueprint $table): void {
            $table->dropIndex('social_events_account_block_idx');
            $table->dropConstrainedForeignId('social_account_block_id');
        });

        Schema::table('social_relationship_requests', function (Blueprint $table): void {
            $table->dropIndex('social_requests_account_window_idx');
            $table->dropIndex('social_requests_message_fingerprint_idx');
            $table->dropIndex('social_requests_account_repeat_idx');
            $table->dropColumn([
                'message_fingerprint',
                'risk_level',
                'risk_signals',
                'prevent_repeats',
            ]);
        });

        Schema::dropIfExists('social_account_blocks');
    }
};
