<?php

declare(strict_types=1);

const FORUM_PRIMARY_PROMPT_TIMESTAMP = 1785397895;
const FORUM_EXTENSION_PROMPT_TIMESTAMP = 1785445633;
const PET_PROFILE_PROMPT_TIMESTAMP = 1785514046;
const SOCIAL_RELATIONSHIPS_PROMPT_TIMESTAMP = 1785521058;
const CONTENT_FEED_PROMPT_TIMESTAMP = 1785527132;
const COMMUNICATION_PROMPT_TIMESTAMP = 1785532239;
const COMMUNITY_PROMPT_TIMESTAMP = 1785538113;

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

    if (in_array($timestamp, [
        FORUM_PRIMARY_PROMPT_TIMESTAMP,
        FORUM_EXTENSION_PROMPT_TIMESTAMP,
        PET_PROFILE_PROMPT_TIMESTAMP,
        SOCIAL_RELATIONSHIPS_PROMPT_TIMESTAMP,
        CONTENT_FEED_PROMPT_TIMESTAMP,
        COMMUNICATION_PROMPT_TIMESTAMP,
        COMMUNITY_PROMPT_TIMESTAMP,
    ], true)) {
        $prompts[$timestamp] = (string) $entry['text'];
    }
}

fclose($handle);

foreach ([
    FORUM_PRIMARY_PROMPT_TIMESTAMP,
    FORUM_EXTENSION_PROMPT_TIMESTAMP,
    PET_PROFILE_PROMPT_TIMESTAMP,
    SOCIAL_RELATIONSHIPS_PROMPT_TIMESTAMP,
    CONTENT_FEED_PROMPT_TIMESTAMP,
    COMMUNICATION_PROMPT_TIMESTAMP,
    COMMUNITY_PROMPT_TIMESTAMP,
] as $timestamp) {
    if (! isset($prompts[$timestamp])) {
        fwrite(STDERR, "Required forum prompt entry {$timestamp} is missing.\n");
        exit(1);
    }
}

$primary = $prompts[FORUM_PRIMARY_PROMPT_TIMESTAMP];
$extension = $prompts[FORUM_EXTENSION_PROMPT_TIMESTAMP];
$petProfile = $prompts[PET_PROFILE_PROMPT_TIMESTAMP];
$socialRelationships = $prompts[SOCIAL_RELATIONSHIPS_PROMPT_TIMESTAMP];
$contentFeed = $prompts[CONTENT_FEED_PROMPT_TIMESTAMP];
$communication = $prompts[COMMUNICATION_PROMPT_TIMESTAMP];
$community = $prompts[COMMUNITY_PROMPT_TIMESTAMP];
$forumPayload = $primary."\n\n".$extension;
$forumChecksum = hash('sha256', $forumPayload);
$petProfileChecksum = hash('sha256', $petProfile);
$petProfileMasterPayload = $forumPayload."\n\n".$petProfile;
$petProfileMasterChecksum = hash('sha256', $petProfileMasterPayload);
$socialRelationshipsChecksum = hash('sha256', $socialRelationships);
$masterPayload = $petProfileMasterPayload."\n\n".$socialRelationships;
$masterChecksum = hash('sha256', $masterPayload);
$contentFeedChecksum = hash('sha256', $contentFeed);
$latestMasterPayload = $masterPayload."\n\n".$contentFeed;
$latestMasterChecksum = hash('sha256', $latestMasterPayload);
$communicationChecksum = hash('sha256', $communication);
$completeMasterPayload = $latestMasterPayload."\n\n".$communication;
$completeMasterChecksum = hash('sha256', $completeMasterPayload);
$communityChecksum = hash('sha256', $community);
$expandedMasterPayload = $completeMasterPayload."\n\n".$community;
$expandedMasterChecksum = hash('sha256', $expandedMasterPayload);
$document = <<<'MARKDOWN'
# Forum Source Prompt

This document is immutable implementation input. It preserves the exact
first-party forum prompt and its additive master extension as recovered from
the local Codex history. Future requirement changes must be appended as dated
revisions; earlier source text must never be silently edited or removed.

