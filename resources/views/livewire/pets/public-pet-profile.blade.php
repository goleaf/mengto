<x-page-stack data-section="canonical-pet-profile">
    <header class="grid gap-5 border-b border-paw-line pb-6 sm:grid-cols-[8rem_minmax(0,1fr)] sm:items-center">
        <div class="aspect-square w-32 overflow-hidden rounded-lg bg-paw-canvas">
            @if ($pet['avatar'] !== null)
                <img
                    src="{{ $pet['avatar'] }}"
                    alt="{{ $pet['avatar_alt'] }}"
                    class="h-full w-full object-cover"
                >
            @else
                <div class="grid h-full place-items-center" role="img" aria-label="{{ $pet['avatar_alt'] }}">
                    <x-ui-icon name="paw-print" size="3xl" />
                </div>
            @endif
        </div>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-paw-muted">{{ $pet['species'] }}</p>
            <h1 class="break-words text-3xl font-semibold">{{ $pet['name'] }}</h1>
            @if ($pet['alternative_names'] !== [])
                <div class="mt-2 flex flex-wrap gap-2" aria-label="{{ __('pet_profiles.names.public_label') }}">
                    @forelse ($pet['alternative_names'] as $alternativeName)
                        <span wire:key="public-pet-name-{{ $alternativeName['id'] }}" class="rounded-full border border-paw-line px-3 py-1 text-sm text-paw-muted" @if ($alternativeName['locale'] !== null) lang="{{ $alternativeName['locale'] }}" @endif>
                            {{ $alternativeName['name'] }} · {{ $alternativeName['type'] }}
                        </span>
                    @empty
                    @endforelse
                </div>
            @endif
            <div class="mt-3 flex flex-wrap gap-2">
                <x-status-badge :label="$pet['status']" icon="circle-check" />
                @if ($pet['breed'] !== null)
                    <x-status-badge :label="$pet['breed']" icon="dna" />
                @endif
            </div>
        </div>
    </header>

    <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(16rem,1fr)] lg:items-start">
        <section aria-labelledby="pet-profile-about-heading">
            <h2 id="pet-profile-about-heading">{{ __('pet_profiles.public.about') }}</h2>
            @if ($pet['bio'] !== '')
                <p class="mt-3 whitespace-pre-line break-words leading-7">{{ $pet['bio'] }}</p>
            @else
                <p class="mt-3 text-paw-muted">{{ __('pet_profiles.public.no_bio') }}</p>
            @endif
        </section>

        <dl class="grid gap-3 border-s border-paw-line ps-5">
            @if ($pet['scientific_name'] !== null)
                <div>
                    <dt class="text-sm text-paw-muted">{{ __('pet_profiles.fields.scientific_name') }}</dt>
                    <dd><i lang="la">{{ $pet['scientific_name'] }}</i></dd>
                </div>
            @endif
            @if ($pet['age'] !== null)
                <div>
                    <dt class="text-sm text-paw-muted">{{ __('pet_profiles.fields.age') }}</dt>
                    <dd>{{ $pet['age'] }}</dd>
                </div>
            @endif
            @if ($pet['celebration_day'] !== null)
                <div>
                    <dt class="text-sm text-paw-muted">{{ __('pet_profiles.fields.celebration_day') }}</dt>
                    <dd>{{ $pet['celebration_day'] }}</dd>
                </div>
            @endif
            @if ($pet['owner'] !== null)
                <div>
                    <dt class="text-sm text-paw-muted">{{ __('pet_profiles.fields.manager') }}</dt>
                    <dd>{{ $pet['owner'] }}</dd>
                </div>
            @endif
            @if ($pet['location'] !== null)
                <div>
                    <dt class="text-sm text-paw-muted">{{ __('pet_profiles.fields.public_location') }}</dt>
                    <dd>{{ $pet['location'] }}</dd>
                </div>
            @endif
        </dl>
    </div>
</x-page-stack>
