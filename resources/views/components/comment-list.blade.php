@props(['comments', 'post'])

<div role="list" class="comment-list">
    @forelse ($comments as $comment)
        <x-comment-item :comment="$comment" :post="$post" />
    @empty
        <x-empty-state
            icon="messages-square"
            title="{{ __('ui.start_this_conversation_71cd4bdb26') }}"
            description="{{ __('ui.add_a_useful_care_note_a_kind_question_70928ec16b') }}"
            compact
            role="listitem"
        />
    @endforelse
</div>
