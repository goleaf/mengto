<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full min-w-0 max-w-5xl gap-6">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('experts.show', $expert['slug']) }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                {{ $expert['name'] }}
            </a>
            <h1 class="mt-4 text-3xl font-bold">Request a consultation</h1>
            <p class="mt-2 text-paw-muted">{{ $expert['type'] }} · {{ $expert['city'] }} · {{ implode(', ', $expert['formats']) }}</p>
        </header>

        <section class="flex gap-3 border-l-4 border-red-500 bg-red-50 p-4 text-red-950" aria-label="Emergency warning">
            <x-lucide-siren class="mt-0.5 size-5 shrink-0" aria-hidden="true" />
            <div>
                <h2 class="font-bold">Do not use planned booking for an emergency</h2>
                <p class="mt-1 text-sm">Breathing difficulty, collapse, seizures, severe bleeding, poisoning, inability to urinate, major trauma, overheating, or severe weakness require immediate contact with a clinic.</p>
            </div>
        </section>

        @if ($errors->any())
            <section class="rounded-md border border-red-300 bg-red-50 p-4 text-red-950" role="alert">
                <h2 class="font-bold">The appointment was not submitted</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>Review the form and try again.</li>
                    @endforelse
                </ul>
            </section>
        @endif

        <form method="POST" action="{{ route('experts.bookings.store', $expert['slug']) }}" enctype="multipart/form-data" class="grid w-full min-w-0 gap-8">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotency_key) }}">

            <section aria-labelledby="service-selection">
                <h2 id="service-selection" class="text-xl font-bold">1. Choose a service</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @forelse ($services as $service)
                        <label class="grid cursor-pointer gap-2 rounded-md border border-paw-line bg-white p-4 has-[:checked]:border-paw-leaf has-[:checked]:ring-2 has-[:checked]:ring-paw-leaf/20">
                            <span class="flex items-start gap-3">
                                <input type="radio" name="service_id" value="{{ $service['id'] }}" required @checked((string) old('service_id', request('service')) === (string) $service['id']) class="mt-1 size-4 text-paw-leaf">
                                <span class="flex-1">
                                    <strong class="block">{{ $service['name'] }}</strong>
                                    <span class="mt-1 block text-sm text-paw-muted">{{ $service['format'] }} · {{ $service['duration'] }} · {{ $service['price'] !== null ? $service['currency'].' '.$service['price'] : 'Price on request' }}</span>
                                </span>
                            </span>
                            <span class="text-sm leading-6">{{ $service['description'] }}</span>
                            <span class="text-xs text-paw-muted">{{ $service['cancellation_policy'] }}</span>
                        </label>
                    @empty
                        <p class="text-paw-muted">No active services are available.</p>
                    @endforelse
                </div>
            </section>

            <section class="grid gap-4 border-y border-paw-line py-7" aria-labelledby="time-selection">
                <h2 id="time-selection" class="text-xl font-bold">2. Select pet and time</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Pet
                        <select name="pet_key" required class="w-full min-w-0 max-w-full rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($pets as $key => $pet)
                                <option value="{{ $key }}" @selected(old('pet_key') === $key)>{{ $pet['name'] }} · {{ $pet['species_label'] }}</option>
                            @empty
                                <option disabled>No pets available</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Available time
                        <select name="availability_slot_id" required class="w-full min-w-0 max-w-full rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">Choose an opening</option>
                            @forelse ($slots as $slot)
                                <option value="{{ $slot['id'] }}" @selected((string) old('availability_slot_id') === (string) $slot['id'])>
                                    {{ $slot['label'] }}–{{ $slot['ends_at'] }} · {{ $slot['format'] }}
                                    @if ($slot['location']) · {{ $slot['location'] }} @endif
                                    · {{ $slot['remaining'] }} left
                                </option>
                            @empty
                                <option disabled>No open time slots</option>
                            @endforelse
                        </select>
                    </label>
                </div>
            </section>

            <section class="grid gap-4" aria-labelledby="consultation-context">
                <div>
                    <h2 id="consultation-context" class="text-xl font-bold">3. Consultation context</h2>
                    <p class="mt-1 text-sm text-paw-muted">Share only information needed for this appointment. The form does not diagnose the pet.</p>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    Main question
                    <textarea name="main_question" required minlength="20" rows="5" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Describe what you need help with and the most important context.">{{ old('main_question') }}</textarea>
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">When it started<textarea name="started_at" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('started_at') }}</textarea></label>
                    <label class="grid gap-1 text-sm font-semibold">What you already tried<textarea name="tried" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('tried') }}</textarea></label>
                    <label class="grid gap-1 text-sm font-semibold">Previous professional input<textarea name="previous_professional" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('previous_professional') }}</textarea></label>
                    <label class="grid gap-1 text-sm font-semibold">Desired result<textarea name="desired_result" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('desired_result') }}</textarea></label>
                </div>
                <label class="grid gap-1 text-sm font-semibold">Communication or accessibility needs<textarea name="access_needs" rows="2" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('access_needs') }}</textarea></label>
                <input type="hidden" name="urgent_signs" value="0">
                <label class="flex items-start gap-2 rounded-md border border-red-200 bg-red-50 p-3 text-sm">
                    <input type="checkbox" name="urgent_signs" value="1" @checked(old('urgent_signs')) class="mt-0.5 size-4">
                    <span><strong>There are urgent warning signs.</strong> Selecting this stops planned booking and directs you to immediate veterinary help.</span>
                </label>
            </section>

            <section class="grid gap-4 border-y border-paw-line py-7" aria-labelledby="document-sharing">
                <div>
                    <h2 id="document-sharing" class="text-xl font-bold">4. Optional temporary document access</h2>
                    <p class="mt-1 text-sm text-paw-muted">The selected specialist receives access only to this file. You can revoke it later from the appointment page.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">Document label<input name="document_label" value="{{ old('document_label') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Blood test from July 20"></label>
                    <label class="grid gap-1 text-sm font-semibold">Document type<input name="document_type" value="{{ old('document_type') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="lab-result"></label>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    PDF or image, up to 10 MB
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.webp" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                </label>
            </section>

            <section class="grid gap-3" aria-labelledby="booking-consent">
                <h2 id="booking-consent" class="text-xl font-bold">5. Confirm boundaries and consent</h2>
                <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="terms_accepted" value="1" required class="mt-0.5 size-4"><span>I accept the displayed service scope, price, preparation, and cancellation policy.</span></label>
                <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="data_consent" value="1" required class="mt-0.5 size-4"><span>I authorize the minimum pet and contact data needed to manage this appointment.</span></label>
                <input type="hidden" name="recording_consent" value="0">
                <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="recording_consent" value="1" class="mt-0.5 size-4"><span>I agree to a consultation recording if the specialist requests it. Recording cannot start without visible confirmation.</span></label>
            </section>

            <footer class="flex flex-wrap justify-end gap-2">
                <x-ui.action-control label="Cancel" icon="x" :href="route('experts.show', $expert['slug'])" />
                <button type="submit" class="action action--primary action--comfortable" @disabled($services === [] || $slots === [])>
                    <x-lucide-calendar-check class="icon icon--sm" aria-hidden="true" />
                    <span>Submit appointment request</span>
                </button>
            </footer>
        </form>
    </div>
</x-layout.app-shell>
