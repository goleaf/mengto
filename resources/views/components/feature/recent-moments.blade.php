@props([
    'posts',
    'eyebrow',
    'title' => 'Recent moments',
    'section' => 'recent-moments',
])

<x-layout.collection-section
    :section="$section"
    :eyebrow="$eyebrow"
    :title="$title"
>
    @forelse ($posts as $post)
        <x-feature.feed-card :post="$post" heading-level="3" />
    @empty
        <x-ui.empty-state
            icon="images"
            title="No moments shared yet"
            compact
            role="listitem"
        />
    @endforelse
</x-layout.collection-section>
