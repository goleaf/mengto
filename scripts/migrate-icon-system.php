<?php

declare(strict_types=1);

$projectRoot = realpath(dirname(__DIR__));

if ($projectRoot === false) {
    throw new RuntimeException('Unable to resolve the project root.');
}

$viewsRoot = $projectRoot.'/resources/views';
$scssRoot = $projectRoot.'/resources/scss';
$writeChanges = in_array('--write', $argv, true);
$sizeByLegacyToken = [
    'icon--xs' => 'xs',
    'size-3' => 'xs',
    'size-3.5' => 'sm',
    'icon--sm' => 'sm',
    'size-4' => 'sm',
    'size-5' => 'lg',
    'size-6' => 'xl',
    'size-7' => 'xl',
    'size-8' => '2xl',
    'size-9' => '3xl',
    'size-10' => '3xl',
    'size-12' => '4xl',
    'size-20' => 'display',
    'size-24' => 'hero',
];
$changedFiles = [];
$convertedInstances = 0;
$convertedDynamicInstances = 0;
$changedStyleFiles = [];
$convertedStyleSelectors = 0;
$dynamicLucideConsumers = [];

$normalizeIconAttributes = static function (string $attributes) use ($sizeByLegacyToken): array {
    $size = null;

    if (preg_match('/\sclass="([^"]*)"/s', $attributes, $classMatch) === 1) {
        $classValue = $classMatch[1];

        foreach ($sizeByLegacyToken as $legacyToken => $mappedSize) {
            if (preg_match('/(?:^|\s)'.preg_quote($legacyToken, '/').'(?=\s|$)/', $classValue) === 1) {
                $size ??= $mappedSize;
            }
        }

        $classValue = preg_replace(
            '/(?:^|\s)(?:icon|icon--xs|icon--sm|fill-current|size-(?:3|3\.5|4|5|6|7|8|9|10|12|20|24))(?=\s|$)/',
            ' ',
            $classValue,
        );
        $classValue = trim((string) preg_replace('/\s+/', ' ', (string) $classValue));
        $attributes = str_replace($classMatch[0], '', $attributes);

        if ($classValue !== '') {
            $attributes .= ' class="'.$classValue.'"';
        }
    }

    if (preg_match('/\saria-label="([^"]+)"/', $attributes, $labelMatch) === 1) {
        $attributes = str_replace($labelMatch[0], '', $attributes);
        $attributes .= ' label="'.$labelMatch[1].'"';
    }

    $attributes = preg_replace('/\saria-hidden=(?:"true"|\'true\')/', '', $attributes);

    return [
        trim((string) preg_replace('/\s+/', ' ', (string) $attributes)),
        $size,
    ];
};

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsRoot, FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $source = file_get_contents($file->getPathname());

    if ($source === false) {
        throw new RuntimeException('Unable to read '.$file->getPathname().'.');
    }

    $expectedInstances = preg_match_all('/<x-lucide-[a-z0-9-]+\b/', $source);
    $fileConversions = 0;
    $migrated = preg_replace_callback(
        '/<x-lucide-([a-z0-9-]+)\b(.*?)\/>/s',
        static function (array $matches) use ($normalizeIconAttributes, &$fileConversions): string {
            [$attributes, $size] = $normalizeIconAttributes($matches[2]);
            $parts = ['<x-ui-icon name="'.$matches[1].'"'];

            if ($size !== null) {
                $parts[] = 'size="'.$size.'"';
            }

            if ($attributes !== '') {
                $parts[] = $attributes;
            }

            $fileConversions++;

            return implode(' ', $parts).' />';
        },
        $source,
    );

    if (! is_string($migrated) || $fileConversions !== $expectedInstances) {
        throw new RuntimeException(sprintf(
            'Refusing partial direct migration for %s: expected %d, converted %d.',
            $file->getPathname(),
            $expectedInstances,
            $fileConversions,
        ));
    }

    $expectedDynamicInstances = 0;

    if (! str_ends_with($file->getPathname(), '/components/ui-icon.blade.php')) {
        preg_match_all('/<x-dynamic-component\b.*?\/>/s', $migrated, $dynamicTags);

        foreach ($dynamicTags[0] as $dynamicTag) {
            if (str_contains($dynamicTag, 'lucide-')) {
                $expectedDynamicInstances++;
            }
        }
    }

    $fileDynamicConversions = 0;
    $migrated = preg_replace_callback(
        '/<x-dynamic-component\b(.*?)\/>/s',
        static function (array $matches) use (
            $file,
            $normalizeIconAttributes,
            &$fileDynamicConversions,
        ): string {
            if (str_ends_with($file->getPathname(), '/components/ui-icon.blade.php')) {
                return $matches[0];
            }

            $attributes = $matches[1];

            if (! str_contains($attributes, 'lucide-')) {
                return $matches[0];
            }

            if (preg_match('/\s:component="([^"]+)"/s', $attributes, $componentMatch) !== 1) {
                throw new RuntimeException('Unable to parse dynamic Lucide component in '.$file->getPathname().'.');
            }

            $iconExpression = $componentMatch[1];

            if (preg_match("/^'lucide-'\\.(.+)$/s", $iconExpression, $prefixMatch) === 1) {
                $iconExpression = $prefixMatch[1];
            }

            $iconExpression = preg_replace(
                "/'lucide-([a-z0-9-]+)'/",
                "'$1'",
                $iconExpression,
            );

            if (! is_string($iconExpression) || str_contains($iconExpression, 'lucide-')) {
                throw new RuntimeException('Unable to normalize dynamic Lucide expression in '.$file->getPathname().'.');
            }

            $attributes = str_replace($componentMatch[0], ' :name="'.$iconExpression.'"', $attributes);
            [$attributes, $size] = $normalizeIconAttributes($attributes);
            $parts = ['<x-ui-icon'];

            if ($size !== null) {
                $parts[] = 'size="'.$size.'"';
            }

            if ($attributes !== '') {
                $parts[] = $attributes;
            }

            $fileDynamicConversions++;

            return implode(' ', $parts).' />';
        },
        $migrated,
    );

    if (! is_string($migrated) || $fileDynamicConversions !== $expectedDynamicInstances) {
        throw new RuntimeException(sprintf(
            'Refusing partial dynamic migration for %s: expected %d, converted %d.',
            $file->getPathname(),
            $expectedDynamicInstances,
            $fileDynamicConversions,
        ));
    }

    $convertedInstances += $fileConversions;
    $convertedDynamicInstances += $fileDynamicConversions;

    if ($expectedDynamicInstances > 0) {
        $dynamicLucideConsumers[] = str_replace($projectRoot.'/', '', $file->getPathname());
    }

    if ($fileConversions > 0 || $fileDynamicConversions > 0) {
        $changedFiles[] = str_replace($projectRoot.'/', '', $file->getPathname());

        if ($writeChanges && file_put_contents($file->getPathname(), $migrated) === false) {
            throw new RuntimeException('Unable to write '.$file->getPathname().'.');
        }
    }
}

