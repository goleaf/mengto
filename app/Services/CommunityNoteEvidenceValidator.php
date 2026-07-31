<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Validation\ValidationException;

final class CommunityNoteEvidenceValidator
{
    /** @param list<array{url: string, label: string}> $evidence */
    public function validate(array $evidence): void
    {
        if ($evidence === [] || count($evidence) > 8) {
            throw ValidationException::withMessages([
                'evidence' => __('forum_review.validation.evidence_count'),
            ]);
        }

        foreach ($evidence as $item) {
            $url = $item['url'] ?? '';
            $label = trim($item['label'] ?? '');

            if (
                filter_var($url, FILTER_VALIDATE_URL) === false
                || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)
                || mb_strlen($url) > 500
                || mb_strlen($label) < 2
                || mb_strlen($label) > 120
            ) {
                throw ValidationException::withMessages([
                    'evidence' => __('forum_review.validation.evidence_item'),
                ]);
            }
        }
    }
}
