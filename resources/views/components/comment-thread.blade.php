@props(['post', 'comments', 'count'])

<section
    data-section="conversation"
    aria-labelledby="conversation-title"
    class="panel panel--clip"
>
    <header class="comment-thread__header">
        <x-section-heading
            eyebrow="Neighbor conversation"
            title="Replies"
            title-id="conversation-title"
            size="compact"
        />
        <x-status-badge
            :label="$count.' visible '.str('reply')->plural($count)"
            icon="messages-square"
            tone="mint"
        />
    </header>

    <x-comment-list :comments="$comments" :post="$post" />
    <x-comment-composer :post="$post" />
</section>
