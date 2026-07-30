<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-7">
        <a href="{{ route('marketplace.show', $listing['slug']) }}" class="inline-flex w-fit items-center gap-2 text-sm font-bold text-paw-leaf">
            <x-lucide-arrow-left class="size-4" aria-hidden="true" />
            {{ $listing['title'] }}
        </a>

        @if (session('feedback'))
            <div class="market-feedback" role="status">
                <x-lucide-circle-check-big class="size-5 shrink-0" aria-hidden="true" />
                {{ session('feedback') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="form-errors" role="alert">
                <x-lucide-circle-alert class="icon" aria-hidden="true" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <header class="flex flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase text-paw-leaf">{{ $order['kind'] }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $order['reference'] }}</h1>
                <p class="mt-2 text-paw-muted">{{ $order['ordered_at'] }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-status-badge :label="$order['status_label']" icon="package-check" />
                <x-status-badge :label="$order['payment_label']" icon="shield-check" tone="success" />
            </div>
        </header>

        <section class="market-safety">
            <x-lucide-lock-keyhole class="size-6 shrink-0" aria-hidden="true" />
            <div>
                <h2 class="font-bold">{{ __('ui.terms_captured_at_acceptance_baa049fd91') }}</h2>
                <p class="mt-1 text-sm leading-6">{{ __('ui.this_page_uses_the_order_snapshot_later_edits_dc89366d76') }}</p>
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)]">
            <div class="grid content-start gap-8">
                <section aria-labelledby="item-heading">
                    <h2 id="item-heading" class="text-2xl font-bold">{{ __('ui.order_item_758d9570c2') }}</h2>
                    <div class="mt-4 grid gap-5 sm:grid-cols-[10rem_minmax(0,1fr)]">
                        <div class="aspect-square overflow-hidden rounded-md bg-paw-paper">
                            @if (data_get($order['item'], 'cover_url'))
                                <img src="{{ data_get($order['item'], 'cover_url') }}" alt="{{ data_get($order['item'], 'title') }}" class="size-full object-cover">
                            @else
                                <span class="grid size-full place-items-center text-paw-leaf" aria-hidden="true">
                                    <x-lucide-package class="size-10" />
                                </span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xl font-bold">{{ data_get($order['item'], 'title') }}</h3>
                            <p class="mt-2 leading-6 text-paw-muted">{{ data_get($order['item'], 'description') }}</p>
                            <dl class="market-attributes mt-4">
                                <div><dt>{{ __('ui.brand_and_model_ed264a49a9') }}</dt><dd>{{ $order['item_brand_model'] }}</dd></div>
                                <div><dt>{{ __('ui.condition_39b36d38d6') }}</dt><dd>{{ $order['item_condition_label'] }}</dd></div>
                                <div><dt>{{ __('ui.quantity_822bab8d41') }}</dt><dd>{{ $order['quantity'] }}</dd></div>
                                <div><dt>{{ __('ui.unit_price_e5018fc945') }}</dt><dd>{{ $order['unit_price'] }}</dd></div>
                            </dl>
                        </div>
                    </div>

                    @if (data_get($order['item'], 'defects'))
                        <div class="mt-5 border-l-4 border-paw-coral pl-4">
                            <h3 class="font-bold">{{ __('ui.disclosed_defects_7d533ada7a') }}</h3>
                            <p class="mt-1 leading-6">{{ data_get($order['item'], 'defects') }}</p>
                        </div>
                    @endif
                </section>

                <section aria-labelledby="terms-heading">
                    <h2 id="terms-heading" class="text-2xl font-bold">{{ __('ui.captured_terms_5e1ba8a7b1') }}</h2>
                    <dl class="market-attributes mt-4">
                        <div><dt>{{ __('ui.handover_c012b47252') }}</dt><dd>{{ $order['delivery_method'] }}</dd></div>
                        <div><dt>{{ __('ui.area_024dc204d7') }}</dt><dd>{{ $order['public_delivery_area'] ?: __('ui.confirmed_privately_2ee8b0cd39') }}</dd></div>
                        <div><dt>{{ __('ui.return_policy_e2c3428cb5') }}</dt><dd>{{ data_get($order['terms'], 'return_policy') ?: __('ui.no_separate_policy_provided_7f0757ee30') }}</dd></div>
                        <div><dt>{{ __('ui.rental_period_edc91fddcc') }}</dt><dd>{{ data_get($order['terms'], 'rental_starts_at') ? __('presentation.rental_period', ['starts' => data_get($order['terms'], 'rental_starts_at'), 'ends' => data_get($order['terms'], 'rental_ends_at')]) : __('ui.not_a_rental_e6d4a9ca31') }}</dd></div>
                        <div><dt>{{ __('ui.request_message_5c8c66d886') }}</dt><dd>{{ data_get($order['terms'], 'request_message') }}</dd></div>
                        <div><dt>{{ __('ui.captured_8a03fa9ad7') }}</dt><dd>{{ data_get($order['terms'], 'captured_at') }}</dd></div>
                    </dl>
                </section>

                <section aria-labelledby="disputes-heading">
                    <h2 id="disputes-heading" class="text-2xl font-bold">{{ __('ui.disputes_110fa2bb77') }}</h2>
                    <div class="mt-4 grid gap-4">
                        @forelse ($disputes as $dispute)
                            <article class="border-b border-paw-line pb-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-badge :label="$dispute['status']" icon="scale" />
                                    <strong>{{ $dispute['reason'] }}</strong>
                                    <span class="text-xs text-paw-muted">{{ $dispute['created_label'] }}</span>
                                </div>
                                <p class="mt-2 leading-6">{{ $dispute['details'] }}</p>
                                @if ($dispute['resolution'])
                                    <p class="mt-2 text-sm font-semibold">{{ __('presentation.resolution', ['resolution' => $dispute['resolution']]) }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="border-y border-paw-line py-5 text-paw-muted">{{ __('ui.no_disputes_opened_fb5abdba0d') }}</p>
                        @endforelse
                    </div>
                </section>

                @if ($review)
                    <section aria-labelledby="review-heading">
                        <h2 id="review-heading" class="text-2xl font-bold">{{ __('ui.verified_review_06614f89a3') }}</h2>
                        <article class="mt-4 border-y border-paw-line py-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <strong>{{ $review['reviewer_name'] }}</strong>
                                @if ($review['verified'])
                                    <x-status-badge label="{{ __('ui.verified_order_8fc02a0ab6') }}" icon="badge-check" tone="success" />
                                @endif
                                <span class="text-sm font-semibold">{{ __('presentation.review_ratings', ['item' => $review['item_rating'], 'seller' => $review['seller_rating']]) }}</span>
                            </div>
                            <p class="mt-3 leading-6">{{ $review['body'] }}</p>
                        </article>
                    </section>
                @endif
            </div>

            <aside class="grid content-start gap-7">
                <section class="border-y border-paw-line py-5" aria-labelledby="summary-heading">
                    <h2 id="summary-heading" class="text-xl font-bold">{{ __('ui.payment_summary_3381448b3d') }}</h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">{{ __('ui.unit_price_e5018fc945') }}</dt><dd class="font-semibold">{{ $order['unit_price'] }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">{{ __('ui.delivery_52bfe584a5') }}</dt><dd class="font-semibold">{{ $order['delivery_amount'] }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">{{ __('ui.deposit_0da00b600a') }}</dt><dd class="font-semibold">{{ $order['deposit_amount'] }}</dd></div>
                        <div class="flex justify-between gap-3 border-t border-paw-line pt-3 text-base"><dt class="font-bold">{{ __('ui.total_c9b3c38247') }}</dt><dd class="font-bold">{{ $order['total_amount'] }}</dd></div>
                    </dl>
                    <dl class="mt-5 grid gap-2 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">{{ __('ui.buyer_a2a54d668c') }}</dt><dd class="font-semibold">{{ $order['buyer_name'] }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">{{ __('ui.seller_01498fa31d') }}</dt><dd class="font-semibold">{{ $order['seller_name'] }}</dd></div>
                    </dl>
                </section>

                @if ($can_dispute)
                    <form method="POST" action="{{ route('marketplace.orders.disputes.store', [$listing['slug'], $order['reference']]) }}" class="grid gap-3" aria-labelledby="open-dispute-heading">
                        @csrf
                        <h2 id="open-dispute-heading" class="text-xl font-bold">{{ __('ui.open_a_dispute_3618e3f6b5') }}</h2>
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.reason_f81ab834de') }}
                            <select name="reason" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                @forelse ($dispute_reasons as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                    <option disabled>{{ __('ui.no_reasons_available_0ec12a640b') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.what_happened_483bd49023') }}
                            <textarea name="details" required minlength="20" maxlength="3000" rows="5" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></textarea>
                        </label>
                        <button type="submit" class="action action--surface">
                            <x-lucide-scale class="icon" aria-hidden="true" />
                            <span>{{ __('ui.open_dispute_ecda2ce6c5') }}</span>
                        </button>
                    </form>
                @endif

                @if ($can_review)
                    <form method="POST" action="{{ route('marketplace.orders.reviews.store', [$listing['slug'], $order['reference']]) }}" class="grid gap-3 border-t border-paw-line pt-5" aria-labelledby="leave-review-heading">
                        @csrf
                        <h2 id="leave-review-heading" class="text-xl font-bold">{{ __('ui.leave_a_verified_review_c403c564db') }}</h2>
                        @forelse ([
                            'item_rating' => __('ui.item_or_service_577afb1143'),
                            'seller_rating' => __('ui.seller_01498fa31d'),
                            'delivery_rating' => __('ui.delivery_52bfe584a5'),
                        ] as $field => $label)
                            <label class="grid gap-1 text-sm font-semibold">
                                {{ $label }}
                                <select name="{{ $field }}" @if ($field !== 'delivery_rating') required @endif class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                    @if ($field === 'delivery_rating')
                                        <option value="">{{ __('ui.not_rated_f5ad087112') }}</option>
                                    @endif
                                    @forelse ([5, 4, 3, 2, 1] as $rating)
                                        <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_ratings_6a2059f3e2') }}</option>
                                    @endforelse
                                </select>
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_review_criteria_21e89628e5') }}</p>
                        @endforelse
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ __('ui.review_aff0766a52') }}
                            <textarea name="body" required minlength="20" maxlength="2000" rows="5" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></textarea>
                        </label>
                        <button type="submit" class="action action--primary">
                            <x-lucide-star class="icon" aria-hidden="true" />
                            <span>{{ __('ui.publish_review_f795632a1e') }}</span>
                        </button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-app-shell>
