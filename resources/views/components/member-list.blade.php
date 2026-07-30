@props([
    'members' => [],
    'empty' => 'No members listed.',
])

<div role="list" {{ $attributes->class(['member-list']) }}>
    @forelse ($members as $member)
        <article role="listitem" class="member-list__item">
            <x-initials-avatar
                :initials="$member['initials']"
                :tone="$member['tone']"
                size="regular"
            />
            <div class="member-list__content">
                <h3 class="member-list__name">{{ $member['name'] }}</h3>
                <p class="member-list__detail">{{ $member['detail'] }}</p>
            </div>
        </article>
    @empty
        <p role="listitem" class="member-list__empty">{{ $empty }}</p>
    @endforelse
</div>
