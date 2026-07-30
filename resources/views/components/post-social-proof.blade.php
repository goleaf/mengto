@props(['post'])

<div class="post-social-proof">
    <span>
        @if ($post['selected_reaction_label'])
            {{ __('presentation.your_reaction', ['reaction' => $post['selected_reaction_label']]) }} ·
        @endif
        {{ trans_choice('presentation.reactions_count', $post['reaction_total'], ['count' => $post['reaction_total']]) }}
    </span>
    <span>{{ __('presentation.comments_reposts', ['comments' => $post['reply_total'], 'reposts' => $post['reposts']]) }}</span>
</div>
