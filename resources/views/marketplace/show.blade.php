<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <a href="{{ route('marketplace.index') }}" class="inline-flex w-fit items-center gap-2 text-sm font-bold text-paw-leaf">
            <x-lucide-arrow-left class="size-4" aria-hidden="true" />
            Marketplace
        </a>

        <header class="market-detail">
            <div class="market-detail__media">
                @if ($listing['cover_url'])
                    <img src="{{ $listing['cover_url'] }}" alt="">
                @else
                    <span class="market-card__placeholder" aria-hidden="true">
                        <x-dynamic-component :component="'lucide-'.$listing['type_icon']" class="size-12" />
                    </span>
                @endif
            </div>

            <div class="market-detail__summary">
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :label="$listing['type_label']" :icon="$listing['type_icon']" tone="success" />
                    <x-status-badge :label="$listing['status_label']" icon="circle-check-big" />
                    <span class="text-xs font-bold uppercase text-paw-muted">{{ $listing['category_label'] }}</span>
                </div>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $listing['title'] }}</h1>
                <div class="mt-4 flex flex-wrap items-end gap-x-5 gap-y-2">
                    <strong class="text-3xl">{{ $listing['price_label'] }}</strong>
                    <span class="inline-flex items-center gap-1.5 text-sm text-paw-muted">
                        <x-lucide-map-pin class="size-4" aria-hidden="true" />
                        {{ $listing['location_label'] }}
                    </span>
                    <span class="text-sm text-paw-muted">{{ $listing['view_count'] }} views</span>
                </div>
                <p class="mt-5 whitespace-pre-line text-lg leading-8">{{ $listing['description'] }}</p>

                <div class="mt-6 flex flex-wrap gap-2">
                    <x-action-control
                        :label="$engagement['is_saved'] ? 'Saved' : 'Save'"
                        icon="bookmark"
                        active-icon="bookmark-check"
                        :active="$engagement['is_saved']"
                        :pressed="$engagement['is_saved']"
                        :endpoint="route('marketplace.actions', $listing['slug'])"
                        :payload="['action' => 'toggle-save']"
                    />
                    <x-action-control label="Message safely" icon="message-circle" :href="route('messages.index')" />
                    @if ($can_manage)
                        <x-status-badge label="Your listing" icon="user-round-check" tone="success" />
                    @endif
                </div>
            </div>
        </header>

        <section class="market-safety" aria-labelledby="safety-heading">
            <x-lucide-shield-alert class="size-6 shrink-0" aria-hidden="true" />
            <div>
                <h2 id="safety-heading" class="font-bold">Keep the exchange inside the platform</h2>
                <p class="mt-1 text-sm leading-6">
                    Never share verification codes or card details in chat. Inspect items before payment, meet in a public place, and do not reveal your exact home address before a request is accepted.
                </p>
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(19rem,1fr)]">
            <div class="grid content-start gap-8">
                <section aria-labelledby="details-heading">
                    <h2 id="details-heading" class="text-2xl font-bold">Listing details</h2>
                    <dl class="market-facts">
                        <div><dt>Condition</dt><dd>{{ $listing['condition_label'] }}</dd></div>
                        <div><dt>Suitable for</dt><dd>{{ implode(', ', $listing['species_labels']) }}</dd></div>
                        <div><dt>Pet size</dt><dd>{{ $listing['pet_size_label'] ?? 'Any size' }}</dd></div>
                        <div><dt>Published</dt><dd>{{ $listing['published_at'] ?? 'Not published' }}</dd></div>
                        <div class="sm:col-span-2"><dt>Handover options</dt><dd>{{ implode(', ', $listing['delivery_labels']) }}</dd></div>
                    </dl>

                    @if ($listing['exchange_preferences'])
                        <div class="mt-5 border-l-2 border-paw-leaf pl-4">
                            <h3 class="font-bold">Exchange preference</h3>
                            <p class="mt-1 leading-6 text-paw-muted">{{ $listing['exchange_preferences'] }}</p>
                        </div>
                    @endif

                    @if ($listing['meetup_notes'])
                        <div class="mt-5 border-l-2 border-paw-line pl-4">
                            <h3 class="font-bold">Safe handover note</h3>
                            <p class="mt-1 leading-6 text-paw-muted">{{ $listing['meetup_notes'] }}</p>
                        </div>
                    @endif
                </section>

                @if ($can_manage)
                    <section aria-labelledby="requests-heading">
                        <div class="flex flex-wrap items-end justify-between gap-2">
                            <div>
                                <h2 id="requests-heading" class="text-2xl font-bold">Requests</h2>
                                <p class="mt-1 text-sm text-paw-muted">Accept one person only after the item, service, or adoption process is confirmed.</p>
                            </div>
                            <span class="text-sm font-semibold">{{ count($reservations) }} active</span>
                        </div>

                        <div class="mt-4 grid gap-4">
                            @forelse ($reservations as $reservation)
                                <article class="border-b border-paw-line pb-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div>
                                            <h3 class="font-bold">{{ $reservation['requester_name'] }}</h3>
                                            <p class="mt-1 text-xs text-paw-muted">{{ $reservation['created_label'] }} · {{ $reservation['exchange_method'] }}</p>
                                        </div>
                                        <x-status-badge :label="$reservation['status_label']" icon="clock-3" />
                                    </div>
                                    <p class="mt-3 leading-6">{{ $reservation['message'] }}</p>
                                    @if ($reservation['proposed_at'])
                                        <p class="mt-2 text-sm font-semibold">Suggested: {{ $reservation['proposed_at'] }}</p>
                                    @endif
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @if ($reservation['status'] === 'requested')
                                            <x-action-control
                                                label="Accept"
                                                icon="check"
                                                variant="primary"
                                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                                :payload="['action' => 'accept-request', 'reservation_id' => $reservation['id']]"
                                            />
                                            <x-action-control
                                                label="Decline"
                                                icon="x"
                                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                                :payload="['action' => 'decline-request', 'reservation_id' => $reservation['id']]"
                                            />
                                        @elseif ($reservation['status'] === 'accepted')
                                            <x-action-control
                                                label="Mark complete"
                                                icon="badge-check"
                                                variant="primary"
                                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                                :payload="['action' => 'mark-complete', 'reservation_id' => $reservation['id']]"
                                            />
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="border-y border-paw-line py-6 text-paw-muted">No active requests yet.</div>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if ($related !== [])
                    <section aria-labelledby="related-heading">
                        <h2 id="related-heading" class="text-2xl font-bold">Similar listings</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @forelse ($related as $relatedListing)
                                <x-listing-card :listing="$relatedListing" />
                            @empty
                                <p class="text-paw-muted">No related listings are available.</p>
                            @endforelse
                        </div>
                    </section>
                @endif
            </div>

            <aside class="grid content-start gap-7">
                <section class="border-y border-paw-line py-5" aria-labelledby="owner-heading">
                    <h2 id="owner-heading" class="text-xl font-bold">Listed by</h2>
                    <div class="mt-4 flex items-center gap-3">
                        <span class="grid size-11 place-items-center rounded-full bg-paw-mint font-bold text-paw-leaf" aria-hidden="true">{{ $listing['owner_initials'] }}</span>
                        <div>
                            <p class="font-bold">{{ $listing['business_name'] ?? $listing['owner_name'] }}</p>
                            <p class="text-sm text-paw-muted">{{ $listing['business_name'] ? 'Business listing' : 'Community member' }}</p>
                        </div>
                    </div>
                    <dl class="mt-4 grid gap-2 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">Contact</dt><dd class="font-semibold">Platform messages</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">Safety status</dt><dd class="font-semibold">{{ $listing['safety_status'] }}</dd></div>
                    </dl>
                </section>

                @if ($can_request)
                    <form method="POST" action="{{ route('marketplace.actions', $listing['slug']) }}" class="grid gap-3" aria-labelledby="request-heading">
                        @csrf
                        <input type="hidden" name="action" value="request">
                        <input type="hidden" name="idempotency_key" value="{{ $idempotency_key }}">
                        <h2 id="request-heading" class="text-xl font-bold">Request this listing</h2>
                        <p class="text-sm leading-6 text-paw-muted">The owner sees your message, not your phone number or exact address.</p>
                        <label class="grid gap-1 text-sm font-semibold">
                            Handover
                            <select name="exchange_method" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                @forelse ($delivery_options as $value => $label)
                                    @if (in_array($value, $listing['delivery_options'], true))
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endif
                                @empty
                                    <option disabled>No options available</option>
                                @endforelse
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold">
                            Message
                            <textarea name="message" required minlength="10" maxlength="1500" rows="5" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Ask a specific question and suggest a safe handover.">{{ old('message') }}</textarea>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold">
                            Suggested time, optional
                            <input type="datetime-local" name="proposed_at" value="{{ old('proposed_at') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        </label>
                        <button type="submit" class="action action--primary w-full">
                            <x-lucide-send class="icon" aria-hidden="true" />
                            <span>Send request</span>
                        </button>
                    </form>
                @elseif ($my_reservation)
                    <section aria-labelledby="my-request-heading">
                        <h2 id="my-request-heading" class="text-xl font-bold">Your request</h2>
                        <div class="mt-3 flex items-center justify-between gap-3">
                            <x-status-badge :label="$my_reservation['status_label']" icon="clock-3" />
                            <span class="text-xs text-paw-muted">{{ $my_reservation['created_label'] }}</span>
                        </div>
                        <p class="mt-3 text-sm leading-6">{{ $my_reservation['message'] }}</p>
                        @if (in_array($my_reservation['status'], ['requested', 'accepted'], true))
                            <x-action-control
                                class="mt-4 w-full"
                                label="Cancel request"
                                icon="x-circle"
                                :endpoint="route('marketplace.actions', $listing['slug'])"
                                :payload="['action' => 'cancel-request', 'reservation_id' => $my_reservation['id']]"
                            />
                        @endif
                    </section>
                @elseif (! $can_manage)
                    <section class="border-y border-paw-line py-5">
                        <h2 class="text-xl font-bold">Not currently available</h2>
                        <p class="mt-2 text-sm leading-6 text-paw-muted">This listing is reserved, completed, or no longer accepting requests.</p>
                    </section>
                @endif

                <form method="POST" action="{{ route('marketplace.actions', $listing['slug']) }}" class="grid gap-2 border-t border-paw-line pt-5">
                    @csrf
                    <input type="hidden" name="action" value="report">
                    <label class="grid gap-1 text-sm font-semibold">
                        Report a concern
                        <select name="reason" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($report_reasons as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @empty
                                <option disabled>No report reasons</option>
                            @endforelse
                        </select>
                    </label>
                    <textarea name="details" rows="3" maxlength="2000" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Describe the specific issue."></textarea>
                    <button type="submit" class="action action--surface action--compact w-fit">
                        <x-lucide-flag class="icon icon--sm" aria-hidden="true" />
                        <span>Send report</span>
                    </button>
                </form>
            </aside>
        </div>
    </div>
</x-app-shell>
