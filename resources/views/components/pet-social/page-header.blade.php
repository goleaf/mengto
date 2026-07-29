@props([
    'eyebrow',
    'title',
    'description',
    'count' => null,
    'actionLabel' => null,
    'actionIcon' => null,
])

<header {{ $attributes->class('pc-page-header') }}>
    <div class="pc-page-header__content">
        <p class="pc-page-header__eyebrow">{{ $eyebrow }}</p>
        <h1 class="pc-page-header__title">{{ $title }}</h1>
        <p class="pc-page-header__description">{{ $description }}</p>
    </div>

    @if ($count !== null || $actionLabel !== null)
        <div class="pc-page-header__aside">
            @if ($count !== null)
                <p class="pc-page-header__count">{{ $count }}</p>
            @endif

            @if ($actionLabel !== null)
                <x-pet-social.static-action :label="$actionLabel" :icon="$actionIcon" size="regular" />
            @endif
        </div>
    @endif
</header>
