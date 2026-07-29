@props(['text' => null])

<p {{ $attributes->class(['pc-section-copy']) }}>
    {{ $text ?? $slot }}
</p>
