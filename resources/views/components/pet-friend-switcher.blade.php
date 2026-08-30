@props([
    'pets',
])

<nav aria-label="{{ __('ui.choose_one_of_your_pets') }}" {{ $attributes->class(['pet-switcher']) }}>
    <span class="pet-switcher__label">{{ __('ui.managing_friendships_for') }}</span>

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
                    <x-ui-icon name="check" size="sm" />
                @endif
            </a>
        @empty
            <span class="text-sm text-paw-muted">{{ __('ui.no_managed_pet_profiles') }}</span>
        @endforelse
    </div>
</nav>
