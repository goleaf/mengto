@props(['title', 'facts' => [], 'section'])

<section data-section="{{ $section }}" {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <h2 class="text-base font-semibold text-paw-ink">{{ $title }}</h2>

    <x-ui.definition-list :items="$facts" class="mt-3" />
</section>
