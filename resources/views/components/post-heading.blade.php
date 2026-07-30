@props(['post', 'level' => 2])

<div class="post-identity">
    @if ($post['avatar'] ?? null)
        @if ($post['author_route'] ?? null)
            <a href="{{ route($post['author_route'], $post['author_parameters'] ?? []) }}" class="post-identity__avatar">
                <x-avatar :src="$post['avatar']" :alt="$post['author']" size="thread" />
            </a>
        @else
            <x-avatar :src="$post['avatar']" :alt="$post['author']" size="thread" />
        @endif
    @endif

    <div class="min-w-0">
        @if ((int) $level === 3)
            <h3 class="post-identity__name">
        @else
            <h2 class="post-identity__name">
        @endif
                @if ($post['author_route'] ?? null)
                    <a href="{{ route($post['author_route'], $post['author_parameters'] ?? []) }}">{{ $post['author'] }}</a>
                @else
                    {{ $post['author'] }}
                @endif
                @if ($post['verified'] ?? false)
                    <x-lucide-badge-check class="icon icon--sm text-paw-teal" aria-label="{{ __('ui.verified_profile_445947549b') }}" />
                @endif
        @if ((int) $level === 3)
            </h3>
        @else
            </h2>
        @endif

        <p class="post-identity__meta">
            {{ $post['handle'] ?? $post['pet'] }} ·
            <time datetime="{{ $post['datetime'] }}">{{ $post['time'] }}</time>
            @if ($post['audience'] ?? null)
                · <x-lucide-users class="icon icon--xs" aria-hidden="true" />
                <span>{{ $post['audience'] }}</span>
            @endif
        </p>

        @if ($post['manager'] ?? null)
            <p class="post-identity__manager">{{ $post['manager'] }}</p>
        @endif
    </div>
</div>
