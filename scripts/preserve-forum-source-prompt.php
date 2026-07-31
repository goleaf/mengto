<?php

declare(strict_types=1);

const FORUM_PRIMARY_PROMPT_TIMESTAMP = 1785397895;
const FORUM_EXTENSION_PROMPT_TIMESTAMP = 1785445633;

$root = dirname(__DIR__);
$target = $root.'/docs/requirements/forum-source-prompt.md';
$history = getenv('CODEX_HISTORY_PATH') ?: (getenv('HOME').'/.codex/history.jsonl');
$checkOnly = in_array('--check', $argv, true);

if (! is_file($history)) {
    fwrite(STDERR, "Codex history was not found at {$history}.\n");
    exit(1);
}

$prompts = [];
$handle = fopen($history, 'rb');

if ($handle === false) {
    fwrite(STDERR, "Codex history could not be opened at {$history}.\n");
    exit(1);
}

while (($line = fgets($handle)) !== false) {
    $entry = json_decode($line, true);

    if (! is_array($entry) || ! isset($entry['ts'], $entry['text'])) {
        continue;
    }

    $timestamp = (int) $entry['ts'];

    if (in_array($timestamp, [FORUM_PRIMARY_PROMPT_TIMESTAMP, FORUM_EXTENSION_PROMPT_TIMESTAMP], true)) {
        $prompts[$timestamp] = (string) $entry['text'];
    }
}

fclose($handle);

foreach ([FORUM_PRIMARY_PROMPT_TIMESTAMP, FORUM_EXTENSION_PROMPT_TIMESTAMP] as $timestamp) {
    if (! isset($prompts[$timestamp])) {
        fwrite(STDERR, "Required forum prompt entry {$timestamp} is missing.\n");
        exit(1);
    }
}

$primary = $prompts[FORUM_PRIMARY_PROMPT_TIMESTAMP];
$extension = $prompts[FORUM_EXTENSION_PROMPT_TIMESTAMP];
$payload = $primary."\n\n".$extension;
$checksum = hash('sha256', $payload);
$document = <<<'MARKDOWN'
# Forum Source Prompt

This document is immutable implementation input. It preserves the exact
first-party forum prompt and its additive master extension as recovered from
the local Codex history. Future requirement changes must be appended as dated
revisions; earlier source text must never be silently edited or removed.

MARKDOWN;
$document .= "\n- Primary source timestamp: `".FORUM_PRIMARY_PROMPT_TIMESTAMP."`\n";
$document .= '- Additive extension timestamp: `'.FORUM_EXTENSION_PROMPT_TIMESTAMP."`\n";
$document .= "- Combined raw payload SHA-256: `{$checksum}`\n";
$document .= "- Checksum payload: exact primary prompt, two LF characters, exact extension prompt\n\n";
$document .= "## Source Part A: Original Forum Specification\n\n";
$document .= "<forum-source-primary>\n{$primary}\n</forum-source-primary>\n\n";
$document .= "## Source Part B: Additive Master Extension\n\n";
$document .= "<forum-source-extension>\n{$extension}\n</forum-source-extension>\n";

if (is_file($target)) {
    $existing = file_get_contents($target);

    if ($existing === $document) {
        fwrite(STDOUT, "Forum source prompt is unchanged ({$checksum}).\n");
        exit(0);
    }

    fwrite(STDERR, "Refusing to overwrite the immutable forum source prompt.\n");
    exit(1);
}

if ($checkOnly) {
    fwrite(STDERR, "Forum source prompt has not been preserved yet.\n");
    exit(1);
}

$directory = dirname($target);

if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
    fwrite(STDERR, "Unable to create {$directory}.\n");
    exit(1);
}

if (file_put_contents($target, $document, LOCK_EX) === false) {
    fwrite(STDERR, "Unable to write {$target}.\n");
    exit(1);
}

fwrite(STDOUT, "Preserved forum source prompt ({$checksum}).\n");
