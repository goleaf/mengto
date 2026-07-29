@props(['title', 'facts' => [], 'section'])

<section data-section="{{ $section }}" {{ $attributes->merge(['class' => 'pc-panel pc-panel--padded']) }}>
    <h2 class="text-base font-semibold text-paw-ink">{{ $title }}</h2>

    <x-pet-social.definition-list :items="$facts" class="mt-3" />
</section>
