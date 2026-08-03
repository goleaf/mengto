@props(['channel'])

<article {{ $attributes->class(['share-channel']) }}>
    <span class="share-channel__icon" aria-hidden="true">
        <x-ui-icon :name="$channel['icon']" />
    </span>

    <div class="share-channel__copy">
        <h3 class="share-channel__title">{{ $channel['title'] }}</h3>
        <p class="share-channel__description">{{ $channel['description'] }}</p>
    </div>

    <x-action-control
        :href="$channel['href']"
        :label="$channel['label']"
        icon="arrow-up-right"
        variant="paper"
        size="compact"
        class="share-channel__action"
    />
</article>
