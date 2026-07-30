@props(['post'])

<a href="{{ route('posts.show', ['post' => $post['key']]) }}" class="repost-source">
    <span class="repost-source__meta">
        <x-lucide-repeat-2 class="icon icon--sm" aria-hidden="true" />
        {{ __('presentation.originally_published_by', ['author' => $post['author']]) }}
    </span>
    @if ($post['title'])
        <strong>{{ $post['title'] }}</strong>
    @endif
    <span>{{ $post['body'] }}</span>
</a>
