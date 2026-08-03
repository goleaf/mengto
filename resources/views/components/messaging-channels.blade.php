@props(['channels', 'activeChannel', 'conversation', 'activeFilter'])

<nav class="messaging-channels" aria-label="{{ __('ui.conversation_channels_73ec88c25d') }}">
    @forelse ($channels as $channel)
        <a
            href="{{ route('messages.index', ['conversation' => $conversation, 'filter' => $activeFilter, 'channel' => $channel['key']]) }}"
            @if ($activeChannel === $channel['key']) aria-current="page" @endif
            @class(['messaging-channel', 'messaging-channel--active' => $activeChannel === $channel['key']])
        >
            <x-ui-icon size="sm" :name="$channel['icon']" />
            <span>{{ $channel['label'] }}</span>
            @if ($channel['count'] > 0)
                <small>{{ $channel['count'] }}</small>
            @endif
        </a>
    @empty
        <span class="messaging-channel">{{ __('ui.general_c910d474dc') }}</span>
    @endforelse
</nav>
