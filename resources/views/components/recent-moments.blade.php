@props([
    'posts',
    'eyebrow',
    'title' => 'Recent moments',
    'section' => 'recent-moments',
])

<x-collection-section
    :section="$section"
    :eyebrow="$eyebrow"
    :title="$title"
>
    @forelse ($posts as $post)
        <x-feed-card :post="$post" heading-level="3" />
    @empty
        <x-empty-state
            icon="images"
            title="No moments shared yet"
            compact
            role="listitem"
        />
    @endforelse
</x-collection-section>
