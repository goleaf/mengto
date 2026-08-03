<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    @if ($expert === null)
        <div class="grid w-full min-w-0 gap-7">
            <x-page-header
                :eyebrow="__('ui.professional_workspace_eb8eb6dde6')"
                :title="__('ui.create_your_professional_workspace_c025183333')"
                :description="__('ui.publish_a_precise_scope_submit_credentials_privately_offer_3099c3a8b0')"
                heading-id="expert-workspace-heading"
                :action-label="__('ui.create_professional_profile_30276b75d3')"
                action-icon="badge-plus"
                :action-href="route('experts.create')"
                data-section="expert-workspace-header"
            />
        </div>
    @else
        <div class="grid w-full min-w-0 gap-7">
            <x-page-header
                :eyebrow="__('ui.professional_workspace_eb8eb6dde6')"
                :title="$expert['name']"
                :description="$expert['type'].' · '.$expert['profile_status'].' · '.$expert['verification']"
                heading-id="expert-workspace-heading"
                data-section="expert-workspace-header"
            >
                <x-slot:actions>
                    <x-action-control :label="__('ui.view_public_profile_9acb2dbb15')" icon="external-link" :href="route('experts.show', $expert['slug'])" />
                    <x-action-control :label="__('ui.edit_profile_15c4aa1303')" icon="pencil" variant="primary" :href="route('experts.edit', $expert['slug'])" />
                </x-slot:actions>
            </x-page-header>

            <section class="grid min-w-0 grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line lg:grid-cols-4" aria-label="{{ __('ui.workspace_metrics_729c6cbd06') }}">
                @forelse ($metrics as $metric)
                    <div class="bg-white p-4">
                        <strong class="text-2xl">{{ $metric['value'] }}</strong>
                        <span class="mt-1 block text-sm font-semibold">{{ $metric['label'] }}</span>
                        <span class="mt-1 block text-xs text-paw-muted">{{ $metric['note'] }}</span>
                    </div>
                @empty
                    <p class="col-span-full bg-white p-4 text-paw-muted">{{ __('ui.no_metrics_yet_01104486d0') }}</p>
                @endforelse
            </section>

            <div class="grid min-w-0 gap-8 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
                <div class="grid min-w-0 content-start gap-8">
                    <section class="min-w-0" aria-labelledby="upcoming-bookings">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <h2 id="upcoming-bookings" class="text-2xl font-bold">{{ __('ui.consultation_queue_94bab5430d') }}</h2>
                                <p class="mt-1 text-sm text-paw-muted">{{ __('ui.the_client_sees_who_currently_handles_the_appointment_e792b5ab4c') }}</p>
                            </div>
                        </div>
                        <div class="mt-4 max-w-full min-w-0 overflow-x-auto">
                            <table class="w-full min-w-[44rem] border-collapse text-left text-sm">
                                <thead>
                                    <tr class="border-b border-paw-line text-xs uppercase text-paw-muted">
                                        <th class="px-3 py-2">{{ __('ui.time_33b93476cf') }}</th>
                                        <th class="px-3 py-2">{{ __('ui.client_and_pet_d27897b01c') }}</th>
                                        <th class="px-3 py-2">{{ __('ui.service_d677190e0a') }}</th>
                                        <th class="px-3 py-2">{{ __('ui.status_920e413c7d') }}</th>
                                        <th class="px-3 py-2">{{ __('ui.action_64cff1319d') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bookings as $booking)
                                        <tr class="border-b border-paw-line">
                                            <td class="px-3 py-3 font-semibold">{{ $booking['starts_at'] }}</td>
                                            <td class="px-3 py-3">{{ $booking['client_name'] }}<span class="block text-paw-muted">{{ $booking['pet_name'] }} · {{ $booking['pet_species'] }}</span></td>
                                            <td class="px-3 py-3">{{ $booking['service'] }}<span class="block text-paw-muted">{{ $booking['format'] }}</span></td>
                                            <td class="px-3 py-3">{{ $booking['status'] }}<span class="block text-paw-muted">{{ $booking['payment_status'] }}</span></td>
                                            <td class="px-3 py-3 text-right">
                                                <a href="{{ route('bookings.show', $booking['reference']) }}" class="inline-flex items-center gap-1 font-bold text-paw-leaf">
                                                    <span>{{ __('ui.open_ed077f3d81') }}</span>
                                                    <x-ui-icon name="arrow-up-right" size="sm" />
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-3 py-6 text-center text-paw-muted">{{ __('ui.no_consultation_requests_yet_cbc6f79994') }}</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="min-w-0" aria-labelledby="workspace-services">
                        <h2 id="workspace-services" class="text-2xl font-bold">{{ __('ui.published_services_2ef178868d') }}</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @forelse ($services as $service)
                                <x-service-card :service="$service" />
                            @empty
                                <p class="text-paw-muted">{{ __('ui.no_services_have_been_configured_df990f6b57') }}</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 content-start gap-7">
                    <section aria-labelledby="workspace-verification">
                        <h2 id="workspace-verification" class="text-xl font-bold">{{ __('ui.verification_status_aedfff7efc') }}</h2>
                        <div class="mt-4">
                            <x-verification-list :items="$expert['verification_items']" :expires="$expert['verification_expires']" />
                        </div>
                    </section>

                    <section class="border-y border-paw-line py-5" aria-labelledby="workspace-credentials">
                        <h2 id="workspace-credentials" class="text-xl font-bold">{{ __('ui.submitted_credentials_70fa5b0df3') }}</h2>
                        <div class="mt-3 grid gap-3 text-sm">
                            @forelse ($credentials as $credential)
                                <article>
                                    <h3 class="font-bold">{{ $credential['title'] }}</h3>
                                    <p class="text-paw-muted">{{ $credential['issuer'] }} · {{ $credential['status'] }}</p>
                                    @if ($credential['expires_at'])<p class="text-xs text-paw-muted">{{ __('presentation.credential_expires', ['date' => $credential['expires_at']]) }}</p>@endif
                                </article>
                            @empty
                                <p class="text-paw-muted">{{ __('ui.no_credentials_submitted_1d62729151') }}</p>
                            @endforelse
                        </div>
                    </section>

                    <section aria-labelledby="privacy-note">
                        <h2 id="privacy-note" class="text-xl font-bold">{{ __('ui.privacy_boundary_fecc0f1b06') }}</h2>
                        <p class="mt-2 text-sm leading-6 text-paw-muted">{{ __('ui.profile_viewers_their_searches_home_addresses_gps_history_7783292e23') }}</p>
                    </section>
                </aside>
            </div>
        </div>
    @endif
</x-app-shell>
