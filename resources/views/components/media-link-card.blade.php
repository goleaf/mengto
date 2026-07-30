<a
    href="{{ $href }}"
    aria-label="{{ $item['title'] }}: {{ $item['meta'] }}"
    {{ $attributes->class('media-link') }}
>
    <x-responsive-image
        :src="$item['image']"
        :small="$item['image_small'] ?? null"
        :medium="$item['image_medium'] ?? null"
        :alt="$item['image_alt']"
        :width="1200"
        :height="800"
        sizes="(min-width: 640px) 33vw, calc(100vw - 2rem)"
        :eager="$eager"
        class="media-link__image"
    />

    <span class="media-link__body">
        <span class="media-link__meta">{{ $item['meta'] }}</span>
        <span class="media-link__title">{{ $item['title'] }}</span>
        <x-dynamic-component :component="'lucide-'.$item['icon']" class="media-link__icon icon" aria-hidden="true" />
    </span>
</a>
