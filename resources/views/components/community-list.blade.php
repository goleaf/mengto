@props([
    'communities' => [],
    'empty' => __('ui.no_communities_joined_yet_03e4154cf1'),
])

<div role="list" {{ $attributes->class(['content-list']) }}>
    @forelse ($communities as $community)
        <article role="listitem" class="content-list__item">
            <div class="flex min-w-0 items-center gap-2">
                @if ($community['icon'] ?? null)
                    <x-ui-icon size="sm" :name="$community['icon']" class="shrink-0 text-paw-leaf" />
                @endif
                <h3 class="content-list__title min-w-0">{{ $community['name'] }}</h3>
            </div>
            <p class="content-list__detail">{{ $community['topic'] }}</p>
            <x-icon-text icon="users" class="content-list__meta">{{ $community['members'] }}</x-icon-text>
        </article>
    @empty
        <p role="listitem" class="content-list__empty">{{ $empty }}</p>
    @endforelse
</div>
