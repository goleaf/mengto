@props([
    'tabs',
    'label',
])

<nav aria-label="{{ $label }}" {{ $attributes->class(['tabs']) }}>
    <div class="tabs__rail" role="list">
        @forelse ($tabs as $tab)
            <a
                href="{{ $tab['href'] }}"
                role="listitem"
                @if ($tab['active']) aria-current="page" @endif
                class="tabs__item"
            >
                @if ($tab['icon'] ?? null)
                    <x-dynamic-component
                        :component="'lucide-'.$tab['icon']"
                        class="icon icon--sm"
                        aria-hidden="true"
                    />
                @endif
                <span>{{ $tab['label'] }}</span>
                @if ($tab['count'] ?? null)
                    <span class="tabs__count">{{ $tab['count'] }}</span>
                @endif
            </a>
        @empty
            <span class="text-sm text-paw-muted">{{ __('ui.profile_sections_unavailable_a85cf2c744') }}</span>
        @endforelse
    </div>
</nav>
