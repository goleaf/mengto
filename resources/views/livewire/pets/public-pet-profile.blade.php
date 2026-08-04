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
                @if ($pet['breed_origin'] !== null)
                    <x-status-badge :label="$pet['breed_origin']['summary']" icon="dna" />
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

            @if ($pet['appearance'] !== null)
                <div class="mt-6 rounded-2xl border border-paw-line bg-paw-canvas p-4" aria-labelledby="pet-profile-appearance-heading">
                    <h3 id="pet-profile-appearance-heading" class="text-lg font-semibold text-paw-ink">{{ __('pet_profiles.appearance.public_title') }}</h3>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('pet_profiles.appearance.public_notice') }}</p>
                    <dl class="mt-4 grid min-w-0 gap-3 sm:grid-cols-2">
                        @if ($pet['appearance']['primary_color'] !== null)
                            <div>
                                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.appearance.primary_color') }}</dt>
                                <dd class="break-words">{{ $pet['appearance']['primary_color'] }}</dd>
                            </div>
                        @endif
                        @if ($pet['appearance']['additional_color_list'] !== null)
                            <div>
                                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.appearance.additional_colors') }}</dt>
                                <dd class="break-words">{{ $pet['appearance']['additional_color_list'] }}</dd>
                            </div>
                        @endif
                        @if ($pet['appearance']['pattern_list'] !== null)
                            <div>
                                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.appearance.pattern_label') }}</dt>
                                <dd class="break-words">{{ $pet['appearance']['pattern_list'] }}</dd>
                            </div>
                        @endif
                        @if ($pet['appearance']['color_details'] !== '')
                            <div>
                                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.appearance.color_details') }}</dt>
                                <dd class="whitespace-pre-line break-words">{{ $pet['appearance']['color_details'] }}</dd>
                            </div>
                        @endif
                        @if ($pet['appearance']['feather_color_details'] !== '')
                            <div>
                                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.appearance.feather_color_details') }}</dt>
                                <dd class="whitespace-pre-line break-words">{{ $pet['appearance']['feather_color_details'] }}</dd>
                            </div>
                        @endif
                        @if ($pet['appearance']['scale_color_details'] !== '')
                            <div>
                                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.appearance.scale_color_details') }}</dt>
                                <dd class="whitespace-pre-line break-words">{{ $pet['appearance']['scale_color_details'] }}</dd>
                            </div>
                        @endif
                        @if ($pet['appearance']['seasonal_color_changes'] !== '')
                            <div>
                                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.appearance.seasonal_color_changes') }}</dt>
                                <dd class="whitespace-pre-line break-words">{{ $pet['appearance']['seasonal_color_changes'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif

            @if ($pet['body_covering'] !== null)
                <div class="mt-6 rounded-2xl border border-paw-line bg-paw-canvas p-4" aria-labelledby="pet-profile-body-covering-heading">
                    <h3 id="pet-profile-body-covering-heading" class="text-lg font-semibold text-paw-ink">{{ __('pet_profiles.body_covering.public_title') }}</h3>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('pet_profiles.body_covering.public_notice') }}</p>
                    <dl class="mt-4 grid min-w-0 gap-3 sm:grid-cols-2">
                        @forelse ([
                            'coat_length',
                            'coat_texture',
                            'undercoat',
                            'hairless',
                            'feather_type',
                            'mane_type',
                            'seasonal_shedding',
                        ] as $field)
                            @if ($pet['body_covering'][$field] !== null)
                                <div>
                                    <dt class="text-sm text-paw-muted">{{ __('pet_profiles.body_covering.public_fields.'.$field) }}</dt>
                                    <dd class="break-words">{{ $pet['body_covering'][$field] }}</dd>
                                </div>
                            @endif
                        @empty
                        @endforelse
                    </dl>
                </div>
            @endif

            @if ($pet['identifying_marks'] !== [])
                <div class="mt-6 rounded-2xl border border-paw-line bg-paw-canvas p-4" aria-labelledby="pet-profile-identifying-marks-heading">
                    <h3 id="pet-profile-identifying-marks-heading" class="text-lg font-semibold text-paw-ink">{{ __('pet_profiles.identifying_marks.public_title') }}</h3>
                    <p class="mt-1 text-sm leading-6 text-paw-muted">{{ __('pet_profiles.identifying_marks.public_notice') }}</p>
                    <ul class="mt-4 grid min-w-0 gap-3 sm:grid-cols-2">
                        @forelse ($pet['identifying_marks'] as $mark)
                            <li wire:key="public-pet-identifying-mark-{{ $mark['key'] }}" class="rounded-xl border border-paw-line bg-paw-surface p-3">
                                <p class="text-sm font-semibold text-paw-ink">{{ $mark['type'] }}</p>
                                <p class="mt-1 whitespace-pre-line break-words text-sm leading-6 text-paw-muted">{{ $mark['description'] }}</p>
                            </li>
                        @empty
                        @endforelse
                    </ul>
                </div>
            @endif
        </section>

        <dl class="grid gap-3 border-s border-paw-line ps-5">
            @if ($pet['breed_origin'] !== null)
                <div>
                    <dt class="text-sm text-paw-muted">{{ __('pet_profiles.breed_origin.type_label') }}</dt>
                    <dd>{{ $pet['breed_origin']['type'] }}</dd>
                    @if ($pet['breed_origin']['origins'] !== [])
                        <ul class="mt-2 grid gap-2" aria-label="{{ __('pet_profiles.breed_origin.entries_label') }}">
                            @forelse ($pet['breed_origin']['origins'] as $origin)
                                <li wire:key="public-pet-breed-origin-{{ $origin['key'] }}" class="rounded-xl border border-paw-line px-3 py-2">
                                    <p class="font-semibold">{{ $origin['name'] }}</p>
                                    <p class="text-sm text-paw-muted">
                                        {{ $origin['confidence'] }} · {{ $origin['source'] }}@if ($origin['share'] !== null) · {{ $origin['share'] }}@endif
                                    </p>
                                </li>
                            @empty
                            @endforelse
                        </ul>
                    @endif
                    <p class="mt-2 text-sm text-paw-muted">{{ $pet['breed_origin']['notice'] }}</p>
                </div>
            @endif
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
            <div>
                <dt class="text-sm text-paw-muted">{{ __('pet_profiles.fields.life_stage') }}</dt>
                <dd>{{ $pet['life_stage']['label'] }}</dd>
                <dd class="text-sm text-paw-muted">{{ $pet['life_stage']['source_label'] }}</dd>
            </div>
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
