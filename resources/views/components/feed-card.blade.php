@props(['post', 'headingLevel' => 2, 'eager' => false])

<article
    role="listitem"
    @if ($post['anchor'] ?? null) id="{{ $post['anchor'] }}" @endif
    {{ $attributes->merge(['class' => 'panel feed-card']) }}
>
    @if (isset($post['format']))
        @if ($post['urgent'])
            <div class="post-alert">
                <x-ui-icon name="siren" size="sm" />
                <span>{{ __('ui.active_local_alert') }}</span>
            </div>
        @elseif ($post['verified'] && $post['format'] === 'expert')
            <div class="post-expert">
                <x-ui-icon name="badge-check" size="sm" />
                <span>{{ __('ui.verified_professional_context') }}</span>
            </div>
        @endif

        <div class="p-4 sm:p-5">
            <div class="post-heading-row">
                <x-post-heading :post="$post" :level="$headingLevel" />
                <x-post-action-menu :post="$post" />
            </div>

            @if ($post['title'])
                @if ((int) $headingLevel === 3)
                    <h3 class="post-title">{{ $post['title'] }}</h3>
                @else
                    <h2 class="post-title">{{ $post['title'] }}</h2>
                @endif
            @endif

            <p class="post-body">{{ $post['body'] }}</p>

            @if ($post['original'] ?? null)
                <x-repost-source :post="$post['original']" />
            @endif

            <x-post-context :post="$post" />
            <x-tag-list :items="$post['tags']" empty="{{ __('ui.no_tags') }}" class="mt-4" />
        </div>

        <x-post-media :post="$post" :eager="$eager" />

        @if ($post['format'] === 'poll' && ($post['poll'] ?? null))
            <x-post-poll :poll="$post['poll']" />
        @endif

        <x-post-social-proof :post="$post" />
        <x-feed-action-bar :post="$post" />

        @if ($post['why'] && $post['status'] === 'published')
            <div class="post-why">
                <x-ui-icon name="sparkles" size="sm" />
                <span>{{ $post['why'] }}</span>
            </div>
        @endif
    @else
    <div class="p-4 sm:p-5">
        <x-post-heading :post="$post" :level="$headingLevel" />

        <p class="mt-4 text-sm leading-6 text-paw-ink">{{ $post['body'] }}</p>

        <x-tag-list :items="$post['tags']" empty="{{ __('ui.no_tags') }}" class="mt-4" />
    </div>

    <x-responsive-image
        :src="$post['image']"
        :small="$post['image_small'] ?? null"
        :medium="$post['image_medium'] ?? null"
        :alt="$post['image_alt']"
        :width="1200"
        :height="900"
        sizes="(min-width: 1024px) 50vw, calc(100vw - 2rem)"
        :eager="$eager"
        class="aspect-[4/3] w-full object-cover"
    />

    <x-feed-action-bar :post="$post" />
    @endif
</article>
