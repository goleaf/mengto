@props([
    'filters',
    'activeFilter',
    'query',
])

<nav class="messaging-folders" aria-label="{{ __('messaging.folders.label') }}" data-messaging-folders>
    <div class="messaging-folders__heading">
        <x-ui-icon name="folder-open" size="sm" />
        <strong>{{ __('messaging.folders.label') }}</strong>
    </div>

    <div class="messaging-folders__list">
        @forelse ($filters as $filter)
            <a
                href="{{ route('messages.index', array_filter(['filter' => $filter['key'], 'q' => $query])) }}"
                @if ($activeFilter === $filter['key']) aria-current="page" @endif
                @class(['messaging-filter', 'messaging-filter--active' => $activeFilter === $filter['key']])
            >
                <x-ui-icon size="sm" :name="$filter['icon']" />
                <span>{{ $filter['label'] }}</span>
            </a>
        @empty
            <span class="messaging-filter">{{ __('messaging.folders.unavailable') }}</span>
        @endforelse
    </div>
</nav>
