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
            title="The date or time changed"
            description="Review the organizer update and confirm that the revised plan still works for you."
        >
            <x-slot:actions>
                <x-action-control
                    label="Confirm revised details"
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
                <x-content-panel section="event-checklist" eyebrow="Before confirming" title="Registration checklist">
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
                            <li class="event-dashboard__empty">No checklist is available.</li>
                        @endforelse
                    </ul>
                </x-content-panel>

                <x-notice
                    icon="shield-check"
                    title="Private details stay protected"
                    :description="$canViewPrivateDetails
                        ? 'Your registration grants access to the attendee-only place or online room details.'
                        : 'Exact meeting and online-room details appear only after the required registration stage.'"
                />
            </div>
        </div>
    @elseif ($activeTab === 'schedule')
        <x-content-panel section="event-schedule" eyebrow="Event program" title="Schedule">
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
                    <li class="event-dashboard__empty">The organizer has not published a schedule.</li>
                @endforelse
            </ol>
        </x-content-panel>
    @elseif ($activeTab === 'attendees')
        <x-content-panel section="event-attendees" eyebrow="Privacy-aware directory" title="Registered people">
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
                    <p class="event-dashboard__empty">The participant list is private for this event.</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'pets')
        <x-content-panel section="event-pets" eyebrow="Owner-controlled profiles" title="Pets attending">
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
                    <p class="event-dashboard__empty">This event is owner-only or the pet directory is private.</p>
                @endforelse
            </div>
        </x-content-panel>
    @elseif ($activeTab === 'chat')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-chat" eyebrow="Registered participants" title="Event chat">
                <div class="event-chat section-body" role="log" aria-label="Event messages">
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
                        <p class="event-dashboard__empty">No event messages yet.</p>
                    @endforelse
                </div>
            </x-content-panel>

            <x-content-panel section="event-message-composer" title="Send a message" meta="Owners speak for pet profiles">
                @if ($registration['registration'] || $event['managed_by_current_user'])
                    <form method="POST" action="{{ route('actions.perform') }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="send-event-message">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="chat">
                        <label for="event-message" class="form-field__label">Message</label>
                        <textarea id="event-message" name="body" rows="5" maxlength="1200" class="field field--textarea" required placeholder="Ask about arrival, equipment, or accessibility"></textarea>
                        <x-action-control type="submit" label="Send message" icon="send" variant="primary" />
                    </form>
                @else
                    <x-notice
                        icon="lock-keyhole"
                        title="Register before joining"
                        description="Public event details remain available without exposing the attendee conversation."
                        class="section-body"
                    />
                @endif
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'announcements')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-announcements" eyebrow="Organizer updates" title="Announcements">
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
                        <p class="event-dashboard__empty">No announcements have been published.</p>
                    @endforelse
                </div>
            </x-content-panel>

            @if ($event['managed_by_current_user'])
                <x-content-panel section="event-announcement-composer" title="Publish an update">
                    <form method="POST" action="{{ $organizerTools['announcement_action'] }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="publish-event-announcement">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="announcements">
                        <label class="form-field">
                            <span class="form-field__label">Title</span>
                            <input name="title" class="field" maxlength="120" required>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">Update</span>
                            <textarea name="body" rows="5" maxlength="1200" class="field field--textarea" required></textarea>
                        </label>
                        <x-action-control type="submit" label="Publish update" icon="megaphone" variant="primary" />
                    </form>
                </x-content-panel>
            @endif
        </div>
    @elseif ($activeTab === 'location')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-location" eyebrow="Privacy-aware place" title="{{ $content['location']['general'] }}">
                <div class="event-location section-body">
                    <div class="event-location__map" role="img" aria-label="{{ $content['location']['map_alt'] }}">
                        <span class="event-location__route"></span>
                        <span class="event-location__pin"><x-lucide-map-pin class="icon" aria-hidden="true" /></span>
                        <span class="event-location__help"><x-lucide-stethoscope class="icon icon--sm" aria-hidden="true" /></span>
                    </div>
                    <x-callout
                        :icon="$event['format'] === 'online' ? 'video' : 'map-pinned'"
                        :title="$canViewPrivateDetails ? 'Attendee details' : 'Protected details'"
                        :description="$content['location']['revealed_exact']"
                    />
                    @if ($content['location']['revealed_online_link'])
                        <x-action-control
                            label="Open protected room"
                            icon="video"
                            :href="$content['location']['revealed_online_link']"
                            variant="primary"
                        />
                    @endif
                </div>
            </x-content-panel>

            <x-content-panel section="event-accessibility" eyebrow="Arrival planning" title="Access and nearby help">
                <x-definition-list :items="$content['location']['details']" strong class="section-body" />
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'media')
        <x-content-panel section="event-media" eyebrow="Consent-aware album" title="Event photos">
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
                    <p class="event-dashboard__empty">No event photos have been approved.</p>
                @endforelse
            </div>

            @if ($registration['registration'] || $event['managed_by_current_user'])
                <form method="POST" action="{{ route('actions.perform') }}" class="event-photo-form section-body">
                    @csrf
                    <input type="hidden" name="action" value="add-event-photo">
                    <input type="hidden" name="target" value="{{ $event['key'] }}">
                    <input type="hidden" name="event_return_tab" value="media">
                    <label class="form-field">
                        <span class="form-field__label">Photo caption</span>
                        <input name="photo_caption" maxlength="240" class="field" placeholder="Confirm context and consent">
                    </label>
                    <x-action-control type="submit" label="Add sample photo" icon="image-plus" variant="paper" />
                </form>
            @endif
        </x-content-panel>
    @elseif ($activeTab === 'rules')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-rules" eyebrow="Participation boundary" title="Rules">
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
                        <li class="event-dashboard__empty">No event rules are published.</li>
                    @endforelse
                </ol>
            </x-content-panel>

            <x-content-panel section="event-files" eyebrow="Current versions" title="Files and guides">
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
                        <p class="event-dashboard__empty">No event files are available.</p>
                    @endforelse
                </div>
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'reviews')
        <div class="event-dashboard__columns">
            <x-content-panel section="event-reviews" eyebrow="Verified attendance" title="Event feedback">
                <div class="event-reviews section-body">
                    @forelse ($content['reviews'] as $review)
                        <article>
                            <header>
                                <x-initials-avatar :initials="$review['initials']" :tone="$review['tone']" />
                                <div>
                                    <h3>{{ $review['title'] }}</h3>
                                    <p>{{ $review['name'] }} · {{ $review['meta'] }}</p>
                                </div>
                                <span aria-label="{{ $review['rating'] }} out of 5 stars">
                                    <x-lucide-star class="icon icon--sm" aria-hidden="true" /> {{ $review['rating'] }}
                                </span>
                            </header>
                            <p>{{ $review['body'] }}</p>
                        </article>
                    @empty
                        <p class="event-dashboard__empty">No verified reviews yet.</p>
                    @endforelse
                </div>
            </x-content-panel>

            <x-content-panel section="event-review-form" title="Share private or public feedback">
                @if (($registration['status'] ?? null) === 'checked_in')
                    <form method="POST" action="{{ route('actions.perform') }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="submit-event-review">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="reviews">
                        <label class="form-field">
                            <span class="form-field__label">Rating</span>
                            <select name="event_rating" class="field field--select" required>
                                @foreach ([5, 4, 3, 2, 1] as $rating)
                                    <option value="{{ $rating }}">{{ $rating }} out of 5</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">Review</span>
                            <textarea name="body" rows="5" maxlength="1200" class="field field--textarea" required></textarea>
                        </label>
                        <x-action-control type="submit" label="Publish review" icon="star" variant="primary" />
                    </form>
                @else
                    <x-notice
                        icon="badge-check"
                        title="Verified attendees only"
                        description="Check-in must be confirmed before a public review can be published."
                        class="section-body"
                    />
                @endif
            </x-content-panel>
        </div>
    @elseif ($activeTab === 'manage' && $event['managed_by_current_user'])
        <div class="event-dashboard__manage">
            <x-content-panel section="event-analytics" eyebrow="Aggregate only" title="Registration funnel">
                <x-stat-grid
                    :items="$content['analytics']['metrics']"
                    label="Event analytics"
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
                <x-content-panel section="event-applications" title="Applications" meta="{{ count($content['applications']) }} visible">
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
                                            label="Approve"
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
                                            label="Decline"
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
                            <p class="event-dashboard__empty">No applications need review.</p>
                        @endforelse
                    </div>
                </x-content-panel>

                <x-content-panel section="event-waitlist" title="Waitlist">
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
                                        label="Offer place"
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
                            <p class="event-dashboard__empty">The waitlist is empty.</p>
                        @endforelse
                    </div>
                </x-content-panel>
            </div>

            <div class="event-dashboard__columns">
                <x-content-panel section="event-reschedule" title="Reschedule">
                    <form method="POST" action="{{ $organizerTools['reschedule_action'] }}" class="event-message-form section-body">
                        @csrf
                        <input type="hidden" name="action" value="reschedule-event">
                        <input type="hidden" name="target" value="{{ $event['key'] }}">
                        <input type="hidden" name="event_return_tab" value="manage">
                        <label class="form-field">
                            <span class="form-field__label">New date</span>
                            <input type="date" name="event_date" min="{{ now()->format('Y-m-d') }}" class="field" required>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">New time</span>
                            <input type="time" name="event_time" class="field" required>
                        </label>
                        <label class="form-field">
                            <span class="form-field__label">Reason and impact</span>
                            <textarea name="event_note" rows="4" maxlength="500" class="field field--textarea" required></textarea>
                        </label>
                        <x-action-control type="submit" label="Reschedule and notify" icon="calendar-sync" variant="primary" />
                    </form>
                </x-content-panel>

                <x-content-panel section="event-history" title="Change history">
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
                            <li class="event-dashboard__empty">No organizer changes have been recorded.</li>
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
                <x-content-panel section="event-about" eyebrow="{{ str($event['event_type'])->headline() }}" title="What to expect">
                    <p class="event-dashboard__copy">{{ $event['long_description'] }}</p>
                    <x-icon-list :items="$content['safety']" class="section-body" />
                </x-content-panel>

                <x-content-panel section="event-schedule-preview" eyebrow="Program" title="The day at a glance">
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
                            <li class="event-dashboard__empty">Schedule pending.</li>
                        @endforelse
                    </ol>
                </x-content-panel>
            </div>

            <aside class="event-dashboard__aside">
                <x-event-registration-panel :event="$event" :registration="$registration" />

                <x-content-panel section="event-organizers" title="Organizers">
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
                            <p class="event-dashboard__empty">Organizer details are unavailable.</p>
                        @endforelse
                    </div>
                </x-content-panel>
            </aside>
        </div>

        <x-content-panel section="event-faq" eyebrow="Before you go" title="Frequently asked questions">
            <div class="event-faq section-body">
                @forelse ($content['faq'] as $item)
                    <details>
                        <summary>{{ $item['question'] }}</summary>
                        <p>{{ $item['answer'] }}</p>
                    </details>
                @empty
                    <p class="event-dashboard__empty">No event questions are listed.</p>
                @endforelse
            </div>
        </x-content-panel>
    @endif
</div>
