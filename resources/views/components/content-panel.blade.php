@props([
    'section' => null,
    'eyebrow' => null,
    'title',
    'titleId' => null,
    'size' => 'regular',
    'tone' => 'leaf',
    'meta' => null,
    'icon' => null,
])

<x-panel :section="$section" {{ $attributes }}>
    @if (! $eyebrow)
        <x-panel-heading :title="$title" :meta="$meta" :icon="$icon">
            @isset($aside)
                <x-slot:aside>{{ $aside }}</x-slot:aside>
            @endisset
        </x-panel-heading>
    @elseif (isset($aside) || $meta)
        <x-panel-heading :meta="$meta">
            <x-slot:heading>
                <x-section-heading
                    :eyebrow="$eyebrow"
                    :title="$title"
                    :title-id="$titleId"
                    :size="$size"
                    :tone="$tone"
                    :icon="$icon"
                />
            </x-slot:heading>

            @isset($aside)
                <x-slot:aside>{{ $aside }}</x-slot:aside>
            @endisset
        </x-panel-heading>
    @else
        <x-section-heading
            :eyebrow="$eyebrow"
            :title="$title"
            :title-id="$titleId"
            :size="$size"
            :tone="$tone"
            :icon="$icon"
        />
    @endif

    {{ $slot }}
</x-panel>
