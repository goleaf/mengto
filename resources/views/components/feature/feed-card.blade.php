@props(['post', 'headingLevel' => 2, 'eager' => false])

<article
    role="listitem"
    @if ($post['anchor'] ?? null) id="{{ $post['anchor'] }}" @endif
    {{ $attributes->merge(['class' => 'panel feed-card']) }}
>
    @if (isset($post['format']))
        @if ($post['urgent'])
            <div class="post-alert">
                <x-lucide-siren class="icon icon--sm" aria-hidden="true" />
                <span>Active local alert</span>
            </div>
        @elseif ($post['verified'] && $post['format'] === 'expert')
            <div class="post-expert">
                <x-lucide-badge-check class="icon icon--sm" aria-hidden="true" />
                <span>Verified professional context</span>
            </div>
        @endif

        <div class="p-4 sm:p-5">
            <div class="post-heading-row">
                <x-object.post-heading :post="$post" :level="$headingLevel" />
                <x-feature.post-action-menu :post="$post" />
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
                <x-object.repost-source :post="$post['original']" />
            @endif

            <x-object.post-context :post="$post" />
            <x-ui.tag-list :items="$post['tags']" empty="No tags" class="mt-4" />
        </div>

        <x-object.post-media :post="$post" :eager="$eager" />

        @if ($post['format'] === 'poll' && ($post['poll'] ?? null))
            <x-object.post-poll :poll="$post['poll']" />
        @endif

        <x-object.post-social-proof :post="$post" />
        <x-feature.feed-action-bar :post="$post" />

        @if ($post['why'] && $post['status'] === 'published')
            <div class="post-why">
                <x-lucide-sparkles class="icon icon--sm" aria-hidden="true" />
                <span>{{ $post['why'] }}</span>
            </div>
        @endif
    @else
    <div class="p-4 sm:p-5">
        <x-object.post-heading :post="$post" :level="$headingLevel" />

        <p class="mt-4 text-sm leading-6 text-paw-ink">{{ $post['body'] }}</p>

        <x-ui.tag-list :items="$post['tags']" empty="No tags" class="mt-4" />
    </div>

    <x-ui.responsive-image
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

    <x-feature.feed-action-bar :post="$post" />
    @endif
</article>
