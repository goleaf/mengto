@props(['conversation'])

<section class="messaging-request" aria-labelledby="message-request-title">
    <img src="{{ $conversation['avatar'] }}" alt="{{ $conversation['avatar_alt'] }}" width="88" height="88">
    <p>New message request</p>
    <h2 id="message-request-title">{{ $conversation['name'] }}</h2>
    <span>{{ $conversation['verified'] }} · {{ $conversation['pet'] }}</span>
    <blockquote>{{ $conversation['preview'] }}</blockquote>

    <div class="messaging-request__context">
        <span><x-lucide-paw-print class="icon icon--sm" aria-hidden="true" /> Linked pet context only</span>
        <span><x-lucide-eye-off class="icon icon--sm" aria-hidden="true" /> Read status hidden</span>
        <span><x-lucide-paperclip class="icon icon--sm" aria-hidden="true" /> Media blocked until accepted</span>
    </div>

    <div class="messaging-request__actions">
        @foreach ([
            ['action' => 'accept-message-request', 'label' => 'Accept', 'icon' => 'check', 'class' => 'action--primary'],
            ['action' => 'decline-message-request', 'label' => 'Decline', 'icon' => 'x', 'class' => 'action--paper'],
            ['action' => 'block-conversation', 'label' => 'Block', 'icon' => 'ban', 'class' => 'action--danger'],
        ] as $requestAction)
            <form method="POST" action="{{ route('pet-social.messages.actions') }}">
                @csrf
                <input type="hidden" name="action" value="{{ $requestAction['action'] }}">
                <input type="hidden" name="conversation" value="{{ $conversation['key'] }}">
                <button type="submit" class="action {{ $requestAction['class'] }} action--regular">
                    <x-dynamic-component :component="'lucide-'.$requestAction['icon']" class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $requestAction['label'] }}</span>
                </button>
            </form>
        @endforeach
    </div>

    <p class="messaging-request__note">Accepting allows replies and call requests. It never reveals your phone, email, home address, exact location, or other profiles.</p>
</section>
