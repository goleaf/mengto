@props(['photos' => []])

<section data-section="gallery" {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <x-section-heading eyebrow="{{ __('ui.field_notes_10894d52cb') }}" title="{{ __('ui.scout_s_gallery_e6c4bbb9a7') }}" />

    <div class="mt-4 grid gap-3 sm:grid-cols-2">
        @forelse ($photos as $photo)
            <figure @class([
                'overflow-hidden rounded-md border border-paw-line bg-paw-paper',
                'sm:col-span-2' => $loop->first,
            ])>
                <x-responsive-image
                    :src="$photo['image']"
                    :small="$photo['image_small'] ?? null"
                    :medium="$photo['image_medium'] ?? null"
                    :alt="$photo['alt']"
                    :width="1200"
                    :height="$loop->first ? 675 : 900"
                    :sizes="$loop->first ? '(min-width: 1024px) 50vw, calc(100vw - 3rem)' : '(min-width: 640px) 50vw, calc(100vw - 3rem)'"
                    @class([
                        'w-full object-cover',
                        'aspect-[16/9]' => $loop->first,
                        'aspect-[4/3]' => ! $loop->first,
                    ])
                />
                <figcaption class="px-3 py-2.5 text-sm text-paw-muted">{{ $photo['caption'] }}</figcaption>
            </figure>
        @empty
            <x-empty-state
                icon="images"
                title="{{ __('ui.no_photos_shared_yet_7c4695ca73') }}"
                compact
                class="sm:col-span-2"
            />
        @endforelse
    </div>
</section>
