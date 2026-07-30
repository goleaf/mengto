@props(['channels', 'activeChannel', 'conversation', 'activeFilter'])

<nav class="messaging-channels" aria-label="Conversation channels">
    @forelse ($channels as $channel)
        <a
            href="{{ route('messages.index', ['conversation' => $conversation, 'filter' => $activeFilter, 'channel' => $channel['key']]) }}"
            @if ($activeChannel === $channel['key']) aria-current="page" @endif
            @class(['messaging-channel', 'messaging-channel--active' => $activeChannel === $channel['key']])
        >
            <x-dynamic-component :component="'lucide-'.$channel['icon']" class="icon icon--sm" aria-hidden="true" />
            <span>{{ $channel['label'] }}</span>
            @if ($channel['count'] > 0)
                <small>{{ $channel['count'] }}</small>
            @endif
        </a>
    @empty
        <span class="messaging-channel">General</span>
    @endforelse
</nav>
