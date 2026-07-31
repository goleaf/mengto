<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\ForumReviewSubjectData;
use App\Models\ForumAnswer;
use App\Models\ForumCommunityNote;
use App\Models\ForumTopic;
use App\Models\KnowledgeArticle;
use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

final readonly class ForumReviewSubjectResolver
{
    public function __construct(private Gate $gate) {}

    public function resolve(User $viewer, string $subjectType, int $subjectId): ForumReviewSubjectData
    {
        return match ($subjectType) {
            'forum-topic' => $this->topic($viewer, $subjectId),
            'forum-answer' => $this->answer($viewer, $subjectId),
            'knowledge-guide' => $this->guide($viewer, $subjectId),
            'community-note' => $this->note($viewer, $subjectId),
            default => throw ValidationException::withMessages([
                'subject_type' => __('forum_review.validation.unsupported_subject'),
            ]),
        };
    }

    private function topic(User $viewer, int $subjectId): ForumReviewSubjectData
    {
        $topic = ForumTopic::query()
            ->select(['id', 'author_id', 'title', 'body', 'is_medical', 'visibility'])
            ->findOrFail($subjectId);
        $this->gate->forUser($viewer)->authorize('view', $topic);

        return new ForumReviewSubjectData(
            type: 'forum-topic',
            id: $topic->id,
            authorUserId: $topic->author_id,
            title: $topic->title,
            excerpt: str($topic->body)->squish()->limit(500)->toString(),
            isMedical: $topic->is_medical,
            containsPrivateEvidence: false,
        );
    }

    private function answer(User $viewer, int $subjectId): ForumReviewSubjectData
    {
        $answer = ForumAnswer::query()
            ->select(['id', 'topic_id', 'author_id', 'body', 'status'])
            ->with('topic:id,title,visibility,is_medical,author_id,author_key')
            ->findOrFail($subjectId);
        $this->gate->forUser($viewer)->authorize('view', $answer);
        $this->gate->forUser($viewer)->authorize('view', $answer->topic);

        return new ForumReviewSubjectData(
            type: 'forum-answer',
            id: $answer->id,
            authorUserId: $answer->author_id,
            title: $answer->topic->title,
            excerpt: str($answer->body)->squish()->limit(500)->toString(),
            isMedical: $answer->topic->is_medical,
            containsPrivateEvidence: false,
        );
    }

    private function guide(User $viewer, int $subjectId): ForumReviewSubjectData
    {
        $article = KnowledgeArticle::query()
            ->select(['id', 'created_by_user_id', 'title', 'summary', 'status'])
            ->findOrFail($subjectId);
        $this->gate->forUser($viewer)->authorize('view', $article);

        return new ForumReviewSubjectData(
            type: 'knowledge-guide',
            id: $article->id,
            authorUserId: $article->created_by_user_id,
            title: $article->title,
            excerpt: str($article->summary)->squish()->limit(500)->toString(),
            isMedical: false,
            containsPrivateEvidence: false,
        );
    }

    private function note(User $viewer, int $subjectId): ForumReviewSubjectData
    {
        $note = ForumCommunityNote::query()
            ->select([
                'id',
                'proposer_user_id',
                'subject_author_user_id',
                'note_type',
                'status',
                'body',
            ])
            ->findOrFail($subjectId);
        $this->gate->forUser($viewer)->authorize('view', $note);

        return new ForumReviewSubjectData(
            type: 'community-note',
            id: $note->id,
            authorUserId: $note->proposer_user_id,
            title: $note->note_type->label(),
            excerpt: str($note->body)->squish()->limit(500)->toString(),
            isMedical: false,
            containsPrivateEvidence: false,
        );
    }

    public function model(string $subjectType, int $subjectId): Model
    {
        return match ($subjectType) {
            'forum-topic' => ForumTopic::query()->findOrFail($subjectId),
            'forum-answer' => ForumAnswer::query()->findOrFail($subjectId),
            'knowledge-guide' => KnowledgeArticle::query()->findOrFail($subjectId),
            'community-note' => ForumCommunityNote::query()->findOrFail($subjectId),
            default => throw ValidationException::withMessages([
                'subject_type' => __('forum_review.validation.unsupported_subject'),
            ]),
        };
    }
}
