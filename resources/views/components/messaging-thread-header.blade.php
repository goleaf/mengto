@props(['conversation', 'activeFilter'])

<header class="messaging-thread-header" data-messaging-thread-header>
    <a
        href="{{ route('messages.index', ['filter' => $activeFilter]) }}"
        class="messaging-thread-header__back"
        aria-label="{{ __('messaging.thread.back') }}"
    >
        <x-ui-icon name="arrow-left" />
    </a>

    <x-linked-media
        :href="$conversation['media_target']['url']"
        :label="$conversation['media_target']['label']"
        variant="avatar"
        class="shrink-0"
    >
        <img src="{{ $conversation['avatar'] }}" alt="{{ $conversation['avatar_alt'] }}" width="48" height="48">
    </x-linked-media>

    <div class="messaging-thread-header__identity">
        <span class="messaging-thread-header__title">
            <h2>{{ $conversation['name'] }}</h2>
            @if ($conversation['professional'])
                <x-ui-icon name="badge-check" size="sm" label="{{ $conversation['verified'] }}" />
            @endif
        </span>
        <p>{{ $conversation['handle'] }} · {{ $conversation['presence'] }}</p>
        <span>{{ __('presentation.writing_as_person', ['purpose' => $conversation['purpose']]) }}</span>
    </div>

    <div class="messaging-thread-header__actions">
        <form method="POST" action="{{ route('messages.actions') }}">
            @csrf
            <input type="hidden" name="action" value="start-message-call">
            <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
            <input type="hidden" name="call_type" value="audio">
            <input type="hidden" name="recording_consent" value="no">
            <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
            <button type="submit" class="messaging-icon-button" title="{{ __('messaging.thread.audio_preflight') }}" @disabled($conversation['blocked'])>
                <x-ui-icon name="phone" size="sm" />
                <span class="sr-only">{{ __('messaging.thread.audio_call') }}</span>
            </button>
        </form>

        <form method="POST" action="{{ route('messages.actions') }}">
            @csrf
            <input type="hidden" name="action" value="start-message-call">
            <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
            <input type="hidden" name="call_type" value="video">
            <input type="hidden" name="recording_consent" value="no">
            <input type="hidden" name="return_filter" value="{{ $activeFilter }}">
            <button type="submit" class="messaging-icon-button" title="{{ __('messaging.thread.video_preflight') }}" @disabled($conversation['blocked'])>
                <x-ui-icon name="video" size="sm" />
                <span class="sr-only">{{ __('messaging.thread.video_call') }}</span>
            </button>
        </form>

        <a
            href="{{ $conversation['details_url'] }}"
            class="messaging-icon-button"
            title="{{ __('messaging.thread.details') }}"
        >
            <x-ui-icon name="info" size="sm" />
            <span class="sr-only">{{ __('messaging.thread.details') }}</span>
        </a>
    </div>
</header>

<div class="messaging-thread-context">
    <x-ui-icon name="paw-print" size="sm" />
    <span>{{ __('presentation.context_pets', ['pets' => implode(', ', $conversation['pet_names'])]) }}</span>
    <strong>{{ $conversation['privacy'] }}</strong>
</div>
