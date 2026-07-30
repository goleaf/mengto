@props([
    'filters',
    'label',
    'size' => 'compact',
    'empty' => __('ui.filters_unavailable_8c77a59db6'),
    'active' => null,
    'submit' => false,
])

<div
    role="group"
    aria-label="{{ $label }}"
    {{ $attributes->class(['flex flex-wrap gap-2']) }}
>
    @forelse ($filters as $filter)
        <x-filter-chip
            :label="$filter['label']"
            :value="$filter['value']"
            :active="$active ? $filter['value'] === $active : $loop->first"
            :size="$size"
            :type="$submit ? 'submit' : 'button'"
        />
    @empty
        <span class="text-sm text-paw-muted">{{ $empty }}</span>
    @endforelse
</div>
