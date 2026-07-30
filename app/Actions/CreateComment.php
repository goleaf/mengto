<?php

namespace App\Actions;

use App\Models\ForumAnswer;
use App\Models\ForumComment;
use App\Models\ForumTopic;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateComment
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(ForumTopic $topic, array $data): ForumComment
    {
        return DB::transaction(function () use ($topic, $data): ForumComment {
            $answer = ForumAnswer::query()
                ->select(['id', 'topic_id'])
                ->whereKey($data['answer_id'])
                ->firstOrFail();

            if ($answer->topic_id !== $topic->id) {
                throw ValidationException::withMessages([
                    'answer_id' => 'The selected answer does not belong to this topic.',
                ]);
            }

            $parentId = $data['parent_id'] ?? null;

            if ($parentId !== null) {
                $parent = ForumComment::query()
                    ->select(['id', 'answer_id', 'parent_id'])
                    ->whereKey($parentId)
                    ->firstOrFail();

                if ($parent->answer_id !== $answer->id || $parent->parent_id !== null) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'Comments support one reply level.',
                    ]);
                }
            }

            $identity = $this->actor->identity();
            $comment = ForumComment::query()->create([
                'topic_id' => $topic->id,
                'answer_id' => $answer->id,
                'parent_id' => $parentId,
                'author_key' => $identity['key'],
                'author_name' => $identity['name'],
                'author_initials' => $identity['initials'],
                'body' => trim((string) $data['body']),
                'status' => 'published',
            ]);

            $topic->update(['last_activity_at' => now()]);

            return $comment;
        });
    }
}
