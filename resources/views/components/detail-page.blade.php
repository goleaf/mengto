@props([
    'owner',
    'title',
    'activeSection',
    'section',
    'backHref',
    'backLabel',
])

<x-app-shell :owner="$owner" :title="$title" :active-section="$activeSection">
    <div data-section="{{ $section }}" {{ $attributes->class(['detail-page']) }}>
        <x-text-link :href="$backHref" icon="arrow-left" variant="back">
            {{ $backLabel }}
        </x-text-link>

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
