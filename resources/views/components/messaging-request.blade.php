@props(['conversation'])

<section class="messaging-request" aria-labelledby="message-request-title" data-messaging-request>
    <img src="{{ $conversation['avatar'] }}" alt="{{ $conversation['avatar_alt'] }}" width="88" height="88">
    <p>{{ __('messaging.request.eyebrow') }}</p>
    <h2 id="message-request-title">{{ $conversation['name'] }}</h2>
    <span>{{ $conversation['verified'] }} · {{ $conversation['pet'] }}</span>
    <blockquote>{{ $conversation['preview'] }}</blockquote>

    <div class="messaging-request__context">
        <span><x-ui-icon name="paw-print" size="sm" /> {{ __('messaging.request.linked_pet') }}</span>
        <span><x-ui-icon name="eye-off" size="sm" /> {{ __('messaging.request.read_status_hidden') }}</span>
        <span><x-ui-icon name="paperclip" size="sm" /> {{ __('messaging.request.media_blocked') }}</span>
    </div>

    <div class="messaging-request__actions">
        @foreach ([
            ['action' => 'accept-message-request', 'label' => __('messaging.request.accept'), 'icon' => 'check', 'class' => 'action--primary'],
            ['action' => 'decline-message-request', 'label' => __('messaging.request.decline'), 'icon' => 'x', 'class' => 'action--paper'],
            ['action' => 'block-conversation', 'label' => __('messaging.request.block'), 'icon' => 'ban', 'class' => 'action--danger'],
        ] as $requestAction)
            <form method="POST" action="{{ route('messages.actions') }}">
                @csrf
                <input type="hidden" name="action" value="{{ $requestAction['action'] }}">
                <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                <button type="submit" class="action {{ $requestAction['class'] }} action--regular">
                    <x-ui-icon size="sm" :name="$requestAction['icon']" />
                    <span>{{ $requestAction['label'] }}</span>
                </button>
            </form>
        @endforeach
    </div>

    <p class="messaging-request__note">{{ __('messaging.request.note') }}</p>
</section>
