<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

final class ValidWebVtt implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('forum_accessibility.validation.invalid_webvtt')->translate();

            return;
        }

        $contents = $value->getContent();
        $normalized = str_starts_with($contents, "\xEF\xBB\xBF")
            ? substr($contents, 3)
            : $contents;

        if (
            str_contains($normalized, "\0")
            || preg_match('/\AWEBVTT(?:[ \t][^\r\n]*)?(?:\r\n|\n|\r|$)/', $normalized) !== 1
            || preg_match(
                '/(?:^|\R)(?:[^\r\n]+\R)?(?:\d{2}:)?\d{2}:\d{2}\.\d{3}[ \t]+-->[ \t]+'
                    .'(?:\d{2}:)?\d{2}:\d{2}\.\d{3}(?:[ \t][^\r\n]*)?(?:\R|$)/m',
                $normalized,
            ) !== 1
        ) {
            $fail('forum_accessibility.validation.invalid_webvtt')->translate();
        }
    }
}
