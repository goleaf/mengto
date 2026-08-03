<article
    id="message-{{ $message['id'] }}"
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
            <x-ui-icon name="badge-check" size="sm" label="{{ __('ui.verified_professional_answer_eed084c091') }}" />
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
                        data-audio-play-label="{{ __('ui.play_audio_message_c1c2401fcb') }}"
                        data-audio-pause-label="{{ __('ui.pause_audio_message_c07d456af7') }}"
                        aria-label="{{ __('ui.play_audio_message_c1c2401fcb') }}"
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
            <span>{{ __('ui.edited_7117f08071') }}</span>
        @endif
        @if ($message['pinned'] ?? false)
            <span><x-ui-icon name="pin" size="xs" /> {{ __('ui.pinned_f20c879465') }}</span>
        @endif
        @if ($message['bookmarked'] ?? false)
            <span><x-ui-icon name="bookmark" size="xs" /> {{ __('ui.saved_privately_8ec8c9b372') }}</span>
        @endif
        @if ($message['reaction'] ?? null)
            <span><x-ui-icon name="smile-plus" size="xs" /> {{ $reactionLabel }}</span>
        @endif
        <span>{{ $message['status'] }}</span>

        <details class="messaging-message-menu">
            <summary aria-label="{{ __('ui.message_actions_f532ee1f72') }}"><x-ui-icon name="ellipsis" size="sm" /></summary>
            <div>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="react-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <input type="hidden" name="reaction" value="thanks">
                    <button type="submit"><x-ui-icon name="smile-plus" size="sm" /> {{ __('ui.thanks_bb47b8ff5f') }}</button>
                </form>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="pin-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-ui-icon name="pin" size="sm" /> {{ __('ui.pin_ff1cee7441') }}</button>
                </form>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="bookmark-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-ui-icon name="bookmark" size="sm" /> {{ __('ui.save_1509f561f2') }}</button>
                </form>
                <button
                    type="button"
                    data-message-reply-trigger
                    data-message-reply-text="{{ $replyText }}"
                >
                    <x-ui-icon name="reply" size="sm" /> {{ __('ui.reply_c253f451bd') }}
                </button>
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="delete-message-self">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <button type="submit"><x-ui-icon name="eye-off" size="sm" /> {{ __('ui.delete_for_me_2c95e9e132') }}</button>
                </form>
                @if ($message['mine'])
                    <form method="POST" action="{{ route('messages.actions') }}">
                        @csrf
                        <input type="hidden" name="action" value="delete-message-everyone">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="message" value="{{ $message['id'] }}">
                        <button type="submit"><x-ui-icon name="trash-2" size="sm" /> {{ __('ui.delete_for_all_423b02dbc6') }}</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('messages.actions') }}">
                    @csrf
                    <input type="hidden" name="action" value="report-message">
                    <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                    <input type="hidden" name="message" value="{{ $message['id'] }}">
                    <input type="hidden" name="report_reason" value="other">
                    <input type="hidden" name="body" value="Review this message with its surrounding context.">
                    <button type="submit"><x-ui-icon name="flag" size="sm" /> {{ __('ui.report_b6ce788d97') }}</button>
                </form>
                @if ($editable)
                    <form method="POST" action="{{ route('messages.actions') }}" class="messaging-message-menu__edit">
                        @csrf
                        <input type="hidden" name="action" value="edit-message">
                        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                        <input type="hidden" name="message" value="{{ $message['id'] }}">
                        <label>
                            <span>{{ __('ui.edit_message_9757ccd5ef') }}</span>
                            <input type="text" name="body" value="{{ $message['body'] }}" maxlength="4000" required>
                        </label>
                        <button type="submit"><x-ui-icon name="check" size="sm" /> {{ __('ui.save_edit_a7fde19840') }}</button>
                    </form>
                @endif
            </div>
        </details>
    </footer>
</article>
