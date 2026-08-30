<?php

declare(strict_types=1);

$projectRoot = realpath(dirname(__DIR__));

if ($projectRoot === false) {
    throw new RuntimeException('Unable to resolve the project root.');
}

$viewsRoot = $projectRoot.'/resources/views';
$scssRoot = $projectRoot.'/resources/scss';
$vendorIconsRoot = $projectRoot.'/vendor/mallardduck/blade-lucide-icons/resources/svg/icons';
$canonicalPrimitive = 'components/ui-icon.blade.php';
$inlineSvgAllowlist = ['components/medical-weight-chart.blade.php'];
$pictogramPattern = '/[←→↗↻×✓✔✕✖⚠★☆♥♡]/u';
$foreignIconPattern = '/(?:heroicon|font-awesome|material-symbol|\bmdi-|\bbi-)/i';
$verbose = in_array('--verbose', $argv, true);
$ratchets = [
    'direct_lucide_instances' => 0,
    'dynamic_lucide_debt' => 0,
    'legacy_icon_class_instances' => 0,
    'legacy_style_selector_instances' => 0,
    'canonical_icon_instances_minimum' => 828,
];

$bladeFiles = [];
$directLucideFiles = [];
$directLucideNames = [];
$staticCanonicalIconNames = [];
$inlineSvgFiles = [];
$foreignIconFiles = [];
$pictogramFiles = [];
$directLucideInstances = 0;
$dynamicLucideDebt = 0;
$canonicalIconInstances = 0;
$legacyIconClassInstances = 0;
$legacyStyleSelectorInstances = 0;
$nativeInteractiveElements = 0;
$textOnlyInteractiveCandidates = 0;
$textOnlyInteractiveFiles = [];
$textOnlyInteractiveDetails = [];

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsRoot, FilesystemIterator::SKIP_DOTS),
);

foreach ($iterator as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $absolutePath = $file->getPathname();
    $relativePath = str_replace($viewsRoot.'/', '', $absolutePath);
    $source = file_get_contents($absolutePath);

    if ($source === false) {
        throw new RuntimeException('Unable to read '.$relativePath.'.');
    }

    $bladeFiles[] = $relativePath;
    $directCount = preg_match_all('/<x-lucide-([a-z0-9-]+)\b/', $source, $directMatches);
    $directLucideInstances += $directCount;

    if ($directCount > 0) {
        $directLucideFiles[] = $relativePath;

        foreach ($directMatches[1] as $name) {
            $directLucideNames[$name] = true;
        }
    }

    if ($relativePath !== $canonicalPrimitive) {
        $dynamicLucideDebt += substr_count($source, 'lucide-');
    }

    $canonicalIconInstances += preg_match_all('/<x-ui-icon\b/', $source);
    preg_match_all('/<x-ui-icon\b[^>]*\sname="([a-z0-9-]+)"[^>]*>/s', $source, $staticIconMatches);

    foreach ($staticIconMatches[1] as $name) {
        $staticCanonicalIconNames[$name] = true;
    }
    preg_match_all('/class="([^"]*)"/', $source, $classMatches);

    foreach ($classMatches[1] as $classValue) {
        foreach (preg_split('/\s+/', trim($classValue)) ?: [] as $className) {
            if (in_array($className, ['icon', 'icon--xs', 'icon--sm'], true)) {
                $legacyIconClassInstances++;
            }
        }
    }

    $interactiveCount = preg_match_all(
        '/<(a|button)\b[^>]*>.*?<\/\1>/is',
        $source,
        $interactiveMatches,
        PREG_SET_ORDER | PREG_OFFSET_CAPTURE,
    );
    $nativeInteractiveElements += $interactiveCount;

    foreach ($interactiveMatches as $interactiveMatch) {
        $interactiveMarkup = $interactiveMatch[0][0];

        if (preg_match('/<x-(?:lucide-[a-z0-9-]+|ui-icon)\b|<svg\b|<img\b/i', $interactiveMarkup) === 1) {
            continue;
        }

        $textOnlyInteractiveCandidates++;
        $textOnlyInteractiveFiles[$relativePath] = ($textOnlyInteractiveFiles[$relativePath] ?? 0) + 1;

        if ($verbose) {
            $offset = $interactiveMatch[0][1];
            $plainText = trim((string) preg_replace('/\s+/', ' ', strip_tags($interactiveMarkup)));
            $textOnlyInteractiveDetails[] = [
                'file' => $relativePath,
                'line' => substr_count(substr($source, 0, $offset), "\n") + 1,
                'element' => strtolower($interactiveMatch[1][0]),
                'text' => substr($plainText, 0, 240),
            ];
        }
    }

    if (preg_match('/<svg\b/i', $source) === 1) {
        $inlineSvgFiles[] = $relativePath;
    }

    if (preg_match($foreignIconPattern, $source) === 1) {
        $foreignIconFiles[] = $relativePath;
    }

    if (preg_match($pictogramPattern, $source) === 1) {
        $pictogramFiles[] = $relativePath;
    }
}

