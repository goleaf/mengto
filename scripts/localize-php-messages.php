<?php

declare(strict_types=1);

/**
 * Move complete first-party PHP messages into stable translation keys.
 *
 * Usage:
 * php scripts/localize-php-messages.php --write
 * php scripts/localize-php-messages.php --check
 */
$root = dirname(__DIR__);
$write = in_array('--write', $argv, true);
$check = in_array('--check', $argv, true);
$verbose = in_array('--verbose', $argv, true);

if ($write === $check) {
    fwrite(STDERR, "Choose exactly one of --write or --check.\n");
    exit(2);
}

$directories = [
    $root.'/app/Actions',
    $root.'/app/Http',
    $root.'/app/Livewire',
    $root.'/app/Services',
];
$files = [];

foreach ($directories as $directory) {
    if (! is_dir($directory)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);
$catalogues = [];

foreach (['en', 'lt', 'ru'] as $locale) {
    $path = "{$root}/lang/{$locale}/messages.php";
    $catalogues[$locale] = is_file($path) ? require $path : [];
}

$replacements = 0;
$changedFiles = 0;

foreach ($files as $file) {
    $source = file_get_contents($file);

    if ($source === false) {
        throw new RuntimeException("Unable to read {$file}.");
    }

    $output = '';
    $fileReplacements = 0;

    $tokens = token_get_all($source);

    foreach ($tokens as $index => $token) {
        if (! is_array($token) || $token[0] !== T_CONSTANT_ENCAPSED_STRING) {
            $output .= is_array($token) ? $token[1] : $token;

            continue;
        }

        $message = decodedPhpString($token[1]);

        if (
            isInsideConstantExpression($tokens, $index)
            || (
                ! isCompleteUserMessage($message)
                && ! isUserFacingArrayValue($tokens, $index, $message)
                && ! isUserFacingNamedArgumentValue($tokens, $index, $message)
                && ! isStandaloneUserFacingValue($tokens, $index, $message, $file)
            )
        ) {
            $output .= $token[1];

            continue;
        }

        $key = messageKey($message);
        $catalogues['en'][$key] = $message;

        foreach (['lt', 'ru'] as $locale) {
            $catalogues[$locale][$key] ??= $message;
        }

        $output .= "__('messages.{$key}')";
        $fileReplacements++;

        if ($verbose) {
            $relativeFile = ltrim(str_replace($root, '', $file), '/');
            fwrite(STDOUT, "{$relativeFile}:{$token[2]}: {$message}\n");
        }
    }

    if ($fileReplacements === 0) {
        continue;
    }

    $replacements += $fileReplacements;
    $changedFiles++;

    if ($write && file_put_contents($file, $output) === false) {
        throw new RuntimeException("Unable to write {$file}.");
    }
}

if ($write) {
    foreach ($catalogues as $locale => $catalogue) {
        ksort($catalogue);
        writeCatalogue("{$root}/lang/{$locale}/messages.php", $catalogue);
    }
}

fwrite(
    STDOUT,
    "Localized {$replacements} PHP messages across {$changedFiles} files.\n",
);

if ($check && $replacements > 0) {
    exit(1);
}

function decodedPhpString(string $token): string
{
    $quote = $token[0];
    $value = substr($token, 1, -1);

    if ($quote === "'") {
        return str_replace(['\\\\', "\\'"], ['\\', "'"], $value);
    }

    return stripcslashes($value);
}

function isCompleteUserMessage(string $value): bool
{
    return mb_strlen($value) >= 8
        && preg_match('/^\p{Lu}.*\s.*[.!?]$/us', $value) === 1
        && ! str_contains($value, "\n")
        && ! str_contains($value, '://');
}

/**
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function isUserFacingArrayValue(array $tokens, int $index, string $value): bool
{
    if (
        $value === ''
        || preg_match('/\pL/u', $value) !== 1
        || (
            preg_match('/\s/u', $value) !== 1
            && preg_match('/^\p{Lu}/u', $value) !== 1
        )
        || str_contains($value, "\n")
        || str_contains($value, '://')
    ) {
        return false;
    }

    $previous = previousSignificantToken($tokens, $index);

    if (
        $previous === null
        || ! is_array($previous['token'])
        || $previous['token'][0] !== T_DOUBLE_ARROW
    ) {
        return false;
    }

    $keyToken = previousSignificantToken($tokens, $previous['index']);

    if (
        $keyToken === null
        || ! is_array($keyToken['token'])
        || $keyToken['token'][0] !== T_CONSTANT_ENCAPSED_STRING
    ) {
        return false;
    }

    $key = decodedPhpString($keyToken['token'][1]);

    return in_array($key, userFacingKeys(), true);
}

/**
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function isUserFacingNamedArgumentValue(array $tokens, int $index, string $value): bool
{
    if (! isPotentialUserFacingValue($value)) {
        return false;
    }

    $previous = previousSignificantToken($tokens, $index);

    if ($previous === null || $previous['token'] !== ':') {
        return false;
    }

    $nameToken = previousSignificantToken($tokens, $previous['index']);

    if (
        $nameToken === null
        || ! is_array($nameToken['token'])
        || $nameToken['token'][0] !== T_STRING
    ) {
        return false;
    }

    return in_array($nameToken['token'][1], userFacingKeys(), true);
}

function isPotentialUserFacingValue(string $value): bool
{
    return $value !== ''
        && preg_match('/\pL/u', $value) === 1
        && (
            preg_match('/\s/u', $value) === 1
            || preg_match('/^\p{Lu}/u', $value) === 1
        )
        && ! str_contains($value, "\n")
        && ! str_contains($value, '://');
}

/**
 * Catalogues and presenters historically pass labels as positional arguments
 * without a named field contract. This conservative fallback handles complete
 * phrases while excluding machine formats, array keys, and concatenation
 * fragments.
 *
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function isStandaloneUserFacingValue(
    array $tokens,
    int $index,
    string $value,
    string $file,
): bool {
    if (
        preg_match(
            '#/app/Services/(?:[A-Za-z]+(?:Presenter|Catalog|State|Visibility)|PreviewService)\.php$#',
            $file,
        ) !== 1
    ) {
        return false;
    }

    if (
        mb_strlen($value) < 3
        || trim($value) !== $value
        || preg_match('/^[.,;:!?()[\]{}]/u', $value) === 1
        || preg_match('/\pL/u', $value) !== 1
        || (
            preg_match('/\s/u', $value) !== 1
            && preg_match('/^\p{Lu}/u', $value) !== 1
        )
        || str_contains($value, "\n")
        || str_contains($value, '://')
        || str_contains($value, '\\')
        || str_contains($value, '/')
        || preg_match('/^[A-Z0-9_-]{2,5}$/', $value) === 1
        || preg_match('/^[YymdHhisgjAaT:\-\/\s]+$/', $value) === 1
        || preg_match('/^[a-z_]+ as [a-z_]+$/i', $value) === 1
        || preg_match('/^\/.*\/[a-z]*$/i', $value) === 1
    ) {
        return false;
    }

    $previous = previousSignificantToken($tokens, $index);
    $next = nextSignificantToken($tokens, $index);

    if (
        ($previous !== null && $previous['token'] === '.')
        || ($next !== null && $next['token'] === '.')
    ) {
        return false;
    }

    return $next === null
        || ! is_array($next['token'])
        || $next['token'][0] !== T_DOUBLE_ARROW;
}

/**
 * @return array<int, string>
 */
function userFacingKeys(): array
{
    return [
        'accessibility',
        'activity',
        'age',
        'alt',
        'answered_at',
        'attribution',
        'audience',
        'body',
        'breed',
        'caption',
        'category_label',
        'closes_at',
        'comment_policy',
        'coordinate_accuracy',
        'crowd_label',
        'data_freshness',
        'description',
        'detail',
        'disclosure',
        'eyebrow',
        'feedback',
        'followers',
        'help',
        'hint',
        'hours_summary',
        'image_alt',
        'imageAlt',
        'label',
        'last_activity',
        'leash_policy',
        'location',
        'message',
        'meta',
        'name',
        'noise_level',
        'open_label',
        'owner',
        'page_description',
        'page_title',
        'placeholder',
        'play_style',
        'quality',
        'queue',
        'recommendation_reason',
        'represented_kind',
        'role',
        'scope',
        'size',
        'species',
        'status_label',
        'subtitle',
        'summary',
        'time',
        'title',
        'type_label',
        'typeLabel',
    ];
}

/**
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 * @return array{index: int, token: array{0: int, 1: string, 2: int}|string}|null
 */
function previousSignificantToken(array $tokens, int $index): ?array
{
    for ($candidate = $index - 1; $candidate >= 0; $candidate--) {
        $token = $tokens[$candidate];

        if (
            is_array($token)
            && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)
        ) {
            continue;
        }

        return ['index' => $candidate, 'token' => $token];
    }

    return null;
}

