@props([
    'section' => null,
    'eyebrow' => null,
    'title',
    'titleId' => null,
    'size' => 'regular',
    'tone' => 'leaf',
    'meta' => null,
])

<x-ui.panel :section="$section" {{ $attributes }}>
    @if (! $eyebrow)
        <x-ui.panel-heading :title="$title" :meta="$meta">
            @isset($aside)
                <x-slot:aside>{{ $aside }}</x-slot:aside>
            @endisset
        </x-ui.panel-heading>
    @elseif (isset($aside) || $meta)
        <x-ui.panel-heading :meta="$meta">
            <x-slot:heading>
                <x-ui.section-heading
                    :eyebrow="$eyebrow"
                    :title="$title"
                    :title-id="$titleId"
                    :size="$size"
                    :tone="$tone"
                />
            </x-slot:heading>

            @isset($aside)
                <x-slot:aside>{{ $aside }}</x-slot:aside>
            @endisset
        </x-ui.panel-heading>
    @else
        <x-ui.section-heading
            :eyebrow="$eyebrow"
            :title="$title"
            :title-id="$titleId"
            :size="$size"
            :tone="$tone"
        />
    @endif

    {{ $slot }}
</x-ui.panel>