sort($bladeFiles);
sort($directLucideFiles);
ksort($directLucideNames);
ksort($staticCanonicalIconNames);
sort($inlineSvgFiles);
sort($foreignIconFiles);
sort($pictogramFiles);
ksort($textOnlyInteractiveFiles);

$missingDirectLucideIcons = [];
$missingStaticCanonicalIcons = [];

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

    $legacyStyleSelectorInstances += preg_match_all(
        '/\.icon(?=--(?:xs|sm)\b|[\s,{:\[])/',
        $source,
    );
}

foreach (array_keys($directLucideNames) as $name) {
    if (! is_file($vendorIconsRoot.'/'.$name.'.svg')) {
        $missingDirectLucideIcons[] = $name;
    }
}

foreach (array_keys($staticCanonicalIconNames) as $name) {
    if (! is_file($vendorIconsRoot.'/'.$name.'.svg')) {
        $missingStaticCanonicalIcons[] = $name;
    }
}

$unexpectedInlineSvgFiles = array_values(array_diff($inlineSvgFiles, $inlineSvgAllowlist));
$violations = [];

if ($directLucideInstances > $ratchets['direct_lucide_instances']) {
    $violations[] = 'Direct Lucide usage exceeded its migration ratchet.';
}

if ($dynamicLucideDebt > $ratchets['dynamic_lucide_debt']) {
    $violations[] = 'Dynamic Lucide usage outside the canonical primitive increased.';
}

if ($legacyIconClassInstances > $ratchets['legacy_icon_class_instances']) {
    $violations[] = 'Legacy icon class usage increased.';
}

if ($legacyStyleSelectorInstances > $ratchets['legacy_style_selector_instances']) {
    $violations[] = 'Legacy icon SCSS selector usage increased.';
}

if ($canonicalIconInstances < $ratchets['canonical_icon_instances_minimum']) {
    $violations[] = 'Canonical icon usage fell below the verified migration floor.';
}

if ($unexpectedInlineSvgFiles !== []) {
    $violations[] = 'Unexpected inline SVG markup was found.';
}

if ($foreignIconFiles !== []) {
    $violations[] = 'A non-Lucide icon system was found.';
}

if ($pictogramFiles !== []) {
    $violations[] = 'Raw pictographic symbols were found in Blade.';
}

if ($missingDirectLucideIcons !== []) {
    $violations[] = 'One or more direct Lucide component names do not exist in the installed package.';
}

if ($missingStaticCanonicalIcons !== []) {
    $violations[] = 'One or more canonical static icon names do not exist in the installed package.';
}

$report = [
    'blade_files' => count($bladeFiles),
    'direct_lucide_instances' => $directLucideInstances,
    'direct_lucide_files' => count($directLucideFiles),
    'unique_direct_lucide_names' => count($directLucideNames),
    'dynamic_lucide_debt' => $dynamicLucideDebt,
    'canonical_icon_instances' => $canonicalIconInstances,
    'unique_static_canonical_icon_names' => count($staticCanonicalIconNames),
    'legacy_icon_class_instances' => $legacyIconClassInstances,
    'legacy_style_selector_instances' => $legacyStyleSelectorInstances,
    'native_interactive_elements' => $nativeInteractiveElements,
    'text_only_interactive_candidates' => $textOnlyInteractiveCandidates,
    'text_only_interactive_files' => count($textOnlyInteractiveFiles),
    'text_only_interactive_by_file' => $textOnlyInteractiveFiles,
    'inline_svg_files' => $inlineSvgFiles,
    'inline_svg_allowlist' => $inlineSvgAllowlist,
    'foreign_icon_files' => $foreignIconFiles,
    'pictogram_files' => $pictogramFiles,
    'missing_direct_lucide_icons' => $missingDirectLucideIcons,
    'missing_static_canonical_icons' => $missingStaticCanonicalIcons,
    'ratchets' => $ratchets,
    'violations' => $violations,
];

if ($verbose) {
    $report['text_only_interactive_details'] = $textOnlyInteractiveDetails;
}

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL;

if (in_array('--check', $argv, true) && $violations !== []) {
    exit(1);
}
