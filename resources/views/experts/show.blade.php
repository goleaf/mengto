<x-app-shell :title="$page_title" :active-section="$active_section">
    <div class="grid gap-8">
        <x-detail-navigation
            :href="route('experts.index')"
            :label="__('ui.expert_directory')"
        />

        <header data-expert-detail-identity class="grid gap-6 border-b border-paw-line pb-8 lg:grid-cols-[1fr_auto] lg:items-start">
            <div class="flex flex-col gap-5 sm:flex-row">
                @if ($expert['avatar_url'])
                    <img src="{{ $expert['avatar_url'] }}" alt="" width="112" height="112" class="size-28 shrink-0 rounded-full object-cover">
                @else
                    <span class="grid size-28 shrink-0 place-items-center rounded-full bg-paw-mint text-2xl font-bold text-paw-leaf" aria-hidden="true">{{ $expert['initials'] }}</span>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold sm:text-4xl">{{ $expert['name'] }}</h1>
                        <x-status-badge
                            :label="$expert['qualification_verified'] ? __('ui.qualification_verified') : $expert['verification']"
                            :icon="$expert['qualification_verified'] ? 'badge-check' : 'circle-help'"
                            :tone="$expert['qualification_verified'] ? 'success' : 'surface'"
                        />
                    </div>
                    <p class="mt-2 font-bold text-paw-leaf">{{ $expert['type'] }} · {{ $expert['city'] }}</p>
                    <p class="mt-3 max-w-3xl text-xl leading-8">{{ $expert['headline'] }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @forelse ($expert['specializations'] as $specialization)
                            <span class="rounded border border-paw-line bg-white px-2.5 py-1 text-sm font-semibold">{{ $specialization }}</span>
                        @empty
                            <span class="text-sm text-paw-muted">{{ __('ui.specialization_details_pending') }}</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid min-w-60 gap-2">
                @if ($expert['accepts_new_clients'])
                    <x-action-control label="{{ __('ui.book_consultation') }}" icon="calendar-plus" variant="primary" :href="route('experts.bookings.create', $expert['slug'])" />
                @endif
                <form method="POST" action="{{ route('experts.actions', $expert['slug']) }}" class="grid grid-cols-2 gap-2">
                    @csrf
                    <button type="submit" name="action" value="toggle-save" class="action action--surface action--compact">
                        <x-ui-icon name="bookmark" size="sm" />
                        <span>{{ $engagement['is_saved'] ? __('ui.saved') : __('ui.save') }}</span>
                    </button>
                    <button type="submit" name="action" value="toggle-subscribe" class="action action--surface action--compact">
                        <x-ui-icon name="bell" size="sm" />
                        <span>{{ $engagement['is_subscribed'] ? __('ui.following') : __('ui.follow') }}</span>
                    </button>
                </form>
                @if ($can_manage)
                    <x-action-control label="{{ __('ui.edit_profile') }}" icon="pencil" :href="route('experts.edit', $expert['slug'])" />
                @else
                    <x-action-control label="{{ __('ui.send_a_message_request') }}" icon="message-circle" :href="url('/messages')" />
                @endif
            </div>
        </header>

        @unless ($expert['offers_emergency_care'])
            <section class="flex gap-3 border-l-4 border-red-500 bg-red-50 p-4 text-red-950" aria-label="{{ __('ui.emergency_boundary') }}">
                <x-ui-icon name="siren" size="lg" class="mt-0.5 shrink-0" />
                <div>
                    <h2 class="font-bold">{{ __('ui.this_profile_is_not_an_emergency_service') }}</h2>
                    <p class="mt-1 text-sm">{{ __('ui.for_breathing_trouble_seizures_poisoning_severe_bleeding_collapse_or_major_trauma_call_a_local_emergency_clinic_instead_of_waiting_for_a_message_or_planned') }}</p>
                </div>
            </section>
        @endunless

        <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <div class="grid gap-8">
                <section aria-labelledby="about-expert">
                    <h2 id="about-expert" class="text-2xl font-bold">{{ __('ui.professional_scope') }}</h2>
                    <p class="mt-3 whitespace-pre-line leading-7">{{ $expert['bio'] }}</p>
                    <dl class="mt-5 grid gap-4 border-y border-paw-line py-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">{{ __('ui.approach') }}</dt>
                            <dd class="mt-1 leading-6">{{ $expert['approach'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">{{ __('ui.boundaries') }}</dt>
                            <dd class="mt-1 leading-6">{{ $expert['boundaries'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">{{ __('ui.experience') }}</dt>
                            <dd class="mt-1">{{ trans_choice('presentation.years_count', $expert['years_experience'], ['count' => $expert['years_experience']]) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">{{ __('ui.service_area') }}</dt>
                            <dd class="mt-1">{{ $expert['service_area'] ?? $expert['city'] }}</dd>
                        </div>
                    </dl>
                </section>

                <section aria-labelledby="services-heading">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h2 id="services-heading" class="text-2xl font-bold">{{ __('ui.services_and_prices') }}</h2>
                            <p class="mt-1 text-sm text-paw-muted">{{ __('ui.the_booking_flow_confirms_the_current_price_and_cancellation_policy_before_submission') }}</p>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @forelse ($services as $service)
                            <x-service-card :service="$service" :expert-slug="$expert['slug']" />
                        @empty
                            <p class="text-paw-muted">{{ __('ui.no_active_services_are_published') }}</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="content-heading">
                    <h2 id="content-heading" class="text-2xl font-bold">{{ __('ui.professional_materials') }}</h2>
                    <div class="mt-4 grid gap-4">
                        @forelse ($publications as $publication)
                            <article class="border-b border-paw-line pb-4">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase text-paw-muted">
                                    <span>{{ $publication['type'] }}</span>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $publication['category'] }}</span>
                                    @if ($publication['reviewed'])
                                        <span aria-hidden="true">·</span>
                                        <span>{{ __('presentation.reviewed_on', ['date' => $publication['reviewed']]) }}</span>
                                    @endif
                                </div>
                                <h3 class="mt-2 text-lg font-bold">{{ $publication['title'] }}</h3>
                                <p class="mt-2 leading-6 text-paw-muted">{{ $publication['summary'] }}</p>
                                @if ($publication['conflict_disclosure'])
                                    <p class="mt-2 text-xs text-paw-muted">{{ __('presentation.disclosure', ['disclosure' => $publication['conflict_disclosure']]) }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="text-paw-muted">{{ __('ui.no_professional_materials_published_yet') }}</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="forum-heading">
                    <h2 id="forum-heading" class="text-2xl font-bold">{{ __('ui.forum_contributions') }}</h2>
                    <div class="mt-4 grid gap-3">
                        @forelse ($forum_answers as $answer)
                            <article class="border-l-2 border-paw-leaf pl-4">
                                <h3 class="font-bold"><a href="{{ url('/forum/topics/'.$answer['topic_slug']) }}" class="hover:text-paw-leaf">{{ $answer['topic_title'] }}</a></h3>
                                <p class="mt-1 text-sm leading-6 text-paw-muted">{{ $answer['excerpt'] }}</p>
                                <p class="mt-2 text-xs font-semibold text-paw-muted">{{ __('presentation.helpful_created', ['count' => $answer['helpful_count'], 'date' => $answer['created_label']]) }}</p>
                            </article>
                        @empty
                            <p class="text-paw-muted">{{ __('ui.no_published_forum_answers_are_linked_to_this_profile') }}</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="reviews-heading">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 id="reviews-heading" class="text-2xl font-bold">{{ __('ui.client_reviews') }}</h2>
                            <p class="mt-1 text-sm text-paw-muted">{{ __('ui.verified_means_a_completed_service_was_recorded_it_does_not_verify_every_statement_in_the_review') }}</p>
                        </div>
                        <strong>{{ $expert['review_count'] > 0 ? $expert['rating'].' / 5' : __('ui.new_profile') }}</strong>
                    </div>
                    <div class="mt-4 grid gap-4">
                        @forelse ($reviews as $review)
                            <article class="border-b border-paw-line pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold">{{ $review['reviewer_name'] }}</h3>
                                    @if ($review['is_verified_client'])
                                        <x-status-badge label="{{ __('ui.verified_client') }}" icon="badge-check" tone="success" />
                                    @endif
                                    <span class="text-sm font-bold">{{ $review['rating'] }} / 5</span>
                                </div>
                                <p class="mt-2 leading-6">{{ $review['body'] }}</p>
                                <p class="mt-2 text-xs text-paw-muted">{{ $review['created_label'] }}</p>
                                @if ($review['expert_reply'])
                                    <div class="mt-3 border-l-2 border-paw-line pl-3 text-sm">
                                        <strong>{{ __('ui.professional_reply') }}</strong>
                                        <p class="mt-1">{{ $review['expert_reply'] }}</p>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="text-paw-muted">{{ __('ui.no_published_reviews_yet') }}</p>
                        @endforelse
                    </div>

                    @if ($reviewable_bookings !== [])
                        <form method="POST" action="{{ route('experts.reviews.store', $expert['slug']) }}" class="mt-6 grid gap-3 border-t border-paw-line pt-5">
                            @csrf
                            <h3 class="text-lg font-bold">{{ __('ui.review_a_completed_service') }}</h3>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <label class="grid gap-1 text-sm font-semibold">
                                    {{ __('ui.completed_booking') }}
                                    <select name="booking_id" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                        @forelse ($reviewable_bookings as $booking)
                                            <option value="{{ $booking['id'] }}">{{ $booking['label'] }}</option>
                                        @empty
                                            <option disabled>{{ __('ui.no_completed_bookings') }}</option>
                                        @endforelse
                                    </select>
                                </label>
                                <label class="grid gap-1 text-sm font-semibold">
                                    {{ __('ui.overall_rating') }}
                                    <select name="rating" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                        @for ($rating = 5; $rating >= 1; $rating--)
                                            <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                        @endfor
                                    </select>
                                </label>
                                @foreach ([
                                    'communication_rating' => __('ui.communication'),
                                    'clarity_rating' => __('ui.clarity'),
                                    'organization_rating' => __('ui.organization'),
                                    'price_transparency_rating' => __('ui.price_transparency'),
                                ] as $field => $label)
                                    <label class="grid gap-1 text-sm font-semibold">
                                        {{ $label }}
                                        <select name="{{ $field }}" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                            @for ($rating = 5; $rating >= 1; $rating--)
                                                <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                            @endfor
                                        </select>
                                    </label>
                                @endforeach
                            </div>
                            <label class="grid gap-1 text-sm font-semibold">
                                {{ __('ui.review') }}
                                <textarea name="body" required rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.describe_the_service_communication_organization_and_price_transparency') }}"></textarea>
                            </label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_anonymous" value="1"> {{ __('ui.show_publicly_as_a_verified_client') }}</label>
                            <button class="action action--primary action--compact w-fit" type="submit"><x-ui-icon name="send" size="sm" /><span>{{ __('ui.publish_review') }}</span></button>
                        </form>
                    @endif
                </section>
            </div>

            <aside class="grid content-start gap-7">
                <section aria-labelledby="verification-heading">
                    <h2 id="verification-heading" class="text-xl font-bold">{{ __('ui.what_was_checked') }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('ui.identity_education_qualification_license_workplace_organization_and_contact_are_separate_checks') }}</p>
                    <div class="mt-4">
                        <x-verification-list :items="$expert['verification_items']" :expires="$expert['verification_expires']" />
                    </div>
                </section>

                <section class="border-y border-paw-line py-5" aria-labelledby="availability-heading">
                    <h2 id="availability-heading" class="text-xl font-bold">{{ __('ui.availability') }}</h2>
                    <dl class="mt-3 grid gap-3 text-sm">
                        <div><dt class="text-paw-muted">{{ __('ui.status') }}</dt><dd class="font-semibold">{{ $expert['availability_status'] }}</dd></div>
                        <div><dt class="text-paw-muted">{{ __('ui.next_opening') }}</dt><dd class="font-semibold">{{ $expert['next_available'] ?? __('ui.by_request') }}</dd></div>
                        <div><dt class="text-paw-muted">{{ __('ui.typical_response') }}</dt><dd class="font-semibold">{{ $expert['response_time'] ?? __('ui.not_stated') }}</dd></div>
                        <div><dt class="text-paw-muted">{{ __('ui.formats') }}</dt><dd class="font-semibold">{{ implode(', ', $expert['formats']) }}</dd></div>
                        <div><dt class="text-paw-muted">{{ __('ui.languages') }}</dt><dd class="font-semibold">{{ implode(', ', $expert['languages']) }}</dd></div>
                    </dl>
                </section>

                <section aria-labelledby="species-heading">
                    <h2 id="species-heading" class="text-xl font-bold">{{ __('ui.works_with') }}</h2>
                    <p class="mt-2 text-sm leading-6">{{ implode(', ', $expert['species']) }}</p>
                    @if ($expert['age_groups'] !== [])
                        <p class="mt-2 text-sm text-paw-muted">{{ __('presentation.age_groups', ['groups' => implode(', ', $expert['age_groups'])]) }}</p>
                    @endif
                </section>

                <section aria-labelledby="credentials-heading">
                    <h2 id="credentials-heading" class="text-xl font-bold">{{ __('ui.credential_register') }}</h2>
                    <div class="mt-3 grid gap-3">
                        @forelse ($credentials as $credential)
                            <article class="border-b border-paw-line pb-3 text-sm">
                                <h3 class="font-bold">{{ $credential['title'] }}</h3>
                                <p class="mt-1 text-paw-muted">{{ $credential['issuer'] }}{{ $credential['region'] ? ' · '.$credential['region'] : '' }}</p>
                                <p class="mt-1">{{ $credential['status'] }}{{ $credential['masked_number'] ? ' · '.$credential['masked_number'] : '' }}</p>
                                @if ($credential['expires_at'])
                                    <p class="mt-1 text-xs text-paw-muted">{{ __('presentation.credential_expires', ['date' => $credential['expires_at']]) }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_public_credential_summary_is_available') }}</p>
                        @endforelse
                    </div>
                </section>

                <form method="POST" action="{{ route('experts.actions', $expert['slug']) }}" class="grid gap-2 border-t border-paw-line pt-5">
                    @csrf
                    <input type="hidden" name="action" value="report">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.report_a_professional_concern') }}
                        <select name="reason" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="false-qualification">{{ __('ui.false_qualification') }}</option>
                            <option value="dangerous-advice">{{ __('ui.dangerous_advice') }}</option>
                            <option value="fraud">{{ __('ui.fraud_or_impersonation') }}</option>
                            <option value="medical-data-exposure">{{ __('ui.privacy_or_medical_data_breach') }}</option>
                            <option value="animal-cruelty">{{ __('ui.animal_welfare_concern') }}</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.details') }}
                        <textarea name="details" required rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.describe_the_specific_concern') }}"></textarea>
                    </label>
                    <button type="submit" class="action action--surface action--compact w-fit"><x-ui-icon name="flag" size="sm" /><span>{{ __('ui.send_report') }}</span></button>
                </form>
            </aside>
        </div>
    </div>
</x-app-shell>
