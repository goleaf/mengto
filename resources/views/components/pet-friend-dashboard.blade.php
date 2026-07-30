@props([
    'summary',
    'center',
])

<section class="friend-dashboard" aria-label="{{ __('ui.pet_friendship_center_7323941f70') }}">
    <x-pet-friend-switcher :pets="$center['pet_switcher']" />

    <x-summary-strip
        :items="$summary['stats']"
        label="{{ __('ui.pet_friendship_summary_fe57f2f733') }}"
        :icons="['heart-handshake', 'inbox', 'send', 'route']"
        :columns="4"
    />

    <x-tab-list :tabs="$center['tabs']" label="{{ __('ui.pet_friendship_views_960c18c50c') }}" />

    @if ($center['last_dismissed'])
        <div class="friend-feedback" role="status">
            <span>
                <x-lucide-eye-off class="icon icon--sm" aria-hidden="true" />
                {{ __('presentation.hidden_from_recommendations', ['name' => $center['last_dismissed']['name']]) }}
            </span>
            <x-action-control
                :label="$center['last_dismissed']['action']['label']"
                :icon="$center['last_dismissed']['action']['icon']"
                :endpoint="$center['last_dismissed']['action']['endpoint']"
                :payload="$center['last_dismissed']['action']['payload']"
                :variant="$center['last_dismissed']['action']['variant']"
                size="regular"
            />
        </div>
    @endif

    @if ($center['last_blocked'])
        <div class="friend-feedback" role="status">
            <span>
                <x-lucide-ban class="icon icon--sm" aria-hidden="true" />
                {{ __('presentation.blocked_owner', ['name' => $center['last_blocked']['name']]) }}
            </span>
            <x-action-control
                :label="$center['last_blocked']['action']['label']"
                :icon="$center['last_blocked']['action']['icon']"
                :endpoint="$center['last_blocked']['action']['endpoint']"
                :payload="$center['last_blocked']['action']['payload']"
                :variant="$center['last_blocked']['action']['variant']"
                size="regular"
            />
        </div>
    @endif

    <x-pet-friend-toolbar :center="$center" />

    <div class="friend-dashboard__body">
        <x-pet-friend-list
            :items="$center['items']"
            :empty="$center['empty']"
            :endpoint="$center['endpoint']"
            :clear-href="route('pet-friends.index', [
                'pet' => $center['source']['slug'],
                'tab' => $center['tab'],
            ])"
        />

        <aside class="friend-safety" aria-labelledby="friend-safety-title">
            <span class="friend-safety__icon" aria-hidden="true">
                <x-lucide-shield-check class="icon" aria-hidden="true" />
            </span>
            <div>
                <h2 id="friend-safety-title">{{ $center['safety_note']['title'] }}</h2>
                <p>{{ $center['safety_note']['description'] }}</p>
                <ul>
                    <li>{{ __('ui.choose_a_neutral_public_place_fd36e64017') }}</li>
                    <li>{{ __('ui.share_exact_meeting_details_only_after_acceptance_91a3d9f1a5') }}</li>
                    <li>{{ __('ui.end_an_introduction_whenever_either_pet_is_uncomfortable_142ce42a01') }}</li>
                </ul>
            </div>
        </aside>
    </div>
</section>
