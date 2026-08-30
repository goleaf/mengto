<?php

declare(strict_types=1);

require_once __DIR__.'/Support/ReadableTranslationKey.php';

use PawCircle\Scripts\Support\ReadableTranslationKey;

/**
 * Moves static first-party Blade presentation strings into lang/{locale}/ui.php.
 *
 * The transformation is intentionally limited to plain text nodes and known
 * presentation attributes. Dynamic sentences remain visible in the report so
 * they can be migrated by hand with named placeholders.
 */
$root = dirname(__DIR__);
$viewRoot = $root.'/resources/views';
$locales = ['en', 'lt', 'ru'];
$arguments = isset($_SERVER['argv']) && is_array($_SERVER['argv'])
    ? $_SERVER['argv']
    : [];
$checkOnly = in_array('--check', $arguments, true);
$attributeNames = [
    'action-label',
    'active-label',
    'aria-label',
    'description',
    'empty',
    'empty-title',
    'eyebrow',
    'filters-label',
    'label',
    'placeholder',
    'search-label',
    'search-placeholder',
    'sort-label',
    'submit-label',
    'summary-label',
    'title',
];

/** @var array<string, string> $english */
$english = is_file($root.'/lang/en/ui.php')
    ? require $root.'/lang/en/ui.php'
    : [];
/** @var array<string, string> $renamedKeys */
$renamedKeys = [];

foreach ($english as $key => $value) {
    if (! str_contains($key, 'pawcircle')) {
        continue;
    }

    $newKey = str_replace('pawcircle', 'brand', $key);
    $english[$newKey] = $value;
    $renamedKeys[$key] = $newKey;
    unset($english[$key]);
}

/** @var array<string, string> $keyByText */
$keyByText = [];

foreach ($english as $key => $value) {
    $keyByText[ReadableTranslationKey::normalizeText($value)] ??= $key;
}

$translationKey = static function (string $text) use (&$english, &$keyByText): string {
    $text = ReadableTranslationKey::normalizeText($text);

    if (isset($keyByText[$text])) {
        return $keyByText[$text];
    }

    $key = ReadableTranslationKey::resolve($text, $english);

    $english[$key] = $text;
    $keyByText[$text] = $key;

    return $key;
};

$isPresentationText = static function (string $text): bool {
    $trimmed = trim($text);

    return $trimmed !== ''
        && preg_match('/\pL/u', $trimmed) === 1
        && ! str_contains($trimmed, '{{')
        && ! str_contains($trimmed, '{!!')
        && ! str_contains($trimmed, '<?')
        && ! str_contains($trimmed, '@')
        && ! str_contains($trimmed, '$')
        && ! str_contains($trimmed, '=>')
        && ! str_contains($trimmed, '->')
        && ! str_contains($trimmed, '::')
        && ! str_contains($trimmed, '])')
        && ! str_contains($trimmed, '}}')
        && ! str_contains($trimmed, 'class=');
};

$localizeExpressionLiterals = static function (
    string $markup,
) use ($translationKey, &$replacements): string {
    $localized = preg_replace_callback(
        "/'((?:\\\\.|[^'])*)'/u",
        static function (array $matches) use ($translationKey, &$replacements): string {
            $literal = $matches[0];
            $value = str_replace(['\\\\', "\\'"], ['\\', "'"], $matches[1]);
            $trimmed = trim($value);

            if (
                $trimmed === ''
                || preg_match('/^\p{Lu}/u', $trimmed) !== 1
                || preg_match('/^(?:GET|POST|PUT|PATCH|DELETE|UTC|[A-Z]{2,4})$/', $trimmed) === 1
                || (
                    preg_match('~[-/,:\\\\]~', $trimmed) === 1
                    && preg_match('~^[A-Za-z]?(?:[YymndjGHhisvcuDFlMSWz][\\\\/:,. T-]*)+$~', $trimmed) === 1
                )
                || (str_contains($trimmed, '/') && ! str_contains($trimmed, ' '))
            ) {
                return $literal;
            }

            $replacements++;
            preg_match('/^\s*/u', $value, $leading);
            preg_match('/\s*$/u', $value, $trailing);
            $parts = [];

            if (($leading[0] ?? '') !== '') {
                $parts[] = var_export($leading[0], true);
            }

            $parts[] = "__('ui.".$translationKey($trimmed)."')";

            if (($trailing[0] ?? '') !== '') {
                $parts[] = var_export($trailing[0], true);
            }

            return implode('.', $parts);
        },
        $markup,
    );

    if ($localized === null) {
        throw new RuntimeException('Unable to localize Blade expression literals.');
    }

    return $localized;
};

