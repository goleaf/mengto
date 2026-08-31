<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-6">
        <x-page-header
            :eyebrow="__('ui.verified_professional_community')"
            :title="__('ui.find_the_right_specialist_for_this_pet')"
            :description="__('ui.compare_scope_species_independently_checked_credentials_availability_language_and_price_before_sharing_any_private_pet_information')"
            heading-id="experts-heading"
            data-section="experts-header"
        >
            <x-slot:actions>
                <x-action-control :label="__('ui.professional_workspace')" icon="briefcase-business" :href="route('experts.dashboard')" />
                <x-action-control :label="__('ui.create_professional_profile')" icon="badge-plus" variant="primary" size="regular" :href="route('experts.create')" />
            </x-slot:actions>
        </x-page-header>

        <section data-expert-stats class="grid grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line lg:grid-cols-4" aria-label="{{ __('ui.expert_directory_summary') }}">
            @forelse ($stats as $stat)
                <div data-expert-stat class="flex items-center gap-3 bg-white p-4">
                    <x-ui-icon size="lg" :name="$stat['icon']" class="shrink-0 text-paw-leaf" />
                    <div>
                        <strong class="block text-xl">{{ $stat['value'] }}</strong>
                        <span class="text-xs text-paw-muted">{{ $stat['label'] }}</span>
                    </div>
                </div>
            @empty
                <p class="col-span-full bg-white p-4 text-sm text-paw-muted">{{ __('ui.directory_statistics_are_not_available_yet') }}</p>
            @endforelse
        </section>

        <form data-expert-filters method="GET" action="{{ route('experts.index') }}" class="grid gap-4 border-y border-paw-line py-5" role="search">
            <div class="grid gap-3 lg:grid-cols-[minmax(16rem,2fr)_repeat(3,minmax(10rem,1fr))]">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.search') }}
                    <span class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3">
                        <x-ui-icon name="search" size="sm" class="text-paw-muted" />
                        <input name="q" value="{{ $filters['q'] ?? '' }}" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 outline-none" placeholder="{{ __('ui.name_skill_city_or_approach') }}">
                    </span>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.specialist') }}
                    <select name="type" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.all_specialist_types') }}</option>
                        @forelse ($types as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_specialist_types') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.species') }}
                    <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.every_species') }}</option>
                        @forelse ($species_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['species'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_species_options') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.problem') }}
                    <select name="specialization" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.all_areas') }}</option>
                        @forelse ($specializations as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['specialization'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_specializations') }}</option>
                        @endforelse
                    </select>
                </label>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.city') }}
                    <input name="city" value="{{ $filters['city'] ?? '' }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.vilnius') }}">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.format') }}
                    <select name="format" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_format') }}</option>
                        @forelse ($formats as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['format'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_formats') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.language') }}
                    <select name="language" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_language') }}</option>
                        @forelse ($languages as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['language'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_languages') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.availability') }}
                    <select name="availability" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        <option value="">{{ __('ui.any_availability') }}</option>
                        @forelse ($availability_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_availability_options') }}</option>
                        @endforelse
                    </select>
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.sort') }}
                    <select name="sort" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse ($sort_options as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'relevance') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>{{ __('ui.no_sorting_options') }}</option>
                        @endforelse
                    </select>
                </label>
                <div class="flex items-end gap-2">
                    <button type="submit" class="action action--primary action--compact flex-1">
                        <x-ui-icon name="sliders-horizontal" size="sm" />
                        <span>{{ __('ui.apply') }}</span>
                    </button>
                    <a href="{{ route('experts.index') }}" class="action action--surface action--icon" title="{{ __('ui.clear_filters') }}">
                        <x-ui-icon name="rotate-ccw" size="sm" />
                        <span class="sr-only">{{ __('ui.clear_filters') }}</span>
                    </a>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="verified" value="1" @checked((bool) ($filters['verified'] ?? false)) class="size-4 rounded border-paw-line text-paw-leaf">
                {{ __('ui.show_only_profiles_with_a_checked_professional_qualification') }}
            </label>
        </form>

        <section aria-labelledby="directory-heading">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="directory-heading" data-expert-results-title class="text-xl font-bold">{{ __('ui.matching_professionals') }}</h2>
                    <p data-expert-results-description class="mt-1 text-sm text-paw-muted">{{ __('ui.a_verification_badge_explains_what_was_checked_it_is_never_a_guarantee_of_outcome') }}</p>
                </div>
                <a data-expert-urgent href="{{ url('/places?category=emergency-vet') }}" class="inline-flex items-center gap-2 text-sm font-bold text-red-700 underline decoration-red-300 underline-offset-4">
                    <x-ui-icon name="siren" size="sm" />
                    {{ __('ui.need_urgent_veterinary_help') }}
                </a>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($experts as $expert)
                    <x-expert-card :expert="$expert" />
                @empty
                    <div class="md:col-span-2 xl:col-span-3">
                        <h3 class="text-xl font-bold">{{ __('ui.no_exact_match_yet') }}</h3>
                        <p class="mt-2 max-w-xl text-paw-muted">{{ __('ui.remove_one_filter_try_a_nearby_city_or_browse_newly_verified_specialists_who_accept_online_consultations') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">{{ $experts->links() }}</div>
        </section>
    </div>
</x-app-shell>
