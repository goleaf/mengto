@props(['media', 'eager' => false])

@php($isCarousel = count($media) > 1)

<div @class(['post-media', 'post-media--carousel' => $isCarousel])>
    @foreach ($media as $item)
        <figure class="post-media__item">
            @if ($item['type'] === 'video')
                <video
                    controls
                    preload="metadata"
                    playsinline
                    poster="{{ $item['poster'] }}"
                    aria-label="{{ $item['alt'] }}"
                    class="post-media__video"
                >
                    <source src="{{ $item['source'] }}" type="{{ $item['mime'] }}">
                    Your browser does not support embedded video.
                </video>
            @else
                <a href="{{ $item['image'] }}" target="_blank" rel="noopener" aria-label="Open full-size image">
                    <x-responsive-image
                        :src="$item['image']"
                        :small="$item['image_small']"
                        :medium="$item['image_medium']"
                        :alt="$item['alt']"
                        :width="1200"
                        :height="900"
                        sizes="(min-width: 1024px) 640px, calc(100vw - 2rem)"
                        :eager="$eager && $loop->first"
                        class="post-media__image"
                    />
                </a>
            @endif

            @if ($item['caption'] ?? null)
                <figcaption>
                    {{ $item['caption'] }}
                    @if ($item['attribution_url'] ?? null)
                        <a href="{{ $item['attribution_url'] }}" target="_blank" rel="noopener">
                            {{ $item['attribution'] }}
                        </a>
                    @endif
                </figcaption>
            @endif
        </figure>
    @endforeach

    @if ($isCarousel)
        <span class="post-media__count">{{ count($media) }} photos</span>
    @endif
</div>
