@props(['channel'])

<article data-share-channel="{{ $channel['code'] }}" {{ $attributes->class(['share-channel']) }}>
    <span class="share-channel__icon" data-share-channel-icon aria-hidden="true">
        <x-ui-icon :name="$channel['icon']" />
    </span>

    <div class="share-channel__copy">
        <h3 class="share-channel__title">{{ $channel['title'] }}</h3>
        <p class="share-channel__description">{{ $channel['description'] }}</p>
    </div>

    <a
        href="{{ $channel['href'] }}"
        class="action action--paper action--compact share-channel__action"
        data-share-channel-action
    >
        <x-ui-icon name="arrow-up-right" size="sm" />
        <span data-action-label>{{ $channel['label'] }}</span>
    </a>
</article>
