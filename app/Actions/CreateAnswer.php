<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumTopicStatus;
use App\Models\ForumAnswer;
use App\Models\ForumNotification;
use App\Models\ForumTopic;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;

class CreateAnswer
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(ForumTopic $topic, array $data): ForumAnswer
    {
        return DB::transaction(function () use ($topic, $data): ForumAnswer {
            $identity = $this->actor->identity();
            $user = $this->actor->requireUser();

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

            $topic->update([
                'status' => $topic->status === ForumTopicStatus::Published
                    ? ForumTopicStatus::Answered
                    : $topic->status,
                'last_activity_at' => now(),
            ]);

            if ($topic->author_key !== $identity['key']) {
                ForumNotification::query()->updateOrCreate(
                    ['deduplication_key' => "answer:{$answer->id}:{$topic->author_key}"],
                    [
                        'topic_id' => $topic->id,
                        'user_key' => $topic->author_key,
                        'type' => 'new-answer',
                        'title' => __('messages.new_answer_added'),
                        'body' => __('presentation.replied_to_topic', ['name' => $answer->author_name]),
                    ],
                );
            }

            return $answer;
        });
    }
}
