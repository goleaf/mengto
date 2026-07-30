@props(['post', 'comments', 'count'])

<section
    data-section="conversation"
    aria-labelledby="conversation-title"
    class="panel panel--clip"
>
    <header class="comment-thread__header">
        <x-section-heading
            eyebrow="{{ __('ui.neighbor_conversation_8ab26ebc32') }}"
            title="{{ __('ui.replies_31ecb5e00f') }}"
            title-id="conversation-title"
            size="compact"
        />
        <x-status-badge
            :label="trans_choice('presentation.visible_replies', $count, ['count' => $count])"
            icon="messages-square"
            tone="mint"
        />
    </header>

    <x-comment-list :comments="$comments" :post="$post" />
    <x-comment-composer :post="$post" />
</section>
