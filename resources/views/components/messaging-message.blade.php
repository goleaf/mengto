<article
    id="message-{{ $message['id'] }}"
    data-messaging-message
    data-messaging-message-type="{{ $message['type'] }}"
    @class([
        'messaging-message',
        'messaging-message--mine' => $message['mine'],
        'messaging-message--structured' => $structured,
        'messaging-message--warning' => $message['type'] === 'warning',
        'messaging-message--system' => $message['type'] === 'system',
    ])
>
    <header>
        <strong>{{ $message['sender'] }}</strong>
        @if ($message['type'] === 'professional')
            <x-ui-icon name="badge-check" size="sm" label="{{ __('messaging.message.verified_professional') }}" />
        @endif
        <time datetime="{{ $message['datetime'] }}">{{ $message['time'] }}</time>
    </header>

    <div class="messaging-message__bubble">
        @if ($message['reply'])
            <blockquote>
                <x-ui-icon name="reply" size="xs" />
                {{ $message['reply'] }}
            </blockquote>
        @endif

        @if ($structured)
            <div class="messaging-message__media">
                <span><x-ui-icon :name="$icon" /></span>
                <div>
                    <small>{{ $typeLabel }}</small>
                    <strong>{{ $message['body'] }}</strong>
                    @if ($message['meta'])
                        <p>{{ $message['meta'] }}</p>
                    @endif
                </div>
                @if ($message['type'] === 'audio')
                    <button
                        type="button"
                        class="messaging-audio"
                        data-audio-toggle
                        data-audio-play-label="{{ __('messaging.message.audio_play') }}"
                        data-audio-pause-label="{{ __('messaging.message.audio_pause') }}"
                        aria-label="{{ __('messaging.message.audio_play') }}"
                        aria-pressed="false"
                    >
                        <x-ui-icon name="play" size="sm" />
                        <span aria-hidden="true">
                            @for ($bar = 0; $bar < 12; $bar++)
                                <i style="--bar: {{ ($bar % 5) + 1 }}"></i>
                            @endfor
                        </span>
                    </button>
                @endif
            </div>
        @else
            <p>{{ $message['body'] }}</p>
        @endif
    </div>

    <footer>
        @if ($message['edited'])
            <span>{{ __('messaging.message.edited') }}</span>
        @endif
        @if ($message['pinned'] ?? false)
            <span><x-ui-icon name="pin" size="xs" /> {{ __('messaging.message.pinned') }}</span>
        @endif
        @if ($message['bookmarked'] ?? false)
            <span><x-ui-icon name="bookmark" size="xs" /> {{ __('messaging.message.saved_privately') }}</span>
        @endif
        @if ($message['reaction'] ?? null)
            <span><x-ui-icon name="smile-plus" size="xs" /> {{ $reactionLabel }}</span>
        @endif
        <span data-messaging-message-status data-messaging-message-status-code="{{ $statusCode }}">{{ $statusLabel }}</span>

        <details class="messaging-message-menu">
            <summary aria-label="{{ __('messaging.message.actions_label') }}"><x-ui-icon name="ellipsis" size="sm" /></summary>
            <div>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="react-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <input type="hidden" name="reaction" value="thanks">
                    <button type="submit"><x-ui-icon name="smile-plus" size="sm" /> {{ __('messaging.message.actions.thanks') }}</button>
                </form>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="pin-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-ui-icon name="pin" size="sm" /> {{ __('messaging.message.actions.pin') }}</button>
                </form>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="bookmark-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-ui-icon name="bookmark" size="sm" /> {{ __('messaging.message.actions.save') }}</button>
                </form>
                <button
                    type="button"
                    data-message-reply-trigger
                    data-message-reply-text="{{ $replyText }}"
                >
                    <x-ui-icon name="reply" size="sm" /> {{ __('messaging.message.actions.reply') }}
                </button>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="delete-message-self">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-ui-icon name="eye-off" size="sm" /> {{ __('messaging.message.actions.delete_self') }}</button>
                </form>
                @if ($message['mine'])
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="delete-message-everyone">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="message" value="{{ $message['id'] }}">
                        <button type="submit"><x-ui-icon name="trash-2" size="sm" /> {{ __('messaging.message.actions.delete_all') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="report-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <input type="hidden" name="report_reason" value="other">
                    <input type="hidden" name="body" value="{{ __('messaging.message.actions.report_default_body') }}">
                    <button type="submit"><x-ui-icon name="flag" size="sm" /> {{ __('messaging.message.actions.report') }}</button>
                </form>
                @if ($editable)
                    <form method="POST" action="{{ route('messages.actions') }}" class="messaging-message-menu__edit">
                        @csrf
                        <input type="hidden" name="action" value="edit-message">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="message" value="{{ $message['id'] }}">
                        <label>
                            <span>{{ __('messaging.message.actions.edit') }}</span>
                            <input type="text" name="body" value="{{ $message['body'] }}" maxlength="4000" required>
                        </label>
                        <button type="submit"><x-ui-icon name="check" size="sm" /> {{ __('messaging.message.actions.save_edit') }}</button>
                    </form>
                @endif
            </div>
        </details>
    </footer>
</article>
