<?php

declare(strict_types=1);

require_once __DIR__.'/Support/ReadableTranslationKey.php';

use PawCircle\Scripts\Support\ReadableTranslationKey;

$root = dirname(__DIR__);
$arguments = isset($_SERVER['argv']) && is_array($_SERVER['argv'])
    ? $_SERVER['argv']
    : [];
$write = in_array('--write', $arguments, true);
$check = in_array('--check', $arguments, true);

if ($write === $check) {
    fwrite(STDERR, "Choose exactly one of --write or --check.\n");
    exit(2);
}

$locales = ['en', 'lt', 'ru'];
$catalogues = ['messages', 'ui'];

/**
 * Normalized English strings can intentionally differ by case, punctuation,
 * or their role as a concatenation prefix. These reviewed names preserve the
 * distinction without falling back to an opaque digest or counter.
 *
 * @var array<string, array<string, string>>
 */
$overrides = [
    'messages' => [
        'About' => 'about',
        'About ' => 'about_prefix',
        'about ' => 'about_lowercase_prefix',
        'around Portland' => 'around_portland_lowercase',
        'Around Portland' => 'around_portland',
        'Community poll' => 'community_poll',
        'community poll' => 'community_poll_lowercase',
        'Dog park' => 'dog_park',
        'dog park' => 'dog_park_lowercase',
        'Dog parks' => 'dog_parks',
        'dog parks' => 'dog_parks_lowercase',
        'Dr. Elena Ruiz' => 'dr_elena_ruiz',
        'Dr Elena Ruiz' => 'dr_elena_ruiz_without_title_period',
        'Emergency triage' => 'emergency_triage',
        'emergency triage' => 'emergency_triage_lowercase',
        'Follow' => 'follow',
        'Follow ' => 'follow_prefix',
        'fully fenced' => 'fully_fenced_lowercase',
        'Fully fenced' => 'fully_fenced',
        'Leash or secure carrier required' => 'leash_or_secure_carrier_required',
        'Leash or secure carrier required.' => 'leash_or_secure_carrier_required_sentence',
        'leash required' => 'leash_required_lowercase',
        'Leash required' => 'leash_required',
        'Location kept private' => 'location_kept_private_profile',
        'Message' => 'message',
        'Message ' => 'message_prefix',
        'Nearby parking' => 'nearby_parking',
        'nearby parking' => 'nearby_parking_lowercase',
        'newest first' => 'newest_first_lowercase',
        'Newest first' => 'newest_first',
        'no active warnings' => 'no_active_warnings_lowercase',
        'No active warnings' => 'no_active_warnings',
        'one family at a time' => 'one_family_at_a_time_lowercase',
        'One family at a time' => 'one_family_at_a_time',
        'Open now' => 'open_now',
        'open now' => 'open_now_lowercase',
        'Pet-friendly cafes' => 'pet_friendly_cafes',
        'pet-friendly cafes' => 'pet_friendly_cafes_lowercase',
        'positive training' => 'positive_training_lowercase',
        'Positive training' => 'positive_training',
        'public transport' => 'public_transport_lowercase',
        'Public transport' => 'public_transport',
        'Quiet zone' => 'quiet_zone',
        'quiet zone' => 'quiet_zone_lowercase',
        'Report ' => 'report_prefix',
        'Report' => 'report',
        'Request cancelled.' => 'request_cancelled_sentence',
        'Request cancelled' => 'request_cancelled',
        'Request declined' => 'request_declined',
        'Request declined.' => 'request_declined_sentence',
        'Review ' => 'review_prefix',
        'Review' => 'review',
        'SE Ankeny entrance beside the covered picnic tables' => 'se_ankeny_entrance_beside_the_covered_picnic_tables',
        'SE Ankeny entrance, beside the covered picnic tables' => 'se_ankeny_entrance_with_comma_beside_the_covered_picnic_tables',
        'seasonal tick activity' => 'seasonal_tick_activity_lowercase',
        'Seasonal tick activity' => 'seasonal_tick_activity',
        'Small pets' => 'small_pets',
        'small pets' => 'small_pets_lowercase',
        'this week' => 'this_week_lowercase',
        'This week' => 'this_week',
        'usually quiet' => 'usually_quiet_lowercase',
        'Usually quiet' => 'usually_quiet',
        'veterinary clinics' => 'veterinary_clinics_lowercase',
        'Veterinary clinics' => 'veterinary_clinics',
        'What happened?' => 'what_happened_question',
    ],
    'ui' => [
        'Brand' => 'brand_label',
        'PawCircle' => 'brand_name',
        'lab-result' => 'lab_result_hyphenated',
        'Lab result' => 'lab_result',
        'No categories available' => 'no_categories_available',
        'No categories available.' => 'no_categories_available_sentence',
        'No handover options.' => 'no_handover_options_sentence',
        'No handover options' => 'no_handover_options',
        'No managed pets.' => 'no_managed_pets_sentence',
        'No managed pets' => 'no_managed_pets',
        'No pets added yet.' => 'no_pets_added_yet_sentence',
        'No pets added yet' => 'no_pets_added_yet',
        'No species options' => 'no_species_options',
        'No species options.' => 'no_species_options_sentence',
        'No tags' => 'no_tags',
        'No tags.' => 'no_tags_sentence',
        'Required' => 'required',
        'required' => 'required_lowercase',
        'Seller' => 'seller',
        'Seller:' => 'seller_label',
        'What happened' => 'what_happened',
        'What happened?' => 'what_happened_question',
    ],
];

