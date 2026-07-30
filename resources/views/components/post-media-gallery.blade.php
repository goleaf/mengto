@props(['media', 'eager' => false])

<div @class(['post-media', 'post-media--carousel' => count($media) > 1])>
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
                    {{ __('ui.your_browser_does_not_support_embedded_video_7dd705b10c') }}
                </video>
            @else
                <a href="{{ $item['image'] }}" target="_blank" rel="noopener" aria-label="{{ __('ui.open_full_size_image_13c6227aee') }}">
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

    @if (count($media) > 1)
        <span class="post-media__count">{{ trans_choice('presentation.photos_count', count($media), ['count' => count($media)]) }}</span>
    @endif
</div>
