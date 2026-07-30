@props(['meetups'])

<x-result-grid
    section="meetup-directory"
    title-id="meetup-directory-title"
    title="{{ __('ui.upcoming_meetup_results_e5c67d5040') }}"
>
    @forelse ($meetups as $meetup)
        <x-meetup-card :meetup="$meetup" :eager="$loop->first" />
    @empty
        <x-empty-state
            icon="calendar-x"
            title="{{ __('ui.no_meetups_match_these_filters_dff62a8ca1') }}"
            role="listitem"
            description="{{ __('ui.try_a_broader_plan_place_or_meetup_type_797f0799eb') }}"
            :href="route('meetups.index')"
            class="sm:col-span-2 xl:col-span-3"
        />
    @endforelse
</x-result-grid>
