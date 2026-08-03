<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <a href="{{ route('marketplace.index') }}" class="inline-flex w-fit items-center gap-2 text-sm font-bold text-paw-leaf">
            <x-ui-icon name="arrow-left" size="sm" />
            {{ __('ui.marketplace_c608981d8d') }}
        </a>

        @if (session('feedback'))
            <div class="market-feedback" role="status">
                <x-ui-icon name="circle-check-big" size="lg" class="shrink-0" />
                {{ session('feedback') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="form-errors" role="alert">
                <x-ui-icon name="circle-alert" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <header class="market-detail">
            <div class="market-detail__media">
                @if ($listing['cover_url'])
                    <img src="{{ $listing['cover_url'] }}" alt="{{ $listing['title'] }}">
                @else
                    <span class="market-card__placeholder" aria-hidden="true">
                        <x-ui-icon size="4xl" :name="$listing['type_icon']" />
                    </span>
                @endif
            </div>

            <div class="market-detail__summary">
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :label="$listing['type_label']" :icon="$listing['type_icon']" tone="success" />
                    <x-status-badge :label="$listing['status_label']" icon="circle-check-big" />
                    <x-status-badge :label="$listing['moderation_label']" icon="shield-check" />
                    <span class="text-xs font-bold uppercase text-paw-muted">{{ $listing['category_label'] }}</span>
                </div>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $listing['title'] }}</h1>
                @if ($listing['brand_model'])
                    <p class="mt-2 text-base font-semibold text-paw-muted">{{ $listing['brand_model'] }}</p>
                @endif
                <div class="mt-4 flex flex-wrap items-end gap-x-5 gap-y-2">
                    <strong class="text-3xl">{{ $listing['price_label'] }}</strong>
                    <span class="inline-flex items-center gap-1.5 text-sm text-paw-muted">
                        <x-ui-icon name="map-pin" size="sm" />
                        {{ $listing['location_label'] }}
                    </span>
                    <span class="text-sm font-semibold">{{ $listing['availability_label'] }} · {{ $listing['quantity'] }}</span>
                    @if ($review_summary['rating'])
                        <span class="inline-flex items-center gap-1 text-sm font-semibold">
                            <x-ui-icon name="star" size="sm" class="text-paw-leaf" />
                            {{ $review_summary['rating'] }} ({{ $review_summary['count'] }})
                        </span>
                    @endif
                </div>
                <p class="mt-5 whitespace-pre-line text-lg leading-8">{{ $listing['description'] }}</p>

                <div class="mt-6 flex flex-wrap gap-2">
                    <x-action-control
                        :label="$engagement['is_saved'] ? __('ui.saved_b5c120b316') : __('ui.save_1509f561f2')"
                        icon="bookmark"
                        active-icon="bookmark-check"
                        :active="$engagement['is_saved']"
                        :pressed="$engagement['is_saved']"
                        :endpoint="route('marketplace.actions', $listing['slug'])"
                        :payload="['action' => 'toggle-save']"
                    />
                    <x-action-control label="{{ __('ui.message_2f77668a9d') }}" icon="message-circle" :href="route('messages.index')" />
                    @if ($can_manage)
                        <x-status-badge label="{{ __('ui.your_listing_8a0799132a') }}" icon="user-round-check" tone="success" />
                    @endif
                </div>
            </div>
        </header>

        <section class="market-safety" aria-labelledby="safety-heading">
            <x-ui-icon name="shield-alert" size="xl" class="shrink-0" />
            <div>
                <h2 id="safety-heading" class="font-bold">{{ __('ui.keep_the_exchange_inside_the_platform_3c7f3ca432') }}</h2>
                <p class="mt-1 text-sm leading-6">{{ __('ui.use_the_order_status_as_proof_of_payment_ee37197aca') }}</p>
            </div>
        </section>

        @if ($listing['risk_flags'] !== [])
            <section class="border-y border-paw-line py-4" aria-labelledby="review-flags-heading">
                <h2 id="review-flags-heading" class="font-bold">{{ __('ui.safety_review_signals_269a55189a') }}</h2>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse ($listing['risk_flags'] as $flag)
                        <span class="tag">{{ $flag }}</span>
                    @empty
                        <span class="text-sm text-paw-muted">{{ __('ui.no_active_signals_0d9d086593') }}</span>
                    @endforelse
                </div>
            </section>
        @endif

        @if ($listing['type'] === 'adoption')
            <livewire:forum.adoption-workflow
                :listing-id="$listing['id']"
                wire:key="adoption-workflow-{{ $listing['id'] }}"
            />
        @endif

        <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(19rem,1fr)]">
            <div class="grid content-start gap-8">
                <section aria-labelledby="details-heading">
                    <h2 id="details-heading" class="text-2xl font-bold">{{ __('ui.listing_details_93c044cba4') }}</h2>
                    <dl class="market-facts">
                        <div><dt>{{ __('ui.condition_39b36d38d6') }}</dt><dd>{{ $listing['condition_label'] }}</dd></div>
                        <div><dt>{{ __('ui.availability_12f67f8539') }}</dt><dd>{{ $listing['availability_label'] }} · {{ $listing['quantity'] }}</dd></div>
                        <div><dt>{{ __('ui.suitable_for_9385705820') }}</dt><dd>{{ implode(', ', $listing['species_labels']) }}</dd></div>
                        <div><dt>{{ __('ui.pet_size_8458af913c') }}</dt><dd>{{ $listing['pet_size_label'] ?? __('ui.any_size_9f46b4f2f6') }}</dd></div>
                        <div><dt>{{ __('ui.age_group_b26d964d1c') }}</dt><dd>{{ $listing['age_group_label'] ?? __('ui.any_age_353d9c2d57') }}</dd></div>
                        <div><dt>{{ __('ui.material_616cb218a6') }}</dt><dd>{{ $listing['material'] ?? __('ui.not_specified_dc12bec5d7') }}</dd></div>
                        <div><dt>{{ __('ui.hygiene_542cc9c508') }}</dt><dd>{{ $listing['hygiene_status'] ?? __('ui.not_specified_dc12bec5d7') }}</dd></div>
                        <div><dt>{{ __('ui.package_59de121db1') }}</dt><dd>{{ $listing['sealed_package'] ? __('ui.sealed_bbb8650627') : __('ui.not_marked_as_sealed_bd0930149a') }}</dd></div>
                        <div class="sm:col-span-2"><dt>{{ __('ui.handover_options_8cd975d804') }}</dt><dd>{{ implode(', ', $listing['delivery_labels']) }}</dd></div>
                    </dl>

                    @if ($listing['attribute_rows'] !== [])
                        <dl class="market-attributes">
                            @forelse ($listing['attribute_rows'] as $attribute)
                                <div>
                                    <dt>{{ $attribute['label'] }}</dt>
                                    <dd>{{ $attribute['value'] }}</dd>
                                </div>
                            @empty
                                <div><dt>{{ __('ui.specifications_015b4e0a76') }}</dt><dd>{{ __('ui.not_specified_dc12bec5d7') }}</dd></div>
                            @endforelse
                        </dl>
                    @endif

                    @if ($listing['defects'])
                        <div class="mt-5 border-l-4 border-paw-coral pl-4">
                            <h3 class="font-bold">{{ __('ui.disclosed_defects_7d533ada7a') }}</h3>
                            <p class="mt-1 leading-6 text-paw-muted">{{ $listing['defects'] }}</p>
                        </div>
                    @endif

                    @if ($listing['exchange_preferences'])
                        <div class="mt-5 border-l-2 border-paw-leaf pl-4">
                            <h3 class="font-bold">{{ __('ui.exchange_preference_04a57bf45e') }}</h3>
                            <p class="mt-1 leading-6 text-paw-muted">{{ $listing['exchange_preferences'] }}</p>
                        </div>
                    @endif

                    @if ($listing['return_policy'])
                        <div class="mt-5 border-l-2 border-paw-line pl-4">
                            <h3 class="font-bold">{{ __('ui.return_or_cancellation_policy_5e1de48db8') }}</h3>
                            <p class="mt-1 leading-6 text-paw-muted">{{ $listing['return_policy'] }}</p>
                        </div>
                    @endif

                    @if ($listing['meetup_notes'])
                        <div class="mt-5 border-l-2 border-paw-line pl-4">
                            <h3 class="font-bold">{{ __('ui.safe_handover_note_554c5ee8c8') }}</h3>
                            <p class="mt-1 leading-6 text-paw-muted">{{ $listing['meetup_notes'] }}</p>
                        </div>
                    @endif

                    @if ($listing['video_url'])
                        <video controls preload="metadata" class="mt-6 aspect-video w-full rounded-md bg-black">
                            <source src="{{ $listing['video_url'] }}">
                        </video>
                    @endif
                </section>

                @if ($can_manage)
                    <section aria-labelledby="requests-heading">
                        <div class="flex flex-wrap items-end justify-between gap-2">
                            <div>
                                <h2 id="requests-heading" class="text-2xl font-bold">{{ __('ui.requests_and_orders_eb98de60c5') }}</h2>
                                <p class="mt-1 text-sm text-paw-muted">{{ __('ui.inventory_is_reserved_when_a_request_is_accepted_6093d14a89') }}</p>
                            </div>
                            <span class="text-sm font-semibold">{{ __('presentation.active_count', ['count' => count($reservations)]) }}</span>
                        </div>

                        <div class="mt-4 grid gap-4">
                            @forelse ($reservations as $reservation)
                                <article class="border-b border-paw-line pb-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <h3 class="font-bold">{{ $reservation['requester_name'] }}</h3>
                                            <p class="mt-1 text-xs text-paw-muted">{{ $reservation['request_kind'] }} · {{ $reservation['quantity'] }} · {{ $reservation['exchange_method'] }}</p>
                                        </div>
                                        <x-status-badge :label="$reservation['status_label']" icon="clock-3" />
                                    </div>
                                    <p class="mt-3 leading-6">{{ $reservation['message'] }}</p>
                                    @if ($reservation['offered_price'])
                                        <p class="mt-2 text-sm font-semibold">{{ __('presentation.offer', ['offer' => $reservation['offered_price']]) }}</p>
                                    @endif
                                    @if ($reservation['rental_starts_at'])
                                        <p class="mt-2 text-sm font-semibold">{{ __('presentation.rental_period', ['starts' => $reservation['rental_starts_at'], 'ends' => $reservation['rental_ends_at']]) }}</p>
                                    @endif
                                    @if ($reservation['questionnaire_rows'] !== [])
                                        <dl class="market-attributes mt-3">
                                            @forelse ($reservation['questionnaire_rows'] as $answer)
                                                <div><dt>{{ $answer['label'] }}</dt><dd>{{ $answer['value'] }}</dd></div>
                                            @empty
                                                <div><dt>{{ __('ui.application_e7ad522ea3') }}</dt><dd>{{ __('ui.no_additional_answers_1b4af57c1c') }}</dd></div>
                                            @endforelse
                                        </dl>
                                    @endif
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if ($reservation['status'] === 'requested')
                                            <x-action-control
                                                label="{{ __('ui.accept_89713b9c9c') }}"
                                                icon="check"
                                                variant="primary"
                                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                                :payload="['action' => 'accept-request', 'reservation_id' => $reservation['id']]"
                                            />
                                            <x-action-control
                                                label="{{ __('ui.decline_a2d285b352') }}"
                                                icon="x"
                                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                                :payload="['action' => 'decline-request', 'reservation_id' => $reservation['id']]"
                                            />
                                        @elseif ($reservation['status'] === 'accepted')
                                            @if ($reservation['order'])
                                                <a href="{{ $reservation['order']['url'] }}" class="action action--surface">
                                                    <x-ui-icon name="receipt-text" />
                                                    <span>{{ $reservation['order']['reference'] }} · {{ $reservation['order']['payment_status'] }}</span>
                                                </a>
                                            @endif
                                            <x-action-control
                                                label="{{ __('ui.mark_complete_6470f48e43') }}"
                                                icon="badge-check"
                                                variant="primary"
                                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                                :payload="['action' => 'mark-complete', 'reservation_id' => $reservation['id']]"
                                            />
                                        @elseif ($reservation['order'])
                                            <a href="{{ $reservation['order']['url'] }}" class="action action--surface">
                                                <x-ui-icon name="receipt-text" />
                                                <span>{{ __('presentation.open_reference', ['reference' => $reservation['order']['reference']]) }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="border-y border-paw-line py-6 text-paw-muted">{{ __('ui.no_active_requests_yet_4581541434') }}</div>
                            @endforelse
                        </div>
                    </section>
                @endif

                <section aria-labelledby="reviews-heading">
                    <div class="flex items-end justify-between gap-3">
                        <h2 id="reviews-heading" class="text-2xl font-bold">{{ __('ui.verified_reviews_dd3744117b') }}</h2>
                        <span class="text-sm font-semibold">{{ $review_summary['count'] }}</span>
                    </div>
                    <div class="mt-4 grid gap-4">
                        @forelse ($reviews as $review)
                            <article class="border-b border-paw-line pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <strong>{{ $review['reviewer_name'] }}</strong>
                                    @if ($review['verified'])
                                        <x-status-badge label="{{ __('ui.verified_order_8fc02a0ab6') }}" icon="badge-check" tone="success" />
                                    @endif
                                    <span class="text-sm font-semibold">{{ $review['item_rating'] }}/5</span>
                                    <span class="text-xs text-paw-muted">{{ $review['created_label'] }}</span>
                                </div>
                                <p class="mt-2 leading-6">{{ $review['body'] }}</p>
                                @if ($review['seller_reply'])
                                    <p class="mt-3 border-l-2 border-paw-line pl-3 text-sm"><strong>{{ __('ui.seller_1666298837') }}</strong> {{ $review['seller_reply'] }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="border-y border-paw-line py-5 text-paw-muted">{{ __('ui.no_completed_order_reviews_yet_952be6e6d5') }}</p>
                        @endforelse
                    </div>
                </section>

                @if ($related !== [])
                    <section aria-labelledby="related-heading">
                        <h2 id="related-heading" class="text-2xl font-bold">{{ __('ui.similar_listings_146de236d3') }}</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @forelse ($related as $relatedListing)
                                <x-listing-card :listing="$relatedListing" />
                            @empty
                                <p class="text-paw-muted">{{ __('ui.no_related_listings_are_available_c649769945') }}</p>
                            @endforelse
                        </div>
                    </section>
                @endif
            </div>

            <aside class="grid content-start gap-7">
                <section class="border-y border-paw-line py-5" aria-labelledby="owner-heading">
                    <h2 id="owner-heading" class="text-xl font-bold">{{ __('ui.listed_by_ca36963186') }}</h2>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-full bg-paw-mint font-bold text-paw-leaf" aria-hidden="true">{{ $listing['owner_initials'] }}</span>
                        <div>
                            <p class="flex items-center gap-1 font-bold">
                                {{ $listing['business_name'] ?? $listing['owner_name'] }}
                                @if ($listing['seller_verified'])
                                    <x-ui-icon name="badge-check" size="sm" class="text-paw-leaf" label="{{ __('ui.verified_seller_8988c729d5') }}" />
                                @endif
                            </p>
                            <p class="text-sm text-paw-muted">{{ $listing['seller_type_label'] }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 grid gap-2 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">{{ __('ui.contact_2b5c3d2672') }}</dt><dd class="font-semibold">{{ __('ui.platform_messages_eabb104a7f') }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">{{ __('ui.safety_status_dfa7981607') }}</dt><dd class="font-semibold">{{ $listing['safety_status'] }}</dd></div>
                    </dl>
                </section>

                @if ($can_request && $listing['type'] !== 'adoption')
                    <form method="POST" action="{{ route('marketplace.actions', $listing['slug']) }}" class="grid gap-3" aria-labelledby="request-heading">
                        @csrf
                        <input type="hidden" name="action" value="request">
                        <input type="hidden" name="idempotency_key" value="{{ $idempotency_key }}">
                        <h2 id="request-heading" class="text-xl font-bold">{{ $listing['request_label'] }}</h2>
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.quantity_822bab8d41') }}
                            <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="{{ $listing['quantity'] }}" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        </label>
                        @if (in_array($listing['type'], ['sale', 'exchange'], true))
                            <label class="grid gap-1 text-sm font-semibold">
                                {{ __('ui.price_offer_optional_a4745d979d') }}
                                <input type="number" name="offered_price" value="{{ old('offered_price') }}" min="0" step="0.01" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            </label>
                        @endif
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.handover_c012b47252') }}
                            <select name="exchange_method" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                @forelse ($delivery_options as $value => $label)
                                    @if (in_array($value, $listing['delivery_options'], true))
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endif
                                @empty
                                    <option disabled>{{ __('ui.no_options_available_be06137346') }}</option>
                                @endforelse
                            </select>
                        </label>
                        @if ($listing['type'] === 'rental')
                            <div class="grid grid-cols-2 gap-3">
                                <label class="grid gap-1 text-sm font-semibold">{{ __('ui.start_e4bb9f1ece') }}
                                    <input type="date" name="rental_starts_at" value="{{ old('rental_starts_at') }}" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                </label>
                                <label class="grid gap-1 text-sm font-semibold">{{ __('ui.end_f4db1e4847') }}
                                    <input type="date" name="rental_ends_at" value="{{ old('rental_ends_at') }}" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                </label>
                            </div>
                        @endif
                        @if ($listing['type'] === 'adoption')
                            @forelse ([
                                'experience' => __('ui.experience_with_animals_f0ded6ca40'),
                                'home_context' => __('ui.home_and_household_cb84a5ff55'),
                                'other_pets' => __('ui.other_pets_4e092d9834'),
                                'care_plan' => __('ui.daily_care_plan_403c157cd0'),
                                'adoption_reason' => __('ui.why_this_animal_cee191dfe7'),
                            ] as $field => $label)
                                <label class="grid gap-1 text-sm font-semibold">
                                    {{ $label }}
                                    <textarea name="{{ $field }}" rows="3" maxlength="1500" @if ($field !== 'other_pets') required @endif class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old($field) }}</textarea>
                                </label>
                            @empty
                                <p class="text-sm text-paw-muted">{{ __('ui.no_application_fields_5e10c3022f') }}</p>
                            @endforelse
                        @endif
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.message_2f77668a9d') }}
                            <textarea name="message" required minlength="10" maxlength="1500" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('message') }}</textarea>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.suggested_time_2ae5e73a32') }}
                            <input type="datetime-local" name="proposed_at" value="{{ old('proposed_at') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        </label>
                        <label class="flex gap-2 text-sm leading-5">
                            <input type="checkbox" name="terms_accepted" value="1" required class="mt-0.5 size-4 rounded border-paw-line text-paw-leaf">
                            <span>{{ __('ui.i_accept_the_displayed_price_condition_handover_and_a718dea173') }}</span>
                        </label>
                        <label class="flex gap-2 text-sm leading-5">
                            <input type="checkbox" name="privacy_accepted" value="1" required class="mt-0.5 size-4 rounded border-paw-line text-paw-leaf">
                            <span>{{ __('ui.i_will_share_only_the_data_required_for_f3a9838797') }}</span>
                        </label>
                        <button type="submit" class="action action--primary w-full">
                            <x-ui-icon name="send" />
                            <span>{{ $listing['request_label'] }}</span>
                        </button>
                    </form>
                @elseif ($my_reservation)
                    <section aria-labelledby="my-request-heading">
                        <h2 id="my-request-heading" class="text-xl font-bold">{{ __('ui.your_request_73023a3991') }}</h2>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <x-status-badge :label="$my_reservation['status_label']" icon="clock-3" />
                            <span class="text-xs text-paw-muted">{{ $my_reservation['created_label'] }}</span>
                        </div>
                        <p class="mt-3 text-sm leading-6">{{ $my_reservation['message'] }}</p>
                        @if ($my_reservation['order'])
                            <a href="{{ $my_reservation['order']['url'] }}" class="action action--primary mt-4 w-full">
                                <x-ui-icon name="receipt-text" />
                                <span>{{ $my_reservation['order']['reference'] }}</span>
                            </a>
                        @endif
                        @if (in_array($my_reservation['status'], ['requested', 'accepted'], true))
                            <x-action-control
                                class="mt-3 w-full"
                                label="{{ __('ui.cancel_request_5619668359') }}"
                                icon="x-circle"
                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                :payload="['action' => 'cancel-request', 'reservation_id' => $my_reservation['id']]"
                            />
                        @endif
                    </section>
                @elseif (! $can_manage)
                    <section class="border-y border-paw-line py-5">
                        <h2 class="text-xl font-bold">{{ __('ui.not_currently_available_77fd067041') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('ui.this_listing_is_reserved_completed_under_review_or_c2813a73df') }}</p>
                    </section>
                @endif

                <form method="POST" action="{{ route('marketplace.actions', $listing['slug']) }}" class="grid gap-2 border-t border-paw-line pt-5">
                    @csrf
                    <input type="hidden" name="action" value="report">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.report_a_concern_72b4b63a84') }}
                        <select name="reason" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($report_reasons as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_report_reasons_2499544500') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <textarea name="details" rows="3" maxlength="2000" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.describe_the_specific_issue_b065c1f092') }}"></textarea>
                    <label class="flex min-h-11 items-center gap-2 text-sm leading-5">
                        <input type="checkbox" name="truthfulness_confirmed" value="1" required class="size-5 shrink-0 rounded border-paw-line text-paw-leaf">
                        <span>{{ __('forum_moderation.forms.truthfulness') }}</span>
                    </label>
                    <label class="flex min-h-11 items-center gap-2 text-sm leading-5">
                        <input type="checkbox" name="immediate_safety" value="1" class="size-5 shrink-0 rounded border-paw-line text-paw-leaf">
                        <span>{{ __('forum_moderation.forms.immediate_safety') }}</span>
                    </label>
                    <button type="submit" class="action action--surface action--compact w-fit">
                        <x-ui-icon name="flag" size="sm" />
                        <span>{{ __('ui.send_report_a44d353113') }}</span>
                    </button>
                </form>
            </aside>
        </div>
    </div>
</x-app-shell>
