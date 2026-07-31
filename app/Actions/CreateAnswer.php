<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicStatus;
use App\Models\ForumAnswer;
use App\Models\ForumNotification;
use App\Models\ForumTopic;
use App\Models\User;
use App\Services\ForumActor;
use App\Services\ForumTopicLifecycle;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\DB;

final readonly class CreateAnswer
{
    public function __construct(
        private ForumActor $actor,
        private ForumTopicLifecycle $lifecycle,
        private Translator $translator,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(ForumTopic $topic, array $data): ForumAnswer
    {
        return DB::transaction(function () use ($topic, $data): ForumAnswer {
            $identity = $this->actor->identity();
            $user = $this->actor->requireUser();

            $topic = ForumTopic::query()->lockForUpdate()->findOrFail($topic->id);
            $answer = $topic->answers()->create([
                'author_id' => $user->id,
                'author_key' => $identity['key'],
                'author_name' => $identity['name'],
                'author_initials' => $identity['initials'],
                'author_role' => $identity['role'],
                'body' => trim((string) $data['body']),
                'experience_type' => $data['experience_type'],
                'sources' => collect(preg_split('/\R/', (string) ($data['sources'] ?? '')))
                    ->map(fn (string $source): string => trim($source))
                    ->filter()
                    ->values()
                    ->all(),
                'status' => 'published',
            ]);

            if (in_array($topic->status->canonical(), [
                ForumTopicStatus::Published,
                ForumTopicStatus::Open,
            ], true)) {
                $topic = $this->lifecycle->transition(
                    topic: $topic,
                    target: ForumTopicStatus::Answered,
                    actor: $user,
                    reasonCode: 'answer-published',
                    expectedLockVersion: $topic->lock_version,
                    idempotencyKey: "topic-first-answer:{$answer->id}",
                );
            } else {
                $topic->forceFill([
                    'last_activity_at' => now(),
                    'lock_version' => $topic->lock_version + 1,
                ])->save();
            }

            if ($topic->author_key !== $identity['key']) {
                $recipientLocale = (string) (User::query()
                    ->where('actor_key', $topic->author_key)
                    ->value('locale') ?? config('app.fallback_locale', 'en'));
                ForumNotification::query()->updateOrCreate(
                    ['deduplication_key' => "answer:{$answer->id}:{$topic->author_key}"],
                    [
                        'topic_id' => $topic->id,
                        'user_key' => $topic->author_key,
                        'type' => 'new-answer',
                        'title' => $this->translator->get(
                            'messages.new_answer_added',
                            locale: $recipientLocale,
                        ),
                        'body' => $this->translator->get(
                            'presentation.replied_to_topic',
                            ['name' => $answer->author_name],
                            $recipientLocale,
                        ),
                    ],
                );
            }

            return $answer;
        });
    }
}
