@props(['media', 'eager' => false])

<div
    data-photo-gallery
    data-photo-close="{{ __('ui.photo_viewer_close') }}"
    data-photo-zoom="{{ __('ui.photo_viewer_zoom') }}"
    data-photo-previous="{{ __('ui.photo_viewer_previous') }}"
    data-photo-next="{{ __('ui.photo_viewer_next') }}"
    data-photo-error="{{ __('ui.photo_viewer_error') }}"
    data-photo-separator="{{ __('ui.photo_viewer_separator') }}"
    aria-label="{{ __('ui.photo_viewer_gallery') }}"
    @class(['post-media', 'post-media--carousel' => count($media) > 1])
>
    @forelse ($media as $item)
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
                    {{ __('ui.your_browser_does_not_support_embedded_video') }}
                </video>
            @else
                <a
                    href="{{ $item['image'] }}"
                    target="_blank"
                    rel="noopener"
                    data-photo-trigger
                    data-photo-key="{{ $item['photo_key'] }}"
                    data-pswp-width="1200"
                    data-pswp-height="900"
                    data-pswp-srcset="{{ $item['viewer_srcset'] }}"
                    data-cropped="true"
                    aria-label="{{ __('ui.photo_viewer_open_photo', ['position' => $item['position']]) }}"
                    class="photo-viewer-trigger"
                >
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
                    <span class="photo-viewer-trigger__hint" aria-hidden="true">
                        <x-ui-icon name="expand" size="sm" />
                    </span>
                </a>

                <template data-photo-panel-template="{{ $item['photo_key'] }}">
                    <x-photo-social-panel :photo="$item" />
                </template>
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
    @empty
        <span class="sr-only">{{ __('ui.photo_viewer_gallery_empty') }}</span>
    @endforelse

    @if (count($media) > 1)
        <span class="post-media__count">{{ trans_choice('presentation.photos_count', count($media), ['count' => count($media)]) }}</span>
    @endif
</div>
