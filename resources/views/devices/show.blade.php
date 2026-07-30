<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="device-detail-header">
            <div class="device-detail-header__identity">
                <span><x-dynamic-component :component="'lucide-'.$device['icon']" class="size-8" aria-hidden="true" /></span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('devices.index') }}" class="text-sm font-bold text-paw-leaf">Smart devices</a>
                        <span class="text-paw-line">/</span>
                        <x-status-badge label="Private" icon="lock-keyhole" tone="surface" />
                    </div>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $device['name'] }}</h1>
                    <p class="mt-2 text-paw-muted">{{ $device['type_label'] }} · {{ $device['brand_model'] ?: 'Manual device record' }} · {{ $device['serial'] ?: 'serial not recorded' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control :href="$device['manage_url']" label="Rules & access" icon="settings-2" />
                <x-action-control :href="$device['lost_found_url']" label="Lost-pet center" icon="search" variant="primary" />
            </div>
        </header>

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>The device action was not saved</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>Validation failed.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <section class="device-status-strip" aria-label="Current device status">
            <div>
                <span class="device-status-strip__icon"><x-lucide-radio class="size-5" aria-hidden="true" /></span>
                <small>Status</small>
                <strong>{{ $device['status_label'] }}</strong>
            </div>
            <div>
                <span class="device-status-strip__icon"><x-lucide-wifi class="size-5" aria-hidden="true" /></span>
                <small>Connection</small>
                <strong>{{ $device['connection_label'] }}</strong>
            </div>
            <div>
                <span class="device-status-strip__icon"><x-lucide-battery-medium class="size-5" aria-hidden="true" /></span>
                <small>Battery</small>
                <strong>{{ $device['battery_label'] }}</strong>
            </div>
            <div>
                <span class="device-status-strip__icon"><x-lucide-clock-3 class="size-5" aria-hidden="true" /></span>
                <small>Last signal</small>
                <strong>{{ $device['last_seen'] }}</strong>
            </div>
        </section>

        <div class="device-dashboard">
            <main class="grid min-w-0 content-start gap-5">
                @if ($device['type'] === 'gps-tracker')
                    <section class="device-panel" aria-labelledby="device-location-title">
                        <div class="device-panel__heading">
                            <div>
                                <p>Owner-only location</p>
                                <h2 id="device-location-title">Last known position</h2>
                            </div>
                            <x-status-badge :label="$device['location_accuracy']" icon="crosshair" tone="surface" />
                        </div>
                        <div class="device-location">
                            <div class="device-location__map" aria-label="Private map preview for {{ $device['location_label'] }}">
                                <span class="device-location__route"></span>
                                <span class="device-location__marker"><x-lucide-paw-print class="size-5" aria-hidden="true" /></span>
                                <span class="device-location__home"><x-lucide-house class="size-4" aria-hidden="true" /></span>
                            </div>
                            <dl>
                                <div><dt>Exact point</dt><dd>{{ $device['exact_location'] ?: 'No current coordinates' }}</dd></div>
                                <div><dt>Installed / last area</dt><dd>{{ $device['location_label'] }}</dd></div>
                                <div><dt>Location received</dt><dd>{{ $device['last_location'] }}</dd></div>
                                <div><dt>Mode</dt><dd>{{ $device['operating_mode'] }}</dd></div>
                            </dl>
                        </div>
                    </section>
                @endif

                <section class="device-panel" aria-labelledby="device-events-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>Human review queue</p>
                            <h2 id="device-events-title">Recent events</h2>
                        </div>
                        <span class="text-sm font-semibold text-paw-muted">{{ count($events) }} shown</span>
                    </div>
                    <x-device-event-list :events="$events" :device="$device" />
                </section>

                <section class="device-panel" aria-labelledby="device-readings-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>Source-aware telemetry</p>
                            <h2 id="device-readings-title">Latest readings</h2>
                        </div>
                    </div>
                    <p class="device-panel__note">A shared source remains unassigned until a person or supported identifier confirms the pet. Device measurements are non-clinical unless explicitly verified.</p>
                    <x-device-reading-table :readings="$readings" :device="$device" />
                </section>
            </main>

            <aside class="grid min-w-0 content-start gap-5">
                <section class="device-panel" aria-labelledby="device-control-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>Idempotent command</p>
                            <h2 id="device-control-title">Device control</h2>
                        </div>
                        <x-lucide-shield-check class="size-5 text-paw-leaf" aria-hidden="true" />
                    </div>
                    <form method="POST" action="{{ route('devices.commands.store', $device['slug']) }}" class="device-form-compact">
                        @csrf
                        <input type="hidden" name="idempotency_key" value="{{ $command_idempotency_key }}">
                        <label>
                            Command
                            <select name="command_type" required>
                                @forelse ($command_options as $command)
                                    <option value="{{ $command['value'] }}">{{ $command['label'] }}</option>
                                @empty
                                    <option value="">No remote commands</option>
                                @endforelse
                            </select>
                        </label>
                        @if ($device['type'] === 'feeder')
                            <label>
                                Portion in grams
                                <input type="number" name="portion_grams" min="1" max="1000" value="20">
                            </label>
                        @endif
                        <label>
                            Reason or context
                            <input name="reason" maxlength="500" placeholder="Manual owner check">
                        </label>
                        <label class="device-check">
                            <input type="checkbox" name="confirmed" value="1">
                            <span>Confirm high-impact command</span>
                        </label>
                        <button class="action action--primary" type="submit">
                            <x-lucide-send class="icon" aria-hidden="true" />
                            <span>Send once</span>
                        </button>
                    </form>
                </section>

                <section class="device-panel" aria-labelledby="device-source-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>Identity boundaries</p>
                            <h2 id="device-source-title">Assigned pets</h2>
                        </div>
                    </div>
                    <div class="device-assignment-list">
                        @forelse ($assignments as $assignment)
                            <article>
                                <span><x-lucide-paw-print class="size-4" aria-hidden="true" /></span>
                                <div>
                                    <h3>{{ $assignment['pet_name'] }}</h3>
                                    <p>{{ $assignment['identification'] }} · {{ $assignment['confidence'] }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No pet assignment. Readings stay unassigned.</p>
                        @endforelse
                    </div>
                </section>

                <section class="device-panel" aria-labelledby="device-technical-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>Technical status</p>
                            <h2 id="device-technical-title">Diagnostics</h2>
                        </div>
                    </div>
                    <dl class="device-definition-list">
                        <div><dt>Connection</dt><dd>{{ $device['connection_type'] }}</dd></div>
                        <div><dt>Firmware</dt><dd>{{ $device['firmware_version'] }}</dd></div>
                        <div><dt>Signal</dt><dd>{{ $device['signal_strength'] ?? 'Not reported' }}</dd></div>
                        <div><dt>Local fallback</dt><dd>{{ $device['supports_local_operation'] ? 'Available' : 'Not confirmed' }}</dd></div>
                        <div><dt>Cloud dependency</dt><dd>{{ $device['requires_cloud'] ? 'Required' : 'Core functions local' }}</dd></div>
                    </dl>
                </section>

                @if ($safe_zones !== [])
                    <section class="device-panel" aria-labelledby="device-zones-title">
                        <div class="device-panel__heading">
                            <div>
                                <p>Exact geometry encrypted</p>
                                <h2 id="device-zones-title">Safe zones</h2>
                            </div>
                        </div>
                        <div class="device-compact-list">
                            @forelse ($safe_zones as $zone)
                                <article>
                                    <x-lucide-map-pinned class="size-4" aria-hidden="true" />
                                    <div>
                                        <h3>{{ $zone['name'] }}</h3>
                                        <p>{{ $zone['public_area_label'] }} · {{ $zone['radius'] ?: $zone['shape'] }}</p>
                                        <small>{{ $zone['exit_delay'] }}</small>
                                    </div>
                                </article>
                            @empty
                                <p>No zones.</p>
                            @endforelse
                        </div>
                    </section>
                @endif

                <section class="device-panel" aria-labelledby="device-reading-form-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>Manual import test</p>
                            <h2 id="device-reading-form-title">Record a reading</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('devices.readings.store', $device['slug']) }}" class="device-form-compact">
                        @csrf
                        <input type="hidden" name="external_event_id" value="{{ $reading_external_id }}">
                        <input type="hidden" name="timezone" value="Europe/Vilnius">
                        <input type="hidden" name="confidence" value="medium">
                        <label>
                            Metric
                            <select name="metric_type" required>
                                @forelse ($metric_options as $metric)
                                    <option value="{{ $metric['value'] }}">{{ $metric['label'] }} ({{ $metric['unit'] }})</option>
                                @empty
                                    <option value="">No metrics</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            Pet
                            <select name="pet_profile_key">
                                <option value="">Unknown / shared</option>
                                @forelse ($assignments as $assignment)
                                    <option value="{{ $assignment['pet_profile_key'] }}">{{ $assignment['pet_name'] }}</option>
                                @empty
                                    <option value="" disabled>No assigned pets</option>
                                @endforelse
                            </select>
                        </label>
                        <div class="device-form-grid device-form-grid--compact">
                            <label>Value<input type="number" step="any" name="numeric_value"></label>
                            <label>Unit<input name="unit" maxlength="40"></label>
                        </div>
                        <label>Recorded at<input type="datetime-local" name="recorded_at" value="{{ $now_local }}" required></label>
                        <button class="action" type="submit"><x-lucide-plus class="icon" aria-hidden="true" /><span>Add unverified reading</span></button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
