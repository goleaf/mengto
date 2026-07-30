@if ($variant === 'mobile')
    <nav {{ $attributes->class(['mobile-dock', 'fixed inset-x-0 bottom-0 z-20 border-t border-paw-line/80 bg-paw-cream/95 px-4 pt-2 backdrop-blur sm:px-6 xl:hidden']) }} aria-label="{{ __('ui.mobile_preview_navigation_2e6900f8d4') }}">
        <div class="mobile-nav__rail">
            @forelse (array_slice($items, 0, 11) as $item)
                <x-mobile-nav-item
                    :href="route($item['route'])"
                    :label="$item['mobile_label']"
                    :icon="$item['icon']"
                    :name="$item['name']"
                    :active="$activeSection === $item['name']"
                />
            @empty
                <span class="sr-only">{{ __('ui.navigation_unavailable_c587c01a09') }}</span>
            @endforelse
        </div>
    </nav>
@else
    <nav {{ $attributes->class(['hidden items-center gap-1 xl:flex']) }} aria-label="{{ __('ui.primary_navigation_e1bfe7eccc') }}">
        @forelse (array_slice($items, 0, 11) as $item)
            <x-desktop-nav-item
                :href="route($item['route'])"
                :label="$item['label']"
                :name="$item['name']"
                :active="$activeSection === $item['name']"
            />
        @empty
            <span class="sr-only">{{ __('ui.navigation_unavailable_c587c01a09') }}</span>
        @endforelse
    </nav>
@endif
