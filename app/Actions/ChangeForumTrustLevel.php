<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ForumTrustHistory;
use App\Models\ForumTrustLevel;
use App\Models\ForumUserTrustLevel;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class ChangeForumTrustLevel
{
    /**
     * @param  array<string, mixed>  $evidence
     */
    public function handle(
        User $actor,
        User $target,
        ForumTrustLevel $level,
        string $scopeType,
        string $scopeKey,
        string $reasonCode,
        array $evidence = [],
    ): ForumUserTrustLevel {
        if (! $actor->isAdministrator()) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use (
            $actor,
            $evidence,
            $level,
            $reasonCode,
            $scopeKey,
            $scopeType,
            $target,
        ): ForumUserTrustLevel {
            $current = ForumUserTrustLevel::query()
                ->where('user_id', $target->id)
                ->where('scope_type', $scopeType)
                ->where('scope_key', $scopeKey)
                ->lockForUpdate()
                ->first();
            $fromLevelId = $current?->forum_trust_level_id;

            if ($current?->forum_trust_level_id === $level->id) {
                return $current;
            }

            $assignment = ForumUserTrustLevel::query()->updateOrCreate(
                [
                    'user_id' => $target->id,
                    'scope_type' => $scopeType,
                    'scope_key' => $scopeKey,
                ],
                [
                    'forum_trust_level_id' => $level->id,
                    'granted_by_user_id' => $actor->id,
                    'reason_code' => $reasonCode,
                    'granted_at' => now(),
                    'expires_at' => null,
                ],
            );

            ForumTrustHistory::query()->create([
                'user_id' => $target->id,
                'from_forum_trust_level_id' => $fromLevelId,
                'to_forum_trust_level_id' => $level->id,
                'actor_user_id' => $actor->id,
                'scope_type' => $scopeType,
                'scope_key' => $scopeKey,
                'reason_code' => $reasonCode,
                'evidence' => $evidence,
                'created_at' => now(),
            ]);

            return $assignment;
        }, 3);
    }
}
