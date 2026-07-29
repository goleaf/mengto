@props([
    'members' => [],
    'empty' => 'No members listed.',
])

<div role="list" {{ $attributes->class(['pc-member-list']) }}>
    @forelse ($members as $member)
        <article role="listitem" class="pc-member-list__item">
            <x-pet-social.initials-avatar
                :initials="$member['initials']"
                :tone="$member['tone']"
                size="regular"
            />
            <div class="pc-member-list__content">
                <h3 class="pc-member-list__name">{{ $member['name'] }}</h3>
                <p class="pc-member-list__detail">{{ $member['detail'] }}</p>
            </div>
        </article>
    @empty
        <p role="listitem" class="pc-member-list__empty">{{ $empty }}</p>
    @endforelse
</div>
