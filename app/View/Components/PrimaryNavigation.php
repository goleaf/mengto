<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PrimaryNavigation extends Component
{
    /**
     * @var list<array{route: string, icon: string, name: string}>
     */
    private const DESTINATIONS = [
        ['route' => 'preview.feed', 'icon' => 'house', 'name' => 'feed'],
        ['route' => 'pets.index', 'icon' => 'paw-print', 'name' => 'pets'],
        ['route' => 'medical-records.index', 'icon' => 'heart-pulse', 'name' => 'health'],
        ['route' => 'care-journals.index', 'icon' => 'notebook-tabs', 'name' => 'care'],
        ['route' => 'meetups.index', 'icon' => 'calendar-days', 'name' => 'meetups'],
        ['route' => 'places.index', 'icon' => 'map-pinned', 'name' => 'places'],
        ['route' => 'lost-found.index', 'icon' => 'scan-search', 'name' => 'lost-found'],
        ['route' => 'marketplace.index', 'icon' => 'store', 'name' => 'marketplace'],
        ['route' => 'experts.index', 'icon' => 'stethoscope', 'name' => 'experts'],
        ['route' => 'forum.index', 'icon' => 'messages-square', 'name' => 'forum'],
        ['route' => 'groups.index', 'icon' => 'users-round', 'name' => 'groups'],
        ['route' => 'neighbors.index', 'icon' => 'user-round', 'name' => 'neighbors'],
        ['route' => 'discover.index', 'icon' => 'search', 'name' => 'discover'],
    ];

    /** @var list<array{route: string, label: string, mobile_label: string, icon: string, name: string}> */
    public array $items;

    public function __construct(
        public string $activeSection,
        public string $variant = 'desktop',
    ) {
        $this->items = array_map(
            static fn (array $destination): array => [
                ...$destination,
                'label' => __("navigation.items.{$destination['name']}.label"),
                'mobile_label' => __("navigation.items.{$destination['name']}.mobile_label"),
            ],
            self::DESTINATIONS,
        );
    }

    public function render(): View
    {
        return view('components.primary-navigation');
    }
}
