@props(['managers'])

<div role="list" {{ $attributes->class(['manager-list']) }}>
    @forelse ($managers as $manager)
        <article role="listitem" class="manager-list__item">
            <x-ui.avatar
                :src="$manager['avatar']"
                :alt="$manager['name']"
                size="compact"
                lazy
            />
            <div class="min-w-0">
                <h3 class="manager-list__name">{{ $manager['name'] }}</h3>
                <p class="manager-list__role">{{ $manager['role'] }}</p>
                <p class="manager-list__detail">{{ $manager['detail'] }}</p>
            </div>
        </article>
    @empty
        <x-ui.empty-state
            icon="users-round"
            title="No managers listed"
            compact
        />
    @endforelse
</div>
