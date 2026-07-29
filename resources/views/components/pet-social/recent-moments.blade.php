@props([
    'posts',
    'eyebrow',
    'title' => 'Recent moments',
    'section' => 'recent-moments',
])

<x-pet-social.collection-section
    :section="$section"
    :eyebrow="$eyebrow"
    :title="$title"
>
    @forelse ($posts as $post)
        <x-pet-social.feed-card :post="$post" heading-level="3" />
    @empty
        <x-pet-social.empty-state
            icon="images"
            title="No moments shared yet"
            compact
            role="listitem"
        />
    @endforelse
</x-pet-social.collection-section>
