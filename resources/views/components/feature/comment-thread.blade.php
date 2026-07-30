@props(['post', 'comments', 'count'])

<section
    data-section="conversation"
    aria-labelledby="conversation-title"
    class="panel panel--clip"
>
    <header class="comment-thread__header">
        <x-ui.section-heading
            eyebrow="Neighbor conversation"
            title="Replies"
            title-id="conversation-title"
            size="compact"
        />
        <x-ui.status-badge
            :label="$count.' visible '.\Illuminate\Support\Str::plural('reply', $count)"
            icon="messages-square"
            tone="mint"
        />
    </header>

    <x-feature.comment-list :comments="$comments" :post="$post" />
    <x-feature.comment-composer :post="$post" />
</section>
