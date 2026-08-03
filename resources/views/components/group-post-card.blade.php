@props(['post'])

<article class="community-post panel">
    <header class="community-post__header">
        <x-initials-avatar :initials="$post['initials']" :tone="$post['tone']" />
        <div class="community-post__identity">
            <p>{{ $post['author'] }}</p>
            <span>{{ $post['role'] }}</span>
        </div>
        <x-status-badge
            :label="$post['expert'] ? __('groups.detail.post.verified_expert') : $post['format']"
            :icon="$post['expert'] ? 'badge-check' : null"
            :tone="$post['expert'] ? 'mint' : 'surface'"
        />
        <time datetime="{{ $post['datetime'] }}">{{ $post['time'] }}</time>
    </header>

    <div class="community-post__body">
        <h3>{{ $post['title'] }}</h3>
        <p>{{ $post['body'] }}</p>
        <x-tag-list :items="$post['tags']" empty="{{ __('groups.detail.post.no_labels') }}" />
    </div>

    @if ($post['image'])
        <x-responsive-image
            :src="$post['image']"
            :alt="$post['image_alt']"
            :width="720"
            :height="480"
            sizes="(min-width: 1024px) 680px, 100vw"
            class="community-post__image"
        />
    @endif

    <footer class="community-post__footer" aria-label="{{ __('groups.detail.post.activity_label') }}">
        <x-icon-text icon="heart">{{ trans_choice('presentation.reactions_count', $post['stats']['reactions'], ['count' => $post['stats']['reactions']]) }}</x-icon-text>
        <x-icon-text icon="message-circle">{{ trans_choice('presentation.comments_count', $post['stats']['comments'], ['count' => $post['stats']['comments']]) }}</x-icon-text>
        <x-icon-text icon="bookmark">{{ trans_choice('presentation.saves_count', $post['stats']['saves'], ['count' => $post['stats']['saves']]) }}</x-icon-text>
    </footer>
</article>
