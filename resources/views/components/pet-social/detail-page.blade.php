@props([
    'owner',
    'title',
    'activeSection',
    'section',
    'backHref',
    'backLabel',
])

<x-pet-social.app-shell :owner="$owner" :title="$title" :active-section="$activeSection">
    <div data-section="{{ $section }}" {{ $attributes->class(['pc-detail-page']) }}>
        <x-pet-social.text-link :href="$backHref" icon="arrow-left" variant="back">
            {{ $backLabel }}
        </x-pet-social.text-link>

        {{ $hero }}

        <div class="pc-detail-layout">
            <div class="pc-detail-main">
                {{ $main }}
            </div>

            <aside class="pc-detail-sidebar">
                {{ $sidebar }}
            </aside>
        </div>
    </div>
</x-pet-social.app-shell>
