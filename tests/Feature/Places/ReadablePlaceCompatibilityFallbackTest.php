<?php

declare(strict_types=1);

test('place compatibility fallbacks use readable localized keys', function (string $locale): void {
    app()->setLocale($locale);

    $keys = [
        'legacy_compatibility_contribution_retained_for_review',
        'legacy_question_retained_for_moderation_review',
        'legacy_review_retained_for_moderation_review',
        'legacy_warning_retained_for_moderation_review',
    ];

    foreach ($keys as $key) {
        $translation = __("messages.{$key}");

        expect($translation, "{$locale}.messages.{$key}")
            ->not->toBe("messages.{$key}")
            ->and($translation)->not->toMatch('/_[0-9a-f]{10}$/');
    }
})->with(['en', 'lt', 'ru']);
