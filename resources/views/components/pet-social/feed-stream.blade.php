@props(['posts'])

<section data-section="feed">
    <div class="flex items-end justify-between gap-4">
        <div>
            <p class="pc-section-heading__eyebrow">Neighborhood feed</p>
            <h1 class="mt-2 text-2xl font-semibold text-paw-ink sm:text-3xl lg:text-2xl xl:text-3xl">Today around your pack</h1>
        </div>

        <x-pet-social.static-action
            label="New post"
            icon="plus"
            size="regular"
            class="pc-action--desktop-only shrink-0"
        />
    </div>

    <div role="list" class="mt-5 grid gap-4">
        @forelse ($posts as $post)
            <x-pet-social.feed-card :post="$post" :eager="$loop->first" />
        @empty
            <x-pet-social.empty-state
                icon="newspaper"
                title="The feed is quiet right now"
                compact
                role="listitem"
            />
        @endforelse
    </div>
</section>
