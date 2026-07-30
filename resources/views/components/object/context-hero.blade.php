@props([
    'context',
    'section',
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['panel', 'panel--clip', 'context-hero']) }}
>
    <x-ui.responsive-image
        :src="$context['image']"
        :small="$context['image_small']"
        :medium="$context['image_medium']"
        :alt="$context['image_alt']"
        :width="1200"
        :height="800"
        sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"
        :eager="true"
        class="context-hero__image"
    />

    <div class="context-hero__body">
        <div class="context-hero__copy">
            <p class="context-hero__eyebrow">{{ $context['eyebrow'] }}</p>
            <h1 class="context-hero__title">{{ $context['title'] }}</h1>
            <p class="context-hero__description">{{ $context['description'] }}</p>
        </div>

        @if (isset($context['status_label']) || isset($actions))
            <div class="context-hero__aside">
                @if (isset($context['status_label']))
                    <x-ui.status-badge
                        :label="$context['status_label']"
                        :icon="$context['status_icon']"
                        :tone="$context['status_tone']"
                    />
                @endif

                @isset($actions)
                    {{ $actions }}
                @endisset
            </div>
        @endif
    </div>
</section>
