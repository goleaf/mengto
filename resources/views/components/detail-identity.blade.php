@props(['detail'])

<div {{ $attributes->class(['detail-hero__identity']) }}>
    <p class="detail-hero__eyebrow">{{ $detail['eyebrow'] }}</p>
    <h1 class="detail-hero__title">{{ $detail['title'] }}</h1>
    <p class="detail-hero__description">{{ $detail['description'] }}</p>
</div>
