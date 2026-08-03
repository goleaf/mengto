@props([
    'filters',
    'activeFilter',
    'query',
])

<nav class="messaging-folders" aria-label="{{ __('ui.inbox_folders_16c4c4771a') }}">
    <div class="messaging-folders__heading">
        <x-ui-icon name="folder-open" size="sm" />
        <strong>{{ __('ui.inbox_folders_16c4c4771a') }}</strong>
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
            <span class="messaging-filter">{{ __('ui.folders_unavailable_c1856009a2') }}</span>
        @endforelse
    </div>
</nav>
