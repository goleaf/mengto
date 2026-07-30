@props([
    'owner',
    'title',
    'activeSection',
    'section',
    'backHref',
    'backLabel',
])

<x-layout.app-shell :owner="$owner" :title="$title" :active-section="$activeSection">
    <div data-section="{{ $section }}" {{ $attributes->class(['detail-page']) }}>
        <x-ui.text-link :href="$backHref" icon="arrow-left" variant="back">
            {{ $backLabel }}
        </x-ui.text-link>

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
</x-layout.app-shell>
