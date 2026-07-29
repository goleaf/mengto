@props([
    'profile',
    'section',
    'eyebrow',
    'avatarAlt',
    'coverHeight' => 720,
    'coverSmall' => null,
    'coverMedium' => null,
    'coverSizes' => '(min-width: 1280px) 1216px, calc(100vw - 2rem)',
    'secondaryLabel',
    'secondaryIcon',
    'primaryLabel',
    'primaryIcon',
    'summaryLabel',
    'summaryIcons' => [],
])

<section
    data-section="{{ $section }}"
    {{ $attributes->class(['pc-panel', 'pc-panel--clip', 'pc-profile-hero']) }}
>
    <x-pet-social.responsive-image
        data-profile-cover
        :src="$profile['cover_image']"
        :small="$coverSmall"
        :medium="$coverMedium"
        :alt="$profile['cover_image_alt']"
        :width="1600"
        :height="$coverHeight"
        :small-width="720"
        :medium-width="1200"
        :sizes="$coverSizes"
        :eager="true"
        class="pc-profile-hero__cover"
    />

    <div class="pc-profile-hero__body">
        <div class="pc-profile-hero__identity-row">
            <x-pet-social.profile-identity
                :profile="$profile"
                :eyebrow="$eyebrow"
                :avatar-alt="$avatarAlt"
            />

            <x-pet-social.action-group class="pc-profile-hero__actions">
                <x-pet-social.static-action :label="$secondaryLabel" :icon="$secondaryIcon" variant="paper" size="profile" />
                <x-pet-social.static-action :label="$primaryLabel" :icon="$primaryIcon" variant="primary" size="profile" />
            </x-pet-social.action-group>
        </div>

        <x-pet-social.status-badge :label="$profile['status']" icon="circle-check" tone="mint" size="regular" class="mt-4" />

        <x-pet-social.stat-grid
            :items="$profile['stats']"
            :label="$summaryLabel"
            :icons="$summaryIcons"
            empty="Profile summary unavailable."
        />
    </div>
</section>
