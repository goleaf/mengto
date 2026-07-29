@props(['results'])

<section data-section="discover-results" aria-labelledby="discover-results-title">
    <div class="flex items-end justify-between gap-4">
        <x-pet-social.section-heading
            eyebrow="Best nearby"
            title="Top matches"
            title-id="discover-results-title"
            size="directory"
        />
        <p class="text-xs font-semibold text-paw-muted">Sorted for Mia & Scout</p>
    </div>

    <div role="list" class="mt-4 grid gap-4">
        @forelse ($results as $result)
            <x-pet-social.discover-result :result="$result" :eager="$loop->first" />
        @empty
            <x-pet-social.empty-state
                icon="search-x"
                title="No nearby matches"
                role="listitem"
                description="New local pets, people, and plans will appear here."
            />
        @endforelse
    </div>
</section>
