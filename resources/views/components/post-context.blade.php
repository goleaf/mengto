@props(['post'])

<div class="post-context" aria-label="{{ __('ui.publication_context_f393183682') }}">
    <span>
        <x-lucide-shapes class="icon icon--xs" aria-hidden="true" />
        {{ $post['topic'] }}
    </span>
    <span>
        <x-lucide-map-pin class="icon icon--xs" aria-hidden="true" />
        {{ $post['location'] }}
    </span>
    <span>
        <x-lucide-message-square class="icon icon--xs" aria-hidden="true" />
        {{ $post['comment_policy'] }}
    </span>
</div>