/** @var array<string, array<string, array<string, string>>> $translations */
$translations = [];

foreach ($catalogues as $catalogue) {
    foreach ($locales as $locale) {
        $path = "{$root}/lang/{$locale}/{$catalogue}.php";
        $values = require $path;

        if (! is_array($values)) {
            throw new RuntimeException("Translation catalogue [{$path}] must return an array.");
        }

        /** @var array<string, string> $values */
        $translations[$catalogue][$locale] = $values;
    }

    assertLocaleParity($catalogue, $translations[$catalogue]);
}

/** @var array<string, array<string, string>> $mappings */
$mappings = [];
$catalogueKeyCount = 0;

foreach ($catalogues as $catalogue) {
    foreach ($translations[$catalogue]['en'] as $oldKey => $englishValue) {
        if (preg_match('/_[0-9a-f]{10}$/', $oldKey) !== 1) {
            continue;
        }

        $newKey = $overrides[$catalogue][$englishValue]
            ?? ReadableTranslationKey::fromText($englishValue);

        if (
            preg_match('/^[\pL\pN]+(?:_[\pL\pN]+)*$/u', $newKey) !== 1
            || mb_strtolower($newKey) !== $newKey
            || preg_match('/_[0-9a-f]{10}$/', $newKey) === 1
        ) {
            throw new RuntimeException("Unreadable migration target [{$catalogue}.{$newKey}].");
        }

        $mappings[$catalogue][$oldKey] = $newKey;
        $catalogueKeyCount++;
    }

    assertUniqueTargets(
        $catalogue,
        $translations[$catalogue],
        $mappings[$catalogue] ?? [],
    );
}

$sourceFiles = sourceFiles($root);
$sourceDigestReferences = digestReferences($sourceFiles);

if ($check) {
    if ($catalogueKeyCount > 0 || $sourceDigestReferences > 0) {
        fwrite(
            STDERR,
            "Readable translation-key migration required: {$catalogueKeyCount} catalogue keys "
            ."and {$sourceDigestReferences} source references remain.\n",
        );
        exit(1);
    }

    fwrite(STDOUT, "Readable translation-key migration is clean.\n");
    exit(0);
}

/** @var array<string, string> $bareMapping */
$bareMapping = [];

foreach ($mappings as $catalogue => $mapping) {
    foreach ($mapping as $oldKey => $newKey) {
        if (isset($bareMapping[$oldKey]) && $bareMapping[$oldKey] !== $newKey) {
            throw new RuntimeException(
                "Translation key [{$oldKey}] has conflicting migration targets across catalogues.",
            );
        }

        $bareMapping[$oldKey] = $newKey;
    }
}

$referenceCount = 0;
$changedFiles = 0;

foreach ($sourceFiles as $path) {
    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException("Unable to read [{$path}].");
    }

    if (preg_match('/_[0-9a-f]{10}\b/', $contents) !== 1) {
        continue;
    }

    $updated = str_replace(
        array_keys($bareMapping),
        array_values($bareMapping),
        $contents,
        $fileReferenceCount,
    );

    if ($updated === $contents) {
        continue;
    }

    if (file_put_contents($path, $updated) === false) {
        throw new RuntimeException("Unable to write [{$path}].");
    }

    $referenceCount += $fileReferenceCount;
    $changedFiles++;
}

foreach ($catalogues as $catalogue) {
    foreach ($locales as $locale) {
        $rewritten = [];

        foreach ($translations[$catalogue][$locale] as $oldKey => $value) {
            $newKey = $mappings[$catalogue][$oldKey] ?? $oldKey;

            if (isset($rewritten[$newKey]) && $rewritten[$newKey] !== $value) {
                throw new RuntimeException(
                    "Migration would merge different {$locale}.{$catalogue} values into [{$newKey}].",
                );
            }

            $rewritten[$newKey] = $value;
        }

        ksort($rewritten);
        writeCatalogue("{$root}/lang/{$locale}/{$catalogue}.php", $rewritten);
    }
}

