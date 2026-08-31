<?php

declare(strict_types=1);

namespace App\Services;

final class MessagePresenter
{
    /**
     * Persisted conversations are not available yet. The authenticated surface
     * deliberately exposes no shared prototype participants or messages.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function page(array $filters): array
    {
        $activeFilter = (string) ($filters['filter'] ?? 'all');

        if (! in_array($activeFilter, array_column($this->filters(), 'key'), true)) {
            $activeFilter = 'all';
        }

        return [
            'summary' => [
                'eyebrow' => __('messaging.page.eyebrow'),
                'title' => __('messaging.page.heading'),
                'description' => __('messaging.page.description'),
                'count' => __('presentation.dialogs_with_unread', [
                    'dialogs' => trans_choice('presentation.dialogs_count', 0, ['count' => 0]),
                    'unread' => __('presentation.unread_count', ['count' => 0]),
                ]),
                'unread_count' => 0,
                'request_count' => 0,
            ],
            'filters' => $this->filters(),
            'active_filter' => $activeFilter,
            'query' => (string) ($filters['q'] ?? ''),
            'conversations' => [],
            'selected' => null,
        ];
    }

    /** @return array<int, array{key: string, label: string, icon: string}> */
    private function filters(): array
    {
        return [
            ['key' => 'all', 'label' => __('messaging.folders.items.all'), 'icon' => 'inbox'],
            ['key' => 'unread', 'label' => __('messaging.folders.items.unread'), 'icon' => 'mail'],
            ['key' => 'friends', 'label' => __('messaging.folders.items.friends'), 'icon' => 'user-round'],
            ['key' => 'groups', 'label' => __('messaging.folders.items.groups'), 'icon' => 'users-round'],
            ['key' => 'events', 'label' => __('messaging.folders.items.events'), 'icon' => 'calendar-days'],
            ['key' => 'specialists', 'label' => __('messaging.folders.items.specialists'), 'icon' => 'badge-check'],
            ['key' => 'family', 'label' => __('messaging.folders.items.family'), 'icon' => 'house'],
            ['key' => 'requests', 'label' => __('messaging.folders.items.requests'), 'icon' => 'message-square-more'],
            ['key' => 'archived', 'label' => __('messaging.folders.items.archived'), 'icon' => 'archive'],
        ];
    }
}
