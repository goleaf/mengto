@props(['booking', 'expert', 'service', 'consultation', 'documents', 'audit', 'canManageExpert', 'consultationMode' => false])

<div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
    <div class="grid content-start gap-7">
        @if ($consultationMode)
            <section class="overflow-hidden rounded-md border border-paw-line bg-paw-ink text-white" aria-labelledby="consultation-room-heading">
                <div class="aspect-video min-h-72 bg-black/30 p-5">
                    <div class="flex h-full flex-col items-center justify-center text-center">
                        <span class="grid size-20 place-items-center rounded-full bg-white/10 text-2xl font-bold">{{ $expert['initials'] }}</span>
                        <h2 id="consultation-room-heading" class="mt-4 text-xl font-bold">Secure consultation room</h2>
                        <p class="mt-2 max-w-lg text-sm text-white/70">Camera and microphone stay off until you enable them. A recording can begin only after every participant sees and accepts the request.</p>
                        <div class="mt-5 flex flex-wrap justify-center gap-2">
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="Toggle microphone" aria-label="Toggle microphone"><x-lucide-mic class="size-5" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="Toggle camera" aria-label="Toggle camera"><x-lucide-video class="size-5" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="Show captions" aria-label="Show captions"><x-lucide-captions class="size-5" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="Open text chat" aria-label="Open text chat"><x-lucide-message-square class="size-5" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full bg-red-600" title="Leave consultation" aria-label="Leave consultation"><x-lucide-phone-off class="size-5" /></button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/15 px-5 py-3 text-xs text-white/70">
                    <span class="inline-flex items-center gap-2"><x-lucide-shield-check class="size-4" />Access is limited to this appointment</span>
                    <span>Connection test ready · recording off</span>
                </div>
            </section>
        @endif

        <section aria-labelledby="appointment-heading">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase text-paw-leaf">Appointment {{ $booking['reference'] }}</p>
                    <h2 id="appointment-heading" class="mt-1 text-2xl font-bold">{{ $service['name'] }}</h2>
                    <p class="mt-2 text-paw-muted">{{ $expert['name'] }} · {{ $booking['pet_name'] }}</p>
                </div>
                <x-status-badge :label="$booking['status']" icon="calendar-clock" :tone="$booking['status_value'] === 'completed' ? 'success' : 'surface'" />
            </div>

            <dl class="mt-5 grid gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line sm:grid-cols-2">
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">Date and time</dt><dd class="mt-1 font-semibold">{{ $booking['starts_at'] }} · {{ $booking['timezone'] }}</dd></div>
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">Format</dt><dd class="mt-1 font-semibold">{{ $booking['format'] }}</dd></div>
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">Place</dt><dd class="mt-1 font-semibold">{{ $booking['location'] ?? 'Secure online room' }}</dd></div>
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">Price</dt><dd class="mt-1 font-semibold">{{ $booking['currency'] }} {{ $booking['amount'] }} · {{ $booking['payment_status'] }}</dd></div>
            </dl>
        </section>

        <section aria-labelledby="context-heading">
            <h2 id="context-heading" class="text-xl font-bold">Submitted context</h2>
            <dl class="mt-4 grid gap-4">
                @forelse ($booking['questionnaire'] as $label => $value)
                    @if (filled($value) && $label !== 'urgent_signs')
                        <div class="border-b border-paw-line pb-3">
                            <dt class="text-xs font-bold uppercase text-paw-muted">{{ str($label)->replace('_', ' ')->headline() }}</dt>
                            <dd class="mt-1 whitespace-pre-line leading-6">{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</dd>
                        </div>
                    @endif
                @empty
                    <p class="text-paw-muted">No additional context was submitted.</p>
                @endforelse
            </dl>
        </section>

        @if ($consultation)
            <section class="border-y border-paw-line py-6" aria-labelledby="summary-heading">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="summary-heading" class="text-xl font-bold">Professional summary</h2>
                        <p class="mt-1 text-sm text-paw-muted">{{ $consultation['is_confirmed'] ? 'Checked and confirmed by the specialist' : 'Not yet confirmed as the specialist’s final summary' }}</p>
                    </div>
                    <x-status-badge :label="$consultation['status']" icon="clipboard-check" />
                </div>

                @if ($consultation['client_summary'])
                    <p class="mt-4 whitespace-pre-line leading-7">{{ $consultation['client_summary'] }}</p>
                @else
                    <p class="mt-4 text-paw-muted">The written summary will appear after the professional reviews and confirms it.</p>
                @endif

                @if ($consultation['action_plan'] !== [])
                    <div class="mt-5">
                        <h3 class="font-bold">Next actions</h3>
                        <ol class="mt-2 grid gap-2">
                            @forelse ($consultation['action_plan'] as $item)
                                <li class="flex gap-2"><x-lucide-circle-check class="mt-0.5 size-4 shrink-0 text-paw-leaf" aria-hidden="true" /><span>{{ $item }}</span></li>
                            @empty
                                <li>No actions listed.</li>
                            @endforelse
                        </ol>
                    </div>
                @endif

                @if ($consultation['referral_summary'])
                    <div class="mt-5 border-l-2 border-paw-leaf pl-4">
                        <h3 class="font-bold">Referral</h3>
                        <p class="mt-1">{{ $consultation['referral_summary'] }}</p>
                    </div>
                @endif
            </section>
        @endif

        @if ($canManageExpert && $consultation && ! $consultation['is_confirmed'])
            <form method="POST" action="{{ route('bookings.actions', $booking['reference']) }}" class="grid gap-4 border-y border-paw-line py-6">
                @csrf
                <input type="hidden" name="action" value="complete-consultation">
                <div>
                    <h2 class="text-xl font-bold">Confirm client-facing summary</h2>
                    <p class="mt-1 text-sm text-paw-muted">Automated notes are never sent as an official conclusion until you check and confirm this text.</p>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    Summary
                    <textarea name="client_summary" required minlength="30" rows="6" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('client_summary') }}</textarea>
                </label>
                <fieldset class="grid gap-2">
                    <legend class="text-sm font-bold">Action plan</legend>
                    @for ($index = 0; $index < 3; $index++)
                        <input name="action_plan[]" value="{{ old('action_plan.'.$index) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Step {{ $index + 1 }}">
                    @endfor
                </fieldset>
                <label class="grid gap-1 text-sm font-semibold">Referral or in-person follow-up<textarea name="referral_summary" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('referral_summary') }}</textarea></label>
                <label class="grid max-w-xs gap-1 text-sm font-semibold">Follow-up available until<input type="date" name="follow_up_until" value="{{ old('follow_up_until') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                <button type="submit" class="action action--primary action--compact w-fit"><x-lucide-badge-check class="icon icon--sm" aria-hidden="true" /><span>Confirm and complete</span></button>
            </form>
        @endif
    </div>

    <aside class="grid content-start gap-7">
        <section aria-labelledby="professional-heading">
            <h2 id="professional-heading" class="text-lg font-bold">Professional</h2>
            <div class="mt-3 flex items-center gap-3">
                @if ($expert['avatar_url'])
                    <img src="{{ $expert['avatar_url'] }}" alt="" class="size-12 rounded-full object-cover">
                @else
                    <span class="grid size-12 place-items-center rounded-full bg-paw-mint font-bold text-paw-leaf">{{ $expert['initials'] }}</span>
                @endif
                <div>
                    <a href="{{ route('experts.show', $expert['slug']) }}" class="font-bold hover:text-paw-leaf">{{ $expert['name'] }}</a>
                    <p class="text-sm text-paw-muted">{{ $expert['type'] }}</p>
                </div>
            </div>
        </section>

        <section class="border-y border-paw-line py-5" aria-labelledby="document-heading">
            <h2 id="document-heading" class="text-lg font-bold">Temporary document access</h2>
            <div class="mt-3 grid gap-3">
                @forelse ($documents as $document)
                    <article class="text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold">{{ $document['label'] }}</h3>
                                <p class="text-paw-muted">{{ $document['type'] }} · expires {{ $document['expires_at'] }}</p>
                            </div>
                            <x-status-badge :label="$document['revoked'] ? 'Revoked' : 'Active'" :icon="$document['revoked'] ? 'lock' : 'file-lock-2'" />
                        </div>
                        @unless ($document['revoked'])
                            <form method="POST" action="{{ route('bookings.actions', $booking['reference']) }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="action" value="revoke-document">
                                <input type="hidden" name="document_grant_id" value="{{ $document['id'] }}">
                                <button type="submit" class="text-xs font-bold text-red-700 underline">Revoke access</button>
                            </form>
                        @endunless
                    </article>
                @empty
                    <p class="text-sm text-paw-muted">No documents were shared.</p>
                @endforelse
            </div>
        </section>

        @if ($booking['can_cancel'])
            <section aria-labelledby="manage-booking-heading">
                <h2 id="manage-booking-heading" class="text-lg font-bold">Manage appointment</h2>
                <div class="mt-3 grid gap-2">
                    <form method="POST" action="{{ route('bookings.actions', $booking['reference']) }}">
                        @csrf
                        <input type="hidden" name="action" value="request-reschedule">
                        <input type="hidden" name="reason" value="Client requested a different time.">
                        <button type="submit" class="action action--surface action--compact w-full"><x-lucide-calendar-sync class="icon icon--sm" aria-hidden="true" /><span>Request reschedule</span></button>
                    </form>
                    <form method="POST" action="{{ route('bookings.actions', $booking['reference']) }}">
                        @csrf
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="reason" value="Cancelled from appointment page.">
                        <button type="submit" class="action action--surface action--compact w-full text-red-700"><x-lucide-calendar-x class="icon icon--sm" aria-hidden="true" /><span>Cancel appointment</span></button>
                    </form>
                </div>
            </section>
        @endif

        <section aria-labelledby="activity-heading">
            <h2 id="activity-heading" class="text-lg font-bold">Activity history</h2>
            <ol class="mt-3 grid gap-3 text-sm">
                @forelse ($audit as $entry)
                    <li class="border-l-2 border-paw-line pl-3">
                        <strong>{{ $entry['action'] }}</strong>
                        <span class="block text-paw-muted">{{ $entry['role'] }} · {{ $entry['created_label'] }}</span>
                    </li>
                @empty
                    <li class="text-paw-muted">No recorded changes.</li>
                @endforelse
            </ol>
        </section>
    </aside>
</div>
