@props([
    'pets',
])

<nav aria-label="{{ __('ui.choose_one_of_your_pets_5ff38ca14c') }}" {{ $attributes->class(['pet-switcher']) }}>
    <span class="pet-switcher__label">{{ __('ui.managing_friendships_for_193f7e8d60') }}</span>

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
            <span class="text-sm text-paw-muted">{{ __('ui.no_managed_pet_profiles_8831d42c02') }}</span>
        @endforelse
    </div>
</nav>
