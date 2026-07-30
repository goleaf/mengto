@props(['conversation', 'activeFilter'])

<section class="messaging-composer" aria-label="Write a message">
    <div class="messaging-composer__reply" data-message-reply hidden>
        <span><x-lucide-reply class="icon icon--sm" aria-hidden="true" /> Replying to selected message</span>
        <button type="button" data-message-reply-clear aria-label="Cancel reply"><x-lucide-x class="icon icon--sm" /></button>
    </div>

    <form method="POST" action="{{ route('messages.actions') }}" data-message-composer>
        @csrf
        <input type="hidden" name="action" value="send-message">
        <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
        <input type="hidden" name="message_type" value="text" data-message-type>
        <input type="hidden" name="reply_to" value="" data-message-reply-value>
        <input type="hidden" name="return_filter" value="{{ $activeFilter }}">

        <div class="messaging-composer__tools" aria-label="Message type">
            @foreach ([
                ['type' => 'image', 'icon' => 'image', 'label' => 'Photo'],
                ['type' => 'video', 'icon' => 'video', 'label' => 'Video'],
                ['type' => 'file', 'icon' => 'paperclip', 'label' => 'File'],
                ['type' => 'audio', 'icon' => 'mic', 'label' => 'Audio'],
                ['type' => 'pet', 'icon' => 'paw-print', 'label' => 'Pet'],
                ['type' => 'place', 'icon' => 'map-pin', 'label' => 'Place'],
                ['type' => 'event', 'icon' => 'calendar-days', 'label' => 'Event'],
                ['type' => 'task', 'icon' => 'list-checks', 'label' => 'Task'],
            ] as $tool)
                <button
                    type="button"
                    data-message-type-button="{{ $tool['type'] }}"
                    title="{{ $tool['label'] }}"
                    aria-label="Send {{ strtolower($tool['label']) }}"
                    aria-pressed="false"
                >
                    <x-dynamic-component :component="'lucide-'.$tool['icon']" class="icon icon--sm" aria-hidden="true" />
                </button>
            @endforeach
        </div>

        <label for="message-body-{{ $conversation['key'] }}" class="sr-only">Message {{ $conversation['name'] }}</label>
        <textarea
            id="message-body-{{ $conversation['key'] }}"
            name="body"
            rows="3"
            maxlength="4000"
            required
            data-message-body
            data-draft-key="message-draft-{{ $conversation['key'] }}"
            placeholder="Message {{ $conversation['name'] }} as Mia"
            @if ($errors->has('body')) aria-invalid="true" aria-describedby="message-body-error" @endif
        >{{ old('body') }}</textarea>

        @error('body')
            <p id="message-body-error" class="messaging-composer__error">{{ $message }}</p>
        @enderror

        <div class="messaging-composer__footer">
            <div>
                <label>
                    <input type="checkbox" name="silent" value="yes">
                    <span><x-lucide-bell-off class="icon icon--sm" aria-hidden="true" /> Send quietly</span>
                </label>
                <span data-message-draft-status>Draft saved on this device</span>
            </div>
            <button type="submit" class="action action--primary action--regular">
                <x-lucide-send class="icon icon--sm" aria-hidden="true" />
                <span>Send</span>
            </button>
        </div>

        <details class="messaging-composer__schedule">
            <summary><x-lucide-clock-3 class="icon icon--sm" aria-hidden="true" /> Schedule delivery</summary>
            <label for="message-scheduled-{{ $conversation['key'] }}">
                Send at
                <input id="message-scheduled-{{ $conversation['key'] }}" type="datetime-local" name="scheduled_for">
            </label>
            <p>You can edit, move, send now, or cancel before delivery. Business messages may be held until working hours.</p>
        </details>
    </form>

    <p class="messaging-composer__privacy">
        <x-lucide-shield-check class="icon icon--sm" aria-hidden="true" />
        Photo GPS metadata is removed by default. Files require format, size, MIME, and malware checks before real delivery.
    </p>
</section>
