<?php

declare(strict_types=1);

use PawCircle\Scripts\Support\ReadableTranslationKey;

function loadReadableTranslationKey(): void
{
    $path = dirname(__DIR__, 3).'/scripts/Support/ReadableTranslationKey.php';

    if (is_file($path)) {
        require_once $path;
    }

    expect(class_exists(ReadableTranslationKey::class), $path)->toBeTrue();
}

test('readable translation keys describe the complete source text', function (): void {
    loadReadableTranslationKey();

    expect(ReadableTranslationKey::fromText(
        'Legacy question retained for moderation review.',
    ))->toBe('legacy_question_retained_for_moderation_review')
        ->and(ReadableTranslationKey::fromText('  PawCircle &amp; neighbors  '))
        ->toBe('brand_neighbors');
});

test('existing keys remain stable when their normalized source text matches', function (): void {
    loadReadableTranslationKey();

    expect(ReadableTranslationKey::resolve('Saved.', [
        'saved_notice' => 'Saved.',
    ]))->toBe('saved_notice');
});

test('ambiguous readable keys fail instead of receiving an opaque suffix', function (): void {
    loadReadableTranslationKey();

    expect(fn (): string => ReadableTranslationKey::resolve('about', [
        'about_lowercase_prefix' => 'about ',
        'about' => 'About',
    ]))->toThrow(
        RuntimeException::class,
        'Add a deliberate translation key',
    );
});

test('explicit semantic overrides resolve an intentional collision', function (): void {
    loadReadableTranslationKey();

    expect(ReadableTranslationKey::resolve(
        'about',
        ['about' => 'About'],
        ['about' => 'about_lowercase'],
    ))->toBe('about_lowercase');
});

test('semantic overrides must remain readable lower snake case', function (): void {
    loadReadableTranslationKey();

    expect(fn (): string => ReadableTranslationKey::resolve(
        'about',
        ['about' => 'About'],
        ['about' => 'About_Lowercase'],
    ))->toThrow(
        RuntimeException::class,
        'not readable lower snake case',
    );
});
