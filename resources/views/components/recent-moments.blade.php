@props([
    'posts',
    'eyebrow',
    'title' => __('ui.recent_moments_091f9f27cf'),
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
            title="{{ __('ui.no_moments_shared_yet_6f33794cde') }}"
            compact
            role="listitem"
        />
    @endforelse
</x-collection-section>
