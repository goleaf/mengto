<?php

namespace App\Services;

final class GroupCatalog
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
            'route' => 'groups.show',
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
                'name' => __('groups.catalog.apartment_pets.name'),
                'category' => __('groups.catalog.apartment_pets.category'),
                'group_type' => 'interest',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => __('groups.catalog.apartment_pets.location'),
                'local' => true,
                'language' => __('groups.catalog.apartment_pets.language'),
                'member_count' => 2418,
                'pet_count' => 1640,
                'posts_week' => 86,
                'activity_score' => 96,
                'started' => '2021',
                'topic' => __('groups.catalog.apartment_pets.topic'),
                'description' => __('groups.catalog.apartment_pets.description'),
                'long_description' => __('groups.catalog.apartment_pets.long_description'),
                'organizer' => __('groups.catalog.apartment_pets.organizer'),
                'organizer_role' => __('groups.catalog.apartment_pets.organizer_role'),
                'organizer_initials' => 'AJ',
                'image' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1655306963086-a34411c0915b?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('groups.catalog.apartment_pets.image_alt'),
                'tags' => __('groups.catalog.apartment_pets.tags'),
                'recommendation_reason' => __('groups.catalog.apartment_pets.recommendation_reason'),
                'requirements' => __('groups.catalog.apartment_pets.requirements'),
                'next_event' => __('groups.catalog.apartment_pets.next_event'),
            ],
            'trail-tails' => [
                'key' => 'trail-tails',
                'name' => __('groups.catalog.trail_tails.name'),
                'category' => __('groups.catalog.trail_tails.category'),
                'group_type' => 'interest',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => __('groups.catalog.trail_tails.location'),
                'local' => true,
                'language' => __('groups.catalog.trail_tails.language'),
                'member_count' => 8120,
                'pet_count' => 6034,
                'posts_week' => 214,
                'activity_score' => 99,
                'started' => '2019',
                'topic' => __('groups.catalog.trail_tails.topic'),
                'description' => __('groups.catalog.trail_tails.description'),
                'long_description' => __('groups.catalog.trail_tails.long_description'),
                'organizer' => __('groups.catalog.trail_tails.organizer'),
                'organizer_role' => __('groups.catalog.trail_tails.organizer_role'),
                'organizer_initials' => 'NP',
                'image' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1646640237574-34c1c733f452?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('groups.catalog.trail_tails.image_alt'),
                'tags' => __('groups.catalog.trail_tails.tags'),
                'recommendation_reason' => __('groups.catalog.trail_tails.recommendation_reason'),
                'requirements' => __('groups.catalog.trail_tails.requirements'),
                'next_event' => __('groups.catalog.trail_tails.next_event'),
            ],
            'cat-people' => [
                'key' => 'cat-people',
                'name' => __('groups.catalog.cat_people.name'),
                'category' => __('groups.catalog.cat_people.category'),
                'group_type' => 'species',
                'privacy' => 'closed',
                'official' => false,
                'verified_label' => null,
                'location' => __('groups.catalog.cat_people.location'),
                'local' => true,
                'language' => __('groups.catalog.cat_people.language'),
                'member_count' => 1934,
                'pet_count' => 2280,
                'posts_week' => 72,
                'activity_score' => 91,
                'started' => '2022',
                'topic' => __('groups.catalog.cat_people.topic'),
                'description' => __('groups.catalog.cat_people.description'),
                'long_description' => __('groups.catalog.cat_people.long_description'),
                'organizer' => __('groups.catalog.cat_people.organizer'),
                'organizer_role' => __('groups.catalog.cat_people.organizer_role'),
                'organizer_initials' => 'LB',
                'image' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1754894991524-edfa22d8296c?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('groups.catalog.cat_people.image_alt'),
                'tags' => __('groups.catalog.cat_people.tags'),
                'recommendation_reason' => __('groups.catalog.cat_people.recommendation_reason'),
                'requirements' => __('groups.catalog.cat_people.requirements'),
                'next_event' => __('groups.catalog.cat_people.next_event'),
            ],
            'foster-network' => [
                'key' => 'foster-network',
                'name' => __('groups.catalog.foster_network.name'),
                'category' => __('groups.catalog.foster_network.category'),
                'group_type' => 'adoption',
                'privacy' => 'closed',
                'official' => true,
                'verified_label' => __('groups.catalog.foster_network.verified_label'),
                'location' => __('groups.catalog.foster_network.location'),
                'local' => true,
                'language' => __('groups.catalog.foster_network.language'),
                'member_count' => 1420,
                'pet_count' => 816,
                'posts_week' => 48,
                'activity_score' => 89,
                'started' => '2020',
                'topic' => __('groups.catalog.foster_network.topic'),
                'description' => __('groups.catalog.foster_network.description'),
                'long_description' => __('groups.catalog.foster_network.long_description'),
                'organizer' => __('groups.catalog.foster_network.organizer'),
                'organizer_role' => __('groups.catalog.foster_network.organizer_role'),
                'organizer_initials' => 'RC',
                'image' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605484649538-98578113d4f1?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('groups.catalog.foster_network.image_alt'),
                'tags' => __('groups.catalog.foster_network.tags'),
                'recommendation_reason' => __('groups.catalog.foster_network.recommendation_reason'),
                'requirements' => __('groups.catalog.foster_network.requirements'),
                'next_event' => __('groups.catalog.foster_network.next_event'),
            ],
            'portland-labradors' => [
                'key' => 'portland-labradors',
                'name' => __('groups.catalog.portland_labradors.name'),
                'category' => __('groups.catalog.portland_labradors.category'),
                'group_type' => 'breed',
                'privacy' => 'public',
                'official' => false,
                'verified_label' => null,
                'location' => __('groups.catalog.portland_labradors.location'),
                'local' => true,
                'language' => __('groups.catalog.portland_labradors.language'),
                'member_count' => 986,
                'pet_count' => 1108,
                'posts_week' => 39,
                'activity_score' => 84,
                'started' => '2023',
                'topic' => __('groups.catalog.portland_labradors.topic'),
                'description' => __('groups.catalog.portland_labradors.description'),
                'long_description' => __('groups.catalog.portland_labradors.long_description'),
                'organizer' => __('groups.catalog.portland_labradors.organizer'),
                'organizer_role' => __('groups.catalog.portland_labradors.organizer_role'),
                'organizer_initials' => 'JC',
                'image' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('groups.catalog.portland_labradors.image_alt'),
                'tags' => __('groups.catalog.portland_labradors.tags'),
                'recommendation_reason' => __('groups.catalog.portland_labradors.recommendation_reason'),
                'requirements' => __('groups.catalog.portland_labradors.requirements'),
                'next_event' => __('groups.catalog.portland_labradors.next_event'),
            ],
            'senior-companions' => [
                'key' => 'senior-companions',
                'name' => __('groups.catalog.senior_companions.name'),
                'category' => __('groups.catalog.senior_companions.category'),
                'group_type' => 'care',
                'privacy' => 'closed',
                'official' => true,
                'verified_label' => __('groups.catalog.senior_companions.verified_label'),
                'location' => __('groups.catalog.senior_companions.location'),
                'local' => false,
                'language' => __('groups.catalog.senior_companions.language'),
                'member_count' => 3260,
                'pet_count' => 2884,
                'posts_week' => 104,
                'activity_score' => 94,
                'started' => '2018',
                'topic' => __('groups.catalog.senior_companions.topic'),
                'description' => __('groups.catalog.senior_companions.description'),
                'long_description' => __('groups.catalog.senior_companions.long_description'),
                'organizer' => __('groups.catalog.senior_companions.organizer'),
                'organizer_role' => __('groups.catalog.senior_companions.organizer_role'),
                'organizer_initials' => 'EP',
                'image' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1600&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=720&h=480&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=1200&h=600&q=82',
                'thumbnail' => 'https://images.unsplash.com/photo-1605568427561-40dd23c2acea?auto=format&fit=crop&w=240&h=240&q=80',
                'image_alt' => __('groups.catalog.senior_companions.image_alt'),
                'tags' => __('groups.catalog.senior_companions.tags'),
                'recommendation_reason' => __('groups.catalog.senior_companions.recommendation_reason'),
                'requirements' => __('groups.catalog.senior_companions.requirements'),
                'next_event' => __('groups.catalog.senior_companions.next_event'),
            ],
        ];
    }
}
