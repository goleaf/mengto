<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class PrimaryNavigation extends Component
{
    /** @var array<int, array<string, string>> */
    public array $items = [
        ['route' => 'preview.feed', 'label' => 'Feed', 'mobile_label' => 'Feed', 'icon' => 'house', 'name' => 'feed'],
        ['route' => 'pets.index', 'label' => 'Pets', 'mobile_label' => 'Pets', 'icon' => 'paw-print', 'name' => 'pets'],
        ['route' => 'medical-records.index', 'label' => 'Health', 'mobile_label' => 'Health', 'icon' => 'heart-pulse', 'name' => 'health'],
        ['route' => 'care-journals.index', 'label' => 'Care', 'mobile_label' => 'Care', 'icon' => 'notebook-tabs', 'name' => 'care'],
        ['route' => 'meetups.index', 'label' => 'Meetups', 'mobile_label' => 'Meet', 'icon' => 'calendar-days', 'name' => 'meetups'],
        ['route' => 'places.index', 'label' => 'Places', 'mobile_label' => 'Places', 'icon' => 'map-pinned', 'name' => 'places'],
        ['route' => 'lost-found.index', 'label' => 'Lost & found', 'mobile_label' => 'Lost', 'icon' => 'scan-search', 'name' => 'lost-found'],
        ['route' => 'marketplace.index', 'label' => 'Market', 'mobile_label' => 'Market', 'icon' => 'store', 'name' => 'marketplace'],
        ['route' => 'experts.index', 'label' => 'Experts', 'mobile_label' => 'Experts', 'icon' => 'stethoscope', 'name' => 'experts'],
        ['route' => 'forum.index', 'label' => 'Forum', 'mobile_label' => 'Forum', 'icon' => 'messages-square', 'name' => 'forum'],
        ['route' => 'groups.index', 'label' => 'Groups', 'mobile_label' => 'Group', 'icon' => 'users-round', 'name' => 'groups'],
        ['route' => 'neighbors.index', 'label' => 'Neighbors', 'mobile_label' => 'People', 'icon' => 'user-round', 'name' => 'neighbors'],
        ['route' => 'discover.index', 'label' => 'Discover', 'mobile_label' => 'Find', 'icon' => 'search', 'name' => 'discover'],
    ];

    public function __construct(
        public string $activeSection,
        public string $variant = 'desktop',
    ) {}

    public function render(): View
    {
        return view('components.primary-navigation');
    }
}
