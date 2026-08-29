<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumGroupActivityFormat;
use App\Enums\ForumGroupActivityStatus;
use App\Models\ForumGroup;
use App\Models\ForumGroupActivity;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumGroupActivity>
 */
final class ForumGroupActivityFactory extends ApplicationFactory
{
    public function definition(): array
    {
        $key = Str::lower((string) Str::ulid());

        return [
            'forum_group_id' => ForumGroup::factory(),
            'created_by_user_id' => null,
            'stable_key' => "group-activity-{$key}",
            'creation_idempotency_key' => "factory:group-activity:{$key}",
            'title' => fake()->sentence(5),
            'summary' => fake()->paragraph(),
            'format' => ForumGroupActivityFormat::Physical,
            'status' => ForumGroupActivityStatus::Scheduled,
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'timezone' => 'Europe/Vilnius',
            'location_scope' => 'lt-vilnius',
            'capacity' => 24,
            'participation_notes' => fake()->sentence(),
            'lock_version' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(static function (ForumGroupActivity $activity): void {
            if ($activity->forum_group_id !== null) {
                $activity->created_by_user_id = ForumGroup::query()
                    ->whereKey($activity->forum_group_id)
                    ->value('owner_user_id');
            }
        });
    }

    public function online(): static
    {
        return $this->state(fn (): array => [
            'format' => ForumGroupActivityFormat::Online,
            'location_scope' => null,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumGroupActivityStatus::Cancelled,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => ForumGroupActivityStatus::Completed,
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->subWeek()->addHours(2),
        ]);
    }
}
