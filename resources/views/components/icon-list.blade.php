@props([
    'items' => [],
    'empty' => __('ui.no_details_available_1c787606df'),
])

<div role="list" {{ $attributes->class(['icon-list']) }}>
    @forelse ($items as $item)
        <article role="listitem" class="icon-list__item">
            <span class="icon-list__icon" aria-hidden="true">
                <x-ui-icon :name="$item['icon']" />
            </span>
            <div class="icon-list__content">
                <h3 class="icon-list__title">{{ $item['title'] }}</h3>
                <p class="icon-list__description">{{ $item['description'] }}</p>
            </div>
        </article>
    @empty
        <p role="listitem" class="icon-list__empty">{{ $empty }}</p>
    @endforelse
</div>
