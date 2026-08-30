<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ForumAnswer;
use App\Models\ForumComment;
use App\Models\ForumReport;
use App\Models\ForumReportReason;
use App\Models\ForumTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<ForumReport>
 */
class ForumReportFactory extends ApplicationFactory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $topic = ForumTopic::factory();

        return [
            'topic_id' => $topic,
            'subject_type' => ForumTopic::class,
            'subject_id' => fn (array $attributes): string => (string) $attributes['topic_id'],
            'reporter_id' => User::factory(),
            'reporter_key' => fn (array $attributes): string => User::query()
                ->findOrFail($attributes['reporter_id'])
                ->actor_key,
            'reason' => 'misinformation',
            'forum_report_reason_id' => ForumReportReason::factory([
                'stable_key' => 'misinformation-'.fake()->unique()->numerify('#####'),
            ]),
            'details' => fake()->sentence(),
            'priority' => 'standard',
            'status' => 'submitted',
            'urgency' => 'standard',
            'contact_preference' => 'platform',
            'immediate_safety' => false,
            'truthfulness_confirmed' => true,
            'deduplication_key' => hash('sha256', fake()->uuid()),
            'idempotency_key' => (string) Str::uuid(),
            'metadata' => ['source' => 'factory', 'version' => 1],
        ];
    }

    public function forSubject(Model $subject): static
    {
        return $this->state(fn (): array => [
            'topic_id' => match (true) {
                $subject instanceof ForumTopic => $subject->id,
                $subject instanceof ForumAnswer,
                $subject instanceof ForumComment => $subject->topic_id,
                default => null,
            },
            'answer_id' => $subject instanceof ForumAnswer ? $subject->id : null,
            'comment_id' => $subject instanceof ForumComment ? $subject->id : null,
            'subject_type' => $subject::class,
            'subject_id' => (string) $subject->getKey(),
        ]);
    }

    public function legacyWithoutIdempotency(): static
    {
        return $this->state(fn (): array => ['idempotency_key' => null]);
    }
}
