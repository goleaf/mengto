@props(['active' => false])

<div {{ $attributes->class(['header-search hidden min-w-0 flex-1 md:block']) }}>
    <a
        href="{{ route('discover.index') }}"
        aria-label="{{ __('ui.search_brand_a6f8e15d35') }}"
        data-header-link="discover"
        @if ($active) aria-current="page" @endif
        class="search-link"
    >
        <x-ui-icon name="search" size="sm" />
        <span class="truncate">{{ __('ui.search_parks_pets_people_and_routines_ff59363dd9') }}</span>
    </a>
</div>
