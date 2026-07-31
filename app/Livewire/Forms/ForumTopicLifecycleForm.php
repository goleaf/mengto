<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\ForumTopicUpdateRequestKind;
use App\Enums\ForumTopicUpdateRequestStatus;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumTopicLifecycleForm extends Form
{
    public string $requestKind = 'update-request';

    public string $requestReason = '';

    public string $proposedBody = '';

    public string $reviewDecision = 'accepted';

    public string $reviewReason = '';

    public string $redirectSlug = '';

    public string $legalHoldReasonCode = '';

    public string $legalHoldPrivateReason = '';

    public string $legalHoldReviewAt = '';

    public string $legalHoldReleaseReason = '';

    /** @return array{kind: ForumTopicUpdateRequestKind, reason: string, proposed_body: string|null} */
    public function updateRequestData(): array
    {
        /** @var array{requestKind: string, requestReason: string, proposedBody: string} $validated */
        $validated = $this->validate([
            'requestKind' => ['required', Rule::enum(ForumTopicUpdateRequestKind::class)],
            'requestReason' => ['required', 'string', 'min:20', 'max:2000'],
            'proposedBody' => [
                Rule::requiredIf(
                    $this->requestKind === ForumTopicUpdateRequestKind::CommunityProposal->value,
                ),
                'nullable',
                'string',
                'min:40',
                'max:10000',
            ],
        ]);

        return [
            'kind' => ForumTopicUpdateRequestKind::from($validated['requestKind']),
            'reason' => trim($validated['requestReason']),
            'proposed_body' => filled($validated['proposedBody'])
                ? trim($validated['proposedBody'])
                : null,
        ];
    }

    /** @return array{decision: ForumTopicUpdateRequestStatus, reason: string} */
    public function reviewData(): array
    {
        /** @var array{reviewDecision: string, reviewReason: string} $validated */
        $validated = $this->validate([
            'reviewDecision' => [
                'required',
                Rule::in([
                    ForumTopicUpdateRequestStatus::Accepted->value,
                    ForumTopicUpdateRequestStatus::Rejected->value,
                ]),
            ],
            'reviewReason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        return [
            'decision' => ForumTopicUpdateRequestStatus::from($validated['reviewDecision']),
            'reason' => trim($validated['reviewReason']),
        ];
    }

    /** @return array{slug: string} */
    public function redirectData(): array
    {
        /** @var array{redirectSlug: string} $validated */
        $validated = $this->validate([
            'redirectSlug' => [
                'required',
                'string',
                'max:180',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            ],
        ]);

        return ['slug' => $validated['redirectSlug']];
    }

    /** @return array{reason_code: string, private_reason: string, review_at: string|null} */
    public function legalHoldData(): array
    {
        /** @var array{legalHoldReasonCode: string, legalHoldPrivateReason: string, legalHoldReviewAt: string} $validated */
        $validated = $this->validate([
            'legalHoldReasonCode' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9-]+$/',
            ],
            'legalHoldPrivateReason' => ['required', 'string', 'min:20', 'max:5000'],
            'legalHoldReviewAt' => ['nullable', 'date', 'after:now'],
        ]);

        return [
            'reason_code' => $validated['legalHoldReasonCode'],
            'private_reason' => trim($validated['legalHoldPrivateReason']),
            'review_at' => filled($validated['legalHoldReviewAt'])
                ? $validated['legalHoldReviewAt']
                : null,
        ];
    }

    public function legalHoldReleaseReason(): string
    {
        /** @var array{legalHoldReleaseReason: string} $validated */
        $validated = $this->validate([
            'legalHoldReleaseReason' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        return trim($validated['legalHoldReleaseReason']);
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'requestKind' => __('forum_topic_lifecycle.fields.request_kind'),
            'requestReason' => __('forum_topic_lifecycle.fields.request_reason'),
            'proposedBody' => __('forum_topic_lifecycle.fields.proposed_body'),
            'reviewDecision' => __('forum_topic_lifecycle.fields.review_decision'),
            'reviewReason' => __('forum_topic_lifecycle.fields.review_reason'),
            'redirectSlug' => __('forum_topic_lifecycle.fields.redirect_slug'),
            'legalHoldReasonCode' => __('forum_topic_lifecycle.fields.hold_reason_code'),
            'legalHoldPrivateReason' => __('forum_topic_lifecycle.fields.hold_private_reason'),
            'legalHoldReviewAt' => __('forum_topic_lifecycle.fields.hold_review_at'),
            'legalHoldReleaseReason' => __('forum_topic_lifecycle.fields.hold_release_reason'),
        ];
    }
}
