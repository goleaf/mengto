<?php

declare(strict_types=1);

namespace App\Services;

/**
 * The feed presenter may merge canonical current-user publications. Shared
 * demo posts and stories are seed data, not a production fallback catalog.
 */
final class FeedCatalog
{
    /** @return array<int, array<string, mixed>> */
    public function posts(): array
    {
        return [];
    }

    /** @return array<int, array<string, string|bool>> */
    public function stories(): array
    {
        return [];
    }

    /** @return array<string, array<string, string>> */
    public function modes(): array
    {
        return [
            'home' => ['label' => __('messages.for_you'), 'icon' => 'sparkles'],
            'following' => ['label' => __('messages.following'), 'icon' => 'user-check'],
            'friends' => ['label' => __('messages.friends'), 'icon' => 'users-round'],
            'pets' => ['label' => __('messages.pets'), 'icon' => 'paw-print'],
            'local' => ['label' => __('messages.local'), 'icon' => 'map-pin'],
            'groups' => ['label' => __('messages.groups'), 'icon' => 'messages-square'],
            'experts' => ['label' => __('messages.experts'), 'icon' => 'badge-check'],
            'shelters' => ['label' => __('messages.shelters'), 'icon' => 'house-heart'],
            'alerts' => ['label' => __('messages.lost_found'), 'icon' => 'siren'],
            'video' => ['label' => __('messages.video'), 'icon' => 'play'],
            'photos' => ['label' => __('messages.photos'), 'icon' => 'images'],
            'saved' => ['label' => __('messages.saved'), 'icon' => 'bookmark'],
            'drafts' => ['label' => __('messages.drafts'), 'icon' => 'file-pen-line'],
            'archive' => ['label' => __('messages.archive'), 'icon' => 'archive'],
        ];
    }

    /** @return array<string, array<string, mixed>> */
    public function mediaPresets(): array
    {
        return [
            'none' => [
                'label' => __('messages.text_only'),
                'format' => 'text',
                'media' => [],
            ],
        ];
    }

    /** @return array<string, string> */
    public function topics(): array
    {
        return [
            'walks' => __('messages.walks'),
            'care' => __('messages.care'),
            'health' => __('messages.health'),
            'training' => __('messages.training'),
            'enrichment' => __('messages.enrichment'),
            'adoption' => __('messages.adoption'),
            'lost-found' => __('messages.lost_and_found'),
            'community' => __('messages.community'),
            'photography' => __('messages.photography'),
        ];
    }

    /** @return array<string, string> */
    public function audiences(): array
    {
        return [
            'public' => __('messages.everyone'),
            'members' => __('messages.registered_members'),
            'followers' => __('messages.followers'),
            'friends' => __('messages.friends'),
            'close-friends' => __('messages.close_friends'),
            'owners' => __('messages.pet_owners_and_managers'),
            'private' => __('messages.only_me'),
        ];
    }

    /** @return array<string, string> */
    public function commentPolicies(): array
    {
        return [
            'all' => __('messages.everyone'),
            'followers' => __('messages.followers'),
            'friends' => __('messages.friends'),
            'mentioned' => __('messages.mentioned_profiles'),
            'none' => __('messages.comments_off'),
        ];
    }

    /** @return array<string, string> */
    public function safePlaces(): array
    {
        return [
            'none' => __('messages.do_not_show_a_place'),
        ];
    }

    /** @return array<string, string> */
    public function reactionOptions(bool $supportiveOnly = false): array
    {
        if ($supportiveOnly) {
            return [
                'support' => __('messages.support'),
                'useful' => __('messages.useful'),
            ];
        }

        return [
            'like' => __('messages.like'),
            'love' => __('messages.love'),
            'funny' => __('messages.funny'),
            'support' => __('messages.support'),
            'useful' => __('messages.useful'),
        ];
    }

    /** @return array<string, string> */
    public function reportReasons(): array
    {
        return [
            'spam' => __('messages.spam_or_repetitive_promotion'),
            'fraud' => __('messages.fraud_or_scam'),
            'animal-safety' => __('messages.animal_safety_concern'),
            'dangerous-advice' => __('messages.dangerous_medical_advice'),
            'stolen-media' => __('messages.stolen_photos_or_video'),
            'personal-data' => __('messages.personal_information_exposed'),
            'false-alert' => __('messages.false_lost_pet_alert'),
            'harassment' => __('messages.harassment_or_hate'),
            'other' => __('messages.other_concern'),
        ];
    }
}
