<?php

namespace App\Services;

use Illuminate\Support\Str;

final class PlaceContentCatalog
{
    /**
     * @param  array<string, mixed>  $place
     * @return array<string, mixed>
     */
    public function content(array $place): array
    {
        return [
            'gallery' => $this->gallery($place),
            'hours' => $this->hours($place),
            'rules' => $this->rules($place),
            'services' => $this->services($place),
            'facilities' => $this->facilities($place),
            'accessibility' => $this->accessibility($place),
            'safety' => $this->safety($place),
            'specialists' => $this->specialists($place),
            'reviews' => $this->reviews($place),
            'questions' => $this->questions($place),
            'updates' => $this->updates($place),
            'social' => $this->social($place),
            'weather' => $this->weather($place),
            'nearby' => $this->nearby($place),
            'analytics' => $this->analytics($place),
            'verification' => $this->verification($place),
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function gallery(array $place): array
    {
        $category = (string) $place['primary_category'];

        return [
            [
                'image' => (string) $place['image'],
                'image_small' => (string) $place['image_small'],
                'image_medium' => (string) $place['image_medium'],
                'alt' => (string) $place['image_alt'],
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? 'Current conditions' : 'Official overview',
                'date' => 'July 2026',
                'source' => $place['owner_managed'] ? 'Place profile' : 'PawCircle community',
            ],
            [
                'image' => $this->secondaryImage($category),
                'image_small' => $this->secondaryImage($category, 720, 540),
                'image_medium' => $this->secondaryImage($category, 1200, 750),
                'alt' => $this->secondaryAlt($category),
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? 'Entrance and surface' : 'Arrival and access',
                'date' => 'June 2026',
                'source' => 'Verified visitor',
            ],
            [
                'image' => $this->tertiaryImage($category),
                'image_small' => $this->tertiaryImage($category, 720, 540),
                'image_medium' => $this->tertiaryImage($category, 1200, 750),
                'alt' => $this->tertiaryAlt($category),
                'label' => in_array($category, ['park', 'dog-park', 'route'], true) ? 'Facilities' : 'Service area',
                'date' => 'May 2026',
                'source' => 'Community contributor',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{day: string, hours: string, note: string}>
     */
    private function hours(array $place): array
    {
        if ($place['primary_category'] === 'emergency-vet') {
            return [
                ['day' => 'Every day', 'hours' => (string) $place['hours_summary'], 'note' => 'Call before travel when possible.'],
                ['day' => 'Overnight', 'hours' => 'Emergency triage', 'note' => (string) $place['special_hours']],
            ];
        }

        if (in_array($place['primary_category'], ['park', 'dog-park', 'route'], true)) {
            return [
                ['day' => 'Monday–Friday', 'hours' => (string) $place['hours_summary'], 'note' => 'Conditions may change after storms.'],
                ['day' => 'Saturday–Sunday', 'hours' => (string) $place['hours_summary'], 'note' => (string) $place['special_hours']],
            ];
        }

        return [
            ['day' => 'Monday–Friday', 'hours' => (string) $place['hours_summary'], 'note' => 'Check special hours before travel.'],
            ['day' => 'Weekend', 'hours' => 'See current place schedule', 'note' => (string) $place['special_hours']],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, icon: string}>
     */
    private function rules(array $place): array
    {
        return array_map(
            static fn (string $rule, int $index): array => [
                'title' => 'Rule '.($index + 1),
                'detail' => $rule,
                'icon' => ['shield-check', 'paw-print', 'circle-alert'][$index % 3],
            ],
            $place['rules'],
            array_keys($place['rules']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, status: string}>
     */
    private function services(array $place): array
    {
        $prices = $place['pricing'];

        return array_map(
            static fn (string $service, int $index): array => [
                'title' => Str::headline($service),
                'detail' => array_values($prices)[$index % max(1, count($prices))] ?? 'Ask the place for current details.',
                'status' => in_array($place['primary_category'], ['pet-store', 'grooming', 'vet', 'emergency-vet'], true)
                    ? 'Availability may change'
                    : 'Available',
            ],
            $place['services'],
            array_keys($place['services']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string, icon: string}>
     */
    private function facilities(array $place): array
    {
        return array_map(
            static fn (string $feature, int $index): array => [
                'label' => Str::headline($feature),
                'value' => 'Listed by '.($place['owner_managed'] ? 'the place' : 'the community'),
                'icon' => ['circle-check-big', 'sparkles', 'map-pinned', 'badge-info'][$index % 4],
            ],
            $place['features'],
            array_keys($place['features']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string}>
     */
    private function accessibility(array $place): array
    {
        return array_map(
            static fn (string $item): array => [
                'label' => Str::headline($item),
                'value' => 'Available according to the latest place data',
            ],
            $place['accessibility'],
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, tone: string}>
     */
    private function safety(array $place): array
    {
        return array_map(
            static fn (string $item, int $index): array => [
                'title' => Str::headline($item),
                'detail' => $index === 0
                    ? 'Confirm current conditions before relying on this feature.'
                    : 'Use the setting that matches your pet and leave if the situation feels unsafe.',
                'tone' => $index === 0 ? 'positive' : 'neutral',
            ],
            $place['safety'],
            array_keys($place['safety']),
        );
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function specialists(array $place): array
    {
        return match ($place['primary_category']) {
            'grooming' => [
                [
                    'name' => 'Emilia V.',
                    'initials' => 'EV',
                    'role' => 'Cat and low-stress groomer',
                    'experience' => '8 years · quiet handling and senior pets',
                    'languages' => 'Lithuanian · English · Russian',
                    'verification' => 'Identity and demo studio role checked',
                ],
                [
                    'name' => 'Tomas K.',
                    'initials' => 'TK',
                    'role' => 'Coat care specialist',
                    'experience' => '6 years · de-shedding and show preparation',
                    'languages' => 'Lithuanian · English',
                    'verification' => 'Demo specialist profile',
                ],
            ],
            'vet', 'emergency-vet' => [
                [
                    'name' => 'Dr. Lina Petrauskė',
                    'initials' => 'LP',
                    'role' => $place['primary_category'] === 'emergency-vet' ? 'Emergency and avian clinician' : 'General veterinary clinician',
                    'experience' => 'Demo profile · on-site availability varies',
                    'languages' => 'Lithuanian · English',
                    'verification' => 'Prototype qualification label; not a real listing',
                ],
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, mixed>>
     */
    private function reviews(array $place): array
    {
        $category = (string) $place['primary_category'];
        $criterion = match ($category) {
            'park', 'route' => 'Quietness and path condition',
            'dog-park' => 'Fence and entrance safety',
            'vet', 'emergency-vet' => 'Communication and organization',
            'grooming' => 'Handling and communication',
            'pet-cafe' => 'Pet rules and atmosphere',
            'shelter' => 'Visit organization',
            default => 'Accuracy and service',
        };

        return [
            [
                'key' => $place['key'].'-review-one',
                'author' => 'Marta K.',
                'initials' => 'MK',
                'rating' => 5,
                'visited_with' => 'Scout',
                'verified' => true,
                'criterion' => $criterion,
                'body' => 'The description matched our visit, and the practical access notes were useful.',
                'date' => 'Jul 26, 2026',
                'owner_response' => null,
            ],
            [
                'key' => $place['key'].'-review-two',
                'author' => 'Anonymous visitor',
                'initials' => 'AV',
                'rating' => 4,
                'visited_with' => 'Pet profile hidden',
                'verified' => false,
                'criterion' => $criterion,
                'body' => 'Helpful place overall. Check the latest hours or conditions before making a special trip.',
                'date' => 'Jul 19, 2026',
                'owner_response' => $place['owner_managed']
                    ? 'Thank you. We have updated the arrival information in the profile.'
                    : null,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function questions(array $place): array
    {
        return [
            [
                'key' => $place['key'].'-question-one',
                'question' => $this->questionFor($place),
                'author' => 'Pet owner',
                'answer' => $this->answerFor($place),
                'answer_author' => $place['owner_managed'] ? 'Official place response' : 'Verified visitor',
                'answered_at' => 'Updated 2 days ago',
            ],
            [
                'key' => $place['key'].'-question-two',
                'question' => 'How current is the information on this page?',
                'author' => 'Nori’s owner',
                'answer' => (string) $place['data_freshness'],
                'answer_author' => 'PawCircle data note',
                'answered_at' => 'Current status',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array<string, string>>
     */
    private function updates(array $place): array
    {
        $updates = [
            [
                'title' => 'Information review',
                'body' => (string) $place['data_freshness'],
                'time' => 'Latest',
                'icon' => 'history',
                'status' => 'Current profile data',
            ],
        ];

        if ($place['key'] === 'old-town-pet-cafe') {
            $updates[] = [
                'title' => 'Pet access corrected',
                'body' => 'The owner confirmed that pets are now welcome on the terrace only.',
                'time' => 'Today',
                'icon' => 'badge-check',
                'status' => 'Owner confirmed',
            ];
        }

        if ($place['key'] === 'zverynas-small-dog-run') {
            $updates[] = [
                'title' => 'Gate latch warning',
                'body' => 'Four visitors confirmed a loose latch in the small-dog entrance.',
                'time' => '3 hours ago',
                'icon' => 'triangle-alert',
                'status' => 'Temporary warning',
            ];
        }

        return $updates;
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, mixed>
     */
    private function social(array $place): array
    {
        return [
            'friends' => [
                ['name' => 'Ari and Mochi', 'initials' => 'AM', 'detail' => 'Saved this place'],
                ['name' => 'Priya and Luna', 'initials' => 'PL', 'detail' => 'Visited recently'],
            ],
            'summary' => '2 friends have a privacy-permitted connection to this place',
            'story' => $place['key'] === 'zverynas-small-dog-run'
                ? 'A few dogs are here; the small-dog latch needs care.'
                : 'Latest place update is available in the timeline.',
            'story_expires' => 'Temporary stories expire after 24 hours.',
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<string, string>
     */
    private function weather(array $place): array
    {
        if (! in_array($place['primary_category'], ['park', 'dog-park', 'route'], true)) {
            return [
                'summary' => 'Indoor or service location',
                'temperature' => 'Weather is not part of this place record',
                'advisory' => 'External live weather is not connected in this prototype.',
                'source' => 'Integration boundary',
            ];
        }

        return [
            'summary' => 'Warm and dry demo conditions',
            'temperature' => '24°C · illustrative',
            'advisory' => $place['water']
                ? 'Shade and water are listed, but bring an individual bowl.'
                : 'Bring water and avoid hot surfaces.',
            'source' => 'Illustrative place guidance; no live weather provider',
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{title: string, detail: string, icon: string}>
     */
    private function nearby(array $place): array
    {
        return [
            ['title' => 'Public transport', 'detail' => 'Check current pet rules with the operator.', 'icon' => 'bus-front'],
            ['title' => 'Emergency help', 'detail' => 'Suitable clinics appear in emergency map mode.', 'icon' => 'stethoscope'],
            ['title' => 'Entrance guidance', 'detail' => (string) $place['coordinate_accuracy'], 'icon' => 'door-open'],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string, detail: string}>
     */
    private function analytics(array $place): array
    {
        return [
            ['label' => 'Profile views', 'value' => '1.4k', 'detail' => 'Aggregate demo metric'],
            ['label' => 'Route opens', 'value' => '286', 'detail' => 'No individual viewer list'],
            ['label' => 'Saves', 'value' => '114', 'detail' => 'Private collection names hidden'],
            ['label' => 'Data freshness', 'value' => 'Recent', 'detail' => (string) $place['data_freshness']],
        ];
    }

    /**
     * @param  array<string, mixed>  $place
     * @return array<int, array{label: string, value: string}>
     */
    private function verification(array $place): array
    {
        return [
            ['label' => 'Profile label', 'value' => (string) $place['verification']['label']],
            ['label' => 'Verified scope', 'value' => (string) $place['verification']['scope']],
            ['label' => 'Last checked', 'value' => (string) $place['verification']['updated_at']],
            ['label' => 'Coordinate accuracy', 'value' => (string) $place['coordinate_accuracy']],
            ['label' => 'Important limitation', 'value' => 'A verification label applies only to the named scope.'],
        ];
    }

    private function questionFor(array $place): string
    {
        return match ($place['primary_category']) {
            'park', 'route' => 'Is there enough lighting and room to keep distance?',
            'dog-park' => 'Is the small-dog entrance secure today?',
            'vet', 'emergency-vet' => 'Do you accept my pet species without an appointment?',
            'grooming' => 'Can the appointment avoid a loud dryer?',
            'pet-cafe' => 'Are pets allowed inside or only on the terrace?',
            'shelter' => 'Do I need an appointment before visiting?',
            default => 'Should I call before making a special trip?',
        };
    }

    private function answerFor(array $place): string
    {
        return match ($place['primary_category']) {
            'park', 'route' => $place['lighting']
                ? 'Main paths have listed lighting, but quieter outer areas can be darker.'
                : 'Lighting is limited; daylight visits are recommended.',
            'dog-park' => $place['key'] === 'zverynas-small-dog-run'
                ? 'The zone is open, but a temporary latch warning is active.'
                : 'The latest community check found both gates working.',
            'vet', 'emergency-vet' => 'Accepted species are listed here, but call first to confirm the current clinician and intake.',
            'grooming' => 'Yes. Quiet drying and breaks can be requested in a private care note.',
            'pet-cafe' => 'Pets are currently welcome on the terrace only.',
            'shelter' => 'Yes. Timed appointments protect animals and visitors.',
            default => 'Calling first is recommended when live availability is not connected.',
        };
    }

    private function secondaryImage(string $category, int $width = 1600, int $height = 1000): string
    {
        $id = match ($category) {
            'park', 'route' => '1501854140801-50d01698950b',
            'dog-park' => '1530281700549-e82e7bf110d6',
            'vet', 'emergency-vet' => '1559190394-df5a28aab5c5',
            'grooming' => '1518791841217-8f162f1e1131',
            'pet-cafe' => '1554118811-1e0d58224f24',
            'shelter' => '1548767797-d8c844163c4c',
            default => '1586671267731-da2cf3ceeb80',
        };

        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$width}&h={$height}&q=82";
    }

    private function tertiaryImage(string $category, int $width = 1600, int $height = 1000): string
    {
        $id = match ($category) {
            'park', 'route' => '1472396961693-142e6e269027',
            'dog-park' => '1561037404-61cd46aa615b',
            'vet', 'emergency-vet' => '1517849845537-4d257902454a',
            'grooming' => '1533738363-b7f9aef128ce',
            'pet-cafe' => '1495474472287-4d71bcdd2085',
            'shelter' => '1592754862816-1a21a4ea2281',
            default => '1556228578-8c89e6adf883',
        };

        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$width}&h={$height}&q=82";
    }

    private function secondaryAlt(string $category): string
    {
        return match ($category) {
            'park', 'route' => 'Public walking entrance with a broad path',
            'dog-park' => 'Dog exercise area with visible fencing',
            'vet', 'emergency-vet' => 'Bright veterinary waiting and consultation area',
            'grooming' => 'Quiet pet grooming workspace with clean equipment',
            'pet-cafe' => 'Pet-friendly cafe seating near an outdoor entrance',
            'shelter' => 'Calm shelter introduction area',
            default => 'Accessible entrance to the place',
        };
    }

    private function tertiaryAlt(string $category): string
    {
        return match ($category) {
            'park', 'route' => 'Rest area and natural shade beside a walking path',
            'dog-park' => 'Water and seating facilities inside a dog park',
            'vet', 'emergency-vet' => 'Veterinary professional preparing a treatment room',
            'grooming' => 'Fresh towels and quiet grooming tools',
            'pet-cafe' => 'Water bowl placed beside a cafe terrace table',
            'shelter' => 'Shelter volunteer area with pet supplies',
            default => 'Services and facilities available at the place',
        };
    }
}
