@props(['booking', 'expert', 'service', 'consultation', 'documents', 'audit', 'canManageExpert', 'consultationMode' => false])

<div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
    <div class="grid content-start gap-7">
        @if ($consultationMode)
            <section class="overflow-hidden rounded-md border border-paw-line bg-paw-ink text-white" aria-labelledby="consultation-room-heading">
                <div class="aspect-video min-h-72 bg-black/30 p-5">
                    <div class="flex h-full flex-col items-center justify-center text-center">
                        <span class="grid size-20 place-items-center rounded-full bg-white/10 text-2xl font-bold">{{ $expert['initials'] }}</span>
                        <h2 id="consultation-room-heading" class="mt-4 text-xl font-bold">{{ __('ui.secure_consultation_room_87fd40abbb') }}</h2>
                        <p class="mt-2 max-w-lg text-sm text-white/70">{{ __('ui.camera_and_microphone_stay_off_until_you_enable_284f3bb385') }}</p>
                        <div class="mt-5 flex flex-wrap justify-center gap-2">
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="{{ __('ui.toggle_microphone_542e869334') }}" aria-label="{{ __('ui.toggle_microphone_542e869334') }}"><x-ui-icon name="mic" size="lg" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="{{ __('ui.toggle_camera_b50eae6645') }}" aria-label="{{ __('ui.toggle_camera_b50eae6645') }}"><x-ui-icon name="video" size="lg" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="{{ __('ui.show_captions_6920ef3de8') }}" aria-label="{{ __('ui.show_captions_6920ef3de8') }}"><x-ui-icon name="captions" size="lg" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full border border-white/30 bg-white/10" title="{{ __('ui.open_text_chat_29014a0710') }}" aria-label="{{ __('ui.open_text_chat_29014a0710') }}"><x-ui-icon name="message-square" size="lg" /></button>
                            <button type="button" class="grid size-11 place-items-center rounded-full bg-red-600" title="{{ __('ui.leave_consultation_1ccfcad5b1') }}" aria-label="{{ __('ui.leave_consultation_1ccfcad5b1') }}"><x-ui-icon name="phone-off" size="lg" /></button>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/15 px-5 py-3 text-xs text-white/70">
                    <span class="inline-flex items-center gap-2"><x-ui-icon name="shield-check" size="sm" />{{ __('ui.access_is_limited_to_this_appointment_85a8568c05') }}</span>
                    <span>{{ __('ui.connection_test_ready_recording_off_9ef43698f3') }}</span>
                </div>
            </section>
        @endif

        <section aria-labelledby="appointment-heading">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase text-paw-leaf">{{ __('presentation.appointment_reference', ['reference' => $booking['reference']]) }}</p>
                    <h2 id="appointment-heading" class="mt-1 text-2xl font-bold">{{ $service['name'] }}</h2>
                    <p class="mt-2 text-paw-muted">{{ $expert['name'] }} · {{ $booking['pet_name'] }}</p>
                </div>
                <x-status-badge :label="$booking['status']" icon="calendar-clock" :tone="$booking['status_value'] === 'completed' ? 'success' : 'surface'" />
            </div>

            <dl class="mt-5 grid gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line sm:grid-cols-2">
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">{{ __('ui.date_and_time_a079c3bbe2') }}</dt><dd class="mt-1 font-semibold">{{ $booking['starts_at'] }} · {{ $booking['timezone'] }}</dd></div>
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">{{ __('ui.format_2f343666aa') }}</dt><dd class="mt-1 font-semibold">{{ $booking['format'] }}</dd></div>
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">{{ __('ui.place_e9463dccf0') }}</dt><dd class="mt-1 font-semibold">{{ $booking['location'] ?? __('ui.secure_online_room_b38bbd2821') }}</dd></div>
                <div class="bg-white p-4"><dt class="text-xs font-bold uppercase text-paw-muted">{{ __('ui.price_93c91c851e') }}</dt><dd class="mt-1 font-semibold">{{ $booking['currency'] }} {{ $booking['amount'] }} · {{ $booking['payment_status'] }}</dd></div>
            </dl>
        </section>

        <section aria-labelledby="context-heading">
            <h2 id="context-heading" class="text-xl font-bold">{{ __('ui.submitted_context_d2d06a1bc4') }}</h2>
            <dl class="mt-4 grid gap-4">
                @forelse ($booking['questionnaire_rows'] as $row)
                    <div class="border-b border-paw-line pb-3">
                        <dt class="text-xs font-bold uppercase text-paw-muted">{{ $row['label'] }}</dt>
                        <dd class="mt-1 whitespace-pre-line leading-6">{{ $row['value'] }}</dd>
                    </div>
                @empty
                    <p class="text-paw-muted">{{ __('ui.no_additional_context_was_submitted_fcd7c3050b') }}</p>
                @endforelse
            </dl>
        </section>

        @if ($consultation)
            <section class="border-y border-paw-line py-6" aria-labelledby="summary-heading">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 id="summary-heading" class="text-xl font-bold">{{ __('ui.professional_summary_e364d91187') }}</h2>
                        <p class="mt-1 text-sm text-paw-muted">{{ $consultation['is_confirmed'] ? __('ui.checked_and_confirmed_by_the_specialist_77ef0f4e8b') : __('ui.not_yet_confirmed_as_the_specialist_s_final_3017579fcb') }}</p>
                    </div>
                    <x-status-badge :label="$consultation['status']" icon="clipboard-check" />
                </div>

                @if ($consultation['client_summary'])
                    <p class="mt-4 whitespace-pre-line leading-7">{{ $consultation['client_summary'] }}</p>
                @else
                    <p class="mt-4 text-paw-muted">{{ __('ui.the_written_summary_will_appear_after_the_professional_562285dcc4') }}</p>
                @endif

                @if ($consultation['action_plan'] !== [])
                    <div class="mt-5">
                        <h3 class="font-bold">{{ __('ui.next_actions_f6eb776252') }}</h3>
                        <ol class="mt-2 grid gap-2">
                            @forelse ($consultation['action_plan'] as $item)
                                <li class="flex gap-2"><x-ui-icon name="circle-check" size="sm" class="mt-0.5 shrink-0 text-paw-leaf" /><span>{{ $item }}</span></li>
                            @empty
                                <li>{{ __('ui.no_actions_listed_82f4a107bf') }}</li>
                            @endforelse
                        </ol>
                    </div>
                @endif

                @if ($consultation['referral_summary'])
                    <div class="mt-5 border-l-2 border-paw-leaf pl-4">
                        <h3 class="font-bold">{{ __('ui.referral_aeb7b00433') }}</h3>
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
                    <h2 class="text-xl font-bold">{{ __('ui.confirm_client_facing_summary_2de5fbe24b') }}</h2>
                    <p class="mt-1 text-sm text-paw-muted">{{ __('ui.automated_notes_are_never_sent_as_an_official_efb9af3485') }}</p>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    {{ __('ui.summary_8e76a94ac8') }}
                    <textarea name="client_summary" required minlength="30" rows="6" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('client_summary') }}</textarea>
                </label>
                <fieldset class="grid gap-2">
                    <legend class="text-sm font-bold">{{ __('ui.action_plan_02ef5dc133') }}</legend>
                    @for ($index = 0; $index < 3; $index++)
                        <label class="sr-only" for="booking-action-plan-{{ $index }}">{{ __('presentation.step_number', ['number' => $index + 1]) }}</label>
                        <input id="booking-action-plan-{{ $index }}" name="action_plan[]" value="{{ old('action_plan.'.$index) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('presentation.step_number', ['number' => $index + 1]) }}">
                    @endfor
                </fieldset>
                <label class="grid gap-1 text-sm font-semibold">{{ __('ui.referral_or_in_person_follow_up_242191b36a') }}<textarea name="referral_summary" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('referral_summary') }}</textarea></label>
                <label class="grid max-w-xs gap-1 text-sm font-semibold">{{ __('ui.follow_up_available_until_e514fbf633') }}<input type="date" name="follow_up_until" value="{{ old('follow_up_until') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                <button type="submit" class="action action--primary action--compact w-fit"><x-ui-icon name="badge-check" size="sm" /><span>{{ __('ui.confirm_and_complete_32e970e39c') }}</span></button>
            </form>
        @endif
    </div>

    <aside class="grid content-start gap-7">
        <section aria-labelledby="professional-heading">
            <h2 id="professional-heading" class="text-lg font-bold">{{ __('ui.professional_19c73a5cdf') }}</h2>
            <div class="mt-3 flex items-center gap-3">
                <x-linked-media
                    :href="$expert['media_target']['url']"
                    :label="$expert['media_target']['label']"
                    variant="avatar"
                    class="shrink-0"
                >
                    @if ($expert['avatar_url'])
                        <img src="{{ $expert['avatar_url'] }}" alt="" class="size-12 rounded-full object-cover">
                    @else
                        <span class="grid size-12 place-items-center rounded-full bg-paw-mint font-bold text-paw-leaf" aria-hidden="true">{{ $expert['initials'] }}</span>
                    @endif
                </x-linked-media>
                <div>
                    <a href="{{ $expert['profile_url'] }}" class="font-bold hover:text-paw-leaf">{{ $expert['name'] }}</a>
                    <p class="text-sm text-paw-muted">{{ $expert['type'] }}</p>
                </div>
            </div>
        </section>

        <section class="border-y border-paw-line py-5" aria-labelledby="document-heading">
            <h2 id="document-heading" class="text-lg font-bold">{{ __('ui.temporary_document_access_1d7ca6f4cc') }}</h2>
            <div class="mt-3 grid gap-3">
                @forelse ($documents as $document)
                    <article class="text-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold">{{ $document['label'] }}</h3>
                                <p class="text-paw-muted">{{ __('presentation.document_expires', ['type' => $document['type'], 'date' => $document['expires_at']]) }}</p>
                            </div>
                            <x-status-badge :label="$document['revoked'] ? __('ui.revoked_f6f738d043') : __('ui.active_9234069589')" :icon="$document['revoked'] ? 'lock' : 'file-lock-2'" />
                        </div>
                        @unless ($document['revoked'])
                            <form method="POST" action="{{ route('bookings.actions', $booking['reference']) }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="action" value="revoke-document">
                                <input type="hidden" name="document_grant_id" value="{{ $document['id'] }}">
                                <button type="submit" class="inline-flex min-h-11 items-center gap-1 text-xs font-bold text-red-700 underline">
                                    <x-ui-icon name="shield-x" size="sm" />
                                    <span>{{ __('ui.revoke_access_ab292ddb87') }}</span>
                                </button>
                            </form>
                        @endunless
                    </article>
                @empty
                    <p class="text-sm text-paw-muted">{{ __('ui.no_documents_were_shared_83d45fb57c') }}</p>
                @endforelse
            </div>
        </section>

        @if ($booking['can_cancel'])
            <section aria-labelledby="manage-booking-heading">
                <h2 id="manage-booking-heading" class="text-lg font-bold">{{ __('ui.manage_appointment_59a80fa3ac') }}</h2>
                <div class="mt-3 grid gap-2">
                    <form method="POST" action="{{ route('bookings.actions', $booking['reference']) }}">
                        @csrf
                        <input type="hidden" name="action" value="request-reschedule">
                        <input type="hidden" name="reason" value="Client requested a different time.">
                        <button type="submit" class="action action--surface action--compact w-full"><x-ui-icon name="calendar-sync" size="sm" /><span>{{ __('ui.request_reschedule_8fdeaddfce') }}</span></button>
                    </form>
                    <form method="POST" action="{{ route('bookings.actions', $booking['reference']) }}">
                        @csrf
                        <input type="hidden" name="action" value="cancel">
                        <input type="hidden" name="reason" value="Cancelled from appointment page.">
                        <button type="submit" class="action action--surface action--compact w-full text-red-700"><x-ui-icon name="calendar-x" size="sm" /><span>{{ __('ui.cancel_appointment_efd06e321f') }}</span></button>
                    </form>
                </div>
            </section>
        @endif

        <section aria-labelledby="activity-heading">
            <h2 id="activity-heading" class="text-lg font-bold">{{ __('ui.activity_history_e46875181b') }}</h2>
            <ol class="mt-3 grid gap-3 text-sm">
                @forelse ($audit as $entry)
                    <li class="border-l-2 border-paw-line pl-3">
                        <strong>{{ $entry['action'] }}</strong>
                        <span class="block text-paw-muted">{{ $entry['role'] }} · {{ $entry['created_label'] }}</span>
                    </li>
                @empty
                    <li class="text-paw-muted">{{ __('ui.no_recorded_changes_c95354adcb') }}</li>
                @endforelse
            </ol>
        </section>
    </aside>
</div>
