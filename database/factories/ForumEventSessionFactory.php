<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumEventSessionReservationPolicy;
use App\Enums\ForumEventSessionStatus;
use App\Enums\ForumEventSessionType;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumEventSession;
use App\Models\ForumEventSessionStaff;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<ForumEventSession> */
final class ForumEventSessionFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'forum_event_id' => ForumEvent::factory(),
            'forum_event_occurrence_id' => function (array $attributes): int {
                $event = ForumEvent::query()->findOrFail($attributes['forum_event_id']);

                return ForumEventOccurrence::factory()->create([
                    'forum_event_id' => $event->id,
                    'starts_at' => $event->starts_at,
                    'ends_at' => $event->ends_at,
                    'timezone' => $event->timezone,
                    'format' => $event->format,
                    'capacity' => $event->capacity,
                ])->id;
            },
            'forum_event_track_id' => null,
            'forum_event_room_id' => null,
            'created_by_user_id' => User::factory(),
            'updated_by_user_id' => null,
            'stable_key' => 'event-session-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'title' => fake()->sentence(4),
            'summary' => fake()->sentence(),
            'type' => ForumEventSessionType::Session,
            'status' => ForumEventSessionStatus::Scheduled,
            'starts_at' => fn (array $attributes) => ForumEventOccurrence::query()
                ->findOrFail($attributes['forum_event_occurrence_id'])
                ->starts_at,
            'ends_at' => fn (array $attributes) => ForumEventOccurrence::query()
                ->findOrFail($attributes['forum_event_occurrence_id'])
                ->starts_at
                ->addHour(),
            'timezone' => fn (array $attributes): string => ForumEventOccurrence::query()
                ->findOrFail($attributes['forum_event_occurrence_id'])
                ->timezone,
            'capacity' => 20,
            'reservation_policy' => ForumEventSessionReservationPolicy::Optional,
            'is_required' => false,
            'position' => 0,
            'conflict_override_reason' => null,
            'conflict_snapshot' => null,
            'lock_version' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => ForumEventSessionStatus::Draft]);
    }

    public function live(): static
    {
        return $this->state(fn (): array => ['status' => ForumEventSessionStatus::Live]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => ForumEventSessionStatus::Cancelled]);
    }

    public function required(): static
    {
        return $this->state(fn (): array => [
            'is_required' => true,
            'reservation_policy' => ForumEventSessionReservationPolicy::Automatic,
        ]);
    }

    public function withStaff(?User $user = null): static
    {
        return $this->afterCreating(function (ForumEventSession $session) use ($user): void {
            ForumEventSessionStaff::factory()->create([
                'forum_event_session_id' => $session->id,
                'user_id' => ($user ?? User::factory()->create())->id,
            ]);
        });
    }
}
