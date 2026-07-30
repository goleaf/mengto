<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-8">
        <a href="{{ route('experts.index') }}" class="inline-flex w-fit items-center gap-2 text-sm font-bold text-paw-leaf">
            <x-lucide-arrow-left class="size-4" aria-hidden="true" />
            Expert directory
        </a>

        <header class="grid gap-6 border-b border-paw-line pb-8 lg:grid-cols-[1fr_auto] lg:items-start">
            <div class="flex flex-col gap-5 sm:flex-row">
                @if ($expert['avatar_url'])
                    <img src="{{ $expert['avatar_url'] }}" alt="" class="size-28 shrink-0 rounded-full object-cover">
                @else
                    <span class="grid size-28 shrink-0 place-items-center rounded-full bg-paw-mint text-2xl font-bold text-paw-leaf" aria-hidden="true">{{ $expert['initials'] }}</span>
                @endif
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-3xl font-bold sm:text-4xl">{{ $expert['name'] }}</h1>
                        <x-status-badge
                            :label="$expert['qualification_verified'] ? 'Qualification verified' : $expert['verification']"
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
                            <span class="text-sm text-paw-muted">Specialization details pending.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid min-w-60 gap-2">
                @if ($expert['accepts_new_clients'])
                    <x-action-control label="Book consultation" icon="calendar-plus" variant="primary" :href="route('experts.bookings.create', $expert['slug'])" />
                @endif
                <form method="POST" action="{{ route('experts.actions', $expert['slug']) }}" class="grid grid-cols-2 gap-2">
                    @csrf
                    <button type="submit" name="action" value="toggle-save" class="action action--surface action--compact">
                        <x-lucide-bookmark class="icon icon--sm" aria-hidden="true" />
                        <span>{{ $engagement['is_saved'] ? 'Saved' : 'Save' }}</span>
                    </button>
                    <button type="submit" name="action" value="toggle-subscribe" class="action action--surface action--compact">
                        <x-lucide-bell class="icon icon--sm" aria-hidden="true" />
                        <span>{{ $engagement['is_subscribed'] ? 'Following' : 'Follow' }}</span>
                    </button>
                </form>
                @if ($can_manage)
                    <x-action-control label="Edit profile" icon="pencil" :href="route('experts.edit', $expert['slug'])" />
                @else
                    <x-action-control label="Send a message request" icon="message-circle" :href="url('/messages')" />
                @endif
            </div>
        </header>

        @unless ($expert['offers_emergency_care'])
            <section class="flex gap-3 border-l-4 border-red-500 bg-red-50 p-4 text-red-950" aria-label="Emergency boundary">
                <x-lucide-siren class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
                <div>
                    <h2 class="font-bold">This profile is not an emergency service</h2>
                    <p class="mt-1 text-sm">For breathing trouble, seizures, poisoning, severe bleeding, collapse, or major trauma, call a local emergency clinic instead of waiting for a message or planned consultation.</p>
                </div>
            </section>
        @endunless

        <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <div class="grid gap-8">
                <section aria-labelledby="about-expert">
                    <h2 id="about-expert" class="text-2xl font-bold">Professional scope</h2>
                    <p class="mt-3 whitespace-pre-line leading-7">{{ $expert['bio'] }}</p>
                    <dl class="mt-5 grid gap-4 border-y border-paw-line py-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">Approach</dt>
                            <dd class="mt-1 leading-6">{{ $expert['approach'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">Boundaries</dt>
                            <dd class="mt-1 leading-6">{{ $expert['boundaries'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">Experience</dt>
                            <dd class="mt-1">{{ $expert['years_experience'] }} years</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-bold text-paw-muted">Service area</dt>
                            <dd class="mt-1">{{ $expert['service_area'] ?? $expert['city'] }}</dd>
                        </div>
                    </dl>
                </section>

                <section aria-labelledby="services-heading">
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <h2 id="services-heading" class="text-2xl font-bold">Services and prices</h2>
                            <p class="mt-1 text-sm text-paw-muted">The booking flow confirms the current price and cancellation policy before submission.</p>
                        </div>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        @forelse ($services as $service)
                            <x-service-card :service="$service" :expert-slug="$expert['slug']" />
                        @empty
                            <p class="text-paw-muted">No active services are published.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="content-heading">
                    <h2 id="content-heading" class="text-2xl font-bold">Professional materials</h2>
                    <div class="mt-4 grid gap-4">
                        @forelse ($publications as $publication)
                            <article class="border-b border-paw-line pb-4">
                                <div class="flex flex-wrap items-center gap-2 text-xs font-bold uppercase text-paw-muted">
                                    <span>{{ $publication['type'] }}</span>
                                    <span aria-hidden="true">·</span>
                                    <span>{{ $publication['category'] }}</span>
                                    @if ($publication['reviewed'])
                                        <span aria-hidden="true">·</span>
                                        <span>Reviewed {{ $publication['reviewed'] }}</span>
                                    @endif
                                </div>
                                <h3 class="mt-2 text-lg font-bold">{{ $publication['title'] }}</h3>
                                <p class="mt-2 leading-6 text-paw-muted">{{ $publication['summary'] }}</p>
                                @if ($publication['conflict_disclosure'])
                                    <p class="mt-2 text-xs text-paw-muted">Disclosure: {{ $publication['conflict_disclosure'] }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="text-paw-muted">No professional materials published yet.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="forum-heading">
                    <h2 id="forum-heading" class="text-2xl font-bold">Forum contributions</h2>
                    <div class="mt-4 grid gap-3">
                        @forelse ($forum_answers as $answer)
                            <article class="border-l-2 border-paw-leaf pl-4">
                                <h3 class="font-bold"><a href="{{ url('/forum/topics/'.$answer['topic_slug']) }}" class="hover:text-paw-leaf">{{ $answer['topic_title'] }}</a></h3>
                                <p class="mt-1 text-sm leading-6 text-paw-muted">{{ $answer['excerpt'] }}</p>
                                <p class="mt-2 text-xs font-semibold text-paw-muted">{{ $answer['helpful_count'] }} helpful · {{ $answer['created_label'] }}</p>
                            </article>
                        @empty
                            <p class="text-paw-muted">No published forum answers are linked to this profile.</p>
                        @endforelse
                    </div>
                </section>

                <section aria-labelledby="reviews-heading">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 id="reviews-heading" class="text-2xl font-bold">Client reviews</h2>
                            <p class="mt-1 text-sm text-paw-muted">Verified means a completed service was recorded. It does not verify every statement in the review.</p>
                        </div>
                        <strong>{{ $expert['review_count'] > 0 ? $expert['rating'].' / 5' : 'New profile' }}</strong>
                    </div>
                    <div class="mt-4 grid gap-4">
                        @forelse ($reviews as $review)
                            <article class="border-b border-paw-line pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold">{{ $review['reviewer_name'] }}</h3>
                                    @if ($review['is_verified_client'])
                                        <x-status-badge label="Verified client" icon="badge-check" tone="success" />
                                    @endif
                                    <span class="text-sm font-bold">{{ $review['rating'] }} / 5</span>
                                </div>
                                <p class="mt-2 leading-6">{{ $review['body'] }}</p>
                                <p class="mt-2 text-xs text-paw-muted">{{ $review['created_label'] }}</p>
                                @if ($review['expert_reply'])
                                    <div class="mt-3 border-l-2 border-paw-line pl-3 text-sm">
                                        <strong>Professional reply</strong>
                                        <p class="mt-1">{{ $review['expert_reply'] }}</p>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="text-paw-muted">No published reviews yet.</p>
                        @endforelse
                    </div>

                    @if ($reviewable_bookings !== [])
                        <form method="POST" action="{{ route('experts.reviews.store', $expert['slug']) }}" class="mt-6 grid gap-3 border-t border-paw-line pt-5">
                            @csrf
                            <h3 class="text-lg font-bold">Review a completed service</h3>
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <label class="grid gap-1 text-sm font-semibold">
                                    Completed booking
                                    <select name="booking_id" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                        @forelse ($reviewable_bookings as $booking)
                                            <option value="{{ $booking['id'] }}">{{ $booking['label'] }}</option>
                                        @empty
                                            <option disabled>No completed bookings</option>
                                        @endforelse
                                    </select>
                                </label>
                                <label class="grid gap-1 text-sm font-semibold">
                                    Overall rating
                                    <select name="rating" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                        @for ($rating = 5; $rating >= 1; $rating--)
                                            <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                        @endfor
                                    </select>
                                </label>
                                @foreach ([
                                    'communication_rating' => 'Communication',
                                    'clarity_rating' => 'Clarity',
                                    'organization_rating' => 'Organization',
                                    'price_transparency_rating' => 'Price transparency',
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
                                Review
                                <textarea name="body" required rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Describe the service, communication, organization, and price transparency."></textarea>
                            </label>
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_anonymous" value="1"> Show publicly as a verified client</label>
                            <button class="action action--primary action--compact w-fit" type="submit"><x-lucide-send class="icon icon--sm" aria-hidden="true" /><span>Publish review</span></button>
                        </form>
                    @endif
                </section>
            </div>

            <aside class="grid content-start gap-7">
                <section aria-labelledby="verification-heading">
                    <h2 id="verification-heading" class="text-xl font-bold">What was checked</h2>
                    <p class="mt-1 text-sm text-paw-muted">Identity, education, qualification, license, workplace, organization, and contact are separate checks.</p>
                    <div class="mt-4">
                        <x-verification-list :items="$expert['verification_items']" :expires="$expert['verification_expires']" />
                    </div>
                </section>

                <section class="border-y border-paw-line py-5" aria-labelledby="availability-heading">
                    <h2 id="availability-heading" class="text-xl font-bold">Availability</h2>
                    <dl class="mt-3 grid gap-3 text-sm">
                        <div><dt class="text-paw-muted">Status</dt><dd class="font-semibold">{{ $expert['availability_status'] }}</dd></div>
                        <div><dt class="text-paw-muted">Next opening</dt><dd class="font-semibold">{{ $expert['next_available'] ?? 'By request' }}</dd></div>
                        <div><dt class="text-paw-muted">Typical response</dt><dd class="font-semibold">{{ $expert['response_time'] ?? 'Not stated' }}</dd></div>
                        <div><dt class="text-paw-muted">Formats</dt><dd class="font-semibold">{{ implode(', ', $expert['formats']) }}</dd></div>
                        <div><dt class="text-paw-muted">Languages</dt><dd class="font-semibold">{{ implode(', ', $expert['languages']) }}</dd></div>
                    </dl>
                </section>

                <section aria-labelledby="species-heading">
                    <h2 id="species-heading" class="text-xl font-bold">Works with</h2>
                    <p class="mt-2 text-sm leading-6">{{ implode(', ', $expert['species']) }}</p>
                    @if ($expert['age_groups'] !== [])
                        <p class="mt-2 text-sm text-paw-muted">Age groups: {{ implode(', ', $expert['age_groups']) }}</p>
                    @endif
                </section>

                <section aria-labelledby="credentials-heading">
                    <h2 id="credentials-heading" class="text-xl font-bold">Credential register</h2>
                    <div class="mt-3 grid gap-3">
                        @forelse ($credentials as $credential)
                            <article class="border-b border-paw-line pb-3 text-sm">
                                <h3 class="font-bold">{{ $credential['title'] }}</h3>
                                <p class="mt-1 text-paw-muted">{{ $credential['issuer'] }}{{ $credential['region'] ? ' · '.$credential['region'] : '' }}</p>
                                <p class="mt-1">{{ $credential['status'] }}{{ $credential['masked_number'] ? ' · '.$credential['masked_number'] : '' }}</p>
                                @if ($credential['expires_at'])
                                    <p class="mt-1 text-xs text-paw-muted">Expires {{ $credential['expires_at'] }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No public credential summary is available.</p>
                        @endforelse
                    </div>
                </section>

                <form method="POST" action="{{ route('experts.actions', $expert['slug']) }}" class="grid gap-2 border-t border-paw-line pt-5">
                    @csrf
                    <input type="hidden" name="action" value="report">
                    <label class="grid gap-1 text-sm font-semibold">
                        Report a professional concern
                        <select name="reason" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="false-qualification">False qualification</option>
                            <option value="dangerous-advice">Dangerous advice</option>
                            <option value="fraud">Fraud or impersonation</option>
                            <option value="medical-data-exposure">Privacy or medical data breach</option>
                            <option value="animal-cruelty">Animal welfare concern</option>
                        </select>
                    </label>
                    <textarea name="details" required rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Describe the specific concern."></textarea>
                    <button type="submit" class="action action--surface action--compact w-fit"><x-lucide-flag class="icon icon--sm" aria-hidden="true" /><span>Send report</span></button>
                </form>
            </aside>
        </div>
    </div>
</x-app-shell>
