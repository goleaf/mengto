<?php

namespace App\Services;

final class ThreadCatalog
{
    /**
     * @param  array<string, mixed>  $post
     * @return array<int, array{author: string, pet: string, initials: string, tone: string, body: string, time: string, datetime: string, mine: bool}>
     */
    public function comments(array $post): array
    {
        return [];
    }

    /**
     * @return array<int, array{icon: string, title: string, description: string}>
     */
    public function guide(): array
    {
        return [
            [
                'icon' => 'heart-handshake',
                'title' => __('messages.lead_with_care'),
                'description' => __('messages.share_context_that_helps_pets_and_people_feel_understood'),
            ],
            [
                'icon' => 'map-pin',
                'title' => __('messages.keep_it_local'),
                'description' => __('messages.add_useful_route_place_timing_or_accessibility_details'),
            ],
            [
                'icon' => 'shield-check',
                'title' => __('messages.protect_privacy'),
                'description' => __('messages.keep_personal_addresses_and_sensitive_care_details_in_direct_messages'),
            ],
        ];
    }
}
