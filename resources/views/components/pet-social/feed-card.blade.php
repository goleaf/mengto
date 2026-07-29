@props(['post', 'headingLevel' => 2, 'eager' => false])

<article role="listitem" {{ $attributes->merge(['class' => 'pc-panel pc-panel--clip']) }}>
    <div class="p-4 sm:p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                @if ((int) $headingLevel === 3)
                    <h3 class="truncate text-base font-semibold text-paw-ink">{{ $post['author'] }}</h3>
                @else
                    <h2 class="truncate text-base font-semibold text-paw-ink">{{ $post['author'] }}</h2>
                @endif
                <p class="mt-1 text-sm text-paw-muted">
                    {{ $post['pet'] }} ·
                    <time datetime="{{ $post['datetime'] }}">{{ $post['time'] }}</time>
                </p>
            </div>
            <button type="button" aria-label="More options" title="More options" aria-disabled="true" disabled class="pc-card-icon-button">
                <x-lucide-ellipsis class="pc-icon pc-icon--sm" aria-hidden="true" />
            </button>
        </div>

        <p class="mt-4 text-sm leading-6 text-paw-ink">{{ $post['body'] }}</p>

        <x-pet-social.tag-list :items="$post['tags']" empty="No tags" class="mt-4" />
    </div>

    <x-pet-social.responsive-image
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

    <div class="pc-feed-actions">
        <x-pet-social.feed-action :label="$post['stats']['paws'].' Paws'" icon="paw-print" />
        <x-pet-social.feed-action :label="$post['stats']['replies'].' Replies'" icon="message-circle" />
        <x-pet-social.feed-action label="Share" icon="share-2" />
    </div>
</article>
