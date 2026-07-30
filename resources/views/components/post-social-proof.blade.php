@props(['post'])

<div class="post-social-proof">
    <span>
        @if ($post['selected_reaction_label'])
            Your reaction: {{ $post['selected_reaction_label'] }} ·
        @endif
        {{ $post['reaction_total'] }} {{ str('reaction')->plural($post['reaction_total']) }}
    </span>
    <span>{{ $post['reply_total'] }} comments · {{ $post['reposts'] }} reposts</span>
</div>
