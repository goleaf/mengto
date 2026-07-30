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
                <h2 class="font-bold">Terms captured at acceptance</h2>
                <p class="mt-1 text-sm leading-6">This page uses the order snapshot. Later edits to the public listing do not replace these item and handover terms.</p>
            </div>
        </section>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1.6fr)_minmax(18rem,1fr)]">
            <div class="grid content-start gap-8">
                <section aria-labelledby="item-heading">
                    <h2 id="item-heading" class="text-2xl font-bold">Order item</h2>
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
                                <div><dt>Brand and model</dt><dd>{{ collect([data_get($order['item'], 'brand'), data_get($order['item'], 'model')])->filter()->join(' ') ?: 'Not specified' }}</dd></div>
                                <div><dt>Condition</dt><dd>{{ str((string) data_get($order['item'], 'condition'))->headline() }}</dd></div>
                                <div><dt>Quantity</dt><dd>{{ $order['quantity'] }}</dd></div>
                                <div><dt>Unit price</dt><dd>{{ $order['unit_price'] }}</dd></div>
                            </dl>
                        </div>
                    </div>

                    @if (data_get($order['item'], 'defects'))
                        <div class="mt-5 border-l-4 border-paw-coral pl-4">
                            <h3 class="font-bold">Disclosed defects</h3>
                            <p class="mt-1 leading-6">{{ data_get($order['item'], 'defects') }}</p>
                        </div>
                    @endif
                </section>

                <section aria-labelledby="terms-heading">
                    <h2 id="terms-heading" class="text-2xl font-bold">Captured terms</h2>
                    <dl class="market-attributes mt-4">
                        <div><dt>Handover</dt><dd>{{ $order['delivery_method'] }}</dd></div>
                        <div><dt>Area</dt><dd>{{ $order['public_delivery_area'] ?: 'Confirmed privately' }}</dd></div>
                        <div><dt>Return policy</dt><dd>{{ data_get($order['terms'], 'return_policy') ?: 'No separate policy provided' }}</dd></div>
                        <div><dt>Rental period</dt><dd>{{ data_get($order['terms'], 'rental_starts_at') ? data_get($order['terms'], 'rental_starts_at').' to '.data_get($order['terms'], 'rental_ends_at') : 'Not a rental' }}</dd></div>
                        <div><dt>Request message</dt><dd>{{ data_get($order['terms'], 'request_message') }}</dd></div>
                        <div><dt>Captured</dt><dd>{{ data_get($order['terms'], 'captured_at') }}</dd></div>
                    </dl>
                </section>

                <section aria-labelledby="disputes-heading">
                    <h2 id="disputes-heading" class="text-2xl font-bold">Disputes</h2>
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
                                    <p class="mt-2 text-sm font-semibold">Resolution: {{ $dispute['resolution'] }}</p>
                                @endif
                            </article>
                        @empty
                            <p class="border-y border-paw-line py-5 text-paw-muted">No disputes opened.</p>
                        @endforelse
                    </div>
                </section>

                @if ($review)
                    <section aria-labelledby="review-heading">
                        <h2 id="review-heading" class="text-2xl font-bold">Verified review</h2>
                        <article class="mt-4 border-y border-paw-line py-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <strong>{{ $review['reviewer_name'] }}</strong>
                                @if ($review['verified'])
                                    <x-status-badge label="Verified order" icon="badge-check" tone="success" />
                                @endif
                                <span class="text-sm font-semibold">{{ $review['item_rating'] }}/5 item · {{ $review['seller_rating'] }}/5 seller</span>
                            </div>
                            <p class="mt-3 leading-6">{{ $review['body'] }}</p>
                        </article>
                    </section>
                @endif
            </div>

            <aside class="grid content-start gap-7">
                <section class="border-y border-paw-line py-5" aria-labelledby="summary-heading">
                    <h2 id="summary-heading" class="text-xl font-bold">Payment summary</h2>
                    <dl class="mt-4 grid gap-3 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">Unit price</dt><dd class="font-semibold">{{ $order['unit_price'] }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">Delivery</dt><dd class="font-semibold">{{ $order['delivery_amount'] }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">Deposit</dt><dd class="font-semibold">{{ $order['deposit_amount'] }}</dd></div>
                        <div class="flex justify-between gap-3 border-t border-paw-line pt-3 text-base"><dt class="font-bold">Total</dt><dd class="font-bold">{{ $order['total_amount'] }}</dd></div>
                    </dl>
                    <dl class="mt-5 grid gap-2 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">Buyer</dt><dd class="font-semibold">{{ $order['buyer_name'] }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-paw-muted">Seller</dt><dd class="font-semibold">{{ $order['seller_name'] }}</dd></div>
                    </dl>
                </section>

                @if ($can_dispute)
                    <form method="POST" action="{{ route('marketplace.orders.disputes.store', [$listing['slug'], $order['reference']]) }}" class="grid gap-3" aria-labelledby="open-dispute-heading">
                        @csrf
                        <h2 id="open-dispute-heading" class="text-xl font-bold">Open a dispute</h2>
                        <label class="grid gap-1 text-sm font-semibold">
                            Reason
                            <select name="reason" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                @forelse ($dispute_reasons as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @empty
                                    <option disabled>No reasons available</option>
                                @endforelse
                            </select>
                        </label>
                        <label class="grid gap-1 text-sm font-semibold">
                            What happened
                            <textarea name="details" required minlength="20" maxlength="3000" rows="5" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></textarea>
                        </label>
                        <button type="submit" class="action action--surface">
                            <x-lucide-scale class="icon" aria-hidden="true" />
                            <span>Open dispute</span>
                        </button>
                    </form>
                @endif

                @if ($can_review)
                    <form method="POST" action="{{ route('marketplace.orders.reviews.store', [$listing['slug'], $order['reference']]) }}" class="grid gap-3 border-t border-paw-line pt-5" aria-labelledby="leave-review-heading">
                        @csrf
                        <h2 id="leave-review-heading" class="text-xl font-bold">Leave a verified review</h2>
                        @forelse ([
                            'item_rating' => 'Item or service',
                            'seller_rating' => 'Seller',
                            'delivery_rating' => 'Delivery',
                        ] as $field => $label)
                            <label class="grid gap-1 text-sm font-semibold">
                                {{ $label }}
                                <select name="{{ $field }}" @if ($field !== 'delivery_rating') required @endif class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                                    @if ($field === 'delivery_rating')
                                        <option value="">Not rated</option>
                                    @endif
                                    @forelse ([5, 4, 3, 2, 1] as $rating)
                                        <option value="{{ $rating }}">{{ $rating }} / 5</option>
                                    @empty
                                        <option disabled>No ratings</option>
                                    @endforelse
                                </select>
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">No review criteria.</p>
                        @endforelse
                        <label class="grid gap-1 text-sm font-semibold">
                            Review
                            <textarea name="body" required minlength="20" maxlength="2000" rows="5" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></textarea>
                        </label>
                        <button type="submit" class="action action--primary">
                            <x-lucide-star class="icon" aria-hidden="true" />
                            <span>Publish review</span>
                        </button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-app-shell>
