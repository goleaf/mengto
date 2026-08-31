@props([
    'title',
    'activeSection',
    'section',
    'backHref',
    'backLabel',
])

<x-app-shell :title="$title" :active-section="$activeSection">
    <div data-section="{{ $section }}" {{ $attributes->class(['detail-page']) }}>
        <x-detail-navigation :href="$backHref" :label="$backLabel" />

        {{ $hero }}

        <div class="detail-layout">
            <div class="detail-main">
                {{ $main }}
            </div>

            <aside class="detail-sidebar">
                {{ $sidebar }}
            </aside>
        </div>
    </div>
</x-app-shell>
