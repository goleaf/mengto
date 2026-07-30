@props([
    'detail',
    'section',
    'primaryLabel',
    'primaryIcon',
    'secondaryLabel',
    'secondaryIcon',
    'summaryLabel',
    'summaryIcons' => [],
    'primaryEndpoint' => null,
    'primaryPayload' => [],
    'primaryActive' => false,
    'primaryActiveLabel' => null,
    'primaryActiveIcon' => null,
    'secondaryEndpoint' => null,
    'secondaryPayload' => [],
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['panel', 'panel--clip', 'detail-hero']) }}
>
    <x-responsive-image
        :src="$detail['image']"
        :small="$detail['image_small'] ?? null"
        :medium="$detail['image_medium'] ?? null"
        :alt="$detail['image_alt']"
        :width="1200"
        :height="800"
        sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"
        :eager="true"
        class="detail-hero__cover"
    />

    <div class="detail-hero__body">
        <div class="detail-hero__heading-row">
            <x-detail-identity :detail="$detail" />

            <x-action-pair
                :primary-label="$primaryLabel"
                :primary-icon="$primaryIcon"
                :secondary-label="$secondaryLabel"
                :secondary-icon="$secondaryIcon"
                :primary-endpoint="$primaryEndpoint"
                :primary-payload="$primaryPayload"
                :primary-active="$primaryActive"
                :primary-active-label="$primaryActiveLabel"
                :primary-active-icon="$primaryActiveIcon"
                :secondary-endpoint="$secondaryEndpoint"
                :secondary-payload="$secondaryPayload"
                class="detail-hero__actions"
            />
        </div>

        <x-detail-meta-list :items="$detail['meta']" />

        <x-tag-list :items="$detail['tags']" empty="{{ __('ui.no_topics_listed_5812c5b7da') }}" roomy class="detail-hero__tags" />

        <x-stat-grid
            :items="$detail['stats']"
            :label="$summaryLabel"
            :icons="$summaryIcons"
            empty="{{ __('ui.summary_unavailable_3a2c4e48c8') }}"
            large
        />
    </div>
</section>