MARKDOWN;
$document .= "\n- Primary source timestamp: `".FORUM_PRIMARY_PROMPT_TIMESTAMP."`\n";
$document .= '- Additive extension timestamp: `'.FORUM_EXTENSION_PROMPT_TIMESTAMP."`\n";
$document .= "- Combined raw payload SHA-256: `{$forumChecksum}`\n";
$document .= "- Checksum payload: exact primary prompt, two LF characters, exact extension prompt\n\n";
$document .= "## Source Part A: Original Forum Specification\n\n";
$document .= "<forum-source-primary>\n{$primary}\n</forum-source-primary>\n\n";
$document .= "## Source Part B: Additive Master Extension\n\n";
$document .= "<forum-source-extension>\n{$extension}\n</forum-source-extension>\n";
$legacyDocument = $document;
$document .= "\n## Revision 2026-07-31: Pet Profile And Full Lifecycle\n\n";
$document .= "This dated revision is additive. Parts A and B above remain unchanged and\n";
$document .= "mandatory. The revision payload below is preserved verbatim from local Codex\n";
$document .= "history and is part of the indivisible master specification.\n\n";
$document .= '- Revision source timestamp: `'.PET_PROFILE_PROMPT_TIMESTAMP."`\n";
$document .= "- Revision raw payload SHA-256: `{$petProfileChecksum}`\n";
$document .= "- Master raw payload SHA-256: `{$petProfileMasterChecksum}`\n";
$document .= "- Master checksum payload: Parts A and B checksum payload, two LF characters, exact revision payload\n\n";
$document .= "<pet-profile-source-revision>\n{$petProfile}\n</pet-profile-source-revision>\n";
$petProfileDocument = $document;
$document .= "\n## Revision 2026-07-31: Social Relationships And Safe Introductions\n\n";
$document .= "This dated revision is additive. Parts A and B and the pet-profile revision\n";
$document .= "above remain unchanged and mandatory. The revision payload below is preserved\n";
$document .= "verbatim from local Codex history and is part of the indivisible master\n";
$document .= "specification.\n\n";
$document .= '- Social revision source timestamp: `'.SOCIAL_RELATIONSHIPS_PROMPT_TIMESTAMP."`\n";
$document .= "- Social revision raw payload SHA-256: `{$socialRelationshipsChecksum}`\n";
$document .= "- Current master raw payload SHA-256: `{$masterChecksum}`\n";
$document .= "- Current master checksum payload: prior master checksum payload, two LF characters, exact social revision payload\n\n";
$document .= "<social-relationships-source-revision>\n{$socialRelationships}\n</social-relationships-source-revision>\n";
$socialRelationshipsDocument = $document;
$document .= "\n## Revision 2026-07-31: Content Feed And Distribution\n\n";
$document .= "This dated revision is additive. All prior source parts and revisions remain\n";
$document .= "unchanged and mandatory. The revision payload below is preserved verbatim from\n";
$document .= "local Codex history and is part of the indivisible master specification.\n\n";
$document .= '- Content revision source timestamp: `'.CONTENT_FEED_PROMPT_TIMESTAMP."`\n";
$document .= "- Content revision raw payload SHA-256: `{$contentFeedChecksum}`\n";
$document .= "- Latest master raw payload SHA-256: `{$latestMasterChecksum}`\n";
$document .= "- Latest master checksum payload: prior master checksum payload, two LF characters, exact content revision payload\n\n";
$document .= "<content-feed-source-revision>\n{$contentFeed}\n</content-feed-source-revision>\n";
$contentFeedDocument = $document;
$document .= "\n## Source Part F: Safe Communication Revision\n\n";
$document .= '- Source timestamp: `'.COMMUNICATION_PROMPT_TIMESTAMP."`\n";
$document .= "- Communication revision raw payload SHA-256: `{$communicationChecksum}`\n";
$document .= "- Complete master raw payload SHA-256: `{$completeMasterChecksum}`\n";
$document .= "- Checksum payload: prior complete master, two LF characters, exact communication revision\n\n";
$document .= "<communication-source-revision>\n{$communication}\n</communication-source-revision>\n";
$communicationDocument = $document;
$document .= "\n## Source Part G: Communities And Full Lifecycle Revision\n\n";
$document .= '- Source timestamp: `'.COMMUNITY_PROMPT_TIMESTAMP."`\n";
$document .= "- Community revision raw payload SHA-256: `{$communityChecksum}`\n";
$document .= "- Expanded master raw payload SHA-256: `{$expandedMasterChecksum}`\n";
$document .= "- Checksum payload: prior complete master, two LF characters, exact community revision\n\n";
$document .= "<community-source-revision>\n{$community}\n</community-source-revision>\n";

if (is_file($target)) {
    $existing = file_get_contents($target);

    if ($existing === $document) {
        fwrite(STDOUT, "Master source prompt is unchanged ({$expandedMasterChecksum}).\n");
        exit(0);
    }

    if ($existing !== $legacyDocument
        && $existing !== $petProfileDocument
        && $existing !== $socialRelationshipsDocument
        && $existing !== $contentFeedDocument
        && $existing !== $communicationDocument
    ) {
        fwrite(STDERR, "Refusing to replace or rewrite preserved source-prompt content.\n");
        exit(1);
    }

    if ($checkOnly) {
        fwrite(STDERR, "A dated source revision has not been appended yet.\n");
        exit(1);
    }
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

fwrite(STDOUT, "Preserved master source prompt ({$expandedMasterChecksum}).\n");
