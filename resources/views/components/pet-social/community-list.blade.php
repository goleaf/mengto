@props([
    'communities' => [],
    'empty' => 'No communities joined yet.',
])

<div role="list" {{ $attributes->class(['pc-content-list']) }}>
    @forelse ($communities as $community)
        <article role="listitem" class="pc-content-list__item">
            <h3 class="pc-content-list__title">{{ $community['name'] }}</h3>
            <p class="pc-content-list__detail">{{ $community['topic'] }}</p>
            <p class="pc-content-list__meta">{{ $community['members'] }}</p>
        </article>
    @empty
        <p role="listitem" class="pc-content-list__empty">{{ $empty }}</p>
    @endforelse
</div>
