@props(['post'])

<article class="community-post panel">
    <header class="community-post__header">
        <x-ui.initials-avatar :initials="$post['initials']" :tone="$post['tone']" />
        <div class="community-post__identity">
            <p>{{ $post['author'] }}</p>
            <span>{{ $post['role'] }}</span>
        </div>
        <x-ui.status-badge
            :label="$post['expert'] ? 'Verified expert' : $post['format']"
            :icon="$post['expert'] ? 'badge-check' : null"
            :tone="$post['expert'] ? 'mint' : 'surface'"
        />
        <time datetime="{{ $post['datetime'] }}">{{ $post['time'] }}</time>
    </header>

    <div class="community-post__body">
        <h3>{{ $post['title'] }}</h3>
        <p>{{ $post['body'] }}</p>
        <x-ui.tag-list :items="$post['tags']" empty="No labels." />
    </div>

    @if ($post['image'])
        <x-ui.responsive-image
            :src="$post['image']"
            :alt="$post['image_alt']"
            :width="720"
            :height="480"
            sizes="(min-width: 1024px) 680px, 100vw"
            class="community-post__image"
        />
    @endif

    <footer class="community-post__footer" aria-label="Publication activity">
        <x-ui.icon-text icon="heart">{{ $post['stats']['reactions'] }} reactions</x-ui.icon-text>
        <x-ui.icon-text icon="message-circle">{{ $post['stats']['comments'] }} comments</x-ui.icon-text>
        <x-ui.icon-text icon="bookmark">{{ $post['stats']['saves'] }} saves</x-ui.icon-text>
    </footer>
</article>
