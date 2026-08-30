<x-directory-card data-group-card class="group-card" {{ $attributes }}>
    <x-slot:media>
        <x-card-media
            :src="$group['image']"
            :small="$group['image_small'] ?? null"
            :medium="$group['image_medium'] ?? null"
            :alt="$group['image_alt']"
            :width="1200"
            :height="800"
            sizes="(min-width: 1280px) 390px, (min-width: 640px) calc(50vw - 2rem), calc(100vw - 2rem)"
            :eager="$eager"
            :href="$group['media_target']['url'] ?? null"
            :link-label="$group['media_target']['label'] ?? null"
        >
            <div class="group-card__badges">
                <x-status-badge data-group-card-category :label="$group['category']" />
                @if ($group['privacy_label'] ?? null)
                    <x-status-badge
                        data-group-card-privacy
                        :label="$group['privacy_label']"
                        :icon="$group['privacy_icon'] ?? null"
                        tone="paper"
                    />
                @endif
                @if ($group['official'] ?? false)
                    <x-status-badge data-group-card-official label="{{ __('groups.directory.card.official') }}" icon="badge-check" tone="mint" />
                @endif
            </div>
        </x-card-media>
    </x-slot:media>

    @if ($group['recommendation_reason'] ?? null)
        <x-recommendation-reason
            data-group-card-reason
            :reason="$group['recommendation_reason']"
            class="group-card__reason"
        />
    @else
        <p class="group-card__topic">{{ $group['topic'] }}</p>
    @endif

    <x-card-heading
        :title="$group['name']"
        :href="$group['media_target']['url'] ?? null"
        spacing="compact"
    />
    <x-card-description data-group-card-description>{{ $group['description'] }}</x-card-description>

    <x-tag-list data-group-card-tags :items="$group['tags']" empty="{{ __('groups.directory.card.open_to_new_neighbors') }}" reserve class="group-card__tags" />

    <x-stat-grid
        data-group-card-metrics
        :items="$metrics"
        :icons="['users', 'activity']"
        label="{{ __('groups.directory.card.summary') }}"
        variant="profile"
    />

    @if ($group['next_event'] ?? null)
        <x-icon-text data-group-card-event icon="calendar-clock" class="group-card__event">
            {{ $group['next_event'] }}
        </x-icon-text>
    @endif

    <x-slot:footer>
        <div class="group-card__footer">
            <div data-group-card-organizer class="group-card__organizer">
                <x-initials-avatar :initials="$group['organizer_initials']" />
                <div>
                    <p>{{ __('presentation.group_led_by', ['organizer' => $group['organizer']]) }}</p>
                    <span>{{ $group['location'] ?? __('ui.community_organizer') }}</span>
                </div>
            </div>

            <x-card-action-row fill class="group-card__actions">
                @if ($group['secondary_action'] ?? null)
                    <x-action-control
                        :label="$group['secondary_action']['label']"
                        :icon="$group['secondary_action']['icon']"
                        :endpoint="$group['secondary_action']['endpoint']"
                        :payload="$group['secondary_action']['payload']"
                        :variant="$group['secondary_action']['variant'] ?? 'quiet'"
                        size="icon"
                        :title="$group['secondary_action']['label']"
                        :aria-label="$group['secondary_action']['label']"
                    />
                @endif
                <x-action-control
                    data-group-card-primary-action
                    :label="$primary['label']"
                    :icon="$primary['icon'] ?? null"
                    :endpoint="$primary['endpoint'] ?? null"
                    :payload="$primary['payload'] ?? []"
                    :href="$primary['href'] ?? null"
                    :variant="$primary['variant'] ?? 'paper'"
                    :active="$primary['active'] ?? false"
                    :pressed="$primary['pressed'] ?? null"
                />
            </x-card-action-row>
        </div>
    </x-slot:footer>
</x-directory-card>
