@props(['title', 'section' => null, 'href' => null])

<section @if ($section) data-section="{{ $section }}" @endif {{ $attributes->merge(['class' => 'panel panel--padded']) }}>
    <div class="mb-4 flex items-center justify-between gap-3">
        <h2 class="text-sm font-semibold text-paw-ink">{{ $title }}</h2>
        @if ($href)
            <x-action-control :href="$href" label="{{ __('ui.view') }}" icon="eye" variant="quiet" size="micro" />
        @endif
    </div>

    {{ $slot }}
</section>
