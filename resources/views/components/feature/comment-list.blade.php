@props(['comments', 'post'])

<div role="list" class="comment-list">
    @forelse ($comments as $comment)
        <x-feature.comment-item :comment="$comment" :post="$post" />
    @empty
        <x-ui.empty-state
            icon="messages-square"
            title="Start this conversation"
            description="Add a useful care note, a kind question, or a local recommendation."
            compact
            role="listitem"
        />
    @endforelse
</div>
