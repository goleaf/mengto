<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\ForumExpertAnswerStatus;
use App\Models\ForumExpertSessionAnswer;
use App\Models\ForumExpertSessionCorrection;
use App\Models\User;
use App\Services\ForumExpertSessionAudit;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class CorrectForumExpertSessionAnswer
{
    public function __construct(
        private Gate $gate,
        private ForumExpertSessionAudit $audit,
    ) {}

    /**
     * @param  array<int, array{label: string, url: string}>  $sourceLinks
     */
    public function handle(
        User $actor,
        ForumExpertSessionAnswer $answer,
        string $body,
        array $sourceLinks,
        string $reason,
        int $expectedVersion,
    ): ForumExpertSessionCorrection {
        $this->gate->forUser($actor)->authorize('correct', $answer);

        $validated = validator([
            'body' => trim($body),
            'source_links' => $sourceLinks,
            'reason' => trim($reason),
            'expected_version' => $expectedVersion,
        ], [
            'body' => ['required', 'string', 'min:20', 'max:20000'],
            'source_links' => ['array', 'max:10'],
            'source_links.*' => ['array:label,url'],
            'source_links.*.label' => ['required', 'string', 'max:180'],
            'source_links.*.url' => ['required', 'url:http,https', 'max:2000', 'distinct'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'expected_version' => ['required', 'integer', 'min:1'],
        ])->validate();

        return DB::transaction(function () use ($actor, $answer, $expectedVersion, $validated): ForumExpertSessionCorrection {
            $locked = ForumExpertSessionAnswer::query()
                ->with('session.expertProfile')
                ->lockForUpdate()
                ->findOrFail($answer->id);
            $this->gate->forUser($actor)->authorize('correct', $locked);

            if ($locked->current_version !== $expectedVersion) {
                throw ValidationException::withMessages([
                    'correctionForm.body' => __('forum_expert_sessions.validation.concurrent_change'),
                ]);
            }

            $previousStatus = $locked->status;
            $version = $locked->current_version + 1;
            $correction = ForumExpertSessionCorrection::query()->create([
                'forum_expert_session_id' => $locked->forum_expert_session_id,
                'forum_expert_session_answer_id' => $locked->id,
                'actor_user_id' => $actor->id,
                'version' => $version,
                'previous_body' => $locked->body,
                'previous_source_links' => $locked->source_links,
                'corrected_body' => $validated['body'],
                'corrected_source_links' => $validated['source_links'],
                'reason' => $validated['reason'],
                'created_at' => now(),
            ]);

            $locked->forceFill([
                'body' => $validated['body'],
                'source_links' => $validated['source_links'],
                'status' => ForumExpertAnswerStatus::Corrected,
                'current_version' => $version,
            ])->save();

            $this->audit->record(
                session: $locked->session,
                actor: $actor,
                eventType: 'answer-corrected',
                reasonCode: 'answer-corrected',
                summaryTranslationKey: 'forum_expert_sessions.history.answer_corrected',
                answer: $locked,
                fromStatus: $previousStatus->value,
                toStatus: ForumExpertAnswerStatus::Corrected->value,
                metadata: ['version' => $version],
                idempotencyKey: "expert-session:answer:{$locked->id}:correction:{$version}",
            );

            return $correction;
        }, 3);
    }
}
