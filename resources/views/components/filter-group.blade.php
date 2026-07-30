@props([
    'filters',
    'label',
    'size' => 'compact',
    'empty' => 'Filters unavailable.',
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
            :label="$filter"
            :active="$active ? \Illuminate\Support\Str::slug($filter) === $active : $loop->first"
            :size="$size"
            :type="$submit ? 'submit' : 'button'"
        />
    @empty
        <span class="text-sm text-paw-muted">{{ $empty }}</span>
    @endforelse
</div>
