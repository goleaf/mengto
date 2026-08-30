@props(['post'])

<div class="post-context" aria-label="{{ __('ui.publication_context') }}">
    <span>
        <x-ui-icon name="shapes" size="xs" />
        {{ $post['topic'] }}
    </span>
    <span>
        <x-ui-icon name="map-pin" size="xs" />
        {{ $post['location'] }}
    </span>
    <span>
        <x-ui-icon name="message-square" size="xs" />
        {{ $post['comment_policy'] }}
    </span>
</div>
