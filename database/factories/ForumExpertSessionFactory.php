<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumExpertSessionStatus;
use App\Models\ExpertProfile;
use App\Models\ForumExpertSession;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumExpertSession> */
final class ForumExpertSessionFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $user = User::factory()->create();
        $profile = ExpertProfile::factory()->create([
            'owner_id' => $user->id,
            'owner_key' => $user->actor_key,
        ]);
        $opensAt = now()->subHour();

        return [
            'expert_profile_id' => $profile->id,
            'created_by_user_id' => $user->id,
            'stable_key' => 'expert-session-'.Str::lower((string) Str::ulid()),
            'creation_idempotency_key' => (string) Str::uuid(),
            'host_name_snapshot' => $profile->public_name,
            'professional_scope' => $profile->primary_type,
            'jurisdiction' => 'LT',
            'title' => fake()->sentence(6),
            'summary' => fake()->paragraphs(2, true),
            'locale' => 'en',
            'timezone' => 'UTC',
            'status' => ForumExpertSessionStatus::Published,
            'disclaimer_version' => '2026-07',
            'question_opens_at' => $opensAt,
            'question_closes_at' => $opensAt->clone()->addDay(),
            'starts_at' => $opensAt->clone()->addHours(3),
            'ends_at' => $opensAt->clone()->addHours(5),
            'archived_by_user_id' => null,
            'archived_at' => null,
            'archive_reason_code' => null,
            'lock_version' => 0,
        ];
    }

    public function upcoming(): static
    {
        $opensAt = now()->addDay();

        return $this->state(fn (): array => [
            'question_opens_at' => $opensAt,
            'question_closes_at' => $opensAt->clone()->addDay(),
            'starts_at' => $opensAt->clone()->addDays(2),
            'ends_at' => $opensAt->clone()->addDays(2)->addHours(2),
        ]);
    }

    public function ended(): static
    {
        $opensAt = now()->subDays(3);

        return $this->state(fn (): array => [
            'question_opens_at' => $opensAt,
            'question_closes_at' => $opensAt->clone()->addDay(),
            'starts_at' => $opensAt->clone()->addDays(2),
            'ends_at' => $opensAt->clone()->addDays(2)->addHours(2),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ForumExpertSessionStatus::Archived,
            'archived_by_user_id' => $attributes['created_by_user_id'],
            'archived_at' => now(),
            'archive_reason_code' => 'host-archived',
        ]);
    }
}
