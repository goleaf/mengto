<?php

declare(strict_types=1);

namespace PawCircle\Scripts\Support;

use RuntimeException;

final class ReadableTranslationKey
{
    private const int MAX_LENGTH = 160;

    public static function normalizeText(string $text): string
    {
        return trim(self::comparableText($text));
    }

    public static function fromText(string $text): string
    {
        $normalized = mb_strtolower(self::normalizeText($text));
        $key = trim((string) preg_replace('/[^\pL\pN]+/u', '_', $normalized), '_');
        $key = str_replace('pawcircle', 'brand', $key !== '' ? $key : 'text');

        if (mb_strlen($key) <= self::MAX_LENGTH) {
            return $key;
        }

        $truncated = mb_substr($key, 0, self::MAX_LENGTH);
        $wordBoundary = mb_strrpos($truncated, '_');

        return $wordBoundary === false
            ? $truncated
            : rtrim(mb_substr($truncated, 0, $wordBoundary), '_');
    }

    /**
     * @param  array<string, string>  $catalogue
     * @param  array<string, string>  $overrides  Normalized source text to deliberate key.
     */
    public static function resolve(
        string $text,
        array $catalogue,
        array $overrides = [],
    ): string {
        $normalized = self::normalizeText($text);
        $comparable = self::comparableText($text);

        foreach ($catalogue as $key => $value) {
            if (self::comparableText($value) === $comparable) {
                return $key;
            }
        }

        $key = $overrides[$normalized] ?? self::fromText($normalized);
        self::assertReadable($key);

        if (
            array_key_exists($key, $catalogue)
            && self::comparableText($catalogue[$key]) !== $comparable
        ) {
            throw new RuntimeException(
                "Translation key [{$key}] already belongs to another value. "
                .'Add a deliberate translation key for the new source text.',
            );
        }

        return $key;
    }

    private static function comparableText(string $text): string
    {
        return (string) preg_replace(
            '/\s+/u',
            ' ',
            html_entity_decode($text, ENT_QUOTES | ENT_HTML5),
        );
    }

    private static function assertReadable(string $key): void
    {
        if (
            preg_match('/^[\pL\pN]+(?:_[\pL\pN]+)*$/u', $key) !== 1
            || mb_strtolower($key) !== $key
            || preg_match('/_[0-9a-f]{10}$/', $key) === 1
        ) {
            throw new RuntimeException(
                "Translation key [{$key}] is not readable lower snake case.",
            );
        }
    }
}
