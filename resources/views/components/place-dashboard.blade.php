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
                    <p>{{ __('ui.plan_a_visit_4ff9f6d013') }}</p>
                    <h2 id="place-overview-title">{{ __('ui.current_place_details_695cbf57ae') }}</h2>
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
                            <dt>{{ __('ui.pet_access_c1ad8c6f9e') }}</dt>
                            <dd>{{ $place['leash_policy'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.accepted_pets_3b906bd029') }}</dt>
                            <dd>{{ $place['accepted_species_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.typical_crowd_ad06630704') }}</dt>
                            <dd>{{ $place['crowd_label'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.noise_9ada8e83c1') }}</dt>
                            <dd>{{ $place['noise_level'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.hours_21e8492938') }}</dt>
                            <dd>{{ $place['hours_summary'] }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('ui.coordinate_bdc10f0936') }}</dt>
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
                            <p>{{ __('ui.no_facilities_are_listed_69661a736e') }}</p>
                        @endforelse
                    </div>
                </div>

                <aside class="place-visit-panel" aria-labelledby="place-visit-actions-title">
                    <h3 id="place-visit-actions-title">{{ __('ui.your_visit_72db071a47') }}</h3>

                    @if ($checkIn)
                        <div class="place-check-in place-check-in--active">
                            <x-ui-icon name="map-pin-check" />
                            <div>
                                <strong>{{ __('ui.check_in_active_262ec12d90') }}</strong>
                                <span>{{ __('presentation.visibility_ends_automatically', ['visibility' => $checkIn['visibility_label']]) }}</span>
                            </div>
                            <x-action-control
                                :endpoint="route('actions.perform')"
                                :payload="[
                                    'action' => 'clear-place-check-in',
                                    'target' => $place['key'],
                                    'place_return_tab' => 'overview',
                                ]"
                                label="{{ __('ui.end_f4db1e4847') }}"
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
                            <label for="check-in-pet">{{ __('ui.pet_8f0d1b30eb') }}</label>
                            <select id="check-in-pet" name="place_pet" class="field field--select">
                                <option value="scout">{{ __('ui.scout_8a1db462be') }}</option>
                                <option value="nori">{{ __('ui.nori_a64203ba20') }}</option>
                            </select>
                            <label for="check-in-visibility">{{ __('ui.visibility_7448611d5f') }}</label>
                            <select id="check-in-visibility" name="place_visibility" class="field field--select">
                                <option value="private">{{ __('ui.only_me_bdc0857b99') }}</option>
                                <option value="friends">{{ __('ui.friends_bd104d1b98') }}</option>
                                <option value="close-circle">{{ __('ui.close_circle_65c7e67e60') }}</option>
                                <option value="anonymous">{{ __('ui.anonymous_statistics_1d5a04705b') }}</option>
                            </select>
                            <x-action-control type="submit" label="{{ __('ui.check_in_for_2_hours_1802a2f4f1') }}" icon="map-pin-check" variant="primary" size="compact" />
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
                        :label="$place['visited'] ? __('ui.visit_saved_01934e4b3d') : __('ui.mark_visited_c19f13ea99')"
                        :icon="$place['visited'] ? 'history' : 'footprints'"
                        :active="$place['visited']"
                        variant="surface"
                        size="compact"
                    />

                    <h3>{{ __('ui.collections_9f9feade76') }}</h3>
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
                            <span>{{ __('ui.no_collections_available_e714481e05') }}</span>
                        @endforelse
                    </div>
                </aside>
            </div>
        </section>

        <section class="place-section" aria-labelledby="place-social-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.friends_and_plans_f96b90d6a3') }}</p>
                    <h2 id="place-social-title">{{ $content['social']['summary'] }}</h2>
                </div>
                <x-action-control :href="$eventUrl" label="{{ __('ui.create_event_here_755fd04540') }}" icon="calendar-plus" variant="surface" size="compact" />
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
                        <p>{{ __('ui.no_privacy_permitted_friend_activity_091b59e7f1') }}</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('actions.perform') }}" class="place-invite-form">
                    @csrf
                    <input type="hidden" name="action" value="invite-to-place">
                    <input type="hidden" name="target" value="{{ $place['key'] }}">
                    <input type="hidden" name="place_return_tab" value="overview">
                    <label for="place-recipient">{{ __('ui.invite_1fd9ae1607') }}</label>
                    <select id="place-recipient" name="place_recipient" class="field field--select">
                        <option value="ari-mochi">{{ __('ui.ari_and_mochi_6ab978b432') }}</option>
                        <option value="priya-luna">{{ __('ui.priya_and_luna_641f4ef0c8') }}</option>
                        <option value="noah-juniper">{{ __('ui.noah_and_juniper_875732f92f') }}</option>
                    </select>
                    <label for="place-date">{{ __('ui.proposed_date_b656c2c123') }}</label>
                    <input id="place-date" name="place_visit_date" type="date" class="field" min="{{ today()->format('Y-m-d') }}">
                    <label for="place-message">{{ __('ui.message_2f77668a9d') }}</label>
                    <textarea id="place-message" name="body" class="field field--textarea" maxlength="1200" required>{{ __('presentation.place_meeting_prompt', ['place' => $place['short_name']]) }}</textarea>
                    <x-action-control type="submit" label="{{ __('ui.send_privately_d2830b71ee') }}" icon="send" variant="primary" size="compact" />
                </form>
            </div>
        </section>

        <section class="place-section" aria-labelledby="place-weather-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.conditions_97d4be8960') }}</p>
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
                    <div><span>{{ __('ui.no_nearby_guidance_listed_aa81dc000a') }}</span></div>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'photos')
        <section class="place-section" aria-labelledby="place-photos-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.gallery_352cfc749e') }}</p>
                    <h2 id="place-photos-title">{{ __('ui.recent_place_views_ae57b162c6') }}</h2>
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
                    <x-empty-state icon="image-off" title="{{ __('ui.no_photos_yet_af127f7c41') }}" description="{{ __('ui.recent_dated_place_photos_will_appear_here_140479d83d') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'services')
        <section class="place-section" aria-labelledby="place-services-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.services_and_prices_9628c8956c') }}</p>
                    <h2 id="place-services-title">{{ __('ui.what_is_listed_here_fc60af9fc5') }}</h2>
                </div>
                @if ($place['website'])
                    <x-action-control
                        :href="$place['website']"
                        label="{{ __('ui.official_site_509e83904a') }}"
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
                    <x-empty-state icon="package-open" title="{{ __('ui.no_services_listed_7c0d637202') }}" description="{{ __('ui.contact_the_place_before_making_a_special_trip_1869862fec') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'rules')
        <section class="place-section" aria-labelledby="place-rules-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.pet_access_c1ad8c6f9e') }}</p>
                    <h2 id="place-rules-title">{{ __('ui.current_visit_rules_799c89d35e') }}</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="{{ __('ui.correct_a_rule_99c06aa8dc') }}" icon="file-check-2" variant="surface" size="compact" />
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
                    <p>{{ __('ui.no_visit_rules_are_listed_07e64bdb28') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'hours')
        <section class="place-section" aria-labelledby="place-hours-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.schedule_f4830a1dae') }}</p>
                    <h2 id="place-hours-title">{{ __('ui.opening_and_special_hours_2901b0e1c9') }}</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="{{ __('ui.correct_hours_cd3f943c18') }}" icon="clock-arrow-up" variant="surface" size="compact" />
            </header>
            <div class="place-hours-list">
                @forelse ($content['hours'] as $hours)
                    <div>
                        <strong>{{ $hours['day'] }}</strong>
                        <span>{{ $hours['hours'] }}</span>
                        <small>{{ $hours['note'] }}</small>
                    </div>
                @empty
                    <p>{{ __('ui.hours_are_unknown_call_before_travel_d500bbf740') }}</p>
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'specialists')
        <section class="place-section" aria-labelledby="place-specialists-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.people_and_qualifications_daa929011b') }}</p>
                    <h2 id="place-specialists-title">{{ __('ui.listed_specialists_4c4a9e0476') }}</h2>
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
                    <x-empty-state icon="user-round-search" title="{{ __('ui.no_specialists_listed_b4d0919d05') }}" description="{{ __('ui.call_the_place_to_confirm_who_is_available_a32651b9b0') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'reviews')
        <section class="place-section" aria-labelledby="place-reviews-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.visitor_experience_fd45f4c581') }}</p>
                    <h2 id="place-reviews-title">{{ $place['rating_label'] }}</h2>
                </div>
                <x-action-control :href="$reviewUrl" label="{{ __('ui.write_review_2046c14bab') }}" icon="star" variant="primary" size="compact" />
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
                            :label="$review['verified'] ? __('ui.confirmed_visit_7823494edb') : __('ui.unconfirmed_visit_642ec38020')"
                            :icon="$review['verified'] ? 'badge-check' : 'badge-info'"
                            :tone="$review['verified'] ? 'positive' : 'neutral'"
                            size="compact"
                        />
                        <p>{{ $review['body'] }}</p>
                                <small>{{ $review['criterion_label'] }}</small>
                        @if ($review['owner_response'])
                            <div class="place-review__response">
                                <strong>{{ __('ui.place_response_e08f4db048') }}</strong>
                                <p>{{ $review['owner_response'] }}</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="message-square-off" title="{{ __('ui.no_reviews_yet_8b670b7eea') }}" description="{{ __('ui.new_places_remain_discoverable_without_a_rating_194bc38bf9') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'events')
        <section class="place-section" aria-labelledby="place-events-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.meet_here_7a75be8fa1') }}</p>
                    <h2 id="place-events-title">{{ __('ui.events_at_this_place_7795577c43') }}</h2>
                </div>
                @if ($place['allow_events'])
                    <x-action-control :href="$eventUrl" label="{{ __('ui.create_event_here_755fd04540') }}" icon="calendar-plus" variant="primary" size="compact" />
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
                        title="{{ __('ui.no_public_events_listed_3e1fb3de75') }}"
                        :description="$place['allow_events'] ? __('ui.a_permitted_organizer_can_create_a_new_event_98daef5aba') : __('ui.this_place_does_not_currently_allow_group_events_315f0e8de5')"
                    />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'questions')
        <section class="place-section" aria-labelledby="place-questions-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.questions_and_answers_973cb06e50') }}</p>
                    <h2 id="place-questions-title">{{ __('ui.practical_place_details_df3aa8f223') }}</h2>
                </div>
                <x-action-control :href="$questionUrl" label="{{ __('ui.ask_question_8fa2965f2d') }}" icon="message-circle-question" variant="primary" size="compact" />
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
                                <label for="answer-{{ $question['key'] }}">{{ __('ui.official_answer_2b325f8b64') }}</label>
                                <textarea id="answer-{{ $question['key'] }}" name="body" class="field field--textarea" maxlength="1200" required></textarea>
                                <x-action-control type="submit" label="{{ __('ui.publish_answer_a87ef6402e') }}" icon="send" variant="primary" size="compact" />
                            </form>
                        @else
                            <x-status-badge label="{{ __('ui.awaiting_an_answer_7982a0be95') }}" icon="clock-3" tone="neutral" />
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="circle-help" title="{{ __('ui.no_questions_yet_43ba4c6744') }}" description="{{ __('ui.ask_about_access_rules_hours_or_facilities_ae16c3177a') }}" />
                @endforelse
            </div>
        </section>
    @elseif ($activeTab === 'map')
        <section class="place-section place-section--map" aria-labelledby="place-location-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.arrival_794adbbc6c') }}</p>
                    <h2 id="place-location-title">{{ $place['coordinate_accuracy'] }}</h2>
                </div>
                <x-action-control
                    :href="$place['route_url']"
                    label="{{ __('ui.open_route_6728958c30') }}"
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
                <div><dt>{{ __('ui.address_56ef8f2095') }}</dt><dd>{{ $place['address'] }}</dd></div>
                <div><dt>{{ __('ui.public_area_911f5d1f74') }}</dt><dd>{{ $place['general_location'] }}</dd></div>
                <div><dt>{{ __('ui.coordinates_117c132e93') }}</dt><dd>{{ $place['latitude'] }}, {{ $place['longitude'] }}</dd></div>
                <div><dt>{{ __('ui.privacy_54a57c3147') }}</dt><dd>{{ __('ui.only_this_public_place_point_is_displayed_no_5086dcd2d4') }}</dd></div>
            </dl>
        </section>
    @elseif ($activeTab === 'updates')
        <section class="place-section" aria-labelledby="place-updates-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.current_conditions_8e4f7d71d2') }}</p>
                    <h2 id="place-updates-title">{{ __('ui.warnings_and_update_history_bdc8ec318a') }}</h2>
                </div>
                <x-action-control :href="$warningUrl" label="{{ __('ui.report_hazard_1e8b338ac3') }}" icon="triangle-alert" variant="danger" size="compact" />
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
                                    label="{{ __('ui.confirm_current_ef9d8f1546') }}"
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
                                    label="{{ __('ui.problem_resolved_94ba1203f0') }}"
                                    icon="circle-check-big"
                                    variant="ghost"
                                    size="compact"
                                />
                            </div>
                        @endif
                    </article>
                @empty
                    <x-empty-state icon="shield-check" title="{{ __('ui.no_active_warnings_11cfa35292') }}" description="{{ __('ui.current_place_conditions_still_need_normal_personal_judgment_76c74124d5') }}" />
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
                    <p>{{ __('ui.no_updates_recorded_dce24a0942') }}</p>
                @endforelse
            </div>

            @if (! $place['owner_managed'])
                <div class="place-claim">
                    <div>
                        <strong>{{ __('ui.manage_this_place_5121fa7129') }}</strong>
                        <span>{{ __('ui.verification_is_scoped_to_identity_organization_and_the_36e8898769') }}</span>
                    </div>
                    <x-action-control :href="$claimUrl" label="{{ __('ui.claim_profile_3f48eeba89') }}" icon="badge-check" variant="surface" size="compact" />
                </div>
            @endif

            @if (count($claims) > 0)
                <div class="place-claim-status">
                    @forelse ($claims as $claim)
                        <span>{{ $claim['organization'] }} · {{ $claim['status_label'] }}</span>
                    @empty
                        <span>{{ __('ui.no_claims_96fbdbaa5c') }}</span>
                    @endforelse
                </div>
            @endif
        </section>
    @elseif ($activeTab === 'corrections')
        <section class="place-section" aria-labelledby="place-corrections-title">
            <header class="place-section__heading">
                <div>
                    <p>{{ __('ui.data_quality_ebeb78351a') }}</p>
                    <h2 id="place-corrections-title">{{ __('ui.correction_status_79859ed68e') }}</h2>
                </div>
                <x-action-control :href="$correctionUrl" label="{{ __('ui.suggest_correction_c956d0ef71') }}" icon="file-check-2" variant="primary" size="compact" />
            </header>

            <div class="place-verification-grid">
                @forelse ($content['verification'] as $item)
                    <div>
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $item['value'] }}</span>
                    </div>
                @empty
                    <p>{{ __('ui.no_verification_scope_is_listed_d90a20877f') }}</p>
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
                    <x-empty-state icon="file-check-2" title="{{ __('ui.no_pending_corrections_bd310be15e') }}" description="{{ __('ui.important_changes_require_evidence_and_review_1592257a85') }}" />
                @endforelse
            </div>
        </section>
    @endif

    <footer class="place-dashboard__safety">
        <div>
            <x-ui-icon name="shield-alert" />
            <p>
                <strong>{{ __('ui.information_can_change_89fc12eba8') }}</strong>
                {{ __('ui.check_current_rules_live_availability_and_urgent_intake_6299988a57') }}
            </p>
        </div>
        <div>
            <x-action-control :href="$correctionUrl" label="{{ __('ui.correct_data_a626d91028') }}" icon="file-check-2" variant="ghost" size="compact" />
            <x-action-control :href="$reportUrl" label="{{ __('ui.report_place_9f7c95d3a8') }}" icon="flag" variant="ghost" size="compact" />
        </div>
    </footer>
</div>
