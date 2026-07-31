<?php

declare(strict_types=1);

return [
    'validation' => [
        'summary' => 'Please review the highlighted fields.',
        'media_description_required' => 'Describe the uploaded media for people who cannot see it.',
        'video_transcript_required' => 'Provide a text transcript or equivalent description for the video.',
        'caption_video_required' => 'A caption file can only be attached with a video.',
        'caption_locale_required' => 'Choose the language used by the caption file.',
        'caption_file_required' => 'Choose a caption file before setting its language.',
        'invalid_webvtt' => 'The caption file must be a valid WebVTT file beginning with WEBVTT.',
        'media_storage_failed' => 'The media file could not be stored safely. Please try again.',
    ],
    'media' => [
        'description' => 'Media description',
        'description_help' => 'Describe the meaningful visual content without including private information.',
        'video_transcript' => 'Video transcript or equivalent text',
        'video_transcript_help' => 'Include speech, meaningful sounds, and visual information needed to understand the video.',
        'caption_file' => 'WebVTT caption file',
        'caption_file_help' => 'Optional timed captions in WebVTT format, up to 256 KB.',
        'caption_locale' => 'Caption language',
        'captions_label' => ':locale captions',
        'transcript_label' => 'Video transcript',
        'legacy_description' => 'Forum media shared by the topic author.',
    ],
    'tables' => [
        'reports' => 'Submitted forum reports',
        'moderation_cases' => 'Open moderation cases',
        'appeals' => 'Moderation appeals',
        'categories' => 'Forum category administration',
        'guides' => 'Knowledge guide administration',
        'taxonomy_imports' => 'Animal taxonomy imports',
        'professional_verifications' => 'Professional verification reviews',
    ],
];
