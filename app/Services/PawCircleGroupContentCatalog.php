<?php

namespace App\Services;

final class PawCircleGroupContentCatalog
{
    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    public function content(array $group): array
    {
        return [
            'pinned' => $this->pinned($group),
            'principles' => $this->principles($group),
            'posts' => $this->posts($group),
            'discussions' => $this->discussions($group),
            'events' => $this->events($group),
            'members' => $this->members($group),
            'pets' => $this->pets($group),
            'resources' => $this->resources($group),
            'rules' => $this->rules($group),
            'chat' => $this->chat($group),
            'poll' => $this->poll($group),
            'moderators' => $this->moderators($group),
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array{icon: string, eyebrow: string, title: string, description: string, meta: string}
     */
    private function pinned(array $group): array
    {
        return [
            'icon' => $group['privacy'] === 'closed' ? 'shield-check' : 'pin',
            'eyebrow' => 'Pinned by moderators',
            'title' => $group['privacy'] === 'closed'
                ? 'Read this before sharing member information'
                : 'Start here: useful context makes a useful community',
            'description' => $group['privacy'] === 'closed'
                ? 'Keep member names, private posts, precise locations, and application answers inside the group.'
                : 'Choose the closest topic, protect exact addresses, and explain what you have already tried.',
            'meta' => 'Updated July 27 · '.$group['organizer'],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{icon: string, title: string, description: string}>
     */
    private function principles(array $group): array
    {
        $careGuidance = in_array($group['group_type'], ['care', 'adoption'], true)
            ? [
                'icon' => 'stethoscope',
                'title' => 'Separate experience from medical advice',
                'description' => 'Personal routines can help a conversation, but diagnosis and medication belong with qualified professionals.',
            ]
            : [
                'icon' => 'paw-print',
                'title' => 'Treat every pet as an individual',
                'description' => 'Species, breed, age, and size provide context but never guarantee behavior or compatibility.',
            ];

        return [
            [
                'icon' => 'message-circle-heart',
                'title' => 'Share useful context',
                'description' => 'Describe the pet, environment, and goal so advice can remain specific and respectful.',
            ],
            $careGuidance,
            [
                'icon' => 'map-pin-off',
                'title' => 'Keep private locations private',
                'description' => 'Use parks, districts, and public meeting points instead of home addresses or routine GPS history.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, mixed>>
     */
    private function posts(array $group): array
    {
        return [
            [
                'key' => $group['key'].'-welcome',
                'format' => 'Guide',
                'author' => $group['organizer'],
                'role' => $group['organizer_role'],
                'initials' => $group['organizer_initials'],
                'tone' => 'sun',
                'time' => 'Today · 9:20 AM',
                'datetime' => '2026-07-29T09:20:00-07:00',
                'title' => 'A practical starting guide for '.$group['topic'],
                'body' => 'We collected the most useful recent answers into one short guide. Add your pet’s context before trying a routine, and flag anything that needs an expert review.',
                'tags' => ['moderator post', 'start here'],
                'stats' => ['reactions' => 84, 'comments' => 19, 'saves' => 31],
                'image' => $group['image_small'],
                'image_alt' => $group['image_alt'],
                'expert' => (bool) $group['official'],
            ],
            [
                'key' => $group['key'].'-question',
                'format' => 'Question',
                'author' => 'Mia Carter',
                'role' => 'Member · with Scout and Nori',
                'initials' => 'MC',
                'tone' => 'mint',
                'time' => 'Yesterday · 6:45 PM',
                'datetime' => '2026-07-28T18:45:00-07:00',
                'title' => 'What made the first week easier for your pet?',
                'body' => 'I am comparing calm, repeatable routines rather than quick fixes. What helped, what did you change, and how long did you give it?',
                'tags' => ['needs advice', 'lived experience'],
                'stats' => ['reactions' => 42, 'comments' => 27, 'saves' => 12],
                'image' => null,
                'image_alt' => null,
                'expert' => false,
            ],
            [
                'key' => $group['key'].'-event',
                'format' => 'Event update',
                'author' => 'Jamie Cho',
                'role' => 'Event organizer',
                'initials' => 'JC',
                'tone' => 'paper',
                'time' => 'Monday · 11:10 AM',
                'datetime' => '2026-07-27T11:10:00-07:00',
                'title' => $group['next_event'],
                'body' => 'The event page now includes accessibility, arrival, and weather notes. Exact meeting details are shared only with confirmed attendees.',
                'tags' => ['event', 'local'],
                'stats' => ['reactions' => 37, 'comments' => 8, 'saves' => 21],
                'image' => null,
                'image_alt' => null,
                'expert' => false,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{icon: string, title: string, description: string, meta: string, status: string}>
     */
    private function discussions(array $group): array
    {
        return [
            [
                'icon' => 'messages-square',
                'title' => 'Introductions and current routines',
                'description' => 'Meet recent members and learn which topics they want to explore.',
                'meta' => '18 participants · last reply 12 min ago',
                'status' => 'Active',
            ],
            [
                'icon' => 'circle-help',
                'title' => 'Questions waiting for a useful answer',
                'description' => 'Focused questions with pet context and no accepted answer yet.',
                'meta' => '7 open questions · '.$group['language'],
                'status' => 'Needs replies',
            ],
            [
                'icon' => 'badge-check',
                'title' => 'Moderator-reviewed reference thread',
                'description' => 'A durable summary of recurring advice, sources, and important limitations.',
                'meta' => 'Updated this week · 46 saves',
                'status' => 'Resolved',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, mixed>>
     */
    private function events(array $group): array
    {
        return [
            [
                'key' => $group['key'].'-next',
                'month' => 'AUG',
                'day' => '02',
                'datetime' => '2026-08-02T09:30:00-07:00',
                'title' => $group['next_event'],
                'place' => $group['local'] ? 'Public meeting area · '.$group['location'] : 'Online room',
                'access' => 'Exact details after RSVP',
                'attendees' => '18 going · 6 spots left',
                'status' => 'Registration open',
            ],
            [
                'key' => $group['key'].'-qa',
                'month' => 'AUG',
                'day' => '08',
                'datetime' => '2026-08-08T18:00:00-07:00',
                'title' => 'Member Q&A and monthly planning',
                'place' => 'Online · captions available',
                'access' => 'Members may submit questions in advance',
                'attendees' => '34 interested',
                'status' => 'Members only',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{name: string, detail: string, initials: string, tone: string, badge: string}>
     */
    private function members(array $group): array
    {
        return [
            [
                'name' => $group['organizer'],
                'detail' => $group['organizer_role'],
                'initials' => $group['organizer_initials'],
                'tone' => 'sun',
                'badge' => $group['official'] ? 'Verified organizer' : 'Owner',
            ],
            [
                'name' => 'Mia Carter',
                'detail' => 'Member · Scout and Nori',
                'initials' => 'MC',
                'tone' => 'mint',
                'badge' => 'Active member',
            ],
            [
                'name' => 'Lena Brooks',
                'detail' => 'Moderator · Cat enrichment',
                'initials' => 'LB',
                'tone' => 'paper',
                'badge' => 'Moderator',
            ],
            [
                'name' => 'Priya Shah',
                'detail' => 'Volunteer coordinator',
                'initials' => 'PS',
                'tone' => 'mint',
                'badge' => 'Contributor',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array<string, string>>
     */
    private function pets(array $group): array
    {
        return [
            [
                'name' => 'Scout',
                'detail' => 'Border Collie · active learner',
                'image' => 'https://images.unsplash.com/photo-1553882809-a4f57e59501d?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Scout, a black and white Border Collie',
                'status' => 'Open to calm group activities',
            ],
            [
                'name' => 'Nori',
                'detail' => 'Tabby cat · indoor enrichment',
                'image' => 'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Nori, a tabby cat',
                'status' => 'Participates through Mia',
            ],
            [
                'name' => 'Mochi',
                'detail' => 'Shiba mix · neighborhood walks',
                'image' => 'https://images.unsplash.com/photo-1612536057832-2ff7ead58194?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Mochi, a Shiba mix',
                'status' => 'Event regular',
            ],
            [
                'name' => 'Olive',
                'detail' => 'Corgi · gentle introductions',
                'image' => 'https://images.unsplash.com/photo-1612195583950-b8fd34c87093?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Olive, a Corgi',
                'status' => 'New member',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{icon: string, title: string, description: string, meta: string}>
     */
    private function resources(array $group): array
    {
        return [
            [
                'icon' => 'book-open-text',
                'title' => 'New member guide',
                'description' => 'Where to post, how moderation works, and how to protect private information.',
                'meta' => 'Guide · reviewed July 2026',
            ],
            [
                'icon' => 'map',
                'title' => $group['local'] ? 'Public meeting place checklist' : 'Online event accessibility checklist',
                'description' => 'A concise planning reference for organizers and first-time attendees.',
                'meta' => 'Checklist · 4 min read',
            ],
            [
                'icon' => 'stethoscope',
                'title' => 'When community answers are not enough',
                'description' => 'Signs that a question belongs with a veterinarian or qualified behavior professional.',
                'meta' => 'Safety reference · expert reviewed',
            ],
            [
                'icon' => 'shield-check',
                'title' => 'Privacy and photo consent',
                'description' => 'How tags, event photos, locations, and closed-group material should be handled.',
                'meta' => 'Policy summary · updated this month',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{title: string, description: string}>
     */
    private function rules(array $group): array
    {
        return [
            [
                'title' => 'Be useful and respectful',
                'description' => 'No harassment, personal attacks, repetitive promotion, or pressure to move conversations off-platform.',
            ],
            [
                'title' => 'Protect people and locations',
                'description' => 'Do not publish home addresses, private event details, documents, or another member’s contact information.',
            ],
            [
                'title' => 'No dangerous medical instructions',
                'description' => 'Do not diagnose, prescribe doses, recommend stopping treatment, or promise guaranteed outcomes.',
            ],
            [
                'title' => 'Keep commerce transparent',
                'description' => 'No animal sales or unverified fundraising. Approved services must use the correct section and disclosure.',
            ],
            [
                'title' => 'Respect the '.$group['privacy'].' boundary',
                'description' => $group['privacy'] === 'closed'
                    ? 'Member posts, names, and screenshots stay inside the group unless every affected person agrees.'
                    : 'Public posts may be shared, but authorship, context, and photo consent must be preserved.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{name: string, initials: string, tone: string, body: string, time: string}>
     */
    private function chat(array $group): array
    {
        return [
            [
                'name' => 'Jamie',
                'initials' => 'JC',
                'tone' => 'sun',
                'body' => 'I added the shaded arrival point and accessibility notes to the event.',
                'time' => '9:18 AM',
            ],
            [
                'name' => 'Mia',
                'initials' => 'MC',
                'tone' => 'mint',
                'body' => 'Thank you. I will bring Scout’s own water bowl and start with a little distance.',
                'time' => '9:24 AM',
            ],
            [
                'name' => $group['organizer'],
                'initials' => $group['organizer_initials'],
                'tone' => 'paper',
                'body' => 'Perfect. I pinned the full plan so it does not disappear in chat.',
                'time' => '9:31 AM',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<string, mixed>
     */
    private function poll(array $group): array
    {
        return [
            'key' => 'august-focus',
            'question' => 'Which topic should the group prioritize in August?',
            'description' => 'One response per member. Results guide the next resource and event.',
            'options' => [
                ['key' => 'routine', 'label' => 'Practical daily routines', 'votes' => 48],
                ['key' => 'events', 'label' => $group['local'] ? 'Safer local meetups' : 'Accessible online events', 'votes' => 35],
                ['key' => 'expert', 'label' => 'Expert question session', 'votes' => 29],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $group
     * @return array<int, array{name: string, detail: string, initials: string, tone: string}>
     */
    private function moderators(array $group): array
    {
        return [
            [
                'name' => $group['organizer'],
                'detail' => $group['organizer_role'],
                'initials' => $group['organizer_initials'],
                'tone' => 'sun',
            ],
            [
                'name' => 'Lena Brooks',
                'detail' => 'Moderator · Community care',
                'initials' => 'LB',
                'tone' => 'mint',
            ],
            [
                'name' => 'Priya Shah',
                'detail' => 'Moderator · Safety and events',
                'initials' => 'PS',
                'tone' => 'paper',
            ],
        ];
    }
}