/**
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 * @return array{index: int, token: array{0: int, 1: string, 2: int}|string}|null
 */
function nextSignificantToken(array $tokens, int $index): ?array
{
    $count = count($tokens);

    for ($candidate = $index + 1; $candidate < $count; $candidate++) {
        $token = $tokens[$candidate];

        if (
            is_array($token)
            && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)
        ) {
            continue;
        }

        return ['index' => $candidate, 'token' => $token];
    }

    return null;
}

/**
 * Translation lookups are runtime calls and cannot be emitted in class constants.
 *
 * @param  array<int, array{0: int, 1: string, 2: int}|string>  $tokens
 */
function isInsideConstantExpression(array $tokens, int $index): bool
{
    for ($candidate = $index - 1; $candidate >= 0; $candidate--) {
        $token = $tokens[$candidate];

        if ($token === ';' || $token === '{' || $token === '}') {
            return false;
        }

        if (is_array($token) && $token[0] === T_CONST) {
            return true;
        }
    }

    return false;
}

function messageKey(string $message): string
{
    $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($message));
    $slug = trim((string) $slug, '_');
    $slug = substr($slug, 0, 56);

    return $slug.'_'.substr(hash('sha256', $message), 0, 10);
}

/**
 * @param  array<string, string>  $catalogue
 */
function writeCatalogue(string $path, array $catalogue): void
{
    $lines = [
        "<?php\n\n",
        "declare(strict_types=1);\n\n",
        "return [\n",
    ];

    foreach ($catalogue as $key => $value) {
        $lines[] = '    '.var_export($key, true).' => '.var_export($value, true).",\n";
    }

    $lines[] = "];\n";

    if (file_put_contents($path, implode('', $lines)) === false) {
        throw new RuntimeException("Unable to write {$path}.");
    }
}
