@props([
    'filters' => [],
    'label',
    'filtersLabel',
    'sortLabel',
    'section',
    'searchId' => null,
    'searchLabel' => null,
    'searchPlaceholder' => null,
])

@php
    $hasSearch = $searchId && $searchLabel && $searchPlaceholder;
@endphp

<section
    data-section="{{ $section }}"
    aria-label="{{ $label }}"
    {{ $attributes->class(['pc-panel', 'pc-panel--padded-sm']) }}
>
    <div @class([
        'pc-directory-toolbar',
        'pc-directory-toolbar--with-search' => $hasSearch,
    ])>
        @if ($hasSearch)
            <x-pet-social.search-field
                :id="$searchId"
                :label="$searchLabel"
                :placeholder="$searchPlaceholder"
            />
        @endif

        <div class="pc-directory-toolbar__filters" role="group" aria-label="{{ $filtersLabel }}">
            @forelse ($filters as $filter)
                <x-pet-social.filter-chip
                    :label="$filter"
                    :active="$loop->first"
                    size="toolbar"
                />
            @empty
                <span class="pc-directory-toolbar__empty">Filters unavailable.</span>
            @endforelse
        </div>

        <x-pet-social.static-action
            :label="$sortLabel"
            icon="arrow-up-down"
            variant="paper"
            size="toolbar"
            class="pc-directory-toolbar__sort"
        />
    </div>
</section>
