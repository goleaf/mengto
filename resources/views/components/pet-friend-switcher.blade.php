@props([
    'pets',
])

<nav aria-label="Choose one of your pets" {{ $attributes->class(['pet-switcher']) }}>
    <span class="pet-switcher__label">Managing friendships for</span>

    <div class="pet-switcher__options">
        @forelse ($pets as $pet)
            <a
                href="{{ $pet['href'] }}"
                class="pet-switcher__option"
                @if ($pet['active']) aria-current="page" @endif
            >
                <x-avatar
                    :src="$pet['image']"
                    :alt="$pet['image_alt']"
                    size="compact"
                    lazy
                />
                <span>{{ $pet['label'] }}</span>
                @if ($pet['active'])
                    <x-lucide-check class="icon icon--sm" aria-hidden="true" />
                @endif
            </a>
        @empty
            <span class="text-sm text-paw-muted">No managed pet profiles.</span>
        @endforelse
    </div>
</nav>
