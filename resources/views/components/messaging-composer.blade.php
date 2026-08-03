@props(['conversation', 'activeFilter'])

<section class="messaging-composer" aria-label="{{ __('ui.write_a_message_143ec68982') }}">
    <div class="messaging-composer__reply" data-message-reply hidden>
        <span><x-ui-icon name="reply" size="sm" /> {{ __('ui.replying_to_selected_message_0e2599f52e') }}</span>
        <button type="button" data-message-reply-clear aria-label="{{ __('ui.cancel_reply_2355f73150') }}"><x-ui-icon name="x" size="sm" /></button>
    </div>

    <form
        method="POST"
        action="{{ route('messages.actions') }}"
        data-message-composer
        data-draft-saving="{{ __('ui.saving_draft_7ce627c3ef') }}"
        data-draft-saved="{{ __('ui.draft_saved_on_this_device_3aa3ab0be8') }}"
    >
        @csrf
        <input type="hidden" name="action" value="send-message">
        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
        <input type="hidden" name="message_type" value="text" data-message-type>
        <input type="hidden" name="reply_to" value="" data-message-reply-value>
        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">

        <div class="messaging-composer__tools" aria-label="{{ __('ui.message_type_6d646db006') }}">
            @foreach ([
                ['type' => 'image', 'icon' => 'image', 'label' => __('ui.photo_d84eebada9')],
                ['type' => 'video', 'icon' => 'video', 'label' => __('ui.video_d534be829e')],
                ['type' => 'file', 'icon' => 'paperclip', 'label' => __('ui.file_50009ce1da')],
                ['type' => 'audio', 'icon' => 'mic', 'label' => __('ui.audio_bc1b88907d')],
                ['type' => 'pet', 'icon' => 'paw-print', 'label' => __('ui.pet_8f0d1b30eb')],
                ['type' => 'place', 'icon' => 'map-pin', 'label' => __('ui.place_e9463dccf0')],
                ['type' => 'event', 'icon' => 'calendar-days', 'label' => __('ui.event_4e1f49a9c8')],
                ['type' => 'task', 'icon' => 'list-checks', 'label' => __('ui.task_4bc74b2135')],
            ] as $tool)
                <button
                    type="button"
                    data-message-type-button="{{ $tool['type'] }}"
                    data-message-type-label="{{ __('presentation.message_attachment', ['item' => $tool['label']]) }}"
                    title="{{ $tool['label'] }}"
                    aria-label="{{ __('presentation.send_item', ['item' => strtolower($tool['label'])]) }}"
                    aria-pressed="false"
                >
                    <x-ui-icon size="sm" :name="$tool['icon']" />
                </button>
            @endforeach
        </div>

        <label for="message-body-{{ $conversation['key'] }}" class="sr-only">{{ __('presentation.message_recipient', ['name' => $conversation['name']]) }}</label>
        <textarea
            id="message-body-{{ $conversation['key'] }}"
            name="body"
            rows="3"
            maxlength="4000"
            required
            data-message-body
            data-draft-key="message-draft-{{ $conversation['key'] }}"
            placeholder="{{ __('presentation.message_as', ['name' => $conversation['name'], 'sender' => __('ui.mia_4150950870')]) }}"
            @if ($errors->has('body')) aria-invalid="true" aria-describedby="message-body-error" @endif
        >{{ old('body') }}</textarea>

        @error('body')
            <p id="message-body-error" class="messaging-composer__error">{{ $message }}</p>
        @enderror

        <div class="messaging-composer__footer">
            <div>
                <label>
                    <input type="checkbox" name="silent" value="yes">
                    <span><x-ui-icon name="bell-off" size="sm" /> {{ __('ui.send_quietly_fe08c8d89e') }}</span>
                </label>
                <span data-message-draft-status>{{ __('ui.draft_saved_on_this_device_3aa3ab0be8') }}</span>
            </div>
            <button type="submit" class="action action--primary action--regular">
                <x-ui-icon name="send" size="sm" />
                <span>{{ __('ui.send_f6f4688ff2') }}</span>
            </button>
        </div>

        <details class="messaging-composer__schedule">
            <summary><x-ui-icon name="clock-3" size="sm" /> {{ __('ui.schedule_delivery_51ea1cb13f') }}</summary>
            <label for="message-scheduled-{{ $conversation['key'] }}">
                {{ __('ui.send_at_12e76f1262') }}
                <input id="message-scheduled-{{ $conversation['key'] }}" type="datetime-local" name="scheduled_for">
            </label>
            <p>{{ __('ui.you_can_edit_move_send_now_or_cancel_6a7363fed6') }}</p>
        </details>
    </form>

    <p class="messaging-composer__privacy">
        <x-ui-icon name="shield-check" size="sm" />
        {{ __('ui.photo_gps_metadata_is_removed_by_default_files_275f4e697d') }}
    </p>
</section>
