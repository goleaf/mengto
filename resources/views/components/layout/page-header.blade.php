@props([
    'eyebrow',
    'title',
    'description',
    'count' => null,
    'actionLabel' => null,
    'actionIcon' => null,
    'actionHref' => null,
    'actionEndpoint' => null,
    'actionPayload' => [],
])

<header {{ $attributes->class('page-header') }}>
    <div class="page-header__content">
        <p class="page-header__eyebrow">{{ $eyebrow }}</p>
        <h1 class="page-header__title">{{ $title }}</h1>
        <p class="page-header__description">{{ $description }}</p>
    </div>

    @if ($count !== null || $actionLabel !== null || isset($actions))
        <div class="page-header__aside">
            @if ($count !== null)
                <p class="page-header__count">{{ $count }}</p>
            @endif

            @isset($actions)
                {{ $actions }}
            @endisset

            @if ($actionLabel !== null)
                <x-ui.action-control
                    :label="$actionLabel"
                    :icon="$actionIcon"
                    :href="$actionHref"
                    :endpoint="$actionEndpoint"
                    :payload="$actionPayload"
                    size="regular"
                />
            @endif
        </div>
    @endif
</header>
