@props(['recipient'])

<article {{ $attributes->class(['share-recipient']) }}>
    <x-ui.avatar
        :src="$recipient['image']"
        :alt="$recipient['image_alt']"
        size="compact"
        :lazy="true"
    />

    <div class="share-recipient__copy">
        <h3 class="share-recipient__name">{{ $recipient['name'] }}</h3>
        <p class="share-recipient__detail">{{ $recipient['detail'] }}</p>
    </div>

    <x-ui.action-control
        :endpoint="route('actions.perform')"
        :payload="[
            'action' => 'send-message',
            'target' => $recipient['key'],
            'body' => $recipient['message'],
        ]"
        label="Send"
        icon="send"
        variant="primary"
        size="compact"
        class="share-recipient__action"
    />
</article>
