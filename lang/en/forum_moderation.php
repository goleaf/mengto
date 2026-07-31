<?php

declare(strict_types=1);

use App\Services\ForumModerationActionCatalog;
use App\Services\ForumReportReasonCatalog;

$labels = static fn (array $keys): array => array_combine(
    $keys,
    array_map(
        static fn (string $key): string => ucfirst(str_replace('-', ' ', $key)),
        $keys,
    ),
);

return [
    'reasons' => $labels(ForumReportReasonCatalog::KEYS),
    'actions' => $labels(ForumModerationActionCatalog::KEYS),
    'appeal_statuses' => [
        'submitted' => 'Submitted',
        'appeal-review' => 'Under appeal review',
        'upheld' => 'Upheld',
        'modified' => 'Modified',
        'reversed' => 'Reversed',
        'new-review' => 'Returned for a new review',
    ],
    'forms' => [
        'truthfulness' => 'I confirm that this report is truthful to the best of my knowledge.',
        'immediate_safety' => 'This may involve immediate safety risk.',
        'block_user' => 'Block this user after submitting the report.',
    ],
    'messages' => [
        'report_submitted' => 'Your report was received.',
        'case_opened' => 'A moderation case was opened.',
        'case_assigned' => 'The moderation case was assigned for review.',
        'action_applied' => 'A moderation action was applied.',
        'moderator_recused' => 'A moderator recused from the case.',
        'appeal_decided' => 'The appeal review was completed.',
    ],
    'validation' => [
        'truthfulness_required' => 'Confirm that the report is truthful before submitting it.',
        'unsupported_subject' => 'This item cannot be reported through this form.',
        'immediate_safety_not_available' => 'Immediate-safety escalation is unavailable for this report reason.',
        'rate_limited' => 'Too many reports were submitted. Please wait before trying again.',
        'end_required' => 'A temporary restriction requires an end date.',
        'independent_review_required' => 'This action requires approval by a different authorized reviewer.',
        'appeal_reason_length' => 'Explain the appeal in at least 20 characters.',
        'invalid_appeal_outcome' => 'Choose a supported appeal outcome.',
        'closed_case_assignment' => 'A completed moderation case cannot be assigned.',
        'case_report_limit' => 'This case has too many linked reports for an interactive action. Use the bounded operations workflow.',
        'appeal_already_decided' => 'This appeal has already received a decision.',
        'invalid_recusal_reason' => 'Choose a supported reason for recusal.',
    ],
];
