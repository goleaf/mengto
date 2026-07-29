@props([
    'detail',
    'section',
    'primaryLabel',
    'primaryIcon',
    'secondaryLabel',
    'secondaryIcon',
    'summaryLabel',
    'summaryIcons' => [],
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['pc-panel', 'pc-panel--clip', 'pc-detail-hero']) }}
>
    <x-pet-social.responsive-image
        :src="$detail['image']"
        :small="$detail['image_small'] ?? null"
        :medium="$detail['image_medium'] ?? null"
        :alt="$detail['image_alt']"
        :width="1200"
        :height="800"
        sizes="(min-width: 1280px) 1216px, calc(100vw - 2rem)"
        :eager="true"
        class="pc-detail-hero__cover"
    />

    <div class="pc-detail-hero__body">
        <div class="pc-detail-hero__heading-row">
            <x-pet-social.detail-identity :detail="$detail" />

            <x-pet-social.action-group class="pc-detail-hero__actions">
                <x-pet-social.static-action
                    :label="$secondaryLabel"
                    :icon="$secondaryIcon"
                    variant="paper"
                    size="profile"
                />
                <x-pet-social.static-action
                    :label="$primaryLabel"
                    :icon="$primaryIcon"
                    variant="primary"
                    size="profile"
                />
            </x-pet-social.action-group>
        </div>

        <x-pet-social.detail-meta-list :items="$detail['meta']" />

        <x-pet-social.tag-list :items="$detail['tags']" empty="No topics listed." roomy class="pc-detail-hero__tags" />

        <x-pet-social.stat-grid
            :items="$detail['stats']"
            :label="$summaryLabel"
            :icons="$summaryIcons"
            empty="Summary unavailable."
            large
        />
    </div>
</section>