sort($changedFiles);
sort($dynamicLucideConsumers);

$styleIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($scssRoot, FilesystemIterator::SKIP_DOTS),
);

foreach ($styleIterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'scss') {
        continue;
    }

    $source = file_get_contents($file->getPathname());

    if ($source === false) {
        throw new RuntimeException('Unable to read '.$file->getPathname().'.');
    }

    $migrated = preg_replace(
        '/\.icon(?=--(?:xs|sm)\b|[\s,{:\[])/',
        '.ui-icon',
        $source,
        -1,
        $fileConversions,
    );

    if (! is_string($migrated) || $fileConversions === 0) {
        continue;
    }

    $convertedStyleSelectors += $fileConversions;
    $changedStyleFiles[] = str_replace($projectRoot.'/', '', $file->getPathname());

    if ($writeChanges && file_put_contents($file->getPathname(), $migrated) === false) {
        throw new RuntimeException('Unable to write '.$file->getPathname().'.');
    }
}

sort($changedStyleFiles);

echo json_encode([
    'mode' => $writeChanges ? 'write' : 'check',
    'converted_instances' => $convertedInstances,
    'converted_dynamic_instances' => $convertedDynamicInstances,
    'changed_files' => count($changedFiles),
    'files' => $changedFiles,
    'converted_style_selectors' => $convertedStyleSelectors,
    'changed_style_files' => $changedStyleFiles,
    'dynamic_lucide_consumers' => $dynamicLucideConsumers,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;

if (! $writeChanges && (
    $convertedInstances > 0
    || $convertedDynamicInstances > 0
    || $convertedStyleSelectors > 0
    || $dynamicLucideConsumers !== []
)) {
    exit(1);
}