$localizeTextNodes = static function (
    string $markup,
    string $path,
) use ($isPresentationText, $translationKey, &$replacements): string {
    $length = strlen($markup);
    $cursor = 0;
    $result = '';

    while ($cursor < $length) {
        $tagStart = strpos($markup, '<', $cursor);

        if ($tagStart === false) {
            $text = substr($markup, $cursor);
            $tagStart = $length;
        } else {
            $next = $markup[$tagStart + 1] ?? '';

            if ($next === '' || preg_match('/[A-Za-z!\/?]/', $next) !== 1) {
                $result .= substr($markup, $cursor, $tagStart - $cursor + 1);
                $cursor = $tagStart + 1;

                continue;
            }

            $text = substr($markup, $cursor, $tagStart - $cursor);
        }

        if ($isPresentationText($text)) {
            preg_match('/^\s*/u', $text, $leading);
            preg_match('/\s*$/u', $text, $trailing);
            $result .= ($leading[0] ?? '')
                ."{{ __('ui.".$translationKey($text)."') }}"
                .($trailing[0] ?? '');
            $replacements++;
        } else {
            $result .= $text;
        }

        if ($tagStart === $length) {
            break;
        }

        $index = $tagStart + 1;
        $quote = null;
        $roundDepth = 0;
        $squareDepth = 0;
        $curlyDepth = 0;

        for (; $index < $length; $index++) {
            $character = $markup[$index];

            if ($quote !== null) {
                if ($character === $quote && ($markup[$index - 1] ?? '') !== '\\') {
                    $quote = null;
                }

                continue;
            }

            if ($character === '"' || $character === "'") {
                $quote = $character;

                continue;
            }

            match ($character) {
                '(' => $roundDepth++,
                ')' => $roundDepth = max(0, $roundDepth - 1),
                '[' => $squareDepth++,
                ']' => $squareDepth = max(0, $squareDepth - 1),
                '{' => $curlyDepth++,
                '}' => $curlyDepth = max(0, $curlyDepth - 1),
                default => null,
            };

            if (
                $character === '>'
                && $roundDepth === 0
                && $squareDepth === 0
                && $curlyDepth === 0
            ) {
                break;
            }
        }

        if ($index >= $length) {
            throw new RuntimeException("Unclosed HTML tag in {$path}");
        }

        $result .= substr($markup, $tagStart, $index - $tagStart + 1);
        $cursor = $index + 1;
    }

    return $result;
};

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewRoot, FilesystemIterator::SKIP_DOTS),
);
$changedFiles = 0;
$changedPaths = [];
$replacements = 0;

foreach ($files as $file) {
    if (! $file instanceof SplFileInfo || ! str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $path = $file->getPathname();
    $original = file_get_contents($path);

    if ($original === false) {
        throw new RuntimeException("Unable to read {$path}");
    }

    $working = str_replace(
        array_map(static fn (string $key): string => 'ui.'.$key, array_keys($renamedKeys)),
        array_map(static fn (string $key): string => 'ui.'.$key, array_values($renamedKeys)),
        $original,
    );
    $parts = preg_split(
        '/(<script\b[^>]*>.*?<\/script>)/is',
        $working,
        -1,
        PREG_SPLIT_DELIM_CAPTURE,
    );

    if ($parts === false) {
        throw new RuntimeException("Unable to split {$path}");
    }

    foreach ($parts as $index => $part) {
        if (preg_match('/^<script\b/is', $part) === 1) {
            continue;
        }

        $part = preg_replace_callback(
            "/'((?:\\\\.|[^'])*)'/u",
            static function (array $matches) use ($part, $localizeExpressionLiterals): string {
                $literal = $matches[0][0];
                $offset = $matches[0][1];

                $before = substr($part, max(0, $offset - 96), min(96, $offset));
                $after = substr($part, $offset + strlen($literal), 24);

                if (
                    preg_match('/^\s*=>/', $after) === 1
                    || (
                        preg_match('/\[\s*$/', $before) === 1
                        && preg_match('/^\s*\]/', $after) === 1
                    )
                    || preg_match('/(?:===|!==|==|!=)\s*$/', $before) === 1
                    || preg_match('/^\s*(?:===|!==|==|!=)/', $after) === 1
                    || preg_match(
                        "/'(?:class|icon|tone|component|section|variant|size|method)'\\s*=>\\s*$/",
                        $before,
                    ) === 1
                ) {
                    return $literal;
                }

                return $localizeExpressionLiterals($literal);
            },
            $part,
            -1,
            $expressionReplacementCount,
            PREG_OFFSET_CAPTURE,
        );

        if ($part === null) {
            throw new RuntimeException("Unable to localize expressions in {$path}");
        }

        $attributePattern = '/(?<![A-Za-z0-9_:-])('.implode('|', array_map(
            static fn (string $name): string => preg_quote($name, '/'),
            $attributeNames,
        )).')="([^"]*)"/u';

        $part = preg_replace_callback(
            $attributePattern,
            static function (array $matches) use ($isPresentationText, $translationKey, &$replacements): string {
                if (! $isPresentationText($matches[2])) {
                    return $matches[0];
                }

                $replacements++;

                return $matches[1].'="{{ __(\'ui.'.$translationKey($matches[2]).'\') }}"';
            },
            $part,
        );

        if ($part === null) {
            throw new RuntimeException("Unable to localize attributes in {$path}");
        }

        $parts[$index] = $localizeTextNodes($part, $path);
    }

    $localized = implode('', $parts);

    if ($localized === $original) {
        continue;
    }

    if (! $checkOnly && file_put_contents($path, $localized) === false) {
        throw new RuntimeException("Unable to write {$path}");
    }

    $changedFiles++;
    $changedPaths[] = str_replace($root.'/', '', $path);
}

ksort($english);

foreach ($locales as $locale) {
    $path = "{$root}/lang/{$locale}/ui.php";
    /** @var array<string, string> $existing */
    $existing = is_file($path) ? require $path : [];
    $values = [];

    foreach ($english as $key => $value) {
        $values[$key] = $existing[$key] ?? $value;
    }

    $contents = "<?php\n\ndeclare(strict_types=1);\n\nreturn "
        .var_export($values, true)
        .";\n";

    if (! $checkOnly && file_put_contents($path, $contents) === false) {
        throw new RuntimeException("Unable to write {$path}");
    }
}

fwrite(
    STDOUT,
    "Localized {$replacements} literals across {$changedFiles} Blade files into "
    .count($english)." stable keys.\n",
);

if ($checkOnly && $changedPaths !== []) {
    fwrite(STDOUT, implode(PHP_EOL, $changedPaths).PHP_EOL);
}

if ($checkOnly && ($replacements > 0 || $renamedKeys !== [])) {
    exit(1);
}
