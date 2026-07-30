<?php

namespace App\Services;

use InvalidArgumentException;

final class ComposerCatalog
{
    /**
     * @param  array<string, mixed>  $owner
     * @param  array<string, mixed>  $pet
     * @param  array<string, mixed>  $context
     * @param  array<string, string>  $visibilityOptions
     * @return array<string, mixed>
     */
    public function form(
        string $kind,
        array $owner,
        array $pet,
        array $context = [],
        array $visibilityOptions = [],
    ): array {
        return match ($kind) {
            'post' => $this->post($context),
            'post-edit' => $this->post($context, true),
            'delete-post' => $this->deletePost($context),
            'group' => $this->group(),
            'meetup' => $this->meetup($context),
            'walk' => $this->walk(),
            'pet' => $this->pet(),
            'place' => $this->place(),
            'place-correction' => $this->placeCorrection($context),
            'place-warning' => $this->placeWarning($context),
            'place-review' => $this->placeReview($context),
            'place-question' => $this->placeQuestion($context),
            'place-claim' => $this->placeClaim($context),
            'message' => $this->message(),
            'profile' => $this->profile($owner),
            'pet-profile' => $this->petProfile($pet),
            'profile-privacy' => $this->profilePrivacy($context, $visibilityOptions),
            'pet-privacy' => $this->petPrivacy($pet, $context, $visibilityOptions),
            'report-profile' => $this->profileReport($context),
            'report-post' => $this->postReport($context),
            'report-group' => $this->groupReport($context),
            'report-event' => $this->eventReport($context),
            'report-place' => $this->placeReport($context),
            default => throw new InvalidArgumentException("Unknown PawCircle composer kind [{$kind}]."),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function post(array $context, bool $editing = false): array
    {
        $post = $editing ? ($context['post'] ?? null) : [];

        if ($editing && ! is_array($post)) {
            throw new InvalidArgumentException('A valid editable post is required.');
        }

        $mediaOptions = ['none' => 'No media'];

        foreach ($context['media_presets'] ?? [] as $key => $preset) {
            if ($key !== 'none') {
                $mediaOptions[$key] = (string) ($preset['label'] ?? ucfirst((string) $key));
            }
        }

        return $this->definition(
            eyebrow: 'Neighborhood feed',
            title: $editing ? 'Edit your publication' : 'Create a publication',
            description: 'Choose the publishing profile, a safe audience, and only the context neighbors need.',
            action: $editing ? 'update-post' : 'create-post',
            submitLabel: $editing ? 'Save changes' : 'Publish',
            submitIcon: $editing ? 'check' : 'send',
            cancelRoute: 'home',
            activeSection: 'feed',
            fields: [
                $this->field(
                    'identity',
                    'Publish as',
                    'select',
                    (string) ($post['identity'] ?? 'mia'),
                    '',
                    required: true,
                    options: $context['identities'] ?? [],
                ),
                $this->field(
                    'format',
                    'Format',
                    'select',
                    (string) ($post['format'] ?? 'photo'),
                    '',
                    required: true,
                    options: [
                        'text' => 'Text update',
                        'photo' => 'Photo update',
                        'video' => 'Video',
                        'question' => 'Question',
                        'lost' => 'Lost pet alert',
                        'adoption' => 'Adoption profile',
                    ],
                ),
                $this->field('title', 'Headline', 'text', (string) ($post['title'] ?? ''), 'Optional short headline'),
                $this->field('body', 'Post', 'textarea', (string) ($post['body'] ?? ''), 'Share the useful part of the story.', required: true),
                $this->field(
                    'topic',
                    'Topic',
                    'select',
                    (string) ($post['topic'] ?? 'community'),
                    '',
                    required: true,
                    options: $context['topics'] ?? [],
                ),
                $this->field('tags', 'Tags', 'text', (string) ($post['tags'] ?? ''), 'training, Portland, rescue'),
                $this->field(
                    'media',
                    'Media preview',
                    'select',
                    (string) ($post['media'] ?? 'park-carousel'),
                    '',
                    required: true,
                    options: $mediaOptions,
                ),
                $this->field('media_alt', 'Media description', 'text', (string) ($post['media_alt'] ?? ''), 'Required when media is selected'),
                $this->field(
                    'location',
                    'Safe place',
                    'select',
                    (string) ($post['location'] ?? 'none'),
                    '',
                    required: true,
                    options: $context['safe_places'] ?? [],
                ),
                $this->field(
                    'audience',
                    'Audience',
                    'select',
                    (string) ($post['audience'] ?? 'public'),
                    '',
                    required: true,
                    options: $context['audiences'] ?? [],
                ),
                $this->field(
                    'comment_policy',
                    'Who can comment',
                    'select',
                    (string) ($post['comment_policy'] ?? 'all'),
                    '',
                    required: true,
                    options: $context['comment_policies'] ?? [],
                ),
                $this->field(
                    'sensitive',
                    'Sensitive media',
                    'select',
                    (string) ($post['sensitive'] ?? 'no'),
                    '',
                    required: true,
                    options: ['no' => 'No warning needed', 'yes' => 'Hide behind a content warning'],
                ),
            ],
            payload: $editing ? ['target' => $post['key']] : [],
            secondaryActions: [
                [
                    'label' => 'Save draft',
                    'icon' => 'file-pen-line',
                    'name' => 'intent',
                    'value' => 'draft',
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function group(): array
    {
        return $this->definition(
            eyebrow: 'Community builder',
            title: 'Create a focused group',
            description: 'Set the purpose, membership boundary, posting policy, and first rules before inviting anyone.',
            action: 'create-group',
            submitLabel: 'Create group',
            submitIcon: 'users-round',
            cancelRoute: 'groups.index',
            activeSection: 'groups',
            fields: [
                $this->field('title', 'Group name', 'text', '', 'Example: Richmond Morning Walks', required: true),
                $this->field(
                    'category',
                    'Category',
                    'select',
                    'local',
                    '',
                    required: true,
                    options: [
                        'breed' => 'Breed community',
                        'species' => 'Animal type',
                        'local' => 'Local community',
                        'interest' => 'Shared interest',
                        'training' => 'Training and behavior',
                        'care' => 'Care and health support',
                        'adoption' => 'Adoption and fostering',
                        'volunteering' => 'Volunteering',
                    ],
                ),
                $this->field(
                    'privacy',
                    'Privacy',
                    'select',
                    'closed',
                    '',
                    required: true,
                    options: [
                        'public' => 'Public · anyone can read and join',
                        'closed' => 'Closed · members are approved',
                    ],
                ),
                $this->field('city', 'City or region', 'text', '', 'Example: Portland, Oregon', required: true),
                $this->field(
                    'language',
                    'Primary language',
                    'select',
                    'English',
                    '',
                    required: true,
                    options: [
                        'English' => 'English',
                        'English + Spanish' => 'English + Spanish',
                        'Russian' => 'Russian',
                        'Lithuanian' => 'Lithuanian',
                    ],
                ),
                $this->field(
                    'pet_identity',
                    'Participating profiles',
                    'select',
                    'all',
                    '',
                    required: true,
                    options: [
                        'mia' => 'Mia only',
                        'scout' => 'Mia with Scout',
                        'nori' => 'Mia with Nori',
                        'all' => 'Mia with Scout and Nori',
                    ],
                ),
                $this->field(
                    'posting_policy',
                    'Who can publish',
                    'select',
                    'members',
                    '',
                    required: true,
                    options: [
                        'members' => 'All members',
                        'review' => 'Members after moderator review',
                        'staff' => 'Administrators and moderators only',
                    ],
                ),
                $this->field('body', 'Description', 'textarea', '', 'Who is this group for, and what belongs here?', required: true),
                $this->field('rules', 'First community rules', 'textarea', '', 'Add privacy, safety, promotion, and respectful-conversation boundaries.', required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function meetup(array $context): array
    {
        $place = is_array($context['place_context'] ?? null)
            ? $context['place_context']
            : [];

        return $this->definition(
            eyebrow: 'Event studio',
            title: 'Create a pet-friendly event',
            description: 'Set the format, audience, registration boundary, tickets, and safety plan before publishing.',
            action: 'create-meetup',
            submitLabel: 'Publish event',
            submitIcon: 'calendar-plus',
            cancelRoute: 'meetups.index',
            activeSection: 'meetups',
            fields: [
                $this->field('title', 'Event name', 'text', '', 'Example: Quiet Sunday park loop', required: true),
                $this->field(
                    'category',
                    'Category',
                    'select',
                    'walk',
                    '',
                    required: true,
                    options: [
                        'walk' => 'Walk or first meeting',
                        'training' => 'Training',
                        'show' => 'Show or exhibition',
                        'lecture' => 'Lecture',
                        'webinar' => 'Webinar',
                        'adoption' => 'Adoption day',
                        'volunteering' => 'Volunteer action',
                        'charity' => 'Charity event',
                        'contest' => 'Contest',
                        'photo-session' => 'Photo session',
                        'travel' => 'Pet-friendly trip',
                        'celebration' => 'Celebration or memorial',
                        'search-action' => 'Urgent search action',
                        'other' => 'Other',
                    ],
                ),
                $this->field(
                    'event_organizer',
                    'Organize as',
                    'select',
                    'mia',
                    '',
                    required: true,
                    options: [
                        'mia' => 'Mia Carter',
                        'scout' => 'Scout, managed by Mia',
                        'group' => 'Richmond Pet Circle',
                        'organization' => 'PawCircle Community Team',
                    ],
                ),
                $this->field(
                    'event_format',
                    'Format',
                    'select',
                    'offline',
                    '',
                    required: true,
                    options: [
                        'offline' => 'In person',
                        'online' => 'Online',
                    ],
                ),
                $this->field('date', 'Date', 'date', '', '', required: true, min: today()->format('Y-m-d')),
                $this->field('time', 'Start time', 'time', '10:00', '', required: true),
                $this->field(
                    'event_timezone',
                    'Time zone',
                    'select',
                    'America/Los_Angeles',
                    '',
                    required: true,
                    options: [
                        'America/Los_Angeles' => 'Pacific time',
                        'America/New_York' => 'Eastern time',
                        'Europe/Vilnius' => 'Vilnius time',
                        'Europe/London' => 'London time',
                        'UTC' => 'UTC',
                    ],
                ),
                $this->field(
                    'location',
                    'Meeting place',
                    'text',
                    (string) ($place['address'] ?? ''),
                    'Required for in-person events',
                ),
                $this->field('event_online_url', 'Online room link', 'url', '', 'Required for online events'),
                $this->field(
                    'privacy',
                    'Privacy',
                    'select',
                    'public',
                    '',
                    required: true,
                    options: [
                        'public' => 'Public and discoverable',
                        'closed' => 'Closed with limited details',
                        'hidden' => 'Invitation only',
                    ],
                ),
                $this->field(
                    'event_registration_policy',
                    'Registration',
                    'select',
                    'approval',
                    '',
                    required: true,
                    options: [
                        'instant' => 'Instant confirmation',
                        'approval' => 'Organizer approval',
                        'invitation' => 'Invitation only',
                    ],
                ),
                $this->field('event_capacity', 'Capacity', 'number', '8', 'People and pets together', required: true, min: '2'),
                $this->field(
                    'event_ticket_model',
                    'Tickets',
                    'select',
                    'free',
                    '',
                    required: true,
                    options: [
                        'free' => 'Free registration',
                        'paid' => 'Paid ticket',
                    ],
                ),
                $this->field('event_ticket_price', 'Ticket price, USD', 'number', '', 'Required for paid events', min: '1'),
                $this->field(
                    'event_cover',
                    'Cover',
                    'select',
                    'walk',
                    '',
                    required: true,
                    options: [
                        'walk' => 'Calm park walk',
                        'training' => 'Training session',
                        'community' => 'Community gathering',
                        'online' => 'Online learning',
                    ],
                ),
                $this->field(
                    'body',
                    'Description',
                    'textarea',
                    $place === []
                        ? ''
                        : 'Meet at '.$place['name'].'. Confirm the current place status and exact entrance before departure.',
                    'Describe who it is for, what happens, and what to bring.',
                    required: true,
                ),
                $this->field(
                    'rules',
                    'Participation rules',
                    'textarea',
                    $place === [] ? '' : implode("\n", $place['rules'] ?? []),
                    'Add leash, contact, photography, and cancellation rules.',
                    required: true,
                ),
                $this->field('event_safety_plan', 'Safety plan', 'textarea', '', 'Add meeting boundaries, emergency contact path, and animal-comfort precautions.', required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function walk(): array
    {
        return $this->definition(
            eyebrow: 'Walk planner',
            title: 'Plan a neighborhood walk',
            description: 'Set a calm route, clear timing, and an easy pace before sending the plan to a neighbor.',
            action: 'create-walk-plan',
            submitLabel: 'Save walk draft',
            submitIcon: 'calendar-plus',
            cancelRoute: 'walks.index',
            activeSection: 'meetups',
            fields: [
                $this->field(
                    'target',
                    'Walking with',
                    'select',
                    'mochi',
                    '',
                    required: true,
                    options: [
                        'mochi' => 'Ari and Mochi',
                        'juniper' => 'Noah and Juniper',
                        'scout' => 'Scout and Mia',
                    ],
                ),
                $this->field('title', 'Plan name', 'text', '', 'Example: Early Fields Park loop', required: true),
                $this->field('date', 'Date', 'date', '', '', required: true, min: today()->format('Y-m-d')),
                $this->field('time', 'Start time', 'time', '08:30', '', required: true),
                $this->field('location', 'Meeting point', 'text', '', 'Park gate, quiet corner, or familiar block', required: true),
                $this->field(
                    'detail',
                    'Pace',
                    'select',
                    'Easy pace, 30 min',
                    '',
                    options: [
                        'Easy pace, 20 min' => 'Easy pace, 20 min',
                        'Easy pace, 30 min' => 'Easy pace, 30 min',
                        'Steady pace, 45 min' => 'Steady pace, 45 min',
                        'Sniff-friendly, no time limit' => 'Sniff-friendly, no time limit',
                    ],
                ),
                $this->field('body', 'Routine notes', 'textarea', '', 'Add greetings, triggers, water stops, or a quiet finish.', required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function pet(): array
    {
        return $this->definition(
            eyebrow: 'Your pack',
            title: 'Add a pet',
            description: 'Create a simple profile that helps neighbors understand your pet’s routine and pace.',
            action: 'create-pet',
            submitLabel: 'Add pet',
            submitIcon: 'paw-print',
            cancelRoute: 'pets.index',
            activeSection: 'pets',
            fields: [
                $this->field('title', 'Pet name', 'text', '', 'Pet name', required: true),
                $this->field('category', 'Species', 'text', '', 'Dog, cat, rabbit...', required: true),
                $this->field('detail', 'Breed or type', 'text', '', 'Breed or companion type'),
                $this->field('body', 'Short profile', 'textarea', '', 'Share a favorite routine or social preference.', required: true),
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function message(): array
    {
        return $this->definition(
            eyebrow: 'Neighborhood inbox',
            title: 'Start a new message',
            description: 'Write a clear note about a walk, care question, or local plan.',
            action: 'send-message',
            submitLabel: 'Send message',
            submitIcon: 'send',
            cancelRoute: 'messages.index',
            activeSection: 'messages',
            fields: [
                $this->field(
                    'target',
                    'To',
                    'select',
                    'ari',
                    '',
                    required: true,
                    options: [
                        'ari' => 'Ari Jensen and Mochi',
                        'lena' => 'Lena Brooks and Pip',
                        'noah' => 'Noah Patel and Juniper',
                        'priya' => 'Priya Shah and Clover',
                    ],
                ),
                $this->field('body', 'Message', 'textarea', '', 'Write your message', required: true),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $owner
     * @return array<string, mixed>
     */
    private function profile(array $owner): array
    {
        return $this->definition(
            eyebrow: 'Your profile',
            title: 'Edit your PawCircle profile',
            description: 'Keep the details neighbors use to plan walks and introductions current.',
            action: 'update-profile',
            submitLabel: 'Save profile',
            submitIcon: 'check',
            cancelRoute: 'profile.mia',
            activeSection: 'profile',
            fields: [
                $this->field('title', 'Name', 'text', $owner['name'], 'Your name', required: true, autocomplete: 'name'),
                $this->field('location', 'Location', 'text', $owner['location'], 'Neighborhood and city', required: true, autocomplete: 'address-level2'),
                $this->field('detail', 'Availability', 'text', $owner['status'], 'When are you open to meeting?'),
                $this->field('body', 'About you', 'textarea', $owner['bio'], 'Share your routines and interests.', required: true),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $pet
     * @return array<string, mixed>
     */
    private function petProfile(array $pet): array
    {
        return $this->definition(
            eyebrow: 'Pet profile',
            title: 'Edit '.$pet['name'].' profile',
            description: 'Update the details other pet people use before planning time together.',
            action: 'update-pet',
            submitLabel: 'Save pet profile',
            submitIcon: 'check',
            cancelRoute: $pet['route'],
            activeSection: 'pets',
            fields: [
                $this->field('title', 'Name', 'text', $pet['name'], 'Pet name', required: true),
                $this->field('category', 'Breed', 'text', $pet['breed'], 'Breed or companion type', required: true),
                $this->field('detail', 'Availability', 'text', $pet['status'], 'Current social status'),
                $this->field('body', 'Story', 'textarea', $pet['story'], 'Share routines, preferences, and personality.', required: true),
            ],
            payload: ['target' => $pet['slug']],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array<string, string>  $visibilityOptions
     * @return array<string, mixed>
     */
    private function profilePrivacy(array $context, array $visibilityOptions): array
    {
        $privacy = $context['owner_privacy'] ?? [];

        return $this->definition(
            eyebrow: 'Owner profile privacy',
            title: 'Choose what Mia shares',
            description: 'Each profile area has its own audience. Exact addresses and private contact details stay unavailable.',
            action: 'update-profile-privacy',
            submitLabel: 'Save owner privacy',
            submitIcon: 'shield-check',
            cancelRoute: 'profile.mia',
            activeSection: 'profile',
            fields: [
                $this->field('location_visibility', 'City and area', 'select', (string) ($privacy['location'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('pets_visibility', 'Pet list', 'select', (string) ($privacy['pets'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('posts_visibility', 'Owner posts', 'select', (string) ($privacy['posts'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('friends_visibility', 'Friend list', 'select', (string) ($privacy['friends'] ?? 'followers'), '', required: true, options: $visibilityOptions),
                $this->field('activity_visibility', 'Activity status', 'select', (string) ($privacy['activity'] ?? 'followers'), '', required: true, options: $visibilityOptions),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $pet
     * @param  array<string, mixed>  $context
     * @param  array<string, string>  $visibilityOptions
     * @return array<string, mixed>
     */
    private function petPrivacy(array $pet, array $context, array $visibilityOptions): array
    {
        $privacy = $context['pet_privacy'] ?? [];

        return $this->definition(
            eyebrow: 'Pet profile privacy',
            title: 'Choose what '.$pet['name'].' shares',
            description: 'Pet visibility is independent from Mia profile visibility and can be changed at any time.',
            action: 'update-pet-privacy',
            submitLabel: 'Save pet privacy',
            submitIcon: 'shield-check',
            cancelRoute: $pet['route'],
            activeSection: 'pets',
            fields: [
                $this->field('location_visibility', 'City and area', 'select', (string) ($privacy['location'] ?? 'followers'), '', required: true, options: $visibilityOptions),
                $this->field('posts_visibility', 'Pet feed', 'select', (string) ($privacy['posts'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('friends_visibility', 'Pet friends', 'select', (string) ($privacy['friends'] ?? 'public'), '', required: true, options: $visibilityOptions),
                $this->field('care_visibility', 'Care profile', 'select', (string) ($privacy['care'] ?? 'owners'), '', required: true, options: $visibilityOptions),
                $this->field('activity_visibility', 'Activity status', 'select', (string) ($privacy['activity'] ?? 'followers'), '', required: true, options: $visibilityOptions),
            ],
            payload: ['target' => $pet['slug']],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function profileReport(array $context): array
    {
        $report = $context['report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException('A valid profile report target is required.');
        }

        return $this->definition(
            eyebrow: 'Private safety report',
            title: 'Report '.$report['label'],
            description: 'Tell the moderation team what happened. The profile owner will not see who sent this report.',
            action: 'create-profile-report',
            submitLabel: 'Submit report',
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: str_starts_with($report['target'], 'pet-') ? 'pets' : 'profile',
            fields: [
                $this->field(
                    'category',
                    'Reason',
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'fake-profile' => 'Fake or impersonating profile',
                        'stolen-photos' => 'Stolen animal photos',
                        'animal-safety' => 'Animal safety concern',
                        'fraud' => 'Fraud or scam',
                        'spam' => 'Spam or unauthorized advertising',
                        'harassment' => 'Harassment or abuse',
                        'dangerous-advice' => 'Dangerous medical advice',
                        'other' => 'Other concern',
                    ],
                ),
                $this->field('body', 'What happened?', 'textarea', '', 'Add context or evidence for the moderation team.', required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function postReport(array $context): array
    {
        $report = $context['post_report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException('A valid publication report target is required.');
        }

        return $this->definition(
            eyebrow: 'Private safety report',
            title: 'Report this publication',
            description: 'Choose the closest reason and give moderators enough context to review the publication.',
            action: 'create-post-report',
            submitLabel: 'Submit report',
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'feed',
            fields: [
                $this->field(
                    'category',
                    'Reason',
                    'select',
                    '',
                    '',
                    required: true,
                    options: $context['post_report_reasons'] ?? [],
                ),
                $this->field('body', 'What happened?', 'textarea', '', 'Add relevant context or evidence.', required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function groupReport(array $context): array
    {
        $report = $context['group_report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException('A valid group report target is required.');
        }

        return $this->definition(
            eyebrow: 'Private community report',
            title: 'Report '.$report['label'],
            description: 'Choose the closest reason and add enough context for the moderation team. Group moderators will not see who submitted it.',
            action: 'create-group-report',
            submitLabel: 'Submit report',
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'groups',
            fields: [
                $this->field(
                    'category',
                    'Reason',
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'spam' => 'Spam or unauthorized advertising',
                        'harassment' => 'Harassment or abuse',
                        'animal-safety' => 'Animal safety concern',
                        'dangerous-advice' => 'Dangerous medical advice',
                        'fraud' => 'Fraud or unverified fundraising',
                        'personal-data' => 'Private information was exposed',
                        'illegal-sales' => 'Prohibited animal sales',
                        'stolen-media' => 'Stolen photos or video',
                        'other' => 'Other concern',
                    ],
                ),
                $this->field('body', 'What happened?', 'textarea', '', 'Add relevant context, dates, or evidence.', required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function eventReport(array $context): array
    {
        $report = $context['event_report'] ?? null;

        if (! is_array($report)) {
            throw new InvalidArgumentException('A valid event report target is required.');
        }

        return $this->definition(
            eyebrow: 'Private event report',
            title: 'Report '.$report['label'],
            description: 'Tell the safety team what happened. The organizer will not see who submitted this report.',
            action: 'create-event-report',
            submitLabel: 'Submit report',
            submitIcon: 'flag',
            cancelRoute: $report['route'],
            activeSection: 'meetups',
            fields: [
                $this->field(
                    'category',
                    'Reason',
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'fraud' => 'Fraud, hidden fees, or a fake event',
                        'animal-safety' => 'Animal safety or cruel treatment',
                        'harassment' => 'Threats, harassment, or stalking',
                        'personal-data' => 'Private information was exposed',
                        'illegal-sales' => 'Prohibited animal sale',
                        'false-alert' => 'False emergency or search alert',
                        'dangerous-advice' => 'Dangerous professional advice',
                        'other' => 'Other concern',
                    ],
                ),
                $this->field('body', 'What happened?', 'textarea', '', 'Add dates, messages, payment context, or other evidence.', required: true),
            ],
            payload: [
                'target' => $report['target'],
                'label' => $report['label'],
            ],
            cancelParameters: $report['route_parameters'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function place(): array
    {
        return $this->definition(
            eyebrow: 'Community map',
            title: 'Add a place',
            description: 'Share enough source-backed information for moderators to check the place without exposing a private home address.',
            action: 'create-place',
            submitLabel: 'Send for review',
            submitIcon: 'map-pin-plus',
            cancelRoute: 'places.index',
            activeSection: 'places',
            fields: [
                $this->field('title', 'Place name', 'text', '', 'Use the name shown at the location.', required: true),
                $this->field(
                    'category',
                    'Primary category',
                    'select',
                    'park',
                    '',
                    required: true,
                    options: [
                        'park' => 'Park',
                        'dog-park' => 'Dog park',
                        'route' => 'Walking route',
                        'vet' => 'Veterinary clinic',
                        'emergency-vet' => '24-hour veterinary clinic',
                        'pet-store' => 'Pet store',
                        'grooming' => 'Grooming',
                        'shelter' => 'Shelter',
                        'pet-cafe' => 'Pet-friendly cafe',
                    ],
                ),
                $this->field('city', 'City or area', 'text', 'Vilnius', 'City, district, or region', required: true),
                $this->field('place_address', 'Public address or entrance', 'text', '', 'Do not enter a private home address.', required: true),
                $this->field('place_coordinates', 'Approximate coordinates', 'text', '', 'Example: 54.6892, 25.2537'),
                $this->field('body', 'Description', 'textarea', '', 'What is here, who is it useful for, and what should visitors know?', required: true),
                $this->field('place_hours', 'Hours', 'textarea', '', 'Add regular, seasonal, appointment-only, or emergency hours.'),
                $this->field('rules', 'Pet rules', 'textarea', '', 'Add leash, species, size, access, and event rules.', required: true),
                $this->field('place_features', 'Facilities and accessibility', 'textarea', '', 'Water, lighting, fencing, parking, ramps, quiet zones...'),
                $this->field('place_source', 'Information source', 'url', '', 'Official page or another public source'),
                $this->field('place_evidence', 'Evidence note', 'textarea', '', 'Describe a sign, recent visit, or official source.'),
                $this->field(
                    'place_relationship',
                    'Your relationship',
                    'select',
                    'visitor',
                    '',
                    required: true,
                    options: [
                        'visitor' => 'Visitor',
                        'owner' => 'Owner',
                        'employee' => 'Employee',
                        'organization' => 'Organization representative',
                        'city-representative' => 'City representative',
                    ],
                ),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeCorrection(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_correction');

        return $this->definition(
            eyebrow: 'Community correction',
            title: 'Correct '.$place['label'],
            description: 'Propose one precise change and include a recent source. Important details stay unchanged until reviewed.',
            action: 'create-place-correction',
            submitLabel: 'Submit correction',
            submitIcon: 'file-check-2',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'place_field',
                    'What changed?',
                    'select',
                    'hours',
                    '',
                    required: true,
                    options: [
                        'hours' => 'Hours',
                        'pet-rules' => 'Pet rules',
                        'address' => 'Address or map point',
                        'contact' => 'Contact details',
                        'services' => 'Services',
                        'accessibility' => 'Accessibility',
                        'closure' => 'Temporary or permanent closure',
                    ],
                ),
                $this->field('place_current_value', 'Current information', 'textarea', '', 'What does the place page currently say?'),
                $this->field('body', 'Proposed information', 'textarea', '', 'Write the corrected information clearly.', required: true),
                $this->field('place_visit_date', 'Date checked', 'date', today()->format('Y-m-d'), ''),
                $this->field('place_source', 'Public source', 'url', '', 'Official website or public notice'),
                $this->field('place_evidence', 'Evidence', 'textarea', '', 'Describe the sign, source, photo, or visit that confirms this change.', required: true),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeWarning(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: 'Temporary safety alert',
            title: 'Report a hazard at '.$place['label'],
            description: 'Alerts are time-limited and reviewed. Describe the exact area without exposing another person’s private information.',
            action: 'create-place-warning',
            submitLabel: 'Publish alert for review',
            submitIcon: 'triangle-alert',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('title', 'Short warning', 'text', '', 'Example: Broken glass near the north gate', required: true),
                $this->field(
                    'category',
                    'Hazard',
                    'select',
                    'broken-glass',
                    '',
                    required: true,
                    options: [
                        'broken-glass' => 'Broken glass or sharp debris',
                        'poison' => 'Suspected poison',
                        'dangerous-food' => 'Dangerous food',
                        'damaged-fence' => 'Damaged fence or gate',
                        'ice' => 'Ice or slippery surface',
                        'road-closure' => 'Closed route or entrance',
                        'chemicals' => 'Chemical treatment',
                        'water' => 'Unsafe water',
                        'fire' => 'Fire or smoke',
                        'flood' => 'Flooding',
                        'lighting' => 'Lighting failure',
                        'other' => 'Other temporary hazard',
                    ],
                ),
                $this->field('place_zone', 'Area inside the place', 'text', '', 'Entrance, small-dog zone, path marker...'),
                $this->field('body', 'What did you see?', 'textarea', '', 'Add when it happened and what visitors should avoid.', required: true),
                $this->field('place_evidence', 'Evidence note', 'textarea', '', 'Describe a current photo or another verifiable source.'),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeReview(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: 'Verified experience',
            title: 'Review '.$place['label'],
            description: 'Review the place and its published information. Do not include medical records or another person’s private details.',
            action: 'create-place-review',
            submitLabel: 'Publish review',
            submitIcon: 'star',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'place_rating',
                    'Overall rating',
                    'select',
                    '5',
                    '',
                    required: true,
                    options: [
                        '5' => '5 — Excellent',
                        '4' => '4 — Good',
                        '3' => '3 — Mixed',
                        '2' => '2 — Poor',
                        '1' => '1 — Very poor',
                    ],
                ),
                $this->field(
                    'place_pet',
                    'Visited with',
                    'select',
                    'scout',
                    '',
                    required: true,
                    options: [
                        'scout' => 'Scout',
                        'nori' => 'Nori',
                    ],
                ),
                $this->field(
                    'place_review_criterion',
                    'Main topic',
                    'select',
                    'overall',
                    '',
                    options: [
                        'overall' => 'Overall experience',
                        'safety' => 'Safety',
                        'accessibility' => 'Accessibility',
                        'accuracy' => 'Information accuracy',
                        'communication' => 'Communication',
                        'cleanliness' => 'Cleanliness',
                        'price' => 'Price clarity',
                    ],
                ),
                $this->field('place_visit_date', 'Visit date', 'date', today()->format('Y-m-d'), ''),
                $this->field('body', 'Review', 'textarea', '', 'What matched the listing, and what should another owner know?', required: true),
                $this->field(
                    'place_anonymous',
                    'Public identity',
                    'select',
                    'no',
                    '',
                    options: [
                        'no' => 'Show my profile',
                        'yes' => 'Hide my name publicly',
                    ],
                ),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeQuestion(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: 'Place questions',
            title: 'Ask about '.$place['label'],
            description: 'Ask one practical question. Answers identify whether they came from an owner, staff member, specialist, moderator, or visitor.',
            action: 'create-place-question',
            submitLabel: 'Ask question',
            submitIcon: 'message-circle-question',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('body', 'Question', 'textarea', '', 'Example: Is the small-dog gate working today?', required: true),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeClaim(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: 'Business verification',
            title: 'Claim '.$place['label'],
            description: 'Verification grants management tools, not control over community reviews or moderation decisions.',
            action: 'create-place-claim',
            submitLabel: 'Request access',
            submitIcon: 'badge-check',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field('title', 'Organization or business name', 'text', '', 'Legal or public trading name', required: true),
                $this->field(
                    'place_relationship',
                    'Your relationship',
                    'select',
                    'owner',
                    '',
                    required: true,
                    options: [
                        'owner' => 'Owner',
                        'employee' => 'Employee',
                        'organization' => 'Organization representative',
                        'city-representative' => 'City representative',
                        'visitor' => 'Other relationship',
                    ],
                ),
                $this->field('place_contact', 'Official contact', 'text', '', 'Domain email or public business phone', required: true),
                $this->field(
                    'place_verification_method',
                    'Verification method',
                    'select',
                    'domain-email',
                    '',
                    required: true,
                    options: [
                        'domain-email' => 'Email on the official domain',
                        'phone' => 'Call the published business number',
                        'address-code' => 'Code delivered to the place',
                        'organization-document' => 'Organization document',
                        'manual-review' => 'Manual review',
                    ],
                ),
                $this->field('place_evidence', 'Verification evidence', 'textarea', '', 'Explain how the moderation team can verify your authority.', required: true),
            ],
            payload: ['target' => $place['target']],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function placeReport(array $context): array
    {
        $place = $this->requiredPlaceContext($context, 'place_report');

        return $this->definition(
            eyebrow: 'Private place report',
            title: 'Report '.$place['label'],
            description: 'Send a private report for persistent, fraudulent, privacy, or serious safety concerns. Use a temporary alert for a short-lived local hazard.',
            action: 'create-place-report',
            submitLabel: 'Submit report',
            submitIcon: 'flag',
            cancelRoute: $place['route'],
            activeSection: 'places',
            fields: [
                $this->field(
                    'category',
                    'Reason',
                    'select',
                    '',
                    '',
                    required: true,
                    options: [
                        'does-not-exist' => 'Place does not exist',
                        'wrong-address' => 'Wrong address',
                        'closed' => 'Closed permanently',
                        'fake-business' => 'Fake or impersonated business',
                        'dangerous-information' => 'Dangerous or misleading information',
                        'animal-cruelty' => 'Animal cruelty concern',
                        'fraud' => 'Fraud or payment concern',
                        'hidden-fees' => 'Hidden fees',
                        'privacy' => 'Private information exposed',
                        'stolen-photos' => 'Stolen photos',
                        'false-professional-info' => 'False professional information',
                        'other' => 'Other concern',
                    ],
                ),
                $this->field('body', 'What happened?', 'textarea', '', 'Add dates, context, and the practical risk.', required: true),
                $this->field('place_evidence', 'Evidence', 'textarea', '', 'Optional public source, photo description, or supporting context.'),
            ],
            payload: [
                'target' => $place['target'],
                'label' => $place['label'],
            ],
            cancelParameters: $place['route_parameters'],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function requiredPlaceContext(array $context, string $key): array
    {
        $place = $context[$key] ?? null;

        if (! is_array($place)) {
            throw new InvalidArgumentException('A valid place target is required.');
        }

        return $place;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function deletePost(array $context): array
    {
        $post = $context['post'] ?? null;

        if (! is_array($post)) {
            throw new InvalidArgumentException('A valid managed publication is required.');
        }

        return $this->definition(
            eyebrow: 'Publication lifecycle',
            title: 'Delete this publication?',
            description: 'This removes the publication from the prototype feed. Use Archive from the publication menu when you may want to restore it later.',
            action: 'delete-post',
            submitLabel: 'Delete publication',
            submitIcon: 'trash-2',
            cancelRoute: 'posts.show',
            activeSection: 'feed',
            fields: [],
            payload: ['target' => $post['key']],
            cancelParameters: ['post' => $post['key']],
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function definition(
        string $eyebrow,
        string $title,
        string $description,
        string $action,
        string $submitLabel,
        string $submitIcon,
        string $cancelRoute,
        string $activeSection,
        array $fields,
        array $payload = [],
        array $cancelParameters = [],
        array $secondaryActions = [],
    ): array {
        return [
            'eyebrow' => $eyebrow,
            'title' => $title,
            'description' => $description,
            'action' => $action,
            'submit_label' => $submitLabel,
            'submit_icon' => $submitIcon,
            'cancel_route' => $cancelRoute,
            'cancel_parameters' => $cancelParameters,
            'active_section' => $activeSection,
            'fields' => $fields,
            'payload' => $payload,
            'secondary_actions' => $secondaryActions,
        ];
    }

    /**
     * @param  array<string, string>  $options
     * @return array{
     *     name: string,
     *     label: string,
     *     type: string,
     *     value: string,
     *     placeholder: string,
     *     required: bool,
     *     options: array<string, string>,
     *     min: string|null,
     *     autocomplete: string|null
     * }
     */
    private function field(
        string $name,
        string $label,
        string $type,
        string $value,
        string $placeholder,
        bool $required = false,
        array $options = [],
        ?string $min = null,
        ?string $autocomplete = null,
    ): array {
        return compact(
            'name',
            'label',
            'type',
            'value',
            'placeholder',
            'required',
            'options',
            'min',
            'autocomplete',
        );
    }
}
