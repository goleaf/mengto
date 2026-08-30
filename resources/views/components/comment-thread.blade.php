@props(['post', 'comments', 'count'])

<section
    data-section="conversation"
    aria-labelledby="conversation-title"
    class="panel panel--clip"
>
    <header class="comment-thread__header">
        <x-section-heading
            eyebrow="{{ __('ui.neighbor_conversation') }}"
            title="{{ __('ui.replies') }}"
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
