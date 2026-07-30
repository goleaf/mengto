@props([
    'eyebrow',
    'title',
    'titleId',
    'description',
    'items',
])

<section aria-labelledby="{{ $titleId }}" {{ $attributes->class('media-starter') }}>
    <div class="media-starter__heading">
        <x-ui.section-heading
            :eyebrow="$eyebrow"
            :title="$title"
            :title-id="$titleId"
        />
        <p>{{ $description }}</p>
    </div>

    <x-object.media-link-grid :items="$items" class="mt-5" />
</section>
