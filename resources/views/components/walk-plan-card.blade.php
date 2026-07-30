@props(['plan', 'eager' => false])

<article {{ $attributes->class('walk-card') }}>
    <div class="walk-card__media">
        <x-responsive-image
            :src="$plan['participant']['image']"
            :small="$plan['participant']['image_small']"
            :medium="$plan['participant']['image_medium']"
            :alt="$plan['participant']['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1024px) 50vw, 100vw"
            :eager="$eager"
            class="walk-card__image"
        />

        <x-status-badge
            :label="$plan['status_label']"
            :icon="$plan['status_icon']"
            :tone="$plan['status_tone']"
            class="walk-card__status"
        />
    </div>

    <div class="walk-card__body">
        <header class="walk-card__header">
            <p class="walk-card__eyebrow">{{ $plan['participant']['person'] }} and {{ $plan['participant']['pet'] }}</p>
            <h2 class="walk-card__title">{{ $plan['title'] }}</h2>
            <p class="walk-card__description">{{ $plan['body'] }}</p>
        </header>

        <x-walk-plan-meta :plan="$plan" />
        <x-walk-route-timeline :steps="$plan['steps']" />
        <x-walk-plan-actions :plan="$plan" />
    </div>
</article>
