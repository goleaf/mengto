<section class="messaging-composer" aria-label="{{ __('messaging.composer.label') }}" data-messaging-composer>
    <div class="messaging-composer__reply" data-message-reply hidden>
        <span><x-ui-icon name="reply" size="sm" /> {{ __('messaging.composer.replying') }}</span>
        <button type="button" data-message-reply-clear aria-label="{{ __('messaging.composer.cancel_reply') }}"><x-ui-icon name="x" size="sm" /></button>
    </div>

    <form
        method="POST"
        action="{{ route('messages.actions') }}"
        data-message-composer
        data-draft-saving="{{ __('messaging.composer.draft_saving') }}"
        data-draft-saved="{{ __('messaging.composer.draft_saved') }}"
    >
        @csrf
        <input type="hidden" name="action" value="send-message">
        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
        <input type="hidden" name="message_type" value="text" data-message-type>
        <input type="hidden" name="reply_to" value="" data-message-reply-value>
        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">

        <div class="messaging-composer__tools" aria-label="{{ __('messaging.composer.message_type') }}">
            @forelse ($tools as $tool)
                <button
                    type="button"
                    data-message-type-button="{{ $tool['type'] }}"
                    data-message-type-label="{{ __('messaging.composer.attachment', ['item' => $tool['label']]) }}"
                    title="{{ $tool['label'] }}"
                    aria-label="{{ __('messaging.composer.send_item', ['item' => $tool['label']]) }}"
                    aria-pressed="false"
                >
                    <x-ui-icon size="sm" :name="$tool['icon']" />
                </button>
            @empty
                <span>{{ __('messaging.composer.no_tools') }}</span>
            @endforelse
        </div>

        <label for="message-body-{{ $conversation['key'] }}" class="sr-only">{{ __('messaging.composer.recipient', ['name' => $conversation['name']]) }}</label>
        <textarea
            id="message-body-{{ $conversation['key'] }}"
            name="body"
            rows="3"
            maxlength="4000"
            required
            data-message-body
            data-draft-key="message-draft-{{ $conversation['key'] }}"
            placeholder="{{ __('messaging.composer.placeholder', ['name' => $conversation['name'], 'sender' => $sender]) }}"
            @if ($errors->has('body')) aria-invalid="true" aria-describedby="message-body-error" @endif
        >{{ old('body') }}</textarea>

        @error('body')
            <p id="message-body-error" class="messaging-composer__error">{{ $message }}</p>
        @enderror

        <div class="messaging-composer__footer">
            <div>
                <label>
                    <input type="checkbox" name="silent" value="yes">
                    <span><x-ui-icon name="bell-off" size="sm" /> {{ __('messaging.composer.send_quietly') }}</span>
                </label>
                <span data-message-draft-status>{{ __('messaging.composer.draft_saved') }}</span>
            </div>
            <button type="submit" class="action action--primary action--regular">
                <x-ui-icon name="send" size="sm" />
                <span>{{ __('messaging.composer.send') }}</span>
            </button>
        </div>

        <details class="messaging-composer__schedule">
            <summary><x-ui-icon name="clock-3" size="sm" /> {{ __('messaging.composer.schedule') }}</summary>
            <label for="message-scheduled-{{ $conversation['key'] }}">
                {{ __('messaging.composer.send_at') }}
                <input id="message-scheduled-{{ $conversation['key'] }}" type="datetime-local" name="scheduled_for">
            </label>
            <p>{{ __('messaging.composer.schedule_help') }}</p>
        </details>
    </form>

    <p class="messaging-composer__privacy">
        <x-ui-icon name="shield-check" size="sm" />
        {{ __('messaging.composer.privacy') }}
    </p>
</section>
