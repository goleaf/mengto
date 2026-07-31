<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SocialAccountBlockStatus;
use App\Models\SocialAccountBlock;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<SocialAccountBlock> */
final class SocialAccountBlockFactory extends ApplicationFactory
{
    protected $model = SocialAccountBlock::class;

    public function definition(): array
    {
        $blocker = User::factory();
        $blocked = User::factory();

        return [
            'block_key' => (string) Str::uuid(),
            'blocker_user_id' => $blocker,
            'blocked_user_id' => $blocked,
            'source_actor_id' => null,
            'target_actor_id' => null,
            'status' => SocialAccountBlockStatus::Active,
            'scope' => 'all-managed-profiles',
            'active_key' => null,
            'idempotency_key' => (string) Str::uuid(),
            'reason_code' => null,
            'lock_version' => 1,
            'created_by_user_id' => fn (array $attributes): int => (int) $attributes['blocker_user_id'],
            'revoked_by_user_id' => null,
            'blocked_at' => now(),
            'revoked_at' => null,
        ];
    }

    public function configure(): self
    {
        return $this->afterCreating(function (SocialAccountBlock $block): void {
            if ($block->active_key !== null) {
                return;
            }

            $block->forceFill([
                'active_key' => hash('sha256', "{$block->blocker_user_id}|{$block->blocked_user_id}"),
            ])->saveQuietly();
        });
    }
}
