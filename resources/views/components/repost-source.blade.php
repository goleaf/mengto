@props(['post'])

<a href="{{ route('posts.show', ['post' => $post['key']]) }}" class="repost-source">
    <span class="repost-source__meta">
        <x-ui-icon name="repeat-2" size="sm" />
        {{ __('presentation.originally_published_by', ['author' => $post['author']]) }}
    </span>
    @if ($post['title'])
        <strong>{{ $post['title'] }}</strong>
    @endif
    <span>{{ $post['body'] }}</span>
</a>
