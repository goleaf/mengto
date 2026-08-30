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
                    <p>{{ __('ui.plan_a_visit') }}</p>
                    <h2 id="place-overview-title">{{ __('ui.current_place_details') }}</h2>
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
                            <dt>{{ __('ui.pet_access') }}</dt>
                            <dd>{{ $place['leash_policy'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.accepted_pets') }}</dt>
                            <dd>{{ $place['accepted_species_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.typical_crowd') }}</dt>
                            <dd>{{ $place['crowd_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.noise') }}</dt>
                            <dd>{{ $place['noise_level'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.hours') }}</dt>
                            <dd>{{ $place['hours_summary'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.coordinate') }}</dt>
                            <dd>{{ $place['coordinate_accuracy'] }}</dd>
                        </div>
                    </dl>

                    <div class="place-facility-grid">
                        @forelse ($content['facilities'] as $facility)
                            <div class="place-facility">
                                <x-ui-icon :name="$facility['icon']" />
                                <div>
                                    <strong>{{ $facility['label'] }}</strong>
                                    <span>{{ $facility['value'] }}</span>
                                </div>
                            </div>
                        @empty
                            <p>{{ __('ui.no_facilities_are_listed') }}</p>
                        @endforelse
                    </div>
                </div>

                <aside class="place-visit-panel" aria-labelledby="place-visit-actions-title">
                    <h3 id="place-visit-actions-title">{{ __('ui.your_visit') }}</h3>

                    @if ($checkIn)
                        <div class="place-check-in place-check-in--active">
                            <x-ui-icon name="map-pin-check" />
                            <div>
                                <strong>{{ __('ui.check_in_active') }}</strong>
                                <span>{{ __('presentation.visibility_ends_automatically', ['visibility' => $checkIn['visibility_label']]) }}</span>
                            </div>
                            <x-action-control
                                :endpoint="route('actions.perform')"
                                :payload="[
                                    'action' => 'clear-place-check-in',
                                    'target' => $place['key'],
                                    'place_return_tab' => 'overview',
                                ]"
                                label="{{ __('ui.end') }}"
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
                            <label for="check-in-pet">{{ __('ui.pet') }}</label>
                            <select id="check-in-pet" name="place_pet" class="field field--select">
                                <option value="scout">{{ __('ui.scout') }}</option>
                                <option value="nori">{{ __('ui.nori') }}</option>
                            </select>
                            <label for="check-in-visibility">{{ __('ui.visibility') }}</label>
                            <select id="check-in-visibility" name="place_visibility" class="field field--select">
                                <option value="private">{{ __('ui.only_me') }}</option>
                                <option value="friends">{{ __('ui.friends') }}</option>
                                <option value="close-circle">{{ __('ui.close_circle') }}</option>
                                <option value="anonymous">{{ __('ui.anonymous_statistics') }}</option>
                            </select>
                            <x-action-control type="submit" label="{{ __('ui.check_in_for_2_hours') }}" icon="map-pin-check" variant="primary" size="compact" />
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
                        :label="$place['visited'] ? __('ui.visit_saved') : __('ui.mark_visited')"
                        :icon="$place['visited'] ? 'history' : 'footprints'"
                        :active="$place['visited']"
                        variant="surface"
                        size="compact"
                    />

                    <h3>{{ __('ui.collections') }}</h3>
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
                            <span>{{ __('ui.no_collections_available') }}</span>
                        @endforelse
                    </div>
                </aside>
            </div>
        </section>

        <section class="place-section" aria-labelledby="place-social-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.friends_and_plans') }}</p>
                    <h2 id="place-social-title">{{ $content['social']['summary'] }}</h2>
                </div>
                <x-action-control :href="$eventUrl" label="{{ __('ui.create_event_here') }}" icon="calendar-plus" variant="surface" size="compact" />
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
                        <p>{{ __('ui.no_privacy_permitted_friend_activity') }}</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('actions.perform') }}" class="place-invite-form">
                    @csrf
                    <input type="hidden" name="action" value="invite-to-place">
                    <input type="hidden" name="target" value="{{ $place['key'] }}">
                    <input type="hidden" name="place_return_tab" value="overview">
                    <label for="place-recipient">{{ __('ui.invite') }}</label>
                    <select id="place-recipient" name="place_recipient" class="field field--select">
                        <option value="ari-mochi">{{ __('ui.ari_and_mochi') }}</option>
                        <option value="priya-luna">{{ __('ui.priya_and_luna') }}</option>
                        <option value="noah-juniper">{{ __('ui.noah_and_juniper') }}</option>
                    </select>
                    <label for="place-date">{{ __('ui.proposed_date') }}</label>
                    <input id="place-date" name="place_visit_date" type="date" class="field" min="{{ today()->format('Y-m-d') }}">
                    <label for="place-message">{{ __('ui.message') }}</label>
                    <textarea id="place-message" name="body" class="field field--textarea" maxlength="1200" required>{{ __('presentation.place_meeting_prompt', ['place' => $place['short_name']]) }}</textarea>
                    <x-action-control type="submit" label="{{ __('ui.send_privately') }}" icon="send" variant="primary" size="compact" />
                </form>
            </div>
        </section>

        <section class="place-section" aria-labelledby="place-weather-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.conditions') }}</p>
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
                        <x-ui-icon :name="$nearby['icon']" />
                        <strong>{{ $nearby['title'] }}</strong>
                        <span>{{ $nearby['detail'] }}</span>
                    </div>
                @empty
                    <div><span>{{ __('ui.no_nearby_guidance_listed') }}</span></div>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'photos')
        <section class="place-section" aria-labelledby="place-photos-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.gallery') }}</p>
                    <h2 id="place-photos-title">{{ __('ui.recent_place_views') }}</h2>
                </div>
            </header>
            <div class="place-gallery">
                @forelse ($content['gallery'] as $photo)
                    <figure>
                        <x-responsive-image
                            :src="$photo['image']"
                            :small="$photo['image_small']"
                            :medium="$photo['image_medium']"
                            sizes="(max-width: 767px) 100vw, 50vw"
                            :alt="$photo['alt']"
                            :width="1200"
                            :height="900"
                        />
                        <figcaption>
                            <strong>{{ $photo['label'] }}</strong>
                            <span>{{ $photo['date'] }} · {{ $photo['source'] }}</span>
                        </figcaption>
                    </figure>
                @empty
                    <x-empty-state icon="image-off" title="{{ __('ui.no_photos_yet') }}" description="{{ __('ui.recent_dated_place_photos_will_appear_here') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'services')
        <section class="place-section" aria-labelledby="place-services-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.services_and_prices') }}</p>
                    <h2 id="place-services-title">{{ __('ui.what_is_listed_here') }}</h2>
                </div>
                @if ($place['website'])
                    <x-action-control
                        :href="$place['website']"
                        label="{{ __('ui.official_site') }}"
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
                    <x-empty-state icon="package-open" title="{{ __('ui.no_services_listed') }}" description="{{ __('ui.contact_the_place_before_making_a_special_trip') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'rules')
        <section class="place-section" aria-labelledby="place-rules-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.pet_access') }}</p>
                    <h2 id="place-rules-title">{{ __('ui.current_visit_rules') }}</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="{{ __('ui.correct_a_rule') }}" icon="file-check-2" variant="surface" size="compact" />
            </header>
            <div class="place-rule-list">
                @forelse ($content['rules'] as $rule)
                    <div>
                        <x-ui-icon :name="$rule['icon']" />
                        <div>
                            <strong>{{ $rule['title'] }}</strong>
                            <span>{{ $rule['detail'] }}</span>
                        </div>
                    </div>
                @empty
                    <p>{{ __('ui.no_visit_rules_are_listed') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'hours')
        <section class="place-section" aria-labelledby="place-hours-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.schedule') }}</p>
                    <h2 id="place-hours-title">{{ __('ui.opening_and_special_hours') }}</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="{{ __('ui.correct_hours') }}" icon="clock-arrow-up" variant="surface" size="compact" />
            </header>
            <div class="place-hours-list">
                @forelse ($content['hours'] as $hours)
                    <div>
                        <strong>{{ $hours['day'] }}</strong>
                        <span>{{ $hours['hours'] }}</span>
                        <small>{{ $hours['note'] }}</small>
                    </div>
                @empty
                    <p>{{ __('ui.hours_are_unknown_call_before_travel') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'specialists')
        <section class="place-section" aria-labelledby="place-specialists-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.people_and_qualifications') }}</p>
                    <h2 id="place-specialists-title">{{ __('ui.listed_specialists') }}</h2>
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
                    <x-empty-state icon="user-round-search" title="{{ __('ui.no_specialists_listed') }}" description="{{ __('ui.call_the_place_to_confirm_who_is_available') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'reviews')
        <section class="place-section" aria-labelledby="place-reviews-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.visitor_experience') }}</p>
                    <h2 id="place-reviews-title">{{ $place['rating_label'] }}</h2>
                </div>
                <x-action-control :href="$reviewUrl" label="{{ __('ui.write_review') }}" icon="star" variant="primary" size="compact" />
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
                            <span class="place-review__rating" aria-label="{{ __('presentation.rating_out_of_five', ['rating' => $review['rating']]) }}">
                                <x-ui-icon name="star" size="sm" />
                                {{ $review['rating'] }}
                            </span>
                        </header>
                        <x-status-badge
                            :label="$review['verified'] ? __('ui.confirmed_visit') : __('ui.unconfirmed_visit')"
                            :icon="$review['verified'] ? 'badge-check' : 'badge-info'"
                            :tone="$review['verified'] ? 'positive' : 'neutral'"
                            size="compact"
                        />
                        <p>{{ $review['body'] }}</p>
                                <small>{{ $review['criterion_label'] }}</small>
                        @if ($review['owner_response'])
                            <div class="place-review__response">
                                <strong>{{ __('ui.place_response') }}</strong>
                                <p>{{ $review['owner_response'] }}</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="message-square-off" title="{{ __('ui.no_reviews_yet') }}" description="{{ __('ui.new_places_remain_discoverable_without_a_rating') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'events')
        <section class="place-section" aria-labelledby="place-events-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.meet_here') }}</p>
                    <h2 id="place-events-title">{{ __('ui.events_at_this_place') }}</h2>
                </div>
                @if ($place['allow_events'])
                    <x-action-control :href="$eventUrl" label="{{ __('ui.create_event_here') }}" icon="calendar-plus" variant="primary" size="compact" />
                @endif
            </header>
            <div class="place-event-list">
                @forelse ($content['events'] as $event)
                    <a href="{{ $event['href'] }}">
                        <span class="place-event-list__date">{{ $event['starts_at'] }}</span>
                        <div>
                            <strong>{{ $event['title'] }}</strong>
                            <small>{{ $event['category_label'] }} · {{ $event['place'] }} · {{ $event['status'] }}</small>
                        </div>
                        <x-ui-icon name="chevron-right" />
                    </a>
                @empty
                    <x-empty-state
                        icon="calendar-search"
                        title="{{ __('ui.no_public_events_listed') }}"
                        :description="$place['allow_events'] ? __('ui.a_permitted_organizer_can_create_a_new_event_here') : __('ui.this_place_does_not_currently_allow_group_events')"
                    />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'questions')
        <section class="place-section" aria-labelledby="place-questions-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.questions_and_answers') }}</p>
                    <h2 id="place-questions-title">{{ __('ui.practical_place_details') }}</h2>
                </div>
                <x-action-control :href="$questionUrl" label="{{ __('ui.ask_question') }}" icon="message-circle-question" variant="primary" size="compact" />
            </header>
            <div class="place-question-list">
                @forelse ($content['questions'] as $question)
                    <article>
                        <header>
                            <x-ui-icon name="circle-help" />
                            <div>
                                <strong>{{ $question['question'] }}</strong>
                                <span>{{ $question['author'] }}</span>
                            </div>
                        </header>
                        @if ($question['answer'])
                            <div class="place-question__answer">
                                <x-ui-icon name="message-circle-check" />
                                <div>
                                    <p>{{ $question['answer'] }}</p>
                                    <small>{{ $question['answer_author'] }} · {{ $question['answered_at'] }}</small>
                                </div>
                            </div>
                        @elseif ($canManage && $question['answerable'])
                            <form method="POST" action="{{ route('actions.perform') }}">
                                @csrf
                                <input type="hidden" name="action" value="answer-place-question">
                                <input type="hidden" name="target" value="{{ $place['key'] }}">
                                <input type="hidden" name="place_question" value="{{ $question['key'] }}">
                                <input type="hidden" name="place_idempotency_key" value="{{ $question['answer_idempotency_key'] }}">
                                <input type="hidden" name="place_return_tab" value="questions">
                                <label for="answer-{{ $question['key'] }}">{{ __('ui.official_answer') }}</label>
                                <textarea id="answer-{{ $question['key'] }}" name="body" class="field field--textarea" maxlength="1200" required></textarea>
                                <x-action-control type="submit" label="{{ __('ui.publish_answer') }}" icon="send" variant="primary" size="compact" />
                            </form>
                        @else
                            <x-status-badge label="{{ __('ui.awaiting_an_answer') }}" icon="clock-3" tone="neutral" />
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="circle-help" title="{{ __('ui.no_questions_yet') }}" description="{{ __('ui.ask_about_access_rules_hours_or_facilities') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'map')
        <section class="place-section place-section--map" aria-labelledby="place-location-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.arrival') }}</p>
                    <h2 id="place-location-title">{{ $place['coordinate_accuracy'] }}</h2>
                </div>
                @if ($place['route_url'])
                    <x-action-control
                        :href="$place['route_url']"
                        label="{{ __('ui.open_route') }}"
                        icon="navigation"
                        variant="primary"
                        size="compact"
                        target="_blank"
                        rel="noopener noreferrer"
                    />
                @endif
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
                <div><dt>{{ __('ui.address') }}</dt><dd>{{ $place['address'] }}</dd></div>
                <div><dt>{{ __('ui.public_area') }}</dt><dd>{{ $place['general_location'] }}</dd></div>
                <div>
                    <dt>{{ __('ui.coordinates') }}</dt>
                    <dd>
                        @if ($place['latitude'] !== null && $place['longitude'] !== null)
                            {{ $place['latitude'] }}, {{ $place['longitude'] }}
                        @else
                            {{ __('places.presentation.region_only_location') }}
                        @endif
                    </dd>
                </div>
                <div><dt>{{ __('ui.privacy') }}</dt><dd>{{ __('ui.only_this_public_place_point_is_displayed_no_visitor_home_point_is_published') }}</dd></div>
            </dl>
        </section>
    @elseif ($activeTab === 'updates')
        <section class="place-section" aria-labelledby="place-updates-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.current_conditions') }}</p>
                    <h2 id="place-updates-title">{{ __('ui.warnings_and_update_history') }}</h2>
                </div>
                <x-action-control :href="$warningUrl" label="{{ __('ui.report_hazard') }}" icon="triangle-alert" variant="danger" size="compact" />
            </header>

            <div class="place-warning-list">
                @forelse ($content['warnings'] as $warning)
                    <article class="place-warning place-warning--{{ $warning['status'] }}">
                        <header>
                            <x-ui-icon name="triangle-alert" />
                            <div>
                                <strong>{{ $warning['title'] }}</strong>
                                <span>{{ __('presentation.status_confirmations', ['status' => $warning['status_label'], 'confirmations' => $warning['confirmations']]) }}</span>
                            </div>
                        </header>
                        <p>{{ $warning['detail'] }}</p>
                        <small>{{ __('presentation.warning_expires', ['source' => $warning['source'], 'date' => $warning['expires_at']]) }}</small>
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
                                    label="{{ __('ui.confirm_current') }}"
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
                                    label="{{ __('ui.problem_resolved') }}"
                                    icon="circle-check-big"
                                    variant="ghost"
                                    size="compact"
                                />
                            </div>
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="shield-check" title="{{ __('ui.no_active_warnings') }}" description="{{ __('ui.current_place_conditions_still_need_normal_personal_judgment') }}" />
                @endforelse
            </div>

            <div class="place-history">
                @forelse ($content['history'] as $update)
                    <div>
                        <x-ui-icon :name="$update['icon']" />
                        <div>
                            <strong>{{ $update['title'] }}</strong>
                            <span>{{ $update['body'] }}</span>
                            <small>{{ $update['time'] }} · {{ $update['status'] }}</small>
                        </div>
                    </div>
                @empty
                    <p>{{ __('ui.no_updates_recorded') }}</p>
                @endforelse
            </div>

            @if (! $place['owner_managed'])
                <div class="place-claim">
                    <div>
                        <strong>{{ __('ui.manage_this_place') }}</strong>
                        <span>{{ __('ui.verification_is_scoped_to_identity_organization_and_the_claimed_listing') }}</span>
                    </div>
                    <x-action-control :href="$claimUrl" label="{{ __('ui.claim_profile') }}" icon="badge-check" variant="surface" size="compact" />
                </div>
            @endif

            @if (count($claims) > 0)
                <div class="place-claim-status">
                    @forelse ($claims as $claim)
                        <span>{{ $claim['organization'] }} · {{ $claim['status_label'] }}</span>
                    @empty
                        <span>{{ __('ui.no_claims') }}</span>
                    @endforelse
                </div>
            @endif
        </section>
    @elseif ($activeTab === 'corrections')
        <section class="place-section" aria-labelledby="place-corrections-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.data_quality') }}</p>
                    <h2 id="place-corrections-title">{{ __('ui.correction_status') }}</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="{{ __('ui.suggest_correction') }}" icon="file-check-2" variant="primary" size="compact" />
            </header>

            <div class="place-verification-grid">
                @forelse ($content['verification'] as $item)
                    <div>
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $item['value'] }}</span>
                    </div>
                @empty
                    <p>{{ __('ui.no_verification_scope_is_listed') }}</p>
                @endforelse
            </div>

            <div class="place-correction-list">
                @forelse ($corrections as $correction)
                    <article>
                        <header>
                            <strong>{{ $correction['field_label'] }}</strong>
                            <x-status-badge :label="$correction['status_label']" tone="neutral" />
                        </header>
                        <p>{{ $correction['proposed_value'] }}</p>
                        <small>{{ __('presentation.evidence_created', ['evidence' => $correction['evidence'], 'created' => $correction['created_at']]) }}</small>
                    </article>
                @empty
                    <x-empty-state icon="file-check-2" title="{{ __('ui.no_pending_corrections') }}" description="{{ __('ui.important_changes_require_evidence_and_review') }}" />
                @endforelse
            </div>
        </section>
    @endif

    <footer class="place-dashboard__safety">
        <div>
            <x-ui-icon name="shield-alert" />
            <p>
                <strong>{{ __('ui.information_can_change') }}</strong>
                {{ __('ui.check_current_rules_live_availability_and_urgent_intake_directly_with_the_place') }}
            </p>
        </div>
        <div>
            <x-action-control :href="$correctionUrl" label="{{ __('ui.correct_data') }}" icon="file-check-2" variant="ghost" size="compact" />
            <x-action-control :href="$reportUrl" label="{{ __('ui.report_place') }}" icon="flag" variant="ghost" size="compact" />
        </div>
    </footer>
</div>
