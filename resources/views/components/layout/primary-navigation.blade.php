@props([
    'activeSection',
    'variant' => 'desktop',
])

@php
    $items = [
        ['route' => 'pet-social.preview', 'label' => 'Feed', 'mobile_label' => 'Feed', 'icon' => 'house', 'name' => 'feed'],
        ['route' => 'pet-social.pets.index', 'label' => 'Pets', 'mobile_label' => 'Pets', 'icon' => 'paw-print', 'name' => 'pets'],
        ['route' => 'pet-social.meetups.index', 'label' => 'Meetups', 'mobile_label' => 'Meet', 'icon' => 'calendar-days', 'name' => 'meetups'],
        ['route' => 'pet-social.groups.index', 'label' => 'Groups', 'mobile_label' => 'Group', 'icon' => 'users-round', 'name' => 'groups'],
        ['route' => 'pet-social.neighbors.index', 'label' => 'Neighbors', 'mobile_label' => 'People', 'icon' => 'user-round', 'name' => 'neighbors'],
        ['route' => 'pet-social.discover.index', 'label' => 'Discover', 'mobile_label' => 'Find', 'icon' => 'search', 'name' => 'discover'],
    ];
@endphp

@if ($variant === 'mobile')
    <nav {{ $attributes->class(['mobile-dock', 'fixed inset-x-0 bottom-0 z-20 border-t border-paw-line/80 bg-paw-cream/95 px-4 pt-2 backdrop-blur sm:px-6 lg:hidden']) }} aria-label="Mobile preview navigation">
        <div class="mobile-nav__rail">
            @forelse ($items as $item)
                <x-layout.mobile-nav-item
                    :href="route($item['route'])"
                    :label="$item['mobile_label']"
                    :icon="$item['icon']"
                    :name="$item['name']"
                    :active="$activeSection === $item['name']"
                />
            @empty
                <span class="sr-only">Navigation unavailable.</span>
            @endforelse
        </div>
    </nav>
@else
    <nav {{ $attributes->class(['hidden items-center gap-1 lg:flex']) }} aria-label="Primary navigation">
        @forelse (array_slice($items, 0, 5) as $item)
            <x-layout.desktop-nav-item
                :href="route($item['route'])"
                :label="$item['label']"
                :name="$item['name']"
                :active="$activeSection === $item['name']"
            />
        @empty
            <span class="sr-only">Navigation unavailable.</span>
        @endforelse
    </nav>
@endif
