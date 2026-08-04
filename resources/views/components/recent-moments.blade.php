@props([
    'posts',
    'eyebrow',
    'title' => __('ui.recent_moments_091f9f27cf'),
    'section' => 'recent-moments',
    'emptyTitle' => __('ui.no_moments_shared_yet_6f33794cde'),
    'icon' => null,
])

<x-collection-section
    :section="$section"
    :eyebrow="$eyebrow"
    :title="$title"
    :icon="$icon"
    {{ $attributes }}
>
    @forelse ($posts as $post)
        <x-feed-card :post="$post" heading-level="3" data-neighbor-profile-moment />
    @empty
        <x-empty-state
            icon="images"
            :title="$emptyTitle"
            compact
            role="listitem"
        />
    @endforelse
</x-collection-section>
