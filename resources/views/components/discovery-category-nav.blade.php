@props(['categories', 'query', 'activeCategory'])

<section data-section="discover-directions" aria-labelledby="discover-directions-title">
    <x-section-heading
        eyebrow="{{ __('discovery.directions.eyebrow') }}"
        title="{{ __('discovery.directions.title') }}"
        title-id="discover-directions-title"
        size="directory"
    />

    <div class="discovery-directions" role="list">
        @foreach ($categories as $category)
            @if ($category['value'] !== 'all')
                <a
                    href="{{ $category['url'] }}"
                    role="listitem"
                    @if ($category['active']) aria-current="page" @endif
                    class="discovery-direction"
                >
                    <span class="discovery-direction__icon" aria-hidden="true">
                        <x-dynamic-component :component="'lucide-'.$category['icon']" class="icon" />
                    </span>
                    <span class="min-w-0">
                        <span class="discovery-direction__title">{{ $category['label'] }}</span>
                        <span class="discovery-direction__description">{{ $category['description'] }}</span>
                    </span>
                    <x-lucide-chevron-right class="discovery-direction__arrow icon icon--sm" aria-hidden="true" />
                </a>
            @endif
        @endforeach
    </div>
</section>
