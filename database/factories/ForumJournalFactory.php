<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ForumJournalStatus;
use App\Enums\ForumJournalType;
use App\Enums\ForumTopicStatus;
use App\Enums\ForumTopicType;
use App\Enums\ForumVisibility;
use App\Models\ForumJournal;
use App\Models\ForumTopic;
use App\Models\ForumTopicType as ForumTopicTypeModel;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumJournal>
 */
final class ForumJournalFactory extends ApplicationFactory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $title = fake()->sentence(5);
        $startedOn = now()->toDateString();

        return [
            'owner_user_id' => User::factory(),
            'owner_key' => static fn (array $attributes): string => User::query()
                ->whereKey($attributes['owner_user_id'])
                ->valueOrFail('actor_key'),
            'stable_key' => (string) Str::uuid(),
            'creation_idempotency_key' => (string) Str::uuid(),
            'type' => ForumJournalType::General,
            'status' => ForumJournalStatus::Active,
            'started_on' => $startedOn,
            'timezone' => 'Europe/Vilnius',
            'lock_version' => 1,
            'archived_by_user_id' => null,
            'archived_at' => null,
            'archive_reason_code' => null,
            'metadata' => [],
            'forum_topic_id' => static function (array $attributes) use ($title): int {
                $owner = User::query()->findOrFail($attributes['owner_user_id']);
                $type = $attributes['type'] instanceof ForumJournalType
                    ? $attributes['type']
                    : ForumJournalType::from((string) $attributes['type']);
                $startedOn = Carbon::parse($attributes['started_on'])->toDateString();
                $topicTypeId = ForumTopicTypeModel::query()
                    ->where('stable_key', ForumTopicType::Journal->value)
                    ->where('is_active', true)
                    ->value('id');
                $initials = Str::of($owner->name)
                    ->split('/\s+/')
                    ->filter()
                    ->take(2)
                    ->map(static fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
                    ->implode('');

                return ForumTopic::factory()->state([
                    'author_id' => $owner->id,
                    'author_key' => $owner->actor_key,
                    'author_name' => $owner->name,
                    'author_initials' => $initials,
                    'author_role' => null,
                    'forum_topic_type_id' => is_numeric($topicTypeId) ? (int) $topicTypeId : null,
                    'type' => ForumTopicType::Journal,
                    'title' => $title,
                    'status' => ForumTopicStatus::Published,
                    'visibility' => ForumVisibility::Public,
                    'structured_data' => [
                        'journal_type' => $type->value,
                        'started_on' => $startedOn,
                    ],
                    'structured_data_version' => 1,
                    'lock_version' => 1,
                ])->create()->id;
            },
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'owner_user_id' => $user->id,
            'owner_key' => $user->actor_key,
        ]);
    }

    public function withType(ForumJournalType $type): static
    {
        return $this->state(fn (): array => [
            'type' => $type,
        ]);
    }

    public function archived(?User $actor = null): static
    {
        return $this
            ->state(fn (): array => [
                'status' => ForumJournalStatus::Archived,
                'archived_by_user_id' => $actor?->id,
                'archived_at' => now(),
                'archive_reason_code' => 'owner-request',
                'lock_version' => 2,
                'metadata' => ['pre_archive_topic_status' => ForumTopicStatus::Published->value],
            ])
            ->afterCreating(static function (ForumJournal $journal): void {
                $topic = $journal->topic()->firstOrFail();
                $now = now();

                $topic->forceFill([
                    'status' => ForumTopicStatus::Archived,
                    'state_entered_at' => $now,
                    'last_activity_at' => $now,
                    'is_locked' => true,
                    'archived_at' => $now,
                    'lock_version' => $topic->lock_version + 1,
                ])->save();
            });
    }
}
