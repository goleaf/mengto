@props([
    'summary',
    'center',
])

<section class="friend-dashboard" aria-label="Pet friendship center">
    <x-feature.pet-friend-switcher :pets="$center['pet_switcher']" />

    <x-ui.summary-strip
        :items="$summary['stats']"
        label="Pet friendship summary"
        :icons="['heart-handshake', 'inbox', 'send', 'route']"
        :columns="4"
    />

    <x-ui.tab-list :tabs="$center['tabs']" label="Pet friendship views" />

    @if ($center['last_dismissed'])
        <div class="friend-feedback" role="status">
            <span>
                <x-lucide-eye-off class="icon icon--sm" aria-hidden="true" />
                {{ $center['last_dismissed']['name'] }} was hidden from recommendations.
            </span>
            <x-ui.action-control
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
                {{ $center['last_blocked']['name'] }} and their owner are blocked.
            </span>
            <x-ui.action-control
                :label="$center['last_blocked']['action']['label']"
                :icon="$center['last_blocked']['action']['icon']"
                :endpoint="$center['last_blocked']['action']['endpoint']"
                :payload="$center['last_blocked']['action']['payload']"
                :variant="$center['last_blocked']['action']['variant']"
                size="regular"
            />
        </div>
    @endif

    <x-feature.pet-friend-toolbar :center="$center" />

    <div class="friend-dashboard__body">
        <x-feature.pet-friend-list
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
                    <li>Choose a neutral public place.</li>
                    <li>Share exact meeting details only after acceptance.</li>
                    <li>End an introduction whenever either pet is uncomfortable.</li>
                </ul>
            </div>
        </aside>
    </div>
</section>
