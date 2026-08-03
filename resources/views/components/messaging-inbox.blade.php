@props([
    'conversations',
    'activeFilter',
    'query',
    'selected',
    'summary',
])

<aside class="messaging-inbox" aria-label="{{ __('ui.message_inbox_168d0cd2fe') }}">
    <form method="GET" action="{{ route('messages.index') }}" class="messaging-inbox__search">
        <input type="hidden" name="filter" value="{{ $activeFilter }}">
        <label for="message-conversation-search">{{ __('ui.search_dialogs_45fd1f64fc') }}</label>
        <div>
            <x-lucide-search class="icon icon--sm" aria-hidden="true" />
            <input
                id="message-conversation-search"
                name="q"
                value="{{ $query }}"
                type="search"
                placeholder="{{ __('ui.person_pet_group_case_74b3675b57') }}"
                maxlength="80"
            >
            <button type="submit" title="{{ __('ui.search_conversations_8abdf3b226') }}" aria-label="{{ __('ui.search_conversations_8abdf3b226') }}">
                <x-lucide-arrow-right class="icon icon--sm" aria-hidden="true" />
            </button>
        </div>
    </form>

    <div class="messaging-inbox__meta">
        <strong>{{ __('presentation.shown_count', ['count' => count($conversations)]) }}</strong>
        <span>{{ $summary['count'] }}</span>
    </div>

    <nav class="messaging-inbox__list" aria-label="{{ __('ui.conversations_1d432f5869') }}">
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
                        <span>{{ $conversation['type_label'] }}</span>
                        @if ($conversation['pinned'])
                            <x-lucide-pin class="icon icon--xs" aria-label="{{ __('ui.pinned_f20c879465') }}" />
                        @endif
                        @if ($conversation['muted'])
                            <x-lucide-bell-off class="icon icon--xs" aria-label="{{ __('ui.muted_2346f214ad') }}" />
                        @endif
                        @if ($conversation['blocked'])
                            <x-lucide-ban class="icon icon--xs" aria-label="{{ __('ui.blocked_18f2a0947f') }}" />
                        @endif
                    </span>
                </span>

                @if ($conversation['unread'] > 0)
                    <span class="messaging-conversation__unread" aria-label="{{ __('presentation.unread_count', ['count' => $conversation['unread']]) }}">
                        {{ $conversation['unread'] }}
                    </span>
                @endif
            </a>
        @empty
            <div class="messaging-inbox__empty">
                <x-lucide-inbox class="icon" aria-hidden="true" />
                <strong>{{ __('ui.no_matching_dialogs_8fb154f1ae') }}</strong>
                <span>{{ __('ui.change_a_folder_or_search_phrase_4faaa96f3c') }}</span>
            </div>
        @endforelse
    </nav>
</aside>
