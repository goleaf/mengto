@props([
    'eyebrow',
    'title',
    'description',
    'headingId' => 'page-heading',
    'metaLabel' => null,
    'count' => null,
    'actionLabel' => null,
    'actionIcon' => null,
    'actionHref' => null,
    'actionEndpoint' => null,
    'actionPayload' => [],
    'actionVariant' => 'primary',
])

<header
    data-page-identity="canonical"
    aria-labelledby="{{ $headingId }}"
    {{ $attributes->class('page-header') }}
>
    <div class="page-header__content" data-page-header-content>
        <p class="page-header__eyebrow">{{ $eyebrow }}</p>
        <h1 id="{{ $headingId }}" class="page-header__title">{{ $title }}</h1>
        <p class="page-header__description">{{ $description }}</p>
    </div>

    @if ($count !== null || isset($meta) || $actionLabel !== null || isset($actions))
        <div class="page-header__aside">
            @isset($meta)
                <div
                    class="page-header__meta"
                    @if ($metaLabel !== null) aria-label="{{ $metaLabel }}" @endif
                >
                    {{ $meta }}
                </div>
            @elseif ($count !== null)
                <p class="page-header__count">{{ $count }}</p>
            @endisset

            @if ($actionLabel !== null || isset($actions))
                <div class="page-header__actions">
                    @isset($actions)
                        {{ $actions }}
                    @endisset

                    @if ($actionLabel !== null)
                        <x-action-control
                            :label="$actionLabel"
                            :icon="$actionIcon"
                            :href="$actionHref"
                            :endpoint="$actionEndpoint"
                            :payload="$actionPayload"
                            :variant="$actionVariant"
                            size="regular"
                        />
                    @endif
                </div>
            @endif
        </div>
    @endif
</header>
