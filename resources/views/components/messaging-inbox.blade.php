@props([
    'conversations',
    'filters',
    'activeFilter',
    'query',
    'selected',
    'summary',
])

<aside class="messaging-inbox" aria-label="Message inbox">
    <form method="GET" action="{{ route('messages.index') }}" class="messaging-inbox__search">
        <input type="hidden" name="filter" value="{{ $activeFilter }}">
        <label for="message-conversation-search">Search dialogs</label>
        <div>
            <x-lucide-search class="icon icon--sm" aria-hidden="true" />
            <input
                id="message-conversation-search"
                name="q"
                value="{{ $query }}"
                type="search"
                placeholder="Person, pet, group, case"
                maxlength="80"
            >
            <button type="submit" title="Search conversations" aria-label="Search conversations">
                <x-lucide-arrow-right class="icon icon--sm" aria-hidden="true" />
            </button>
        </div>
    </form>

    <nav class="messaging-inbox__filters" aria-label="Inbox folders">
        @forelse ($filters as $filter)
            <a
                href="{{ route('messages.index', array_filter(['filter' => $filter['key'], 'q' => $query])) }}"
                @if ($activeFilter === $filter['key']) aria-current="page" @endif
                @class(['messaging-filter', 'messaging-filter--active' => $activeFilter === $filter['key']])
            >
                <x-dynamic-component :component="'lucide-'.$filter['icon']" class="icon icon--sm" aria-hidden="true" />
                <span>{{ $filter['label'] }}</span>
            </a>
        @empty
            <span class="messaging-filter">Folders unavailable</span>
        @endforelse
    </nav>

    <div class="messaging-inbox__meta">
        <strong>{{ count($conversations) }} shown</strong>
        <span>{{ $summary['count'] }}</span>
    </div>

    <nav class="messaging-inbox__list" aria-label="Conversations">
        @forelse ($conversations as $conversation)
            <a
                href="{{ route('messages.index', ['conversation' => $conversation['key'], 'filter' => $activeFilter, 'q' => $query]) }}"
                @if ($conversation['selected']) aria-current="page" @endif
                @class([
                    'messaging-conversation',
                    'messaging-conversation--selected' => $conversation['selected'],
                    'messaging-conversation--request' => $conversation['request_status'] === 'pending',
                ])
            >
                <img src="{{ $conversation['avatar'] }}" alt="" width="48" height="48" loading="lazy">

                <span class="messaging-conversation__copy">
                    <span class="messaging-conversation__title">
                        <strong>{{ $conversation['name'] }}</strong>
                        <time datetime="{{ $conversation['datetime'] }}">{{ $conversation['time'] }}</time>
                    </span>
                    <span class="messaging-conversation__pet">{{ $conversation['pet'] }}</span>
                    <span class="messaging-conversation__preview">{{ $conversation['preview'] }}</span>
                    <span class="messaging-conversation__status">
                        <span>{{ Str::headline($conversation['type']) }}</span>
                        @if ($conversation['pinned'])
                            <x-lucide-pin class="icon icon--xs" aria-label="Pinned" />
                        @endif
                        @if ($conversation['muted'])
                            <x-lucide-bell-off class="icon icon--xs" aria-label="Muted" />
                        @endif
                        @if ($conversation['blocked'])
                            <x-lucide-ban class="icon icon--xs" aria-label="Blocked" />
                        @endif
                    </span>
                </span>

                @if ($conversation['unread'] > 0)
                    <span class="messaging-conversation__unread" aria-label="{{ $conversation['unread'] }} unread">
                        {{ $conversation['unread'] }}
                    </span>
                @endif
            </a>
        @empty
            <div class="messaging-inbox__empty">
                <x-lucide-inbox class="icon" aria-hidden="true" />
                <strong>No matching dialogs</strong>
                <span>Change a folder or search phrase.</span>
            </div>
        @endforelse
    </nav>
</aside>
