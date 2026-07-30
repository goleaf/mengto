@props(['text' => null])

<p {{ $attributes->class(['section-copy']) }}>
    {{ $text ?? $slot }}
</p>
