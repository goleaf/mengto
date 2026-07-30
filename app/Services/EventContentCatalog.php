<?php

namespace App\Services;

final class EventContentCatalog
{
    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    public function content(array $event): array
    {
        return [
            'schedule' => $this->schedule($event),
            'organizers' => $this->organizers($event),
            'attendees' => $this->attendees($event),
            'pets' => $this->pets($event),
            'announcements' => $this->announcements($event),
            'chat' => $this->chat($event),
            'location' => $this->location($event),
            'files' => $this->files($event),
            'gallery' => $this->gallery($event),
            'rules' => $this->rules($event),
            'safety' => $this->safety($event),
            'faq' => $this->faq($event),
            'reviews' => $this->reviews($event),
            'analytics' => $this->analytics($event),
            'applications' => $this->applications($event),
            'waitlist' => $this->waitlist($event),
            'ticket_options' => $this->ticketOptions($event),
            'checklist' => $this->checklist($event),
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function schedule(array $event): array
    {
        return match ($event['event_type']) {
            'group-walk' => [
                ['time' => '9:50 AM', 'title' => 'Quiet arrival', 'description' => 'Meet the host without bringing pets into a tight cluster.', 'leader' => 'Mia Carter'],
                ['time' => '10:00 AM', 'title' => 'Parallel start', 'description' => 'Pairs leave with comfortable spacing and no direct greeting.', 'leader' => 'Mia Carter'],
                ['time' => '10:35 AM', 'title' => 'Water and reset', 'description' => 'Short pause with individual bowls in a shaded area.', 'leader' => 'Noah Patel'],
                ['time' => '11:30 AM', 'title' => 'Flexible finish', 'description' => 'Leave independently or join the optional calm closing loop.', 'leader' => 'Mia Carter'],
            ],
            'training-course' => [
                ['time' => '10:50 AM', 'title' => 'Check-in and settle', 'description' => 'Enter one at a time and choose a marked station.', 'leader' => 'Ari Jensen'],
                ['time' => '11:00 AM', 'title' => 'Attention and name response', 'description' => 'Short repetitions with individual rest breaks.', 'leader' => 'Ari Jensen'],
                ['time' => '11:30 AM', 'title' => 'Waiting and leash movement', 'description' => 'Practical patterns with adjustable distance.', 'leader' => 'Ari Jensen'],
                ['time' => '12:05 PM', 'title' => 'Home routine briefing', 'description' => 'Review materials and next-session goals.', 'leader' => 'Ari Jensen'],
            ],
            'pet-show' => [
                ['time' => '8:00 AM', 'title' => 'Exhibitor check-in', 'description' => 'QR entry and private document review at Hall D.', 'leader' => 'Registration team'],
                ['time' => '9:30 AM', 'title' => 'Morning categories', 'description' => 'Age and breed categories across Rings 1–3.', 'leader' => 'Show officials'],
                ['time' => '1:00 PM', 'title' => 'Companion celebration', 'description' => 'Inclusive stories, adoption journeys, and friendly parade.', 'leader' => 'Community hosts'],
                ['time' => '4:30 PM', 'title' => 'Results and quiet exit', 'description' => 'Results publish by category before staggered departure.', 'leader' => 'Show officials'],
            ],
            'expert-webinar' => [
                ['time' => '5:45 PM', 'title' => 'Room opens', 'description' => 'Test audio, captions, and question submission.', 'leader' => 'Support team'],
                ['time' => '6:00 PM', 'title' => 'Travel preparation', 'description' => 'Documents, carrier routines, transport, and planning.', 'leader' => 'Dr. Elena Park'],
                ['time' => '6:45 PM', 'title' => 'Moderated questions', 'description' => 'General education without individual diagnosis.', 'leader' => 'Dr. Elena Park'],
                ['time' => '7:10 PM', 'title' => 'Resources and recording', 'description' => 'Slides, citations, and recording access explained.', 'leader' => 'Support team'],
            ],
            'search-action' => [
                ['time' => '5:50 PM', 'title' => 'Volunteer check-in', 'description' => 'Receive a zone and confirm safe sighting instructions.', 'leader' => 'Mia Carter'],
                ['time' => '6:00 PM', 'title' => 'Search starts', 'description' => 'Teams move through assigned public areas.', 'leader' => 'Zone coordinators'],
                ['time' => '7:15 PM', 'title' => 'Status regroup', 'description' => 'Share sightings through the private coordinator channel.', 'leader' => 'Mia Carter'],
                ['time' => '9:00 PM', 'title' => 'Close or reassign', 'description' => 'Checked zones are recorded before volunteers leave.', 'leader' => 'Search team'],
            ],
            default => [
                ['time' => 'Start', 'title' => 'Arrival and check-in', 'description' => 'Confirm registration and review the event boundaries.', 'leader' => $event['organizer']],
                ['time' => 'Main', 'title' => 'Event program', 'description' => 'Follow the published event plan and organizer guidance.', 'leader' => $event['organizer']],
                ['time' => 'Finish', 'title' => 'Closing and next steps', 'description' => 'Collect materials, feedback, and any follow-up link.', 'leader' => $event['organizer']],
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function organizers(array $event): array
    {
        return [
            [
                'name' => $event['organizer'],
                'detail' => $event['organizer_type'].' · Lead organizer',
                'initials' => $event['organizer_initials'],
                'tone' => 'sun',
                'badge' => $event['verification_label'] ?? 'Event host',
            ],
            [
                'name' => 'Noah Patel',
                'detail' => 'Registration and accessibility',
                'initials' => 'NP',
                'tone' => 'mint',
                'badge' => 'Co-organizer',
            ],
            [
                'name' => 'Lena Brooks',
                'detail' => 'Chat and participant support',
                'initials' => 'LB',
                'tone' => 'paper',
                'badge' => 'Moderator',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function attendees(array $event): array
    {
        if ($event['privacy'] === 'hidden') {
            return [];
        }

        return [
            ['name' => 'Ari Jensen', 'detail' => 'Approved · arriving with Mochi', 'initials' => 'AJ', 'tone' => 'sun', 'badge' => 'Confirmed'],
            ['name' => 'Noah Patel', 'detail' => 'Approved · accessibility note shared privately', 'initials' => 'NP', 'tone' => 'mint', 'badge' => 'Confirmed'],
            ['name' => 'Priya Shah', 'detail' => $event['format'] === 'online' ? 'Online attendee' : 'First time at this event', 'initials' => 'PS', 'tone' => 'paper', 'badge' => 'Confirmed'],
            ['name' => 'Lena Brooks', 'detail' => 'Event updates enabled', 'initials' => 'LB', 'tone' => 'mint', 'badge' => 'Confirmed'],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function pets(array $event): array
    {
        if (! $event['pets_allowed']) {
            return [];
        }

        return [
            ['name' => 'Mochi', 'detail' => 'Shiba mix · calm arrival requested', 'initials' => 'MO', 'tone' => 'sun', 'badge' => 'Attending'],
            ['name' => 'Juniper', 'detail' => 'Golden Retriever · steady pace', 'initials' => 'JU', 'tone' => 'mint', 'badge' => 'Attending'],
            ['name' => 'Olive', 'detail' => 'Corgi · one handler', 'initials' => 'OL', 'tone' => 'paper', 'badge' => 'Attending'],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function announcements(array $event): array
    {
        return [
            [
                'title' => $event['format'] === 'online' ? 'Access link timing' : 'Meeting point stays private',
                'body' => $event['format'] === 'online'
                    ? 'The webinar room link appears for paid attendees fifteen minutes before the start.'
                    : 'Approved attendees will see the exact entrance. Public cards continue to show only the general area.',
                'time' => 'Today · 9:20 AM',
                'icon' => $event['format'] === 'online' ? 'video' : 'map-pin-check',
            ],
            [
                'title' => 'Bring and consent checklist updated',
                'body' => 'Review the event rules, photo preference, and item checklist before arrival.',
                'time' => 'Yesterday · 4:15 PM',
                'icon' => 'clipboard-check',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function chat(array $event): array
    {
        return [
            [
                'name' => $event['organizer'],
                'initials' => $event['organizer_initials'],
                'tone' => 'sun',
                'body' => $event['format'] === 'online'
                    ? 'Captions and the text transcript will be available in the same event room.'
                    : 'Please arrive with enough space between pets. The host will direct the first pairs.',
                'time' => '9:28 AM',
            ],
            [
                'name' => 'Ari Jensen',
                'initials' => 'AJ',
                'tone' => 'mint',
                'body' => 'I added our accessibility note privately. Thanks for keeping those details out of the participant list.',
                'time' => '9:34 AM',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function location(array $event): array
    {
        return [
            'general' => $event['general_location'],
            'exact' => $event['exact_location'],
            'online_link' => $event['online_link'],
            'map_alt' => 'Text map showing the generalized event area, accessible arrival route, parking, and nearby help.',
            'details' => [
                ['label' => 'Arrival', 'value' => $event['format'] === 'online' ? 'Join from a modern browser with audio enabled' : 'Use the marked public entrance after registration'],
                ['label' => 'Accessibility', 'value' => $event['format'] === 'online' ? 'Keyboard navigation and captions are available' : 'Step-free route, accessible parking, seating, and a quiet area'],
                ['label' => 'Transport', 'value' => $event['format'] === 'online' ? 'No travel required' : 'Public transit and parking notes appear with the exact location'],
                ['label' => 'Nearby help', 'value' => $event['format'] === 'online' ? 'Technical support opens fifteen minutes early' : '24-hour veterinary clinic details are available to attendees'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function files(array $event): array
    {
        return [
            ['title' => 'Event plan', 'description' => 'Schedule, roles, and important contact paths.', 'meta' => 'PDF · updated today', 'icon' => 'file-text'],
            ['title' => 'Safety and accessibility guide', 'description' => 'Arrival, quiet-area, emergency, and consent information.', 'meta' => 'PDF · 420 KB', 'icon' => 'shield-check'],
            ['title' => $event['format'] === 'online' ? 'Slides and sources' : 'What to bring', 'description' => $event['format'] === 'online' ? 'Available after the webinar.' : 'Compact checklist for owners and pets.', 'meta' => 'Guide · current version', 'icon' => 'clipboard-list'],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function gallery(array $event): array
    {
        return [
            [
                'src' => $event['image'],
                'small' => $event['image_small'],
                'medium' => $event['image_medium'],
                'alt' => $event['image_alt'],
                'caption' => 'Event cover selected by the organizer.',
            ],
            [
                'src' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=1200&h=900&q=85',
                'small' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=576&h=432&q=80',
                'medium' => 'https://images.unsplash.com/photo-1558944351-c3a3471282b0?auto=format&fit=crop&w=900&h=675&q=82',
                'alt' => 'Dog resting on grass during a calm outdoor gathering',
                'caption' => 'A quiet-zone example from a previous community event.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function rules(array $event): array
    {
        $petRule = $event['pets_allowed']
            ? 'Keep each pet supervised and follow the stated leash, carrier, distance, food, and toy boundaries.'
            : 'Attend without your resident pet unless the organizer explicitly approves an exception.';

        return [
            ['title' => 'Respect the participation plan', 'description' => $petRule],
            ['title' => 'Protect private details', 'description' => 'Do not repost exact locations, online links, participant notes, or children’s images.'],
            ['title' => 'Use calm, qualified help', 'description' => 'End an activity when anyone is uncomfortable and contact qualified care for medical or complex behavior concerns.'],
            ['title' => 'Photography is opt-in', 'description' => 'Follow photo-free markers and request approval before tagging people, children, or pets.'],
            ['title' => 'Commercial activity is transparent', 'description' => 'Prices, sponsors, donations, refunds, and organizer responsibility must stay visible.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function safety(array $event): array
    {
        return [
            [
                'icon' => 'shield-check',
                'title' => 'Public first contact',
                'description' => $event['format'] === 'online'
                    ? 'Use the protected event room and do not share private contact details in the public questions.'
                    : 'Meet at the published public entrance and keep home addresses private.',
            ],
            [
                'icon' => 'stethoscope',
                'title' => 'Nearby care plan',
                'description' => $event['format'] === 'online'
                    ? 'The webinar is educational and is not an emergency or diagnosis service.'
                    : 'The organizer has a first-aid location and the nearest 24-hour veterinary clinic details.',
            ],
            [
                'icon' => 'cloud-sun',
                'title' => 'Conditions are reviewed',
                'description' => $event['weather']['advisory'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function faq(array $event): array
    {
        return [
            ['question' => 'Can I attend without a pet?', 'answer' => $event['pets_allowed'] ? 'Yes. Select owner-only attendance during registration.' : 'Yes. This event is designed for owners without resident pets.'],
            ['question' => 'When is the exact location available?', 'answer' => $event['format'] === 'online' ? 'The protected link appears for eligible attendees shortly before the start.' : 'Approved or confirmed attendees can see the exact entrance.'],
            ['question' => 'What happens if plans change?', 'answer' => 'Material date, time, place, price, or organizer changes are logged and may require fresh confirmation.'],
            ['question' => 'Can I cancel?', 'answer' => $event['price_minor'] > 0 ? 'Yes. The ticket panel shows the prototype cancellation and refund terms before payment.' : 'Yes. Cancelling releases the place to the waitlist.'],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function reviews(array $event): array
    {
        return [
            [
                'name' => 'Priya Shah',
                'initials' => 'PS',
                'tone' => 'mint',
                'rating' => '5',
                'title' => 'The description matched the pace',
                'body' => 'Arrival was calm, the organizer stayed reachable, and the quiet option was real.',
                'meta' => 'Verified attendee · Previous edition',
            ],
            [
                'name' => 'Noah Patel',
                'initials' => 'NP',
                'tone' => 'paper',
                'rating' => '4',
                'title' => 'Clear accessibility information',
                'body' => 'The step-free route and rest area were easy to find. A second sign would help at the entrance.',
                'meta' => 'Verified attendee · Previous edition',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<string, mixed>
     */
    private function analytics(array $event): array
    {
        $views = max(184, $event['base_attendees'] * 18);
        $opened = (int) round($views * 0.42);
        $started = (int) round($opened * 0.38);
        $completed = $event['base_attendees'];

        return [
            'metrics' => [
                ['label' => 'Event views', 'value' => (string) $views, 'detail' => 'aggregate views'],
                ['label' => 'Page opens', 'value' => (string) $opened, 'detail' => 'from discovery'],
                ['label' => 'Started', 'value' => (string) $started, 'detail' => 'registration attempts'],
                ['label' => 'Confirmed', 'value' => (string) $completed, 'detail' => 'current places'],
                ['label' => 'Attendance', 'value' => '86%', 'detail' => 'previous edition'],
                ['label' => 'Safety', 'value' => '0', 'detail' => 'open incidents'],
            ],
            'funnel' => [
                ['label' => 'Saw the card', 'value' => $views, 'percent' => 100],
                ['label' => 'Opened event', 'value' => $opened, 'percent' => (int) round(($opened / $views) * 100)],
                ['label' => 'Started registration', 'value' => $started, 'percent' => (int) round(($started / $views) * 100)],
                ['label' => 'Confirmed', 'value' => $completed, 'percent' => (int) round(($completed / $views) * 100)],
            ],
            'privacy_note' => 'Only aggregate registration, attendance, and feedback data is shown. Private health, behavior, location, search, cancellation, and guest-contact details are excluded.',
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function applications(array $event): array
    {
        if (! $event['managed_by_current_user']) {
            return [];
        }

        return [
            [
                'key' => 'ari-mochi',
                'name' => 'Ari & Mochi',
                'detail' => 'Medium dog · parallel introduction requested',
                'initials' => 'AM',
                'tone' => 'sun',
                'status' => 'Pending review',
            ],
            [
                'key' => 'noah-juniper',
                'name' => 'Noah & Juniper',
                'detail' => 'Large dog · calm pace',
                'initials' => 'NJ',
                'tone' => 'mint',
                'status' => 'Pending review',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    private function waitlist(array $event): array
    {
        if (! $event['managed_by_current_user']) {
            return [];
        }

        return [
            [
                'key' => 'lena-pip',
                'name' => 'Lena & Pip',
                'detail' => 'First in line · 12 minutes to confirm after promotion',
                'initials' => 'LP',
                'tone' => 'paper',
                'status' => 'Waiting',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, mixed>>
     */
    private function ticketOptions(array $event): array
    {
        if ($event['ticket_model'] === 'free') {
            return [
                [
                    'key' => 'standard',
                    'title' => 'Standard place',
                    'description' => $event['pets_allowed'] ? 'One owner and one selected pet.' : 'One registered attendee.',
                    'price_minor' => 0,
                    'currency' => $event['currency'],
                ],
            ];
        }

        return [
            [
                'key' => 'standard',
                'title' => $event['format'] === 'online' ? 'Live webinar' : 'Standard ticket',
                'description' => $event['format'] === 'online' ? 'Live access, recording, slides, and cited resources.' : 'One owner and one selected pet.',
                'price_minor' => $event['price_minor'],
                'currency' => $event['currency'],
            ],
            [
                'key' => 'owner-only',
                'title' => 'Owner only',
                'description' => 'Attend without a pet where the event format allows it.',
                'price_minor' => max(0, $event['price_minor'] - 1000),
                'currency' => $event['currency'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $event
     * @return array<int, array{label: string, done: bool}>
     */
    private function checklist(array $event): array
    {
        return [
            ['label' => 'Review the event rules and cancellation terms', 'done' => true],
            ['label' => $event['pets_allowed'] ? 'Choose the attending pet profile' : 'Confirm owner-only attendance', 'done' => false],
            ['label' => $event['format'] === 'online' ? 'Test browser audio and captions' : 'Save the arrival and accessibility notes', 'done' => false],
            ['label' => $event['price_minor'] > 0 ? 'Complete the prototype payment reservation' : 'Confirm the free place', 'done' => false],
        ];
    }
}
