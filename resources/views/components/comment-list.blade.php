@props(['comments', 'post'])

<div role="list" class="comment-list">
    @forelse ($comments as $comment)
        <x-comment-item :comment="$comment" :post="$post" />
    @empty
        <x-empty-state
            icon="messages-square"
            title="{{ __('ui.start_this_conversation') }}"
            description="{{ __('ui.add_a_useful_care_note_a_kind_question_or_a_local_recommendation') }}"
            compact
            role="listitem"
        />
    @endforelse
</div>
