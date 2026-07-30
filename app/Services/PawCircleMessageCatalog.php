<?php

namespace App\Services;

final class PawCircleMessageCatalog
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public function conversations(): array
    {
        return [
            'ari' => [
                'key' => 'ari',
                'type' => 'personal',
                'category' => 'friends',
                'name' => 'Ari Jensen',
                'handle' => '@ari-and-mochi',
                'pet' => 'Mochi and Scout',
                'pet_names' => ['Mochi', 'Scout'],
                'purpose' => 'Calm first walk',
                'preview' => 'The riverside entrance works. I can keep Mochi on the outside lane.',
                'time' => '09:42',
                'datetime' => '2026-07-30T09:42:00+03:00',
                'unread' => 2,
                'avatar' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=240&h=240&q=82',
                'avatar_alt' => 'Ari with Mochi in a park',
                'verified' => 'Email verified',
                'presence' => 'Online for friends',
                'response' => 'Usually replies within an hour',
                'members' => 2,
                'privacy' => 'Accepted personal dialog',
                'role' => 'Pet friend',
                'channel' => 'general',
                'request' => false,
                'professional' => false,
                'sensitive' => false,
            ],
            'family-care' => [
                'key' => 'family-care',
                'type' => 'family',
                'category' => 'family',
                'name' => 'Scout and Nori care',
                'handle' => 'Carter family',
                'pet' => 'Scout and Nori',
                'pet_names' => ['Scout', 'Nori'],
                'purpose' => 'Shared care log',
                'preview' => 'Medication was logged at 08:15. Evening walk still needs an owner.',
                'time' => '08:18',
                'datetime' => '2026-07-30T08:18:00+03:00',
                'unread' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1601758174114-e711c0cbaa69?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => 'A dog and cat resting together at home',
                'verified' => 'Family managed',
                'presence' => '3 managers',
                'response' => 'Care alerts bypass muted summaries',
                'members' => 3,
                'privacy' => 'Family only',
                'role' => 'Owner',
                'channel' => 'care-log',
                'request' => false,
                'professional' => false,
                'sensitive' => true,
            ],
            'vingis-walk' => [
                'key' => 'vingis-walk',
                'type' => 'event',
                'category' => 'events',
                'name' => 'Quiet Vingis walk',
                'handle' => 'Event chat',
                'pet' => '8 registered pets',
                'pet_names' => ['Scout', 'Mochi', 'Juniper'],
                'purpose' => 'Temporary coordination',
                'preview' => 'Meeting point updated: use the lit riverside gate, not the car park.',
                'time' => 'Yesterday',
                'datetime' => '2026-07-29T18:20:00+03:00',
                'unread' => 4,
                'avatar' => 'https://images.unsplash.com/photo-1558788353-f76d92427f16?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => 'Dogs walking together on a green park path',
                'verified' => 'Organizer confirmed',
                'presence' => '7 of 8 online recently',
                'response' => 'Archives three days after the walk',
                'members' => 8,
                'privacy' => 'Confirmed attendees only',
                'role' => 'Organizer',
                'channel' => 'announcements',
                'request' => false,
                'professional' => false,
                'sensitive' => false,
            ],
            'paws-vet' => [
                'key' => 'paws-vet',
                'type' => 'professional',
                'category' => 'specialists',
                'name' => 'Paws 24 Veterinary Center',
                'handle' => 'Case PC-1048',
                'pet' => 'Nori',
                'pet_names' => ['Nori'],
                'purpose' => 'Follow-up consultation',
                'preview' => 'Dr. Emilia added a visit summary and requested one photo before Friday.',
                'time' => 'Mon',
                'datetime' => '2026-07-27T14:05:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1629909613654-28e377c37b09?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => 'Veterinary clinician in a bright examination room',
                'verified' => 'Clinic identity and license checked',
                'presence' => 'Replies 08:00-20:00',
                'response' => 'Not an emergency channel',
                'members' => 3,
                'privacy' => 'Client and assigned staff',
                'role' => 'Client',
                'channel' => 'case',
                'request' => false,
                'professional' => true,
                'sensitive' => true,
            ],
            'foster-adoption' => [
                'key' => 'foster-adoption',
                'type' => 'organization',
                'category' => 'organizations',
                'name' => 'Vilnius Animal Aid',
                'handle' => 'Adoption application VA-218',
                'pet' => 'Luna',
                'pet_names' => ['Luna'],
                'purpose' => 'Structured adoption review',
                'preview' => 'Your introduction visit is held for Saturday. The shelter address stays private until confirmation.',
                'time' => 'Sun',
                'datetime' => '2026-07-26T12:10:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => 'Rescued dog looking calmly toward the camera',
                'verified' => 'Verified shelter',
                'presence' => 'Application team',
                'response' => 'Identity details unlock by application stage',
                'members' => 4,
                'privacy' => 'Applicant and shelter team',
                'role' => 'Applicant',
                'channel' => 'application',
                'request' => false,
                'professional' => true,
                'sensitive' => true,
            ],
            'lost-luna' => [
                'key' => 'lost-luna',
                'type' => 'search',
                'category' => 'groups',
                'name' => 'Search for Luna',
                'handle' => 'Temporary coordination',
                'pet' => 'Luna',
                'pet_names' => ['Luna'],
                'purpose' => 'Lost pet search',
                'preview' => 'Sector C is checked. A new sighting near the tram stop needs verification.',
                'time' => 'Sat',
                'datetime' => '2026-07-25T22:48:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1587300003388-59208cc962cb?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => 'Golden dog standing outside',
                'verified' => 'Owner coordinated',
                'presence' => '14 volunteers',
                'response' => 'Location shares expire automatically',
                'members' => 14,
                'privacy' => 'Approved search volunteers',
                'role' => 'Coordinator',
                'channel' => 'sightings',
                'request' => false,
                'professional' => false,
                'sensitive' => true,
            ],
            'trail-tails' => [
                'key' => 'trail-tails',
                'type' => 'group',
                'category' => 'groups',
                'name' => 'Trail Tails',
                'handle' => 'Community chat',
                'pet' => '1,284 linked pets',
                'pet_names' => ['Scout'],
                'purpose' => 'Routes and outdoor safety',
                'preview' => 'The north loop is muddy after rain. Photos are in the route thread.',
                'time' => 'Fri',
                'datetime' => '2026-07-24T17:30:00+03:00',
                'unread' => 0,
                'avatar' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=240&h=240&q=82',
                'avatar_alt' => 'Green mountain trail under daylight',
                'verified' => 'Community moderated',
                'presence' => '286 active this week',
                'response' => 'Slow mode for new members',
                'members' => 1284,
                'privacy' => 'Group members',
                'role' => 'Member',
                'channel' => 'routes',
                'request' => false,
                'professional' => false,
                'sensitive' => false,
            ],
            'luna-request' => [
                'key' => 'luna-request',
                'type' => 'request',
                'category' => 'requests',
                'name' => 'Elena and Luna',
                'handle' => 'New message request',
                'pet' => 'Luna · Labrador mix',
                'pet_names' => ['Luna', 'Scout'],
                'purpose' => 'Walk invitation',
                'preview' => 'Hi, our dogs are a similar age. Would a calm parallel walk suit Scout?',
                'time' => 'Today',
                'datetime' => '2026-07-30T07:55:00+03:00',
                'unread' => 1,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&crop=faces&w=240&h=240&q=82',
                'avatar_alt' => 'Elena smiling outdoors',
                'verified' => 'Email verified',
                'presence' => 'Read status hidden for requests',
                'response' => 'One preview message allowed',
                'members' => 2,
                'privacy' => 'Request preview only',
                'role' => 'Recipient',
                'channel' => 'request',
                'request' => true,
                'professional' => false,
                'sensitive' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messages(string $conversation): array
    {
        return $this->messageSets()[$conversation] ?? [];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function messageSets(): array
    {
        return [
            'ari' => [
                $this->message('ari-1', 'Ari Jensen', '09:18', 'I am writing as Mochi\'s person. A parallel walk would be easiest for our first hello.', false, 'text'),
                $this->message('ari-2', 'Mia Carter', '09:25', 'That works for Scout. Let\'s use a public park and keep a comfortable distance.', true, 'text', reply: 'A parallel walk would be easiest'),
                $this->message('ari-3', 'Ari Jensen', '09:42', 'The riverside entrance works. I can keep Mochi on the outside lane.', false, 'place', meta: 'Vingis quiet loop · lit riverside gate · 2.4 km'),
                $this->message('ari-4', 'Ari Jensen', '09:43', 'Voice note · calm introduction plan', false, 'audio', meta: '0:32 · transcript available'),
            ],
            'family-care' => [
                $this->message('family-1', 'Mia Carter', '08:15', 'Scout received the prescribed morning medication.', true, 'task', meta: 'Medication · completed by Mia · 08:15'),
                $this->message('family-2', 'Alex Carter', '08:16', 'I was about to mark this too. The duplicate warning worked.', false, 'text'),
                $this->message('family-3', 'Care summary', '08:18', 'Today: two feedings, one walk, medication completed. Food needs restocking.', false, 'system', meta: 'Private family digest'),
            ],
            'vingis-walk' => [
                $this->message('walk-1', 'Organizer · Mia', 'Yesterday', 'Meeting point updated: use the lit riverside gate, not the car park.', true, 'announcement', meta: 'Important · acknowledged by 6'),
                $this->message('walk-2', 'Noah Patel', 'Yesterday', 'I will be about ten minutes late.', false, 'status', meta: 'Travel status · running late'),
                $this->message('walk-3', 'Event details', 'Yesterday', 'Quiet Vingis walk', false, 'event', meta: 'Saturday · 10:00 · 8 pets · leash required'),
            ],
            'paws-vet' => [
                $this->message('vet-1', 'Clinic assistant', 'Mon', 'This chat is monitored during working hours. For urgent symptoms, call an emergency clinic.', false, 'warning', meta: 'Not an emergency service'),
                $this->message('vet-2', 'Mia Carter', 'Mon', 'Nori is eating normally. I am sharing only the discharge summary for this follow-up.', true, 'file', meta: 'nori-discharge-summary.pdf · access until Aug 7'),
                $this->message('vet-3', 'Dr. Emilia Vaitke', 'Mon', 'Please add one clear photo before Friday. Video alone may not be enough for a clinical conclusion.', false, 'professional', meta: 'Verified veterinarian · Lithuania · answered Jul 27'),
                $this->message('vet-4', 'Consultation', 'Mon', 'Video follow-up · 18 minutes · recording disabled', false, 'call', meta: 'Visit summary confirmed by specialist'),
            ],
            'foster-adoption' => [
                $this->message('adopt-1', 'Vilnius Animal Aid', 'Sun', 'Your application passed the first review. Private contact details remain hidden until the visit is confirmed.', false, 'professional', meta: 'Application VA-218 · stage 2 of 4'),
                $this->message('adopt-2', 'Mia Carter', 'Sun', 'Saturday works. Scout will stay home for the first introduction.', true, 'text'),
                $this->message('adopt-3', 'Visit request', 'Sun', 'Meet Luna at the shelter', false, 'event', meta: 'Saturday · 11:30 · exact entrance after confirmation'),
            ],
            'lost-luna' => [
                $this->message('lost-1', 'Search coordinator', 'Sat', 'Sector C is checked. Do not chase Luna if seen; add a sighting and call the coordinator.', false, 'announcement', meta: 'Emergency channel · approved volunteers'),
                $this->message('lost-2', 'Tomas R.', 'Sat', 'Possible sighting by the tram stop at 22:41. Photo attached for verification.', false, 'image', meta: 'Approximate area only · awaiting verification'),
                $this->message('lost-3', 'Search map', 'Sat', '4 of 7 sectors checked', false, 'task', meta: 'Temporary locations expire when search closes'),
            ],
            'trail-tails' => [
                $this->message('trail-1', 'Moderator · Noah', 'Fri', 'North loop conditions are now in the route thread. New media is limited during slow mode.', false, 'announcement', meta: '#routes · pinned'),
                $this->message('trail-2', 'Lena Brooks', 'Fri', 'The first kilometre is muddy, but the shorter return path is dry.', false, 'text', reply: 'North loop conditions'),
                $this->message('trail-3', 'Route report', 'Fri', 'North loop after rain', false, 'video', meta: '0:41 · captions available · sensitive location removed'),
            ],
            'luna-request' => [
                $this->message('request-1', 'Elena Markova', '07:55', 'Hi, our dogs are a similar age. Would a calm parallel walk suit Scout?', false, 'text', meta: 'Reason: walk invitation'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function message(
        string $id,
        string $sender,
        string $time,
        string $body,
        bool $mine,
        string $type,
        ?string $meta = null,
        ?string $reply = null,
    ): array {
        return [
            'id' => $id,
            'sender' => $sender,
            'time' => $time,
            'datetime' => '2026-07-30T09:00:00+03:00',
            'body' => $body,
            'mine' => $mine,
            'type' => $type,
            'meta' => $meta,
            'reply' => $reply,
            'edited' => false,
            'status' => $mine ? 'Read' : 'Delivered',
            'reactions' => [],
        ];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function channels(): array
    {
        return [
            'vingis-walk' => [
                ['key' => 'announcements', 'label' => 'Announcements', 'icon' => 'megaphone', 'count' => 1],
                ['key' => 'general', 'label' => 'General', 'icon' => 'messages-square', 'count' => 3],
                ['key' => 'transport', 'label' => 'Transport', 'icon' => 'car-front', 'count' => 0],
                ['key' => 'photos', 'label' => 'Photos', 'icon' => 'images', 'count' => 0],
            ],
            'lost-luna' => [
                ['key' => 'announcements', 'label' => 'Updates', 'icon' => 'megaphone', 'count' => 2],
                ['key' => 'sightings', 'label' => 'Sightings', 'icon' => 'map-pin', 'count' => 1],
                ['key' => 'tasks', 'label' => 'Search zones', 'icon' => 'list-checks', 'count' => 4],
            ],
            'trail-tails' => [
                ['key' => 'general', 'label' => 'General', 'icon' => 'messages-square', 'count' => 6],
                ['key' => 'routes', 'label' => 'Routes', 'icon' => 'route', 'count' => 2],
                ['key' => 'safety', 'label' => 'Safety', 'icon' => 'shield-alert', 'count' => 0],
                ['key' => 'photos', 'label' => 'Photos', 'icon' => 'images', 'count' => 0],
            ],
        ];
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public function members(): array
    {
        return [
            'family-care' => [
                ['name' => 'Mia Carter', 'role' => 'Owner', 'pet' => 'Scout and Nori'],
                ['name' => 'Alex Carter', 'role' => 'Co-owner', 'pet' => 'Scout'],
                ['name' => 'Sam Carter', 'role' => 'Family member', 'pet' => 'Nori'],
            ],
            'vingis-walk' => [
                ['name' => 'Mia Carter', 'role' => 'Organizer', 'pet' => 'Scout'],
                ['name' => 'Ari Jensen', 'role' => 'Participant', 'pet' => 'Mochi'],
                ['name' => 'Noah Patel', 'role' => 'Participant', 'pet' => 'Juniper'],
            ],
            'paws-vet' => [
                ['name' => 'Mia Carter', 'role' => 'Client', 'pet' => 'Nori'],
                ['name' => 'Dr. Emilia Vaitke', 'role' => 'Verified veterinarian', 'pet' => 'Assigned specialist'],
                ['name' => 'Clinic assistant', 'role' => 'Case coordinator', 'pet' => 'Paws 24'],
            ],
            'lost-luna' => [
                ['name' => 'Mia Carter', 'role' => 'Coordinator', 'pet' => 'Luna'],
                ['name' => 'Tomas R.', 'role' => 'Volunteer', 'pet' => 'Sector C'],
                ['name' => 'Ari Jensen', 'role' => 'Moderator', 'pet' => 'Search map'],
            ],
            'trail-tails' => [
                ['name' => 'Noah Patel', 'role' => 'Moderator', 'pet' => 'Juniper'],
                ['name' => 'Mia Carter', 'role' => 'Member', 'pet' => 'Scout'],
                ['name' => 'Lena Brooks', 'role' => 'Member', 'pet' => 'Pip'],
            ],
        ];
    }
}
