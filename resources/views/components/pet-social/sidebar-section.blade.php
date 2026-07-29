@props(['title', 'section' => null])

<section @if ($section) data-section="{{ $section }}" @endif {{ $attributes->merge(['class' => 'pc-panel pc-panel--padded']) }}>
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-sm font-semibold text-paw-ink">{{ $title }}</h2>
        <x-pet-social.static-action label="View" icon="eye" variant="quiet" size="micro" />
    </div>

    {{ $slot }}
</section>
