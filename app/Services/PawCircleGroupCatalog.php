<?php

namespace App\Services;

final class PawCircleGroupCatalog
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return array_values($this->records());
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(string $key): ?array
    {
        return $this->records()[$key] ?? null;
    }

    /**
     * @return array{target: string, label: string, route: string, route_parameters: array<string, string>}|null
     */
    public function reportContext(string $target): ?array
    {
        $group = $this->find($target);

        if ($group === null) {
            return null;
        }

        return [
            'target' => $target,
            'label' => $group['name'],
            'route' => 'pet-social.groups.show',
            'route_parameters' => ['group' => $target],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function records(): array
    {
        return [
            'apartment-pets' => [
                'key' => 'apartment-pets',
                'name' => 'Apartment Pets PDX',
                'category' => 'Home life',
                'group_type' => 'interest',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => 'Portland, Oregon',
                'local' => true,
                'language' => 'English',
                'member_count' => 2418,
                'pet_count' => 1640,
                'posts_week' => 86,
                'activity_score' => 96,
                'started' => '2021',
                'topic' => 'Small-space routines',
                'description' => 'Practical enrichment, calm-building routines, and neighbor-friendly ideas for pets in smaller homes.',
                'long_description' => 'A practical local circle for people sharing apartments, studios, and compact homes with pets. Members compare real routines for sound, hallways, elevators, indoor play, and safe outdoor access.',
                'organizer' => 'Ari Jensen',
                'organizer_role' => 'Owner · Community organizer',
                'organizer_initials' => 'AJ',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Dog and cat resting together in a compact home',
                'tags' => ['apartments', 'indoor enrichment', 'Portland'],
                'recommendation_reason' => 'Popular with pet owners in your city',
                'requirements' => ['Agree to the community rules', 'Keep exact home addresses private'],
                'next_event' => 'Quiet-home enrichment clinic · Saturday',
            ],
            'trail-tails' => [
                'key' => 'trail-tails',
                'name' => 'Trail Tails Portland',
                'category' => 'Outdoors',
                'group_type' => 'interest',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => 'Portland metro',
                'local' => true,
                'language' => 'English',
                'member_count' => 8120,
                'pet_count' => 6034,
                'posts_week' => 214,
                'activity_score' => 99,
                'started' => '2019',
                'topic' => 'Hikes, route reports, and trail safety',
                'description' => 'Plan trail days, share seasonal conditions, and compare low-pressure routes around Portland.',
                'long_description' => 'Trail Tails connects outdoor-minded owners without turning every outing into a race. Route reports cover shade, water, leash rules, surfaces, parking, and quiet alternatives for pets who need more space.',
                'organizer' => 'Noah Patel',
                'organizer_role' => 'Administrator · Trail host',
                'organizer_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Dogs running together beside a wooded trail',
                'tags' => ['trail walks', 'route reports', 'outdoors'],
                'recommendation_reason' => 'Scout follows outdoor walking topics',
                'requirements' => ['Share public meeting points only', 'Follow posted leash rules'],
                'next_event' => 'Forest Park shaded loop · Sunday',
            ],
            'cat-people' => [
                'key' => 'cat-people',
                'name' => 'Cat People of Portland',
                'category' => 'Cats',
                'group_type' => 'species',
                'privacy' => 'closed',
                'official' => false,
                'verified_label' => null,
                'location' => 'Portland, Oregon',
                'local' => true,
                'language' => 'English',
                'member_count' => 1934,
                'pet_count' => 2280,
                'posts_week' => 72,
                'activity_score' => 91,
                'started' => '2022',
                'topic' => 'Indoor cats and neighborhood care',
                'description' => 'Compare enrichment, share cat-friendly local services, and help indoor companions thrive.',
                'long_description' => 'A moderated space for cat guardians to exchange indoor enrichment, carrier training, multi-cat household routines, and local service recommendations without exposing private home details.',
                'organizer' => 'Lena Brooks',
                'organizer_role' => 'Owner · Cat enrichment moderator',
                'organizer_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Two fluffy cats sitting together indoors',
                'tags' => ['cat care', 'enrichment', 'indoor life'],
                'recommendation_reason' => 'Recommended for Nori’s profile',
                'requirements' => ['Answer one joining question', 'Respect closed-group privacy'],
                'next_event' => 'Carrier confidence Q&A · Wednesday',
            ],
            'foster-network' => [
                'key' => 'foster-network',
                'name' => 'Foster Network PDX',
                'category' => 'Adoption',
                'group_type' => 'adoption',
                'privacy' => 'closed',
                'official' => true,
                'verified_label' => 'Verified shelter network',
                'location' => 'Portland metro',
                'local' => true,
                'language' => 'English + Spanish',
                'member_count' => 1420,
                'pet_count' => 816,
                'posts_week' => 48,
                'activity_score' => 89,
                'started' => '2020',
                'topic' => 'Foster support and responsible adoption',
                'description' => 'Coordinate supplies, temporary homes, transport, and thoughtful transitions with experienced volunteers.',
                'long_description' => 'A verified network for approved foster volunteers and shelter partners. Members coordinate time-sensitive care while keeping adopter, foster, and precise animal-location information inside appropriate channels.',
                'organizer' => 'Rose City Animal Aid',
                'organizer_role' => 'Verified organization',
                'organizer_initials' => 'RC',
                'image' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Foster dog resting safely on a blue couch',
                'tags' => ['foster care', 'adoption', 'volunteering'],
                'recommendation_reason' => 'Mia follows foster and adoption topics',
                'requirements' => ['Complete the volunteer profile', 'Accept confidential-location rules'],
                'next_event' => 'New foster orientation · July 31',
            ],
            'portland-labradors' => [
                'key' => 'portland-labradors',
                'name' => 'Portland Labradors',
                'category' => 'Breed',
                'group_type' => 'breed',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => 'Portland, Oregon',
                'local' => true,
                'language' => 'English',
                'member_count' => 986,
                'pet_count' => 1108,
                'posts_week' => 39,
                'activity_score' => 84,
                'started' => '2023',
                'topic' => 'Labrador life without stereotypes',
                'description' => 'Share individual routines, local walks, training progress, and care resources for Labrador families.',
                'long_description' => 'A local breed community that treats every dog as an individual. Members compare routines and resources while avoiding claims that breed alone predicts temperament, health, or compatibility.',
                'organizer' => 'Jamie Cho',
                'organizer_role' => 'Owner · Walk organizer',
                'organizer_initials' => 'JC',
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Yellow Labrador sitting outdoors',
                'tags' => ['Labradors', 'training', 'local walks'],
                'recommendation_reason' => 'A breed community connected to your pet circle',
                'requirements' => ['Breed interest is enough to join', 'No unverified breeding sales'],
                'next_event' => 'Calm riverside walk · August 2',
            ],
            'senior-companions' => [
                'key' => 'senior-companions',
                'name' => 'Gentle Senior Companions',
                'category' => 'Care',
                'group_type' => 'care',
                'privacy' => 'closed',
                'official' => true,
                'verified_label' => 'Expert-moderated community',
                'location' => 'Online · Pacific Northwest',
                'local' => false,
                'language' => 'English',
                'member_count' => 3260,
                'pet_count' => 2884,
                'posts_week' => 104,
                'activity_score' => 94,
                'started' => '2018',
                'topic' => 'Comfort, mobility, and caregiver support',
                'description' => 'A carefully moderated space for senior-pet routines, mobility, comfort, and caregiver support.',
                'long_description' => 'Owners and verified professionals share supportive routines for older pets. Community experience is clearly separated from veterinary guidance, and urgent or medication-related questions are directed to qualified care.',
                'organizer' => 'Dr. Elena Park',
                'organizer_role' => 'Verified veterinarian · Moderator',
                'organizer_initials' => 'EP',
                'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => 'Calm older dog sitting outdoors',
                'tags' => ['senior pets', 'mobility', 'caregiver support'],
                'recommendation_reason' => 'Matches your interest in thoughtful care',
                'requirements' => ['No diagnosis or dosage instructions', 'Use content warnings for sensitive updates'],
                'next_event' => 'Mobility at home webinar · August 4',
            ],
        ];
    }
}
