@props([
    'event',
    'activeTab',
    'content',
    'registration',
    'canViewPrivateDetails',
    'organizerTools',
])

<div class="event-dashboard">
    @if ($event['status'] === 'rescheduled' && ($registration['registration']['reschedule_acknowledged'] ?? true) === false)
        <x-notice
            icon="calendar-sync"
            title="{{ __('ui.the_date_or_time_changed_82e5541176') }}"
            description="{{ __('ui.review_the_organizer_update_and_confirm_that_the_954af11cff') }}"
        >
            <x-slot:actions>
                <x-action-control
                    label="{{ __('ui.confirm_revised_details_f09c8fb1fa') }}"
                    icon="calendar-check"
                    :endpoint="route('actions.perform')"
                    :payload="[
                        'action' => 'acknowledge-event-reschedule',
                        'target' => $event['key'],
                        'event_return_tab' => $activeTab,
                    ]"
                    variant="primary"
                />
            </x-slot:actions>
        </x-notice>
    @endif

    @if ($activeTab === 'tickets')
        <div class="event-dashboard__columns event-dashboard__columns--registration">
            <x-event-registration-panel :event="$event" :registration="$registration" />

            <div class="event-dashboard__aside">
                <x-content-panel section="event-checklist" eyebrow="{{ __('ui.before_confirming_b01c6ff7a6') }}" title="{{ __('ui.registration_checklist_7ccd63f606') }}">
                    <ul class="event-checklist section-body">
                        @forelse ($content['checklist'] as $item)
                            <li>
                                <x-dynamic-component
                                    :component="'lucide-'.($item['done'] ? 'circle-check-big' : 'circle')"
                                    class="icon icon--sm"
                                    aria-hidden="true"
                                />
                                <span>{{ $item['label'] }}</span>
                            </li>
                        @empty
                            <li class="event-dashboard__empty">{{ __('ui.no_checklist_is_available_6536094711') }}</li>
                        @endforelse
                    </ul>
                </x-content-panel>

                <x-notice
                    icon="shield-check"
                    title="{{ __('ui.private_details_stay_protected_08952eb7d0') }}"
                    :description="$canViewPrivateDetails
                        ? __('ui.your_registration_grants_access_to_the_attendee_only_9f25a9794c')
                        : __('ui.exact_meeting_and_online_room_details_appear_only_88d8fed1cf')"
                />
            </div>
        </div>
    @elseif ($activeTab === 'schedule')
        <x-content-panel section="event-schedule" eyebrow="{{ __('ui.event_program_10c28155ae') }}" title="{{ __('ui.schedule_f4830a1dae') }}">
            <ol class="event-schedule section-body">
                @forelse ($content['schedule'] as $item)
                    <li>
                        <time>{{ $item['time'] }}</time>
                        <span aria-hidden="true"></span>
                        <div>
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['description'] }}</p>
                            <small>{{ $item['leader'] }}</small>
                        </div>
                    </li>
                @empty
                    <li class="event-dashboard__empty">{{ __('ui.the_organizer_has_not_published_a_schedule_9c12115ae0') }}</li>
                @endforelse
            </ol>
        </x-content-panel>
    @elseif ($activeTab === 'attendees')
        <x-content-panel section="event-attendees" eyebrow="{{ __('ui.privacy_aware_directory_81170e8eda') }}" title="{{ __('ui.registered_people_13c4685a1a') }}">
            <div class="event-people section-body" role="list">
                @forelse ($content['attendees'] as $person)
                    <article class="event-person" role="listitem">
                        <x-initials-avatar :initials="$person['initials']" :tone="$person['tone']" />
                        <div>
                            <h3>{{ $person['name'] }}</h3>
                            <p>{{ $person['detail'] }}</p>
                        </div>
                        <x-status-badge :label="$person['badge']" tone="surface" />
                    </article>
                @empty
                    <p class="event-dashboard__empty">{{ __('ui.the_participant_list_is_private_for_this_event_7803b81b75') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'pets')
        <x-content-panel section="event-pets" eyebrow="{{ __('ui.owner_controlled_profiles_95d88f7259') }}" title="{{ __('ui.pets_attending_e246ce7501') }}">
            <div class="event-people section-body" role="list">
                @forelse ($content['pets'] as $pet)
                    <article class="event-person" role="listitem">
                        <x-initials-avatar :initials="$pet['initials']" :tone="$pet['tone']" />
                        <div>
                            <h3>{{ $pet['name'] }}</h3>
                            <p>{{ $pet['detail'] }}</p>
                        </div>
                        <x-status-badge :label="$pet['badge']" tone="surface" />
                    </article>
                @empty
                    <p class="event-dashboard__empty">{{ __('ui.this_event_is_owner_only_or_the_pet_9119e70d54') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'chat')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-chat" eyebrow="{{ __('ui.registered_participants_7e10dfd741') }}" title="{{ __('ui.event_chat_9c298662a4') }}">
                <div class="event-chat section-body" role="log" aria-label="{{ __('ui.event_messages_c7f4f6cfc0') }}">
                    @forelse ($content['chat'] as $message)
                        <article class="event-message">
                            <x-initials-avatar :initials="$message['initials']" :tone="$message['tone']" />
                            <div>
                                <header>
                                    <strong>{{ $message['name'] }}</strong>
                                    <time>{{ $message['time'] }}</time>
                                </header>
                                <p>{{ $message['body'] }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="event-dashboard__empty">{{ __('ui.no_event_messages_yet_86bdd1f081') }}</p>
                    @endforelse
                </div>
            </x-content-panel>

            <x-content-panel section="event-message-composer" title="{{ __('ui.send_a_message_56e541ad86') }}" meta="Owners speak for pet profiles">
                @if ($registration['registration'] || $event['managed_by_current_user'])
                    <form method="POST" action="{{ route('actions.perform') }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="send-event-message">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="chat">
                        <label for="event-message" class="form-field__label">{{ __('ui.message_2f77668a9d') }}</label>
                        <textarea id="event-message" name="body" rows="5" maxlength="1200" class="field field--textarea" required placeholder="{{ __('ui.ask_about_arrival_equipment_or_accessibility_bd5c66d057') }}"></textarea>
                        <x-action-control type="submit" label="{{ __('ui.send_message_93a26b1eaf') }}" icon="send" variant="primary" />
                    </form>
                @else
                    <x-notice
                        icon="lock-keyhole"
                        title="{{ __('ui.register_before_joining_679b825632') }}"
                        description="{{ __('ui.public_event_details_remain_available_without_exposing_the_7ddee68e1d') }}"
                        class="section-body"
                    />
                @endif
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'announcements')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-announcements" eyebrow="{{ __('ui.organizer_updates_01d6197cc4') }}" title="{{ __('ui.announcements_fe02680f24') }}">
                <div class="event-announcements section-body">
                    @forelse ($content['announcements'] as $announcement)
                        <article>
                            <span><x-dynamic-component :component="'lucide-'.$announcement['icon']" class="icon" aria-hidden="true" /></span>
                            <div>
                                <h3>{{ $announcement['title'] }}</h3>
                                <p>{{ $announcement['body'] }}</p>
                                <time>{{ $announcement['time'] }}</time>
                            </div>
                        </article>
                    @empty
                        <p class="event-dashboard__empty">{{ __('ui.no_announcements_have_been_published_5ec22c0b2b') }}</p>
                    @endforelse
                </div>
            </x-content-panel>

            @if ($event['managed_by_current_user'])
                <x-content-panel section="event-announcement-composer" title="{{ __('ui.publish_an_update_9ac595582e') }}">
                    <form method="POST" action="{{ $organizerTools['announcement_action'] }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="publish-event-announcement">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="announcements">
                        <label class="form-field">
                            <span class="form-field__label">{{ __('ui.title_7e8cd2056d') }}</span>
                            <input name="title" class="field" maxlength="120" required>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">{{ __('ui.update_c1c1009d3f') }}</span>
                            <textarea name="body" rows="5" maxlength="1200" class="field field--textarea" required></textarea>
                        </label>
                        <x-action-control type="submit" label="{{ __('ui.publish_update_a74a0a4b0b') }}" icon="megaphone" variant="primary" />
                    </form>
                </x-content-panel>
            @endif
        </div>
    @elseif ($activeTab === 'location')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-location" eyebrow="{{ __('ui.privacy_aware_place_604fff8610') }}" title="{{ $content['location']['general'] }}">
                <div class="event-location section-body">
                    <div class="event-location__map" role="img" aria-label="{{ $content['location']['map_alt'] }}">
                        <span class="event-location__route"></span>
                        <span class="event-location__pin"><x-lucide-map-pin class="icon" aria-hidden="true" /></span>
                        <span class="event-location__help"><x-lucide-stethoscope class="icon icon--sm" aria-hidden="true" /></span>
                    </div>
                    <x-callout
                        :icon="$event['format'] === 'online' ? 'video' : 'map-pinned'"
                        :title="$canViewPrivateDetails ? __('ui.attendee_details_af78547924') : __('ui.protected_details_8137c5b0df')"
                        :description="$content['location']['revealed_exact']"
                    />
                    @if ($content['location']['revealed_online_link'])
                        <x-action-control
                            label="{{ __('ui.open_protected_room_8bf5a4167d') }}"
                            icon="video"
                            :href="$content['location']['revealed_online_link']"
                            variant="primary"
                        />
                    @endif
                </div>
            </x-content-panel>

            <x-content-panel section="event-accessibility" eyebrow="{{ __('ui.arrival_planning_78b6aca0fb') }}" title="{{ __('ui.access_and_nearby_help_03a4fd114d') }}">
                <x-definition-list :items="$content['location']['details']" strong class="section-body" />
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'media')
        <x-content-panel section="event-media" eyebrow="{{ __('ui.consent_aware_album_5c7af7beb4') }}" title="{{ __('ui.event_photos_301c8b92ef') }}">
            <div class="event-gallery section-body">
                @forelse ($content['gallery'] as $photo)
                    <figure>
                        <x-responsive-image
                            :src="$photo['src']"
                            :small="$photo['small']"
                            :medium="$photo['medium']"
                            :alt="$photo['alt']"
                            :width="900"
                            :height="675"
                            sizes="(min-width: 64rem) 38rem, 100vw"
                        />
                        <figcaption>{{ $photo['caption'] }}</figcaption>
                    </figure>
                @empty
                    <p class="event-dashboard__empty">{{ __('ui.no_event_photos_have_been_approved_a1f1009e70') }}</p>
                @endforelse
            </div>

            @if ($registration['registration'] || $event['managed_by_current_user'])
                <form method="POST" action="{{ route('actions.perform') }}" class="event-photo-form section-body">
                    @csrf
                    <input type="hidden" name="action" value="add-event-photo">
                    <input type="hidden" name="target" value="{{ $event['key'] }}">
                    <input type="hidden" name="event_return_tab" value="media">
                    <label class="form-field">
                        <span class="form-field__label">{{ __('ui.photo_caption_bad132b9f3') }}</span>
                        <input name="photo_caption" maxlength="240" class="field" placeholder="{{ __('ui.confirm_context_and_consent_72119449db') }}">
                    </label>
                    <x-action-control type="submit" label="{{ __('ui.add_sample_photo_44f304aea2') }}" icon="image-plus" variant="paper" />
                </form>
            @endif
        </x-content-panel>
    @elseif ($activeTab === 'rules')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-rules" eyebrow="{{ __('ui.participation_boundary_f418c80be9') }}" title="{{ __('ui.rules_4228aeb07c') }}">
                <ol class="event-rules section-body">
                    @forelse ($content['rules'] as $rule)
                        <li>
                            <span>{{ $loop->iteration }}</span>
                            <div>
                                <h3>{{ $rule['title'] }}</h3>
                                <p>{{ $rule['description'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="event-dashboard__empty">{{ __('ui.no_event_rules_are_published_7013ed6162') }}</li>
                    @endforelse
                </ol>
            </x-content-panel>

            <x-content-panel section="event-files" eyebrow="{{ __('ui.current_versions_09eccee26a') }}" title="{{ __('ui.files_and_guides_afd6d8b0a1') }}">
                <div class="event-files section-body">
                    @forelse ($content['files'] as $file)
                        <article>
                            <span><x-dynamic-component :component="'lucide-'.$file['icon']" class="icon" aria-hidden="true" /></span>
                            <div>
                                <h3>{{ $file['title'] }}</h3>
                                <p>{{ $file['description'] }}</p>
                                <small>{{ $file['meta'] }}</small>
                            </div>
                        </article>
                    @empty
                        <p class="event-dashboard__empty">{{ __('ui.no_event_files_are_available_f7f98e5a6f') }}</p>
                    @endforelse
                </div>
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'reviews')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-reviews" eyebrow="{{ __('ui.verified_attendance_aed3a629f9') }}" title="{{ __('ui.event_feedback_90537662ce') }}">
                <div class="event-reviews section-body">
                    @forelse ($content['reviews'] as $review)
                        <article>
                            <header>
                                <x-initials-avatar :initials="$review['initials']" :tone="$review['tone']" />
                                <div>
                                    <h3>{{ $review['title'] }}</h3>
                                    <p>{{ $review['name'] }} · {{ $review['meta'] }}</p>
                                </div>
                                <span aria-label="{{ __('presentation.rating_out_of_five_stars', ['rating' => $review['rating']]) }}">
                                    <x-lucide-star class="icon icon--sm" aria-hidden="true" /> {{ $review['rating'] }}
                                </span>
                            </header>
                            <p>{{ $review['body'] }}</p>
                        </article>
                    @empty
                        <p class="event-dashboard__empty">{{ __('ui.no_verified_reviews_yet_dfc981f141') }}</p>
                    @endforelse
                </div>
            </x-content-panel>

            <x-content-panel section="event-review-form" title="{{ __('ui.share_private_or_public_feedback_8378a56b19') }}">
                @if (($registration['status'] ?? null) === 'checked_in')
                    <form method="POST" action="{{ route('actions.perform') }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="submit-event-review">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="reviews">
                        <label class="form-field">
                            <span class="form-field__label">{{ __('ui.rating_9f29530464') }}</span>
                            <select name="event_rating" class="field field--select" required>
                                @foreach ([5, 4, 3, 2, 1] as $rating)
                                    <option value="{{ $rating }}">{{ __('presentation.rating_out_of_five', ['rating' => $rating]) }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">{{ __('ui.review_aff0766a52') }}</span>
                            <textarea name="body" rows="5" maxlength="1200" class="field field--textarea" required></textarea>
                        </label>
                        <x-action-control type="submit" label="{{ __('ui.publish_review_f795632a1e') }}" icon="star" variant="primary" />
                    </form>
                @else
                    <x-notice
                        icon="badge-check"
                        title="{{ __('ui.verified_attendees_only_6e86f902b4') }}"
                        description="{{ __('ui.check_in_must_be_confirmed_before_a_public_daebef9a0d') }}"
                        class="section-body"
                    />
                @endif
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'manage' && $event['managed_by_current_user'])
        <div class="event-dashboard__manage">
            <x-content-panel section="event-analytics" eyebrow="{{ __('ui.aggregate_only_a550b453b3') }}" title="{{ __('ui.registration_funnel_bc4801ac74') }}">
                <x-stat-grid
                    :items="$content['analytics']['metrics']"
                    label="{{ __('ui.event_analytics_fd96f0715d') }}"
                    :icons="['eye', 'mouse-pointer-click', 'list-checks', 'ticket-check', 'badge-check', 'shield-check']"
                    class="section-body"
                />
                <div class="event-funnel section-body">
                    @foreach ($content['analytics']['funnel'] as $step)
                        <x-progress-meter
                            :label="$step['label'].' · '.$step['value']"
                            :value="$step['percent']"
                        />
                    @endforeach
                </div>
                <p class="event-dashboard__privacy">{{ $content['analytics']['privacy_note'] }}</p>
            </x-content-panel>

            <div class="event-dashboard__columns">
                <x-content-panel section="event-applications" title="{{ __('ui.applications_98e33b0f31') }}" :meta="__('presentation.visible_count', ['count' => count($content['applications'])])">
                    <div class="event-decisions section-body">
                        @forelse ($content['applications'] as $application)
                            <article>
                                <x-initials-avatar :initials="$application['initials']" :tone="$application['tone']" />
                                <div>
                                    <h3>{{ $application['name'] }}</h3>
                                    <p>{{ $application['detail'] }}</p>
                                    <x-status-badge :label="$application['status']" tone="surface" />
                                </div>
                                @if ($application['state'] === 'pending')
                                    <div>
                                        <x-action-control
                                            label="{{ __('ui.approve_6007acbe30') }}"
                                            icon="check"
                                            :endpoint="route('actions.perform')"
                                            :payload="[
                                                'action' => 'approve-event-application',
                                                'target' => $event['key'],
                                                'event_application' => $application['key'],
                                                'event_return_tab' => 'manage',
                                            ]"
                                            variant="primary"
                                        />
                                        <x-action-control
                                            label="{{ __('ui.decline_a2d285b352') }}"
                                            icon="x"
                                            :endpoint="route('actions.perform')"
                                            :payload="[
                                                'action' => 'decline-event-application',
                                                'target' => $event['key'],
                                                'event_application' => $application['key'],
                                                'event_return_tab' => 'manage',
                                            ]"
                                            variant="paper"
                                        />
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="event-dashboard__empty">{{ __('ui.no_applications_need_review_b16dbcb198') }}</p>
                        @endforelse
                    </div>
                </x-content-panel>

                <x-content-panel section="event-waitlist" title="{{ __('ui.waitlist_ec08d977c6') }}">
                    <div class="event-decisions section-body">
                        @forelse ($content['waitlist'] as $candidate)
                            <article>
                                <x-initials-avatar :initials="$candidate['initials']" :tone="$candidate['tone']" />
                                <div>
                                    <h3>{{ $candidate['name'] }}</h3>
                                    <p>{{ $candidate['detail'] }}</p>
                                    <x-status-badge :label="$candidate['status']" tone="surface" />
                                </div>
                                @if ($candidate['state'] === 'waiting')
                                    <x-action-control
                                        label="{{ __('ui.offer_place_8bde3ea826') }}"
                                        icon="ticket-plus"
                                        :endpoint="route('actions.perform')"
                                        :payload="[
                                            'action' => 'promote-event-waitlist',
                                            'target' => $event['key'],
                                            'event_candidate' => $candidate['key'],
                                            'event_return_tab' => 'manage',
                                        ]"
                                        variant="primary"
                                    />
                                @endif
                            </article>
                        @empty
                            <p class="event-dashboard__empty">{{ __('ui.the_waitlist_is_empty_a5cf804b42') }}</p>
                        @endforelse
                    </div>
                </x-content-panel>
            </div>

            <div class="event-dashboard__columns">
                <x-content-panel section="event-reschedule" title="{{ __('ui.reschedule_a6a80431b0') }}">
                    <form method="POST" action="{{ $organizerTools['reschedule_action'] }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="reschedule-event">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="manage">
                        <label class="form-field">
                            <span class="form-field__label">{{ __('ui.new_date_c6918d9406') }}</span>
                            <input type="date" name="event_date" min="{{ now()->format('Y-m-d') }}" class="field" required>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">{{ __('ui.new_time_70231ba056') }}</span>
                            <input type="time" name="event_time" class="field" required>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">{{ __('ui.reason_and_impact_a36e797b8d') }}</span>
                            <textarea name="event_note" rows="4" maxlength="500" class="field field--textarea" required></textarea>
                        </label>
                        <x-action-control type="submit" label="{{ __('ui.reschedule_and_notify_e9b31fecae') }}" icon="calendar-sync" variant="primary" />
                    </form>
                </x-content-panel>

                <x-content-panel section="event-history" title="{{ __('ui.change_history_964de7c92e') }}">
                    <ol class="event-history section-body">
                        @forelse ($content['history'] as $item)
                            <li>
                                <x-lucide-history class="icon icon--sm" aria-hidden="true" />
                                <div>
                                    <p>{{ $item['message'] }}</p>
                                    <time datetime="{{ $item['created_at'] }}">{{ $item['created_at_label'] }}</time>
                                </div>
                            </li>
                        @empty
                            <li class="event-dashboard__empty">{{ __('ui.no_organizer_changes_have_been_recorded_fb3a175fa2') }}</li>
                        @endforelse
                    </ol>
                    <div class="section-body">
                        <x-action-control
                            :label="$organizerTools['cancel_action']['label']"
                            :icon="$organizerTools['cancel_action']['icon']"
                            :endpoint="$organizerTools['cancel_action']['endpoint']"
                            :payload="$organizerTools['cancel_action']['payload']"
                            variant="paper"
                        />
                    </div>
                </x-content-panel>
            </div>
        </div>
    @else
        <div class="event-dashboard__overview">
            <div class="event-dashboard__main">
                <x-content-panel section="event-about" :eyebrow="$event['event_type_label']" title="{{ __('ui.what_to_expect_7ef84dcd83') }}">
                    <p class="event-dashboard__copy">{{ $event['long_description'] }}</p>
                    <x-icon-list :items="$content['safety']" class="section-body" />
                </x-content-panel>

                <x-content-panel section="event-schedule-preview" eyebrow="{{ __('ui.program_90920d93e2') }}" title="{{ __('ui.the_day_at_a_glance_c3c6e480a5') }}">
                    <ol class="event-schedule event-schedule--compact section-body">
                        @forelse (array_slice($content['schedule'], 0, 3) as $item)
                            <li>
                                <time>{{ $item['time'] }}</time>
                                <span aria-hidden="true"></span>
                                <div>
                                    <h3>{{ $item['title'] }}</h3>
                                    <p>{{ $item['description'] }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="event-dashboard__empty">{{ __('ui.schedule_pending_2696393341') }}</li>
                        @endforelse
                    </ol>
                </x-content-panel>
            </div>

            <aside class="event-dashboard__aside">
                <x-event-registration-panel :event="$event" :registration="$registration" />

                <x-content-panel section="event-organizers" title="{{ __('ui.organizers_a2c0cec8bf') }}">
                    <div class="event-people section-body">
                        @forelse ($content['organizers'] as $person)
                            <article class="event-person">
                                <x-initials-avatar :initials="$person['initials']" :tone="$person['tone']" />
                                <div>
                                    <h3>{{ $person['name'] }}</h3>
                                    <p>{{ $person['detail'] }}</p>
                                </div>
                                <x-status-badge :label="$person['badge']" tone="surface" />
                            </article>
                        @empty
                            <p class="event-dashboard__empty">{{ __('ui.organizer_details_are_unavailable_91ab862446') }}</p>
                        @endforelse
                    </div>
                </x-content-panel>
            </aside>
        </div>

        <x-content-panel section="event-faq" eyebrow="{{ __('ui.before_you_go_c682ac1a8b') }}" title="{{ __('ui.frequently_asked_questions_e956a9404b') }}">
            <div class="event-faq section-body">
                @forelse ($content['faq'] as $item)
                    <details>
                        <summary>{{ $item['question'] }}</summary>
                        <p>{{ $item['answer'] }}</p>
                    </details>
                @empty
                    <p class="event-dashboard__empty">{{ __('ui.no_event_questions_are_listed_bb32496557') }}</p>
                @endforelse
            </div>
        </x-content-panel>
    @endif
</div>
