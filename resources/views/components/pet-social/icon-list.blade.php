@props([
    'items' => [],
    'empty' => 'No details available.',
])

<div role="list" {{ $attributes->class(['pc-icon-list']) }}>
    @forelse ($items as $item)
        <article role="listitem" class="pc-icon-list__item">
            <span class="pc-icon-list__icon" aria-hidden="true">
                <x-dynamic-component :component="'lucide-'.$item['icon']" class="pc-icon" />
            </span>
            <div class="pc-icon-list__content">
                <h3 class="pc-icon-list__title">{{ $item['title'] }}</h3>
                <p class="pc-icon-list__description">{{ $item['description'] }}</p>
            </div>
        </article>
    @empty
        <p role="listitem" class="pc-icon-list__empty">{{ $empty }}</p>
    @endforelse
</div>
