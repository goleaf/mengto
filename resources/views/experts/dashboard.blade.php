<x-layout.app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    @if ($expert === null)
        <section class="mx-auto max-w-2xl py-16 text-center">
            <x-lucide-briefcase-business class="mx-auto size-12 text-paw-leaf" aria-hidden="true" />
            <h1 class="mt-5 text-3xl font-bold">Create your professional workspace</h1>
            <p class="mt-3 leading-7 text-paw-muted">Publish a precise scope, submit credentials privately, offer services, and manage consultation requests without mixing professional activity with your personal profile.</p>
            <div class="mt-6 flex justify-center">
                <x-ui.action-control label="Create professional profile" icon="badge-plus" variant="primary" :href="route('experts.create')" />
            </div>
        </section>
    @else
        <div class="grid w-full min-w-0 gap-7">
            <header class="flex min-w-0 flex-col gap-4 border-b border-paw-line pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-bold uppercase text-paw-leaf">Professional workspace</p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $expert['name'] }}</h1>
                    <p class="mt-2 text-paw-muted">{{ $expert['type'] }} · {{ $expert['profile_status'] }} · {{ $expert['verification'] }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-ui.action-control label="View public profile" icon="external-link" :href="route('experts.show', $expert['slug'])" />
                    <x-ui.action-control label="Edit profile" icon="pencil" variant="primary" :href="route('experts.edit', $expert['slug'])" />
                </div>
            </header>

            <section class="grid min-w-0 grid-cols-2 gap-px overflow-hidden rounded-md border border-paw-line bg-paw-line lg:grid-cols-4" aria-label="Workspace metrics">
                @forelse ($metrics as $metric)
                    <div class="bg-white p-4">
                        <strong class="text-2xl">{{ $metric['value'] }}</strong>
                        <span class="mt-1 block text-sm font-semibold">{{ $metric['label'] }}</span>
                        <span class="mt-1 block text-xs text-paw-muted">{{ $metric['note'] }}</span>
                    </div>
                @empty
                    <p class="col-span-full bg-white p-4 text-paw-muted">No metrics yet.</p>
                @endforelse
            </section>

            <div class="grid min-w-0 gap-8 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
                <div class="grid min-w-0 content-start gap-8">
                    <section class="min-w-0" aria-labelledby="upcoming-bookings">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <h2 id="upcoming-bookings" class="text-2xl font-bold">Consultation queue</h2>
                                <p class="mt-1 text-sm text-paw-muted">The client sees who currently handles the appointment.</p>
                            </div>
                        </div>
                        <div class="mt-4 max-w-full min-w-0 overflow-x-auto">
                            <table class="w-full min-w-[44rem] border-collapse text-left text-sm">
                                <thead>
                                    <tr class="border-b border-paw-line text-xs uppercase text-paw-muted">
                                        <th class="px-3 py-2">Time</th>
                                        <th class="px-3 py-2">Client and pet</th>
                                        <th class="px-3 py-2">Service</th>
                                        <th class="px-3 py-2">Status</th>
                                        <th class="px-3 py-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bookings as $booking)
                                        <tr class="border-b border-paw-line">
                                            <td class="px-3 py-3 font-semibold">{{ $booking['starts_at'] }}</td>
                                            <td class="px-3 py-3">{{ $booking['client_name'] }}<span class="block text-paw-muted">{{ $booking['pet_name'] }} · {{ $booking['pet_species'] }}</span></td>
                                            <td class="px-3 py-3">{{ $booking['service'] }}<span class="block text-paw-muted">{{ $booking['format'] }}</span></td>
                                            <td class="px-3 py-3">{{ $booking['status'] }}<span class="block text-paw-muted">{{ $booking['payment_status'] }}</span></td>
                                            <td class="px-3 py-3 text-right"><a href="{{ route('bookings.show', $booking['reference']) }}" class="font-bold text-paw-leaf">Open</a></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-3 py-6 text-center text-paw-muted">No consultation requests yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="min-w-0" aria-labelledby="workspace-services">
                        <h2 id="workspace-services" class="text-2xl font-bold">Published services</h2>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            @forelse ($services as $service)
                                <x-object.service-card :service="$service" />
                            @empty
                                <p class="text-paw-muted">No services have been configured.</p>
                            @endforelse
                        </div>
                    </section>
                </div>

                <aside class="grid min-w-0 content-start gap-7">
                    <section aria-labelledby="workspace-verification">
                        <h2 id="workspace-verification" class="text-xl font-bold">Verification status</h2>
                        <div class="mt-4">
                            <x-object.verification-list :items="$expert['verification_items']" :expires="$expert['verification_expires']" />
                        </div>
                    </section>

                    <section class="border-y border-paw-line py-5" aria-labelledby="workspace-credentials">
                        <h2 id="workspace-credentials" class="text-xl font-bold">Submitted credentials</h2>
                        <div class="mt-3 grid gap-3 text-sm">
                            @forelse ($credentials as $credential)
                                <article>
                                    <h3 class="font-bold">{{ $credential['title'] }}</h3>
                                    <p class="text-paw-muted">{{ $credential['issuer'] }} · {{ $credential['status'] }}</p>
                                    @if ($credential['expires_at'])<p class="text-xs text-paw-muted">Expires {{ $credential['expires_at'] }}</p>@endif
                                </article>
                            @empty
                                <p class="text-paw-muted">No credentials submitted.</p>
                            @endforelse
                        </div>
                    </section>

                    <section aria-labelledby="privacy-note">
                        <h2 id="privacy-note" class="text-xl font-bold">Privacy boundary</h2>
                        <p class="mt-2 text-sm leading-6 text-paw-muted">Profile viewers, their searches, home addresses, GPS history, unrelated pets, and hidden medical records are never exposed in workspace analytics.</p>
                    </section>
                </aside>
            </div>
        </div>
    @endif
</x-layout.app-shell>
