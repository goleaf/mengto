@props([
    'communities' => [],
    'empty' => __('ui.no_communities_joined_yet_03e4154cf1'),
])

<div role="list" {{ $attributes->class(['content-list']) }}>
    @forelse ($communities as $community)
        <article role="listitem" class="content-list__item">
            <h3 class="content-list__title">{{ $community['name'] }}</h3>
            <p class="content-list__detail">{{ $community['topic'] }}</p>
            <p class="content-list__meta">{{ $community['members'] }}</p>
        </article>
    @empty
        <p role="listitem" class="content-list__empty">{{ $empty }}</p>
    @endforelse
</div>
