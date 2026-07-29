@props(['detail'])

<div {{ $attributes->class(['pc-detail-hero__identity']) }}>
    <p class="pc-detail-hero__eyebrow">{{ $detail['eyebrow'] }}</p>
    <h1 class="pc-detail-hero__title">{{ $detail['title'] }}</h1>
    <p class="pc-detail-hero__description">{{ $detail['description'] }}</p>
</div>
