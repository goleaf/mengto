<?php

declare(strict_types=1);

return [
    'page' => [
        'title' => 'Set up your PawCircle account',
        'eyebrow' => 'A private start',
        'description' => 'Confirm a few account choices before entering the community. Your progress is saved securely after each step.',
        'resume_note' => 'You can sign out and continue from this step later.',
        'logout' => 'Sign out',
    ],
    'progress' => [
        'label' => 'Onboarding progress',
        'step' => 'Step :current of :total',
    ],
    'steps' => [
        'introduction' => [
            'label' => 'Welcome',
            'title' => 'Start with control over your information',
            'body' => 'PawCircle keeps account, pet, care and social identities separate. Nothing in this setup publishes a pet, exact location or private care record.',
            'acknowledgement' => 'I understand that I can review and change these account choices later.',
            'continue' => 'Continue to preferences',
        ],
        'preferences' => [
            'label' => 'Preferences',
            'title' => 'Choose language and timezone',
            'body' => 'These settings control interface language and how dates and times are shown. They do not change stored event times.',
            'save' => 'Save and continue',
        ],
        'pet_relationship' => [
            'label' => 'Pet relationship',
            'title' => 'Connect a pet when it is right for you',
            'body' => 'You may create a private pet profile, find an existing profile and request access, or continue without a pet. Choosing not now does not remove this option later.',
            'create_or_find' => 'Create or find a pet profile',
            'managed_pet' => 'Continue with my managed pet',
            'access_requested' => 'Continue with my access request',
            'not_now' => 'Continue without a pet for now',
        ],
        'privacy_discovery' => [
            'label' => 'Privacy',
            'title' => 'Confirm discovery and contact choices',
            'body' => 'All options start off. Enable only the ways you want other members to find or contact your account.',
            'discoverable_label' => 'Show my account in member discovery',
            'discoverable_description' => 'Allows eligible members to find the public-safe part of your account profile.',
            'recommendable_label' => 'Include my account in recommendations',
            'recommendable_description' => 'Allows PawCircle to recommend your public-safe account profile to eligible members.',
            'messages_label' => 'Allow message requests',
            'messages_description' => 'Allows eligible members outside your existing relationships to request a conversation.',
            'save' => 'Finish account setup',
        ],
    ],
    'completion' => [
        'feedback' => 'Account setup is complete.',
    ],
    'states' => [
        'saving' => 'Saving securely…',
        'offline' => 'You are offline. Reconnect before saving this step.',
        'unsaved' => 'This step has unsaved changes.',
    ],
    'validation' => [
        'summary' => 'Review the highlighted information before continuing.',
        'acknowledgement' => 'Confirm that you understand these choices can be reviewed later.',
        'pet_choice' => 'Choose one of the available pet relationship options.',
        'pet_evidence' => 'We could not confirm that pet relationship for your account yet.',
    ],
    'errors' => [
        'state_unavailable' => 'Your saved account setup could not be found. Refresh the page and try again.',
        'stale_state' => 'This setup changed in another tab or request. Refresh before continuing.',
        'transition_conflict' => 'That setup step is not available yet.',
    ],
    'middleware' => [
        'incomplete_detail' => 'Complete account setup before accessing this resource.',
    ],
    'accessibility' => [
        'skip_to_content' => 'Skip to account setup',
    ],
];
