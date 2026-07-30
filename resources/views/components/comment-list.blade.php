@props(['comments', 'post'])

<div role="list" class="comment-list">
    @forelse ($comments as $comment)
        <x-comment-item :comment="$comment" :post="$post" />
    @empty
        <x-empty-state
            icon="messages-square"
            title="Start this conversation"
            description="Add a useful care note, a kind question, or a local recommendation."
            compact
            role="listitem"
        />
    @endforelse
</div>
