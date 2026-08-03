<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="device-detail-header">
            <div class="device-detail-header__identity">
                <span><x-ui-icon :name="$device['icon']" size="2xl" /></span>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('devices.index') }}" class="inline-flex items-center gap-1 text-sm font-bold text-paw-leaf">
                            <x-ui-icon name="arrow-left" size="sm" />
                            <span>{{ __('ui.smart_devices_228fd3f770') }}</span>
                        </a>
                        <span class="text-paw-line">/</span>
                        <x-status-badge label="{{ __('ui.private_c63eb6720c') }}" icon="lock-keyhole" tone="surface" />
                    </div>
                    <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ $device['name'] }}</h1>
                    <p class="mt-2 text-paw-muted">{{ $device['type_label'] }} · {{ $device['brand_model'] ?: __('ui.manual_device_record_a4104fd24f') }} · {{ $device['serial'] ?: 'serial not recorded' }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-action-control :href="$device['manage_url']" label="{{ __('ui.rules_access_5f59f5debe') }}" icon="settings-2" />
                <x-action-control :href="$device['lost_found_url']" label="{{ __('ui.lost_pet_center_a82025ff33') }}" icon="search" variant="primary" />
            </div>
        </header>

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_device_action_was_not_saved_a87ecd9fff') }}</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>{{ __('ui.validation_failed_fa0dce7e0b') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <section class="device-status-strip" aria-label="{{ __('ui.current_device_status_31c25170b2') }}">
            <div>
                <span class="device-status-strip__icon"><x-ui-icon name="radio" size="lg" /></span>
                <small>{{ __('ui.status_920e413c7d') }}</small>
                <strong>{{ $device['status_label'] }}</strong>
            </div>
            <div>
                <span class="device-status-strip__icon"><x-ui-icon name="wifi" size="lg" /></span>
                <small>{{ __('ui.connection_639a40e82b') }}</small>
                <strong>{{ $device['connection_label'] }}</strong>
            </div>
            <div>
                <span class="device-status-strip__icon"><x-ui-icon name="battery-medium" size="lg" /></span>
                <small>{{ __('ui.battery_dfcb7c1619') }}</small>
                <strong>{{ $device['battery_label'] }}</strong>
            </div>
            <div>
                <span class="device-status-strip__icon"><x-ui-icon name="clock-3" size="lg" /></span>
                <small>{{ __('ui.last_signal_6f2cfbf3ce') }}</small>
                <strong>{{ $device['last_seen'] }}</strong>
            </div>
        </section>

        <div class="device-dashboard">
            <div class="grid min-w-0 content-start gap-5">
                @if ($device['type'] === 'gps-tracker')
                    <section class="device-panel" aria-labelledby="device-location-title">
                        <div class="device-panel__heading">
                            <div>
                                <p>{{ __('ui.owner_only_location_6a8732fbbf') }}</p>
                                <h2 id="device-location-title">{{ __('ui.last_known_position_45a6c99e28') }}</h2>
                            </div>
                            <x-status-badge :label="$device['location_accuracy']" icon="crosshair" tone="surface" />
                        </div>
                        <div class="device-location">
                            <div class="device-location__map" aria-label="{{ __('presentation.private_map_preview', ['location' => $device['location_label']]) }}">
                                <span class="device-location__route"></span>
                                <span class="device-location__marker"><x-ui-icon name="paw-print" size="lg" /></span>
                                <span class="device-location__home"><x-ui-icon name="house" size="sm" /></span>
                            </div>
                            <dl>
                                <div><dt>{{ __('ui.exact_point_bab90d06d4') }}</dt><dd>{{ $device['exact_location'] ?: __('ui.no_current_coordinates_d53a111f93') }}</dd></div>
                                <div><dt>{{ __('ui.installed_last_area_62990fb5df') }}</dt><dd>{{ $device['location_label'] }}</dd></div>
                                <div><dt>{{ __('ui.location_received_afd13ab77f') }}</dt><dd>{{ $device['last_location'] }}</dd></div>
                                <div><dt>{{ __('ui.mode_5e23ec6a30') }}</dt><dd>{{ $device['operating_mode'] }}</dd></div>
                            </dl>
                        </div>
                    </section>
                @endif

                <section class="device-panel" aria-labelledby="device-events-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('ui.human_review_queue_2dd715e9d0') }}</p>
                            <h2 id="device-events-title">{{ __('ui.recent_events_8ecce94dc3') }}</h2>
                        </div>
                        <span class="text-sm font-semibold text-paw-muted">{{ __('presentation.shown_count', ['count' => count($events)]) }}</span>
                    </div>
                    <x-device-event-list :events="$events" :device="$device" />
                </section>

                <section class="device-panel" aria-labelledby="device-readings-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('ui.source_aware_telemetry_5f88e716e9') }}</p>
                            <h2 id="device-readings-title">{{ __('ui.latest_readings_5f0d3d253c') }}</h2>
                        </div>
                    </div>
                    <p class="device-panel__note">{{ __('ui.a_shared_source_remains_unassigned_until_a_person_98f394a271') }}</p>
                    <x-device-reading-table :readings="$readings" :device="$device" />
                </section>
            </div>

            <aside class="grid min-w-0 content-start gap-5">
                <section class="device-panel" aria-labelledby="device-control-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('ui.idempotent_command_2b03806393') }}</p>
                            <h2 id="device-control-title">{{ __('ui.device_control_1c3da27ae2') }}</h2>
                        </div>
                        <x-ui-icon name="shield-check" size="lg" class="text-paw-leaf" />
                    </div>
                    <form method="POST" action="{{ route('devices.commands.store', $device['slug']) }}" class="device-form-compact">
                        @csrf
                        <input type="hidden" name="idempotency_key" value="{{ $command_idempotency_key }}">
                        <label>
                            {{ __('ui.command_713166971d') }}
                            <select name="command_type" required>
                                @forelse ($command_options as $command)
                                    <option value="{{ $command['value'] }}">{{ $command['label'] }}</option>
                                @empty
                                    <option value="">{{ __('ui.no_remote_commands_c3ab88273c') }}</option>
                                @endforelse
                            </select>
                        </label>
                        @if ($device['type'] === 'feeder')
                            <label>
                                {{ __('ui.portion_in_grams_6136d07a42') }}
                                <input type="number" name="portion_grams" min="1" max="1000" value="20">
                            </label>
                        @endif
                        <label>
                            {{ __('ui.reason_or_context_1d108ab24a') }}
                            <input name="reason" maxlength="500" placeholder="{{ __('ui.manual_owner_check_aacd44ff7d') }}">
                        </label>
                        <label class="device-check">
                            <input type="checkbox" name="confirmed" value="1">
                            <span>{{ __('ui.confirm_high_impact_command_42b24522e4') }}</span>
                        </label>
                        <button class="action action--primary" type="submit">
                            <x-ui-icon name="send" />
                            <span>{{ __('ui.send_once_477fa53d9a') }}</span>
                        </button>
                    </form>
                </section>

                <section class="device-panel" aria-labelledby="device-source-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('ui.identity_boundaries_0d8ce3b1b8') }}</p>
                            <h2 id="device-source-title">{{ __('ui.assigned_pets_dd50d74ca4') }}</h2>
                        </div>
                    </div>
                    <div class="device-assignment-list">
                        @forelse ($assignments as $assignment)
                            <article>
                                <span><x-ui-icon name="paw-print" size="sm" /></span>
                                <div>
                                    <h3>{{ $assignment['pet_name'] }}</h3>
                                    <p>{{ $assignment['identification'] }} · {{ $assignment['confidence'] }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_pet_assignment_readings_stay_unassigned_778ebf6526') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="device-panel" aria-labelledby="device-technical-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('ui.technical_status_c44a5a72b5') }}</p>
                            <h2 id="device-technical-title">{{ __('ui.diagnostics_268f14bbfe') }}</h2>
                        </div>
                    </div>
                    <dl class="device-definition-list">
                        <div><dt>{{ __('ui.connection_639a40e82b') }}</dt><dd>{{ $device['connection_type'] }}</dd></div>
                        <div><dt>{{ __('devices.lifecycle.provider') }}</dt><dd>{{ $device['provider_status'] }}</dd></div>
                        <div><dt>{{ __('ui.firmware_c2a314c3b3') }}</dt><dd>{{ $device['firmware_version'] }}</dd></div>
                        <div><dt>{{ __('ui.signal_1e9806e422') }}</dt><dd>{{ $device['signal_strength'] ?? __('ui.not_reported_adadface01') }}</dd></div>
                        <div><dt>{{ __('ui.local_fallback_0ef5a6159f') }}</dt><dd>{{ $device['supports_local_operation'] ? __('ui.available_e674447337') : __('ui.not_confirmed_bc1c29a467') }}</dd></div>
                        <div><dt>{{ __('ui.cloud_dependency_22a834d986') }}</dt><dd>{{ $device['requires_cloud'] ? __('ui.required_4850b174b7') : __('ui.core_functions_local_f8de935179') }}</dd></div>
                        <div><dt>{{ __('devices.retention.location') }}</dt><dd>{{ trans_choice('devices.retention.days', $device['location_retention_days'], ['count' => $device['location_retention_days']]) }}</dd></div>
                        <div><dt>{{ __('devices.retention.media') }}</dt><dd>{{ trans_choice('devices.retention.days', $device['media_retention_days'], ['count' => $device['media_retention_days']]) }}</dd></div>
                        <div><dt>{{ __('devices.retention.telemetry') }}</dt><dd>{{ trans_choice('devices.retention.days', $device['telemetry_retention_days'], ['count' => $device['telemetry_retention_days']]) }}</dd></div>
                        <div>
                            <dt>{{ __('devices.lifecycle.safety_interlock') }}</dt>
                            <dd>{{ $device['has_fresh_safety_state'] ? __('devices.lifecycle.safety_fresh') : __('devices.lifecycle.safety_missing_or_stale') }}</dd>
                        </div>
                    </dl>
                </section>

                @if ($safe_zones !== [])
                    <section class="device-panel" aria-labelledby="device-zones-title">
                        <div class="device-panel__heading">
                            <div>
                                <p>{{ __('ui.exact_geometry_encrypted_1f0b6f6a6a') }}</p>
                                <h2 id="device-zones-title">{{ __('ui.safe_zones_7092f20d84') }}</h2>
                            </div>
                        </div>
                        <div class="device-compact-list">
                            @forelse ($safe_zones as $zone)
                                <article>
                                    <x-ui-icon name="map-pinned" size="sm" />
                                    <div>
                                        <h3>{{ $zone['name'] }}</h3>
                                        <p>{{ $zone['public_area_label'] }} · {{ $zone['radius'] ?: $zone['shape'] }}</p>
                                        <small>{{ $zone['exit_delay'] }}</small>
                                    </div>
                                </article>
                            @empty
                                <p>{{ __('ui.no_zones_a39700efdc') }}</p>
                            @endforelse
                        </div>
                    </section>
                @endif

                <section class="device-panel" aria-labelledby="device-reading-form-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('ui.manual_import_test_776404428c') }}</p>
                            <h2 id="device-reading-form-title">{{ __('ui.record_a_reading_e82c73ca1b') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('devices.readings.store', $device['slug']) }}" class="device-form-compact">
                        @csrf
                        <input type="hidden" name="external_event_id" value="{{ $reading_external_id }}">
                        <input type="hidden" name="timezone" value="Europe/Vilnius">
                        <input type="hidden" name="confidence" value="medium">
                        <label>
                            {{ __('ui.metric_2d275a7491') }}
                            <select name="metric_type" required>
                                @forelse ($metric_options as $metric)
                                    <option value="{{ $metric['value'] }}">{{ $metric['label'] }} ({{ $metric['unit'] }})</option>
                                @empty
                                    <option value="">{{ __('ui.no_metrics_fd195330a4') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <label>
                            {{ __('ui.pet_8f0d1b30eb') }}
                            <select name="pet_profile_key">
                                <option value="">{{ __('ui.unknown_shared_e91fada2b0') }}</option>
                                @forelse ($assignments as $assignment)
                                    <option value="{{ $assignment['pet_profile_key'] }}">{{ $assignment['pet_name'] }}</option>
                                @empty
                                    <option value="" disabled>{{ __('ui.no_assigned_pets_740f9d089b') }}</option>
                                @endforelse
                            </select>
                        </label>
                        <div class="device-form-grid device-form-grid--compact">
                            <label>{{ __('ui.value_8e37953d23') }}<input type="number" step="any" name="numeric_value"></label>
                            <label>{{ __('ui.unit_4e545960f1') }}<input name="unit" maxlength="40"></label>
                        </div>
                        <label>{{ __('ui.recorded_at_e4cea0827a') }}<input type="datetime-local" name="recorded_at" value="{{ $now_local }}" required></label>
                        <button class="action" type="submit"><x-ui-icon name="plus" /><span>{{ __('ui.add_unverified_reading_3164d45d9b') }}</span></button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
