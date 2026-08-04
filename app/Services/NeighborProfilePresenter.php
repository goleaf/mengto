<?php

declare(strict_types=1);

namespace App\Services;

final class NeighborProfilePresenter
{
    /**
     * @param  array{name: string, location: string, avatar: string, summary: string}  $owner
     * @param  array<int, array<string, mixed>>  $recentMoments
     * @return array<string, mixed>
     */
    public function present(array $owner, array $recentMoments, bool $followed): array
    {
        $mutualNeighbors = $this->mutualNeighbors();
        $mutualCount = count($mutualNeighbors);

        return [
            'owner' => $owner,
            'copy' => $this->copy($mutualCount),
            'neighbor' => $this->neighbor($followed, $mutualCount),
            'pet' => $this->pet(),
            'mutualNeighbors' => $mutualNeighbors,
            'communities' => $this->communities(),
            'recentMoments' => $recentMoments,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function copy(int $mutualCount): array
    {
        return [
            'page' => [
                'title' => __('neighbors.profile.page.title'),
                'back' => __('neighbors.profile.page.back'),
                'actions_label' => __('neighbors.profile.page.actions_label', [
                    'name' => __('neighbors.profile.identity.name'),
                ]),
            ],
            'hero' => [
                'summary_label' => __('neighbors.profile.hero.summary_label'),
                'summary_unavailable' => __('neighbors.profile.hero.summary_unavailable'),
            ],
            'about' => [
                'eyebrow' => __('neighbors.profile.sections.about.eyebrow'),
                'title' => __('neighbors.profile.sections.about.title'),
                'icon' => 'user-round',
            ],
            'interests' => [
                'title' => __('neighbors.profile.sections.interests.title'),
                'empty' => __('neighbors.profile.sections.interests.empty'),
                'icon' => 'sparkles',
            ],
            'mutual_neighbors' => [
                'title' => __('neighbors.profile.sections.mutual_neighbors.title'),
                'count' => trans_choice('neighbors.profile.sections.mutual_neighbors.count', $mutualCount, [
                    'count' => $mutualCount,
                ]),
                'empty' => __('neighbors.profile.sections.mutual_neighbors.empty'),
                'icon' => 'users',
            ],
            'communities' => [
                'title' => __('neighbors.profile.sections.communities.title'),
                'empty' => __('neighbors.profile.sections.communities.empty'),
                'icon' => 'users-round',
            ],
            'moments' => [
                'eyebrow' => __('neighbors.profile.sections.moments.eyebrow'),
                'title' => __('neighbors.profile.sections.moments.title'),
                'empty' => __('neighbors.profile.sections.moments.empty'),
                'icon' => 'images',
            ],
            'pet' => [
                'lives_with' => __('neighbors.profile.pet.lives_with', [
                    'owner' => __('neighbors.profile.pet.owner_name'),
                ]),
                'traits_empty' => __('neighbors.profile.pet.traits_empty'),
                'routine_empty' => __('neighbors.profile.pet.routine_empty'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function neighbor(bool $followed, int $mutualCount): array
    {
        return [
            'key' => 'ari',
            'name' => __('neighbors.profile.identity.name'),
            'handle' => __('neighbors.profile.identity.handle'),
            'role' => __('neighbors.profile.identity.category'),
            'category' => __('neighbors.profile.identity.category'),
            'location' => __('neighbors.profile.identity.location'),
            'distance' => __('neighbors.profile.identity.distance'),
            'member_since' => __('neighbors.profile.identity.member_since'),
            'status' => __('neighbors.profile.identity.status'),
            'bio' => __('neighbors.profile.identity.bio'),
            'avatar' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&crop=faces&w=480&h=480&q=85',
            'avatar_alt' => __('neighbors.profile.identity.avatar_alt'),
            'cover_image' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=1600&h=720&q=85',
            'cover_image_small' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=720&h=480&q=80',
            'cover_image_medium' => 'https://images.unsplash.com/photo-1748835600580-8a57c3f168af?auto=format&fit=crop&w=1200&h=600&q=82',
            'cover_image_alt' => __('neighbors.profile.identity.cover_image_alt'),
            'mutual_count' => $mutualCount,
            'stats' => [
                [
                    'label' => __('neighbors.profile.stats.pet.label'),
                    'value' => __('neighbors.profile.pet.name'),
                    'detail' => __('neighbors.profile.stats.pet.detail'),
                ],
                [
                    'label' => __('neighbors.profile.stats.mutuals.label'),
                    'value' => (string) $mutualCount,
                    'detail' => __('neighbors.profile.stats.mutuals.detail'),
                ],
                [
                    'label' => __('neighbors.profile.stats.home.label'),
                    'value' => __('neighbors.profile.stats.home.value'),
                    'detail' => __('neighbors.profile.stats.home.detail'),
                ],
            ],
            'interests' => [
                __('neighbors.profile.interests.city_walks'),
                __('neighbors.profile.interests.training'),
                __('neighbors.profile.interests.quiet_patios'),
                __('neighbors.profile.interests.urban_routines'),
            ],
            'followed' => $followed,
            'actions' => [
                [
                    'label' => __('neighbors.profile.actions.follow'),
                    'icon' => 'user-plus',
                    'endpoint' => route('actions.perform'),
                    'payload' => [
                        'action' => 'toggle-follow',
                        'target' => 'ari',
                        'label' => __('neighbors.profile.identity.name'),
                    ],
                    'variant' => 'primary',
                    'active' => $followed,
                    'active_label' => __('neighbors.profile.actions.following'),
                    'active_icon' => 'user-check',
                    'pressed' => $followed,
                ],
                [
                    'label' => __('neighbors.profile.actions.message'),
                    'icon' => 'message-circle',
                    'href' => route('messages.index'),
                    'variant' => 'paper',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pet(): array
    {
        return [
            'name' => __('neighbors.profile.pet.name'),
            'owner_name' => __('neighbors.profile.pet.owner_name'),
            'breed' => __('neighbors.profile.pet.breed'),
            'age' => __('neighbors.profile.pet.age'),
            'status' => __('neighbors.profile.pet.status'),
            'image' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=1200&h=900&q=85',
            'image_small' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=576&h=432&q=80',
            'image_medium' => 'https://images.unsplash.com/photo-1765193091032-da4cc0f568e8?auto=format&fit=crop&w=900&h=675&q=82',
            'image_alt' => __('neighbors.profile.pet.image_alt'),
            'traits' => [
                __('neighbors.profile.pet.traits.patient_hellos'),
                __('neighbors.profile.pet.traits.city_confident'),
                __('neighbors.profile.pet.traits.treat_motivated'),
            ],
            'routine' => [
                [
                    'label' => __('neighbors.profile.pet.routine.route_label'),
                    'value' => __('neighbors.profile.pet.routine.route_value'),
                    'icon' => 'route',
                ],
                [
                    'label' => __('neighbors.profile.pet.routine.time_label'),
                    'value' => __('neighbors.profile.pet.routine.time_value'),
                    'icon' => 'sunrise',
                ],
                [
                    'label' => __('neighbors.profile.pet.routine.cafe_label'),
                    'value' => __('neighbors.profile.pet.routine.cafe_value'),
                    'icon' => 'coffee',
                ],
            ],
            'walk_action' => [
                'label' => __('neighbors.profile.actions.plan_walk'),
                'icon' => 'footprints',
                'endpoint' => route('actions.perform'),
                'payload' => [
                    'action' => 'plan-walk',
                    'target' => 'mochi',
                    'label' => __('neighbors.profile.pet.name'),
                ],
                'variant' => 'paper',
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, initials: string, context: string, tone: string}>
     */
    private function mutualNeighbors(): array
    {
        return [
            [
                'name' => __('neighbors.profile.mutual_neighbors.mia.name'),
                'initials' => 'MC',
                'context' => __('neighbors.profile.mutual_neighbors.mia.context'),
                'tone' => 'sun',
            ],
            [
                'name' => __('neighbors.profile.mutual_neighbors.jamie.name'),
                'initials' => 'JC',
                'context' => __('neighbors.profile.mutual_neighbors.jamie.context'),
                'tone' => 'mint',
            ],
            [
                'name' => __('neighbors.profile.mutual_neighbors.noah.name'),
                'initials' => 'NP',
                'context' => __('neighbors.profile.mutual_neighbors.noah.context'),
                'tone' => 'paper',
            ],
            [
                'name' => __('neighbors.profile.mutual_neighbors.lena.name'),
                'initials' => 'LB',
                'context' => __('neighbors.profile.mutual_neighbors.lena.context'),
                'tone' => 'mint',
            ],
        ];
    }

    /**
     * @return array<int, array{name: string, topic: string, members: string, icon: string}>
     */
    private function communities(): array
    {
        return [
            [
                'name' => __('neighbors.profile.communities.apartment_pets.name'),
                'topic' => __('neighbors.profile.communities.apartment_pets.topic'),
                'members' => __('neighbors.profile.communities.apartment_pets.members'),
                'icon' => 'building-2',
            ],
            [
                'name' => __('neighbors.profile.communities.trail_tails.name'),
                'topic' => __('neighbors.profile.communities.trail_tails.topic'),
                'members' => __('neighbors.profile.communities.trail_tails.members'),
                'icon' => 'trees',
            ],
        ];
    }
}
