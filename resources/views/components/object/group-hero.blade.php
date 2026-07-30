@props(['group'])

<section class="group-hero" aria-labelledby="group-title">
    <div class="group-hero__media">
        <x-ui.responsive-image
            :src="$group['image']"
            :small="$group['image_small']"
            :medium="$group['image_medium']"
            :alt="$group['image_alt']"
            :width="1600"
            :height="800"
            sizes="(min-width: 1280px) 1216px, 100vw"
            eager
            class="group-hero__image"
        />
        <div class="group-hero__badges">
            <x-ui.status-badge :label="$group['category']" tone="paper" />
            <x-ui.status-badge
                :label="$group['privacy_label']"
                :icon="$group['privacy_icon']"
                tone="paper"
            />
            @if ($group['official'])
                <x-ui.status-badge
                    :label="$group['verified_label']"
                    icon="badge-check"
                    tone="mint"
                />
            @endif
        </div>
    </div>

    <div class="group-hero__body">
        <div class="group-hero__copy">
            <p class="group-hero__eyebrow">{{ $group['topic'] }}</p>
            <h1 id="group-title" class="group-hero__title">{{ $group['name'] }}</h1>
            <p class="group-hero__description">{{ $group['long_description'] }}</p>

            <div class="group-hero__meta" role="list" aria-label="Group details">
                @forelse ($group['meta'] as $item)
                    <x-ui.icon-text :icon="$item['icon']" role="listitem">
                        {{ $item['label'] }}
                    </x-ui.icon-text>
                @empty
                    <x-ui.icon-text icon="info" role="listitem">
                        No public group details.
                    </x-ui.icon-text>
                @endforelse
            </div>
        </div>

        <div class="group-hero__commands">
            <x-ui.action-control
                :label="$group['primary_action']['label']"
                :icon="$group['primary_action']['icon']"
                :endpoint="$group['primary_action']['endpoint']"
                :payload="$group['primary_action']['payload']"
                :variant="$group['primary_action']['variant']"
                :active="$group['primary_action']['active']"
                :pressed="$group['primary_action']['pressed']"
                size="regular"
            />
            <x-ui.action-control
                :label="$group['share_action']['label']"
                :icon="$group['share_action']['icon']"
                :href="$group['share_action']['href']"
                :variant="$group['share_action']['variant']"
                size="regular"
            />
            <x-ui.action-control
                :label="$group['report_action']['label']"
                :icon="$group['report_action']['icon']"
                :href="$group['report_action']['href']"
                :variant="$group['report_action']['variant']"
                size="icon"
                :title="$group['report_action']['label']"
                :aria-label="$group['report_action']['label']"
            />
        </div>
    </div>

    <x-ui.summary-strip
        :items="$group['stats']"
        label="Group summary"
        :icons="['users', 'paw-print', 'newspaper', 'calendar-days']"
        :columns="4"
        class="group-hero__summary"
    />
</section>
