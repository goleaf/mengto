@props(['active' => false])

<div {{ $attributes->class(['header-search hidden min-w-0 flex-1 md:block']) }}>
    <a
        href="{{ route('discover.index') }}"
        aria-label="{{ __('navigation.utility.search_label') }}"
        data-header-link="discover"
        @if ($active) aria-current="page" @endif
        class="search-link"
    >
        <x-ui-icon name="search" size="sm" />
        <span class="truncate">{{ __('navigation.utility.search_placeholder') }}</span>
    </a>
</div>
