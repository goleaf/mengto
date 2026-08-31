<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_event_invitations', function (Blueprint $table): void {
            $table->string('active_pair_key', 64)->nullable()->after('idempotency_key');
            $table->string('request_checksum', 64)->nullable()->after('active_pair_key');
        });

        DB::table('forum_event_invitations')
            ->select([
                'id', 'forum_event_id', 'invited_by_user_id', 'invited_user_id',
                'expires_at', 'idempotency_key', 'status',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($invitations): void {
                foreach ($invitations as $invitation) {
                    $isActive = in_array($invitation->status, ['pending', 'accepted'], true)
                        && now()->lt($invitation->expires_at);
                    DB::table('forum_event_invitations')
                        ->where('id', $invitation->id)
                        ->update([
                            'active_pair_key' => $isActive
                                ? hash('sha256', $invitation->forum_event_id.'|'.$invitation->invited_user_id)
                                : null,
                            'request_checksum' => hash('sha256', json_encode([
                                'event_id' => $invitation->forum_event_id,
                                'inviter_id' => $invitation->invited_by_user_id,
                                'recipient_id' => $invitation->invited_user_id,
                                'expires_at' => CarbonImmutable::parse($invitation->expires_at)->toISOString(),
                                'idempotency_key' => $invitation->idempotency_key,
                            ], JSON_THROW_ON_ERROR)),
                        ]);
                }
            });

        Schema::table('forum_event_invitations', function (Blueprint $table): void {
            $table->dropUnique('forum_event_invitations_event_user_unique');
            $table->unique('active_pair_key', 'forum_event_invitations_active_pair_unique');
            $table->index(
                ['forum_event_id', 'invited_user_id', 'created_at', 'id'],
                'forum_event_invitations_pair_history_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::table('forum_event_invitations', function (Blueprint $table): void {
            $table->dropIndex('forum_event_invitations_pair_history_idx');
            $table->dropUnique('forum_event_invitations_active_pair_unique');
            $table->dropColumn(['active_pair_key', 'request_checksum']);
            $table->unique(
                ['forum_event_id', 'invited_user_id'],
                'forum_event_invitations_event_user_unique',
            );
        });
    }
};
