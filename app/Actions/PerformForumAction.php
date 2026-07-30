<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumSubscriptionLevel;
use App\Enums\ForumTopicStatus;
use App\Enums\KnowledgeStatus;
use App\Models\ForumAnswer;
use App\Models\ForumBlock;
use App\Models\ForumEngagement;
use App\Models\ForumNotification;
use App\Models\ForumReport;
use App\Models\ForumTopic;
use App\Models\ForumVote;
use App\Models\KnowledgeArticle;
use App\Services\ForumActor;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PerformForumAction
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly Gate $gate,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array{message: string, topic: ForumTopic|null, article: KnowledgeArticle|null}
     */
    public function handle(array $data): array
    {
        return DB::transaction(fn (): array => match ($data['action']) {
            'toggle-bookmark' => $this->toggleBookmark((int) $data['topic_id']),
            'set-subscription' => $this->setSubscription((int) $data['topic_id'], (string) $data['value']),
            'vote-answer' => $this->vote((int) $data['answer_id'], (string) $data['value'], $data['reason'] ?? null),
            'accept-answer' => $this->acceptAnswer((int) $data['answer_id']),
            'resolve-topic' => $this->changeStatus((int) $data['topic_id'], ForumTopicStatus::Resolved),
            'reopen-topic' => $this->changeStatus((int) $data['topic_id'], ForumTopicStatus::Answered),
            'report-topic' => $this->report('topic', (int) $data['topic_id'], (string) $data['reason'], $data['details'] ?? null),
            'report-answer' => $this->report('answer', (int) $data['answer_id'], (string) $data['reason'], $data['details'] ?? null),
            'block-author' => $this->blockAuthor((string) $data['author_key']),
            'mark-notification-read' => $this->markNotificationRead((int) $data['notification_id']),
            'convert-to-knowledge' => $this->convertToKnowledge((int) $data['topic_id']),
            default => throw ValidationException::withMessages([
                'action' => __('actions.invalid'),
            ]),
        });
    }

    /** @return array{message: string, topic: ForumTopic, article: null} */
    private function toggleBookmark(int $topicId): array
    {
        $topic = $this->topic($topicId);
        $engagement = ForumEngagement::query()->firstOrCreate(
            ['topic_id' => $topic->id, 'user_key' => $this->actor->key()],
            ['subscription_level' => ForumSubscriptionLevel::None, 'is_bookmarked' => false],
        );

        $engagement->update(['is_bookmarked' => ! $engagement->is_bookmarked]);

        return [
            'message' => $engagement->is_bookmarked
                ? __('forum.feedback.topic_saved')
                : __('forum.feedback.topic_unsaved'),
            'topic' => $topic,
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: ForumTopic, article: null} */
    private function setSubscription(int $topicId, string $value): array
    {
        $level = ForumSubscriptionLevel::tryFrom($value);

        if ($level === null) {
            throw ValidationException::withMessages([
                'value' => __('forum.validation.notification_level'),
            ]);
        }

        $topic = $this->topic($topicId);

        ForumEngagement::query()->updateOrCreate(
            ['topic_id' => $topic->id, 'user_key' => $this->actor->key()],
            ['subscription_level' => $level],
        );

        return [
            'message' => __('forum.feedback.notifications_updated'),
            'topic' => $topic,
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: ForumTopic, article: null} */
    private function vote(int $answerId, string $value, ?string $reason): array
    {
        if (! in_array($value, ['helpful', 'not-helpful', 'needs-source', 'outdated', 'dangerous', 'off-topic'], true)) {
            throw ValidationException::withMessages([
                'value' => __('forum.validation.answer_rating'),
            ]);
        }

        $answer = ForumAnswer::query()
            ->select(['id', 'topic_id'])
            ->findOrFail($answerId);

        ForumVote::query()->updateOrCreate(
            ['answer_id' => $answer->id, 'user_key' => $this->actor->key()],
            ['value' => $value, 'reason' => $reason],
        );

        $answer->update([
            'helpful_count' => ForumVote::query()
                ->where('answer_id', $answer->id)
                ->where('value', 'helpful')
                ->count(),
        ]);

        return [
            'message' => __('forum.feedback.answer_rating_saved'),
            'topic' => $this->topic($answer->topic_id),
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: ForumTopic, article: null} */
    private function acceptAnswer(int $answerId): array
    {
        $answer = ForumAnswer::query()
            ->select(['id', 'topic_id'])
            ->findOrFail($answerId);
        $topic = $this->topic($answer->topic_id);

        $this->ensureOwner($topic);

        ForumAnswer::query()
            ->where('topic_id', $topic->id)
            ->where('is_accepted', true)
            ->update(['is_accepted' => false]);

        $answer->update(['is_accepted' => true, 'is_highlighted' => true]);
        $topic->update([
            'accepted_answer_id' => $answer->id,
            'status' => ForumTopicStatus::Resolved,
            'last_activity_at' => now(),
        ]);

        return [
            'message' => __('forum.feedback.answer_accepted'),
            'topic' => $topic,
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: ForumTopic, article: null} */
    private function changeStatus(int $topicId, ForumTopicStatus $status): array
    {
        $topic = $this->topic($topicId);
        $this->ensureOwner($topic);
        $topic->update(['status' => $status, 'last_activity_at' => now()]);

        return [
            'message' => $status === ForumTopicStatus::Resolved
                ? __('forum.feedback.topic_resolved')
                : __('forum.feedback.topic_reopened'),
            'topic' => $topic,
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: ForumTopic, article: null} */
    private function report(string $type, int $id, string $reason, ?string $details): array
    {
        $answer = $type === 'answer'
            ? ForumAnswer::query()->select(['id', 'topic_id'])->findOrFail($id)
            : null;
        $topic = $this->topic($answer === null ? $id : $answer->topic_id);
        $highPriority = in_array($reason, [
            'dangerous-advice',
            'animal-cruelty',
            'fraud',
            'personal-data',
        ], true);

        ForumReport::query()->create([
            'topic_id' => $topic->id,
            'answer_id' => $answer?->id,
            'reporter_key' => $this->actor->key(),
            'reason' => $reason,
            'details' => $details,
            'priority' => $highPriority ? 'high' : 'normal',
            'status' => 'submitted',
        ]);

        return [
            'message' => __('forum.feedback.report_submitted'),
            'topic' => $topic,
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: null, article: null} */
    private function blockAuthor(string $authorKey): array
    {
        if ($authorKey === $this->actor->key()) {
            throw ValidationException::withMessages([
                'author_key' => __('forum.validation.block_self'),
            ]);
        }

        ForumBlock::query()->updateOrCreate(
            ['user_key' => $this->actor->key(), 'blocked_author_key' => $authorKey],
            ['reason' => 'user-choice'],
        );

        return [
            'message' => __('forum.feedback.author_blocked'),
            'topic' => null,
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: ForumTopic|null, article: null} */
    private function markNotificationRead(int $notificationId): array
    {
        $notification = ForumNotification::query()
            ->select(['id', 'topic_id', 'user_key', 'read_at'])
            ->where('user_key', $this->actor->key())
            ->findOrFail($notificationId);
        $notification->update(['read_at' => now()]);

        return [
            'message' => __('forum.feedback.notification_read'),
            'topic' => $notification->topic_id ? $this->topic($notification->topic_id) : null,
            'article' => null,
        ];
    }

    /** @return array{message: string, topic: ForumTopic, article: KnowledgeArticle} */
    private function convertToKnowledge(int $topicId): array
    {
        $topic = ForumTopic::query()
            ->select([
                'id',
                'author_key',
                'author_name',
                'slug',
                'title',
                'body',
                'category',
                'tags',
                'language',
                'status',
                'accepted_answer_id',
            ])
            ->with(['acceptedAnswer' => fn ($query) => $query->select([
                'id',
                'topic_id',
                'author_name',
                'body',
                'sources',
            ])])
            ->findOrFail($topicId);

        $this->ensureOwner($topic);

        if ($topic->status !== ForumTopicStatus::Resolved || $topic->acceptedAnswer === null) {
            throw ValidationException::withMessages([
                'topic_id' => __('forum.validation.resolve_before_knowledge'),
            ]);
        }

        $answer = $topic->acceptedAnswer;
        $body = __('forum.knowledge.question_context')
            ."\n\n{$topic->body}\n\n"
            .__('forum.knowledge.recommended_approach')
            ."\n\n{$answer->body}";
        $article = KnowledgeArticle::query()->firstOrCreate(
            ['source_topic_id' => $topic->id],
            [
                'slug' => Str::slug($topic->title).'-guide',
                'title' => $topic->title,
                'summary' => Str::limit(strip_tags($answer->body), 240),
                'body' => $body,
                'category' => $topic->category,
                'type' => 'guide',
                'difficulty' => 'beginner',
                'audience' => __('forum.knowledge.default_audience'),
                'status' => KnowledgeStatus::Review,
                'language' => $topic->language,
                'tags' => $topic->tags,
                'sources' => $answer->sources,
                'contributors' => [
                    ['name' => $topic->author_name, 'role' => __('forum.knowledge.question_author')],
                    ['name' => $answer->author_name, 'role' => __('forum.knowledge.answer_author')],
                ],
                'current_version' => 1,
            ],
        );

        if ($article->wasRecentlyCreated) {
            $article->versions()->create([
                'version_number' => 1,
                'title' => $article->title,
                'body' => $body,
                'edited_by' => $this->actor->identity()['name'],
                'change_summary' => __('forum.knowledge.initial_change_summary'),
            ]);
        }

        return [
            'message' => __('forum.feedback.knowledge_draft_created'),
            'topic' => $topic,
            'article' => $article,
        ];
    }

    private function topic(int $topicId): ForumTopic
    {
        $topic = ForumTopic::query()
            ->select(['id', 'author_key', 'slug', 'status', 'visibility'])
            ->findOrFail($topicId);

        $this->gate->authorize('view', $topic);

        return $topic;
    }

    private function ensureOwner(ForumTopic $topic): void
    {
        $this->gate->authorize('update', $topic);
    }
}
