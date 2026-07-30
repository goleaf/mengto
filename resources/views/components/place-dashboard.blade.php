@props([
    'place',
    'activeTab',
    'content',
    'checkIn',
    'collections',
    'claims',
    'corrections',
    'canManage',
    'reportUrl',
    'correctionUrl',
    'warningUrl',
    'reviewUrl',
    'questionUrl',
    'claimUrl',
    'eventUrl',
])

<div class="place-dashboard">
    @if ($activeTab === 'overview')
        <section class="place-section place-section--overview" aria-labelledby="place-overview-title">
            <header class="place-section__heading">
                <div>
                    <p>Plan a visit</p>
                    <h2 id="place-overview-title">Current place details</h2>
                </div>
                <x-status-badge
                    :label="$place['pet_fit']['label']"
                    :tone="$place['pet_fit']['tone']"
                    icon="paw-print"
                />
            </header>

            <div class="place-overview-grid">
                <div class="place-overview-grid__main">
                    <dl class="place-fact-list">
                        <div>
                            <dt>Pet access</dt>
                            <dd>{{ $place['leash_policy'] }}</dd>
                        </div>
                        <div>
                            <dt>Accepted pets</dt>
                            <dd>{{ implode(' · ', array_map(fn ($item) => str($item)->headline()->toString(), $place['accepted_species'])) }}</dd>
                        </div>
                        <div>
                            <dt>Typical crowd</dt>
                            <dd>{{ $place['crowd_label'] }}</dd>
                        </div>
                        <div>
                            <dt>Noise</dt>
                            <dd>{{ $place['noise_level'] }}</dd>
                        </div>
                        <div>
                            <dt>Hours</dt>
                            <dd>{{ $place['hours_summary'] }}</dd>
                        </div>
                        <div>
                            <dt>Coordinate</dt>
                            <dd>{{ $place['coordinate_accuracy'] }}</dd>
                        </div>
                    </dl>

                    <div class="place-facility-grid">
                        @forelse ($content['facilities'] as $facility)
                            <div class="place-facility">
                                <x-dynamic-component :component="'lucide-'.$facility['icon']" class="icon" aria-hidden="true" />
                                <div>
                                    <strong>{{ $facility['label'] }}</strong>
                                    <span>{{ $facility['value'] }}</span>
                                </div>
                            </div>
                        @empty
                            <p>No facilities are listed.</p>
                        @endforelse
                    </div>
                </div>

                <aside class="place-visit-panel" aria-labelledby="place-visit-actions-title">
                    <h3 id="place-visit-actions-title">Your visit</h3>

                    @if ($checkIn)
                        <div class="place-check-in place-check-in--active">
                            <x-lucide-map-pin-check class="icon" aria-hidden="true" />
                            <div>
                                <strong>Check-in active</strong>
                                <span>{{ str($checkIn['visibility'])->headline() }} · ends automatically</span>
                            </div>
                            <x-action-control
                                :endpoint="route('actions.perform')"
                                :payload="[
                                    'action' => 'clear-place-check-in',
                                    'target' => $place['key'],
                                    'place_return_tab' => 'overview',
                                ]"
                                label="End"
                                icon="x"
                                variant="ghost"
                                size="compact"
                            />
                        </div>
                    @else
                        <form method="POST" action="{{ route('actions.perform') }}" class="place-check-in-form">
                            @csrf
                            <input type="hidden" name="action" value="check-in-place">
                            <input type="hidden" name="target" value="{{ $place['key'] }}">
                            <input type="hidden" name="place_return_tab" value="overview">
                            <label for="check-in-pet">Pet</label>
                            <select id="check-in-pet" name="place_pet" class="field field--select">
                                <option value="scout">Scout</option>
                                <option value="nori">Nori</option>
                            </select>
                            <label for="check-in-visibility">Visibility</label>
                            <select id="check-in-visibility" name="place_visibility" class="field field--select">
                                <option value="private">Only me</option>
                                <option value="friends">Friends</option>
                                <option value="close-circle">Close circle</option>
                                <option value="anonymous">Anonymous statistics</option>
                            </select>
                            <x-action-control type="submit" label="Check in for 2 hours" icon="map-pin-check" variant="primary" size="compact" />
                        </form>
                    @endif

                    <x-action-control
                        :endpoint="route('actions.perform')"
                        :payload="[
                            'action' => 'mark-place-visited',
                            'target' => $place['key'],
                            'place_pet' => 'scout',
                            'place_return_tab' => 'overview',
                        ]"
                        :label="$place['visited'] ? 'Visit saved' : 'Mark visited'"
                        :icon="$place['visited'] ? 'history' : 'footprints'"
                        :active="$place['visited']"
                        variant="surface"
                        size="compact"
                    />

                    <h3>Collections</h3>
                    <div class="place-collection-list">
                        @forelse ($collections as $key => $collection)
                            <x-action-control
                                :endpoint="route('actions.perform')"
                                :payload="[
                                    'action' => 'toggle-place-collection',
                                    'target' => $place['key'],
                                    'place_collection' => $key,
                                    'place_return_tab' => 'overview',
                                ]"
                                :label="$collection['name']"
                                :icon="$collection['active'] ? 'folder-check' : 'folder-plus'"
                                :active="$collection['active']"
                                :pressed="$collection['active']"
                                variant="surface"
                                size="compact"
                            />
                        @empty
                            <span>No collections available.</span>
                        @endforelse
                    </div>
                </aside>
            </div>
        </section>

        <section class="place-section" aria-labelledby="place-social-title">
            <header class="place-section__heading">
                <div>
                    <p>Friends and plans</p>
                    <h2 id="place-social-title">{{ $content['social']['summary'] }}</h2>
                </div>
                <x-action-control :href="$eventUrl" label="Create event here" icon="calendar-plus" variant="surface" size="compact" />
            </header>

            <div class="place-social-grid">
                <div class="place-social-grid__friends">
                    @forelse ($content['social']['friends'] as $friend)
                        <div class="place-friend">
                            <x-initials-avatar :initials="$friend['initials']" size="sm" />
                            <div>
                                <strong>{{ $friend['name'] }}</strong>
                                <span>{{ $friend['detail'] }}</span>
                            </div>
                        </div>
                    @empty
                        <p>No privacy-permitted friend activity.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('actions.perform') }}" class="place-invite-form">
                    @csrf
                    <input type="hidden" name="action" value="invite-to-place">
                    <input type="hidden" name="target" value="{{ $place['key'] }}">
                    <input type="hidden" name="place_return_tab" value="overview">
                    <label for="place-recipient">Invite</label>
                    <select id="place-recipient" name="place_recipient" class="field field--select">
                        <option value="ari-mochi">Ari and Mochi</option>
                        <option value="priya-luna">Priya and Luna</option>
                        <option value="noah-juniper">Noah and Juniper</option>
                    </select>
                    <label for="place-date">Proposed date</label>
                    <input id="place-date" name="place_visit_date" type="date" class="field" min="{{ today()->format('Y-m-d') }}">
                    <label for="place-message">Message</label>
                    <textarea id="place-message" name="body" class="field field--textarea" maxlength="1200" required>Would you like to meet at {{ $place['short_name'] }}?</textarea>
                    <x-action-control type="submit" label="Send privately" icon="send" variant="primary" size="compact" />
                </form>
            </div>
        </section>

        <section class="place-section" aria-labelledby="place-weather-title">
            <header class="place-section__heading">
                <div>
                    <p>Conditions</p>
                    <h2 id="place-weather-title">{{ $content['weather']['summary'] }}</h2>
                </div>
                <x-status-badge :label="$content['weather']['source']" icon="cloud-sun" tone="neutral" />
            </header>
            <div class="place-condition-grid">
                <div>
                    <strong>{{ $content['weather']['temperature'] }}</strong>
                    <span>{{ $content['weather']['advisory'] }}</span>
                </div>
                @forelse ($content['nearby'] as $nearby)
                    <div>
                        <x-dynamic-component :component="'lucide-'.$nearby['icon']" class="icon" aria-hidden="true" />
                        <strong>{{ $nearby['title'] }}</strong>
                        <span>{{ $nearby['detail'] }}</span>
                    </div>
                @empty
                    <div><span>No nearby guidance listed.</span></div>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'photos')
        <section class="place-section" aria-labelledby="place-photos-title">
            <header class="place-section__heading">
                <div>
                    <p>Gallery</p>
                    <h2 id="place-photos-title">Recent place views</h2>
                </div>
            </header>
            <div class="place-gallery">
                @forelse ($content['gallery'] as $photo)
                    <figure>
                        <img
                            src="{{ $photo['image_small'] }}"
                            srcset="{{ $photo['image_small'] }} 720w, {{ $photo['image_medium'] }} 1200w, {{ $photo['image'] }} 1600w"
                            sizes="(max-width: 767px) 100vw, 50vw"
                            alt="{{ $photo['alt'] }}"
                            width="720"
                            height="540"
                            loading="lazy"
                        >
                        <figcaption>
                            <strong>{{ $photo['label'] }}</strong>
                            <span>{{ $photo['date'] }} · {{ $photo['source'] }}</span>
                        </figcaption>
                    </figure>
                @empty
                    <x-empty-state icon="image-off" title="No photos yet" description="Recent, dated place photos will appear here." />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'services')
        <section class="place-section" aria-labelledby="place-services-title">
            <header class="place-section__heading">
                <div>
                    <p>Services and prices</p>
                    <h2 id="place-services-title">What is listed here</h2>
                </div>
                @if ($place['website'])
                    <x-action-control
                        :href="$place['website']"
                        label="Official site"
                        icon="external-link"
                        variant="surface"
                        size="compact"
                        target="_blank"
                        rel="noopener noreferrer"
                    />
                @endif
            </header>
            <div class="place-service-list">
                @forelse ($content['services'] as $service)
                    <article>
                        <div>
                            <strong>{{ $service['title'] }}</strong>
                            <span>{{ $service['detail'] }}</span>
                        </div>
                        <x-status-badge :label="$service['status']" tone="neutral" />
                    </article>
                @empty
                    <x-empty-state icon="package-open" title="No services listed" description="Contact the place before making a special trip." />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'rules')
        <section class="place-section" aria-labelledby="place-rules-title">
            <header class="place-section__heading">
                <div>
                    <p>Pet access</p>
                    <h2 id="place-rules-title">Current visit rules</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="Correct a rule" icon="file-check-2" variant="surface" size="compact" />
            </header>
            <div class="place-rule-list">
                @forelse ($content['rules'] as $rule)
                    <div>
                        <x-dynamic-component :component="'lucide-'.$rule['icon']" class="icon" aria-hidden="true" />
                        <div>
                            <strong>{{ $rule['title'] }}</strong>
                            <span>{{ $rule['detail'] }}</span>
                        </div>
                    </div>
                @empty
                    <p>No visit rules are listed.</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'hours')
        <section class="place-section" aria-labelledby="place-hours-title">
            <header class="place-section__heading">
                <div>
                    <p>Schedule</p>
                    <h2 id="place-hours-title">Opening and special hours</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="Correct hours" icon="clock-arrow-up" variant="surface" size="compact" />
            </header>
            <div class="place-hours-list">
                @forelse ($content['hours'] as $hours)
                    <div>
                        <strong>{{ $hours['day'] }}</strong>
                        <span>{{ $hours['hours'] }}</span>
                        <small>{{ $hours['note'] }}</small>
                    </div>
                @empty
                    <p>Hours are unknown. Call before travel.</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'specialists')
        <section class="place-section" aria-labelledby="place-specialists-title">
            <header class="place-section__heading">
                <div>
                    <p>People and qualifications</p>
                    <h2 id="place-specialists-title">Listed specialists</h2>
                </div>
            </header>
            <div class="place-specialist-list">
                @forelse ($content['specialists'] as $specialist)
                    <article>
                        <x-initials-avatar :initials="$specialist['initials']" size="md" />
                        <div>
                            <strong>{{ $specialist['name'] }}</strong>
                            <span>{{ $specialist['role'] }}</span>
                            <small>{{ $specialist['experience'] }}</small>
                            <small>{{ $specialist['languages'] }}</small>
                        </div>
                        <x-status-badge :label="$specialist['verification']" icon="badge-check" tone="neutral" />
                    </article>
                @empty
                    <x-empty-state icon="user-round-search" title="No specialists listed" description="Call the place to confirm who is available." />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'reviews')
        <section class="place-section" aria-labelledby="place-reviews-title">
            <header class="place-section__heading">
                <div>
                    <p>Visitor experience</p>
                    <h2 id="place-reviews-title">{{ $place['rating_label'] }}</h2>
                </div>
                <x-action-control :href="$reviewUrl" label="Write review" icon="star" variant="primary" size="compact" />
            </header>
            <div class="place-review-list">
                @forelse ($content['reviews'] as $review)
                    <article>
                        <header>
                            <x-initials-avatar :initials="$review['initials']" size="sm" />
                            <div>
                                <strong>{{ $review['author'] }}</strong>
                                <span>{{ $review['date'] }} · {{ $review['visited_with'] }}</span>
                            </div>
                            <span class="place-review__rating" aria-label="{{ $review['rating'] }} out of 5">
                                <x-lucide-star class="icon icon--sm" aria-hidden="true" />
                                {{ $review['rating'] }}
                            </span>
                        </header>
                        <x-status-badge
                            :label="$review['verified'] ? 'Confirmed visit' : 'Unconfirmed visit'"
                            :icon="$review['verified'] ? 'badge-check' : 'badge-info'"
                            :tone="$review['verified'] ? 'positive' : 'neutral'"
                            size="compact"
                        />
                        <p>{{ $review['body'] }}</p>
                                <small>{{ str($review['criterion'])->headline() }}</small>
                        @if ($review['owner_response'])
                            <div class="place-review__response">
                                <strong>Place response</strong>
                                <p>{{ $review['owner_response'] }}</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="message-square-off" title="No reviews yet" description="New places remain discoverable without a rating." />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'events')
        <section class="place-section" aria-labelledby="place-events-title">
            <header class="place-section__heading">
                <div>
                    <p>Meet here</p>
                    <h2 id="place-events-title">Events at this place</h2>
                </div>
                @if ($place['allow_events'])
                    <x-action-control :href="$eventUrl" label="Create event here" icon="calendar-plus" variant="primary" size="compact" />
                @endif
            </header>
            <div class="place-event-list">
                @forelse ($content['events'] as $event)
                    <a href="{{ $event['href'] }}">
                        <span class="place-event-list__date">{{ $event['starts_at'] }}</span>
                        <div>
                            <strong>{{ $event['title'] }}</strong>
                            <small>{{ str($event['category'])->headline() }} · {{ $event['place'] }} · {{ $event['status'] }}</small>
                        </div>
                        <x-lucide-chevron-right class="icon" aria-hidden="true" />
                    </a>
                @empty
                    <x-empty-state
                        icon="calendar-search"
                        title="No public events listed"
                        :description="$place['allow_events'] ? 'A permitted organizer can create a new event here.' : 'This place does not currently allow group events.'"
                    />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'questions')
        <section class="place-section" aria-labelledby="place-questions-title">
            <header class="place-section__heading">
                <div>
                    <p>Questions and answers</p>
                    <h2 id="place-questions-title">Practical place details</h2>
                </div>
                <x-action-control :href="$questionUrl" label="Ask question" icon="message-circle-question" variant="primary" size="compact" />
            </header>
            <div class="place-question-list">
                @forelse ($content['questions'] as $question)
                    <article>
                        <header>
                            <x-lucide-circle-help class="icon" aria-hidden="true" />
                            <div>
                                <strong>{{ $question['question'] }}</strong>
                                <span>{{ $question['author'] }}</span>
                            </div>
                        </header>
                        @if ($question['answer'])
                            <div class="place-question__answer">
                                <x-lucide-message-circle-check class="icon" aria-hidden="true" />
                                <div>
                                    <p>{{ $question['answer'] }}</p>
                                    <small>{{ $question['answer_author'] }} · {{ $question['answered_at'] }}</small>
                                </div>
                            </div>
                        @elseif ($canManage)
                            <form method="POST" action="{{ route('actions.perform') }}">
                                @csrf
                                <input type="hidden" name="action" value="answer-place-question">
                                <input type="hidden" name="target" value="{{ $place['key'] }}">
                                <input type="hidden" name="place_question" value="{{ $question['key'] }}">
                                <input type="hidden" name="place_return_tab" value="questions">
                                <label for="answer-{{ $question['key'] }}">Official answer</label>
                                <textarea id="answer-{{ $question['key'] }}" name="body" class="field field--textarea" maxlength="1200" required></textarea>
                                <x-action-control type="submit" label="Publish answer" icon="send" variant="primary" size="compact" />
                            </form>
                        @else
                            <x-status-badge label="Awaiting an answer" icon="clock-3" tone="neutral" />
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="circle-help" title="No questions yet" description="Ask about access, rules, hours, or facilities." />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'map')
        <section class="place-section place-section--map" aria-labelledby="place-location-title">
            <header class="place-section__heading">
                <div>
                    <p>Arrival</p>
                    <h2 id="place-location-title">{{ $place['coordinate_accuracy'] }}</h2>
                </div>
                <x-action-control
                    :href="$place['route_url']"
                    label="Open route"
                    icon="navigation"
                    variant="primary"
                    size="compact"
                    target="_blank"
                    rel="noopener noreferrer"
                />
            </header>
            <x-place-map
                :places="[[
                    'key' => $place['key'],
                    'name' => $place['name'],
                    'category' => $place['primary_category'],
                    'category_icon' => $place['category_icon'],
                    'category_tone' => $place['category_tone'],
                    'x' => 50,
                    'y' => 48,
                    'label' => $place['marker_label'],
                    'status' => $place['open_label'],
                    'distance' => $place['distance_label'],
                    'detail_url' => $place['detail_url'],
                    'warning_count' => $place['warning_count'],
                    'emergency' => $place['emergency'],
                    'position' => 1,
                ]]"
                :selected="$place"
                :emergency="$place['emergency']"
            />
            <dl class="place-fact-list">
                <div><dt>Address</dt><dd>{{ $place['address'] }}</dd></div>
                <div><dt>Public area</dt><dd>{{ $place['general_location'] }}</dd></div>
                <div><dt>Coordinates</dt><dd>{{ $place['latitude'] }}, {{ $place['longitude'] }}</dd></div>
                <div><dt>Privacy</dt><dd>Only this public place point is displayed; no visitor home point is published.</dd></div>
            </dl>
        </section>
    @elseif ($activeTab === 'updates')
        <section class="place-section" aria-labelledby="place-updates-title">
            <header class="place-section__heading">
                <div>
                    <p>Current conditions</p>
                    <h2 id="place-updates-title">Warnings and update history</h2>
                </div>
                <x-action-control :href="$warningUrl" label="Report hazard" icon="triangle-alert" variant="danger" size="compact" />
            </header>

            <div class="place-warning-list">
                @forelse ($content['warnings'] as $warning)
                    <article class="place-warning place-warning--{{ $warning['status'] }}">
                        <header>
                            <x-lucide-triangle-alert class="icon" aria-hidden="true" />
                            <div>
                                <strong>{{ $warning['title'] }}</strong>
                                <span>{{ str($warning['status'])->headline() }} · {{ $warning['confirmations'] }} confirmations</span>
                            </div>
                        </header>
                        <p>{{ $warning['detail'] }}</p>
                        <small>{{ $warning['source'] }} · expires {{ $warning['expires_at'] }}</small>
                        @if (! in_array($warning['status'], ['resolved', 'expired', 'false'], true))
                            <div class="place-warning__actions">
                                <x-action-control
                                    :endpoint="route('actions.perform')"
                                    :payload="[
                                        'action' => 'confirm-place-warning',
                                        'target' => $place['key'],
                                        'place_warning' => $warning['key'],
                                        'place_return_tab' => 'updates',
                                    ]"
                                    label="Confirm current"
                                    icon="check-check"
                                    variant="surface"
                                    size="compact"
                                />
                                <x-action-control
                                    :endpoint="route('actions.perform')"
                                    :payload="[
                                        'action' => 'resolve-place-warning',
                                        'target' => $place['key'],
                                        'place_warning' => $warning['key'],
                                        'place_return_tab' => 'updates',
                                    ]"
                                    label="Problem resolved"
                                    icon="circle-check-big"
                                    variant="ghost"
                                    size="compact"
                                />
                            </div>
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="shield-check" title="No active warnings" description="Current place conditions still need normal personal judgment." />
                @endforelse
            </div>

            <div class="place-history">
                @forelse ($content['history'] as $update)
                    <div>
                        <x-dynamic-component :component="'lucide-'.$update['icon']" class="icon" aria-hidden="true" />
                        <div>
                            <strong>{{ $update['title'] }}</strong>
                            <span>{{ $update['body'] }}</span>
                            <small>{{ $update['time'] }} · {{ $update['status'] }}</small>
                        </div>
                    </div>
                @empty
                    <p>No updates recorded.</p>
                @endforelse
            </div>

            @if (! $place['owner_managed'])
                <div class="place-claim">
                    <div>
                        <strong>Manage this place?</strong>
                        <span>Verification is scoped to identity, organization, and the claimed listing.</span>
                    </div>
                    <x-action-control :href="$claimUrl" label="Claim profile" icon="badge-check" variant="surface" size="compact" />
                </div>
            @endif

            @if (count($claims) > 0)
                <div class="place-claim-status">
                    @forelse ($claims as $claim)
                        <span>{{ $claim['organization'] }} · {{ str($claim['status'])->headline() }}</span>
                    @empty
                        <span>No claims.</span>
                    @endforelse
                </div>
            @endif
        </section>
    @elseif ($activeTab === 'corrections')
        <section class="place-section" aria-labelledby="place-corrections-title">
            <header class="place-section__heading">
                <div>
                    <p>Data quality</p>
                    <h2 id="place-corrections-title">Correction status</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="Suggest correction" icon="file-check-2" variant="primary" size="compact" />
            </header>

            <div class="place-verification-grid">
                @forelse ($content['verification'] as $item)
                    <div>
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $item['value'] }}</span>
                    </div>
                @empty
                    <p>No verification scope is listed.</p>
                @endforelse
            </div>

            <div class="place-correction-list">
                @forelse ($corrections as $correction)
                    <article>
                        <header>
                            <strong>{{ str($correction['field'])->headline() }}</strong>
                            <x-status-badge :label="str($correction['status'])->headline()" tone="neutral" />
                        </header>
                        <p>{{ $correction['proposed_value'] }}</p>
                        <small>Evidence: {{ $correction['evidence'] }} · {{ $correction['created_at'] }}</small>
                    </article>
                @empty
                    <x-empty-state icon="file-check-2" title="No pending corrections" description="Important changes require evidence and review." />
                @endforelse
            </div>
        </section>
    @endif

    <footer class="place-dashboard__safety">
        <div>
            <x-lucide-shield-alert class="icon" aria-hidden="true" />
            <p>
                <strong>Information can change.</strong>
                Check current rules, live availability, and urgent intake directly with the place.
            </p>
        </div>
        <div>
            <x-action-control :href="$correctionUrl" label="Correct data" icon="file-check-2" variant="ghost" size="compact" />
            <x-action-control :href="$reportUrl" label="Report place" icon="flag" variant="ghost" size="compact" />
        </div>
    </footer>
</div>