$remainingCatalogueKeys = 0;

foreach ($catalogues as $catalogue) {
    foreach ($locales as $locale) {
        /** @var array<string, string> $rewritten */
        $rewritten = require "{$root}/lang/{$locale}/{$catalogue}.php";
        $remainingCatalogueKeys += count(array_filter(
            array_keys($rewritten),
            static fn (string $key): bool => preg_match('/_[0-9a-f]{10}$/', $key) === 1,
        ));
    }
}

$remainingReferences = digestReferences($sourceFiles);

if ($remainingCatalogueKeys > 0 || $remainingReferences > 0) {
    throw new RuntimeException(
        "Migration left {$remainingCatalogueKeys} catalogue keys and {$remainingReferences} source references.",
    );
}

fwrite(
    STDOUT,
    "Renamed {$catalogueKeyCount} English catalogue keys, preserved EN/LT/RU values, "
    ."and updated {$referenceCount} references across {$changedFiles} files.\n",
);

/**
 * @param  array<string, array<string, string>>  $localeCatalogues
 */
function assertLocaleParity(string $catalogue, array $localeCatalogues): void
{
    $englishKeys = array_keys($localeCatalogues['en']);
    sort($englishKeys);

    foreach (['lt', 'ru'] as $locale) {
        $localeKeys = array_keys($localeCatalogues[$locale]);
        sort($localeKeys);

        if ($localeKeys !== $englishKeys) {
            throw new RuntimeException(
                "Locale key parity failed for [{$locale}.{$catalogue}] before migration.",
            );
        }
    }
}

/**
 * @param  array<string, array<string, string>>  $localeCatalogues
 * @param  array<string, string>  $mapping
 */
function assertUniqueTargets(
    string $catalogue,
    array $localeCatalogues,
    array $mapping,
): void {
    /** @var array<string, array{old_key: string, values: array<string, string>}> $owners */
    $owners = [];

    foreach ($localeCatalogues['en'] as $oldKey => $_value) {
        $target = $mapping[$oldKey] ?? $oldKey;
        $values = [];

        foreach (['en', 'lt', 'ru'] as $locale) {
            $values[$locale] = $localeCatalogues[$locale][$oldKey];
        }

        if (isset($owners[$target]) && $owners[$target]['values'] !== $values) {
            throw new RuntimeException(
                "Readable key collision [{$catalogue}.{$target}] between "
                ."[{$owners[$target]['old_key']}] and [{$oldKey}]. "
                .'Add a deliberate semantic override.',
            );
        }

        $owners[$target] = ['old_key' => $oldKey, 'values' => $values];
    }
}

/** @return list<string> */
function sourceFiles(string $root): array
{
    $directories = [
        'app',
        'bootstrap',
        'config',
        'database',
        'docs',
        'resources',
        'routes',
        'scripts',
        'tests',
    ];
    $extensions = ['css', 'js', 'json', 'md', 'mjs', 'php', 'scss', 'xml', 'yaml', 'yml'];
    $paths = [];

    foreach ($directories as $directory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator("{$root}/{$directory}", FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (
                $file instanceof SplFileInfo
                && $file->isFile()
                && in_array(strtolower($file->getExtension()), $extensions, true)
                && ! str_starts_with($file->getPathname(), "{$root}/lang/")
            ) {
                $paths[] = $file->getPathname();
            }
        }
    }

    foreach (['CHANGELOG.md', 'README.md', 'composer.json', 'package.json'] as $rootFile) {
        if (is_file("{$root}/{$rootFile}")) {
            $paths[] = "{$root}/{$rootFile}";
        }
    }

    sort($paths);

    return array_values(array_unique($paths));
}

/** @param list<string> $paths */
function digestReferences(array $paths): int
{
    $count = 0;

    foreach ($paths as $path) {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read [{$path}].");
        }

        $qualifiedMatches = [];
        $bareMatches = [];
        preg_match_all(
            '/\b(?:messages|ui)\.[\pL\pN_]*_[0-9a-f]{10}\b/u',
            $contents,
            $qualifiedMatches,
        );
        preg_match_all(
            '/([\'\"])[\pL\pN_]*_[0-9a-f]{10}\1/u',
            $contents,
            $bareMatches,
        );

        $count += count($qualifiedMatches[0]) + count($bareMatches[0]);
    }

    return $count;
}

/** @param array<string, string> $catalogue */
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
        throw new RuntimeException("Unable to write [{$path}].");
    }
}
