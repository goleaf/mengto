<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="device-detail-header">
            <div>
                <a href="{{ $device['show_url'] }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                    {{ $device['name'] }}
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.owner_controls_c468d0a8ad') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.rules_access_and_audit_f547744ca7') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.least_privilege_bdec4748d6') }}" icon="shield-check" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>{{ __('ui.the_change_was_not_saved_5515ca43db') }}</strong>
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

        @if (session('device_access_url'))
            <section class="device-access-link" aria-labelledby="device-access-link-title">
                <div>
                    <p>{{ __('ui.shown_once_22548d041f') }}</p>
                    <h2 id="device-access-link-title">{{ __('ui.temporary_device_link_cf6a3b5be0') }}</h2>
                    <code>{{ session('device_access_url') }}</code>
                </div>
                <x-lucide-link class="size-6" aria-hidden="true" />
            </section>
        @endif

        <nav class="device-anchor-nav" aria-label="{{ __('ui.device_management_1a7c41d58a') }}">
            <a href="#retention"><x-lucide-history class="size-4" aria-hidden="true" /> {{ __('devices.retention.heading') }}</a>
            <a href="#lifecycle"><x-lucide-wrench class="size-4" aria-hidden="true" /> {{ __('devices.lifecycle.heading') }}</a>
            <a href="#automations"><x-lucide-workflow class="size-4" aria-hidden="true" /> {{ __('ui.automations_ad1fb9ec0c') }}</a>
            <a href="#access"><x-lucide-key-round class="size-4" aria-hidden="true" /> {{ __('ui.access_ec5ba0abb7') }}</a>
            <a href="#audit"><x-lucide-shield-check class="size-4" aria-hidden="true" /> {{ __('ui.audit_bb6aea2873') }}</a>
            @if ($device['type'] === 'gps-tracker')
                <a href="#safe-zone"><x-lucide-map-pinned class="size-4" aria-hidden="true" /> {{ __('ui.safe_zone_2333d88d6a') }}</a>
            @endif
        </nav>

        <div class="device-manage-grid">
            <main class="grid min-w-0 content-start gap-5">
                <section id="retention" class="device-form-section">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('devices.retention.eyebrow') }}</p>
                            <h2>{{ __('devices.retention.heading') }}</h2>
                        </div>
                    </div>
                    <p class="device-panel__note">{{ __('devices.retention.description') }}</p>
                    <form method="POST" action="{{ route('devices.retention.update', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        @method('PUT')
                        <div class="device-form-grid">
                            <label>
                                {{ __('devices.retention.location') }}
                                <select name="location_retention_days" required>
                                    @forelse ($retention_options['location'] as $option)
                                        <option value="{{ $option['value'] }}" @selected((int) old('location_retention_days', $device['location_retention_days']) === $option['value'])>{{ $option['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable_ca18449697') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.retention.media') }}
                                <select name="media_retention_days" required>
                                    @forelse ($retention_options['media'] as $option)
                                        <option value="{{ $option['value'] }}" @selected((int) old('media_retention_days', $device['media_retention_days']) === $option['value'])>{{ $option['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable_ca18449697') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.retention.telemetry') }}
                                <select name="telemetry_retention_days" required>
                                    @forelse ($retention_options['telemetry'] as $option)
                                        <option value="{{ $option['value'] }}" @selected((int) old('telemetry_retention_days', $device['telemetry_retention_days']) === $option['value'])>{{ $option['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable_ca18449697') }}</option>
                                    @endforelse
                                </select>
                            </label>
                        </div>
                        <button class="action" type="submit">
                            <x-lucide-save class="icon" aria-hidden="true" />
                            <span>{{ __('devices.retention.save') }}</span>
                        </button>
                    </form>
                </section>

                <section id="lifecycle" class="device-form-section">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('devices.lifecycle.eyebrow') }}</p>
                            <h2>{{ __('devices.lifecycle.heading') }}</h2>
                        </div>
                    </div>
                    <p class="device-panel__note">{{ __('devices.lifecycle.description') }}</p>
                    <form method="POST" action="{{ route('devices.lifecycle.store', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="device-form-grid">
                            <label>
                                {{ __('devices.lifecycle.type') }}
                                <select name="kind" required>
                                    @forelse ($lifecycle_kinds as $kind)
                                        <option value="{{ $kind['value'] }}">{{ $kind['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable_ca18449697') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.lifecycle.status_label') }}
                                <select name="status" required>
                                    @forelse ($lifecycle_statuses as $status)
                                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable_ca18449697') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.lifecycle.severity') }}
                                <select name="severity" required>
                                    @forelse ($lifecycle_severities as $severity)
                                        <option value="{{ $severity['value'] }}">{{ $severity['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable_ca18449697') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>{{ __('devices.lifecycle.effective_at') }}<input type="datetime-local" name="effective_at" value="{{ old('effective_at', $lifecycle_effective_at) }}" required></label>
                            <label>{{ __('devices.lifecycle.version_from') }}<input name="version_from" maxlength="80" value="{{ old('version_from', $device['firmware_version']) }}"></label>
                            <label>{{ __('devices.lifecycle.version_to') }}<input name="version_to" maxlength="80" value="{{ old('version_to') }}"></label>
                            <label>{{ __('devices.lifecycle.reference') }}<input name="reference" maxlength="255" value="{{ old('reference') }}"></label>
                        </div>
                        <label>{{ __('devices.lifecycle.note') }}<textarea name="note" rows="3" maxlength="2000">{{ old('note') }}</textarea></label>
                        <div class="device-check-grid">
                            <label class="device-check device-check--boxed">
                                <input type="checkbox" name="consequences_reviewed" value="1">
                                <span>{{ __('devices.lifecycle.consequences_reviewed') }}</span>
                            </label>
                            <label class="device-check device-check--boxed">
                                <input type="checkbox" name="block_remote_control" value="1">
                                <span>{{ __('devices.lifecycle.block_remote_control') }}</span>
                            </label>
                        </div>
                        <button class="action" type="submit">
                            <x-lucide-clipboard-check class="icon" aria-hidden="true" />
                            <span>{{ __('devices.lifecycle.record') }}</span>
                        </button>
                    </form>
                </section>

                <section class="device-panel" aria-labelledby="device-lifecycle-history-title">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('devices.lifecycle.history_eyebrow') }}</p>
                            <h2 id="device-lifecycle-history-title">{{ __('devices.lifecycle.history') }}</h2>
                        </div>
                    </div>
                    <div class="device-rule-list">
                        @forelse ($lifecycle_records as $record)
                            <article>
                                <div>
                                    <h3>{{ $record['kind'] }} · {{ $record['status'] }}</h3>
                                    <p>{{ $record['severity'] }} · {{ $record['effective_at'] }}</p>
                                    @if ($record['version'])
                                        <small>{{ $record['version'] }}</small>
                                    @endif
                                    @if ($record['note'])
                                        <small>{{ $record['note'] }}</small>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('devices.lifecycle.empty') }}</p>
                        @endforelse
                    </div>
                </section>

                <section id="automations" class="device-form-section">
                    <div class="device-panel__heading">
                        <div>
                            <p>{{ __('ui.test_before_enabling_3a27d5afb1') }}</p>
                            <h2>{{ __('ui.create_an_automation_30ea569c1a') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('devices.automations.store', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="device-form-grid">
                            <label>{{ __('ui.name_dcd1d5223f') }}<input name="name" maxlength="140" required placeholder="{{ __('ui.notify_when_device_goes_offline_5576bd71ac') }}"></label>
                            <label>
                                {{ __('ui.trigger_8b9c643731') }}
                                <select name="trigger_type" required>
                                    @forelse ($automation_triggers as $trigger)
                                        <option value="{{ $trigger['value'] }}">{{ $trigger['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.no_triggers_ea3c76042e') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>{{ __('ui.threshold_0da627ada4') }}<input type="number" step="any" name="trigger_value"></label>
                            <label>
                                {{ __('ui.home_mode_6e0bd795bb') }}
                                <select name="condition_mode">
                                    @forelse ($automation_modes as $mode)
                                        <option value="{{ $mode['value'] }}">{{ $mode['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.any_2b505597da') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.action_64cff1319d') }}
                                <select name="action_type">
                                    @forelse ($automation_actions as $action)
                                        <option value="{{ $action['value'] }}">{{ $action['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.no_actions_219e734fd9') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.priority_d60dbba079') }}
                                <select name="priority">
                                    @forelse ($automation_priorities as $priority)
                                        <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.normal_a7248eeb45') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.initial_status_4484edc323') }}
                                <select name="status"><option value="draft">{{ __('ui.draft_ebf12ef47c') }}</option><option value="enabled">{{ __('ui.enabled_92c1cdfdf4') }}</option></select>
                            </label>
                            <label>{{ __('ui.maximum_runs_hour_877518d93d') }}<input type="number" name="max_runs_per_hour" min="1" max="12" value="2" required></label>
                            <label>{{ __('ui.cooldown_seconds_20dde92b9c') }}<input type="number" name="cooldown_seconds" min="30" max="86400" value="300" required></label>
                        </div>
                        <label class="device-check device-check--boxed">
                            <input type="checkbox" name="safety_acknowledged" value="1" required>
                            <span>{{ __('ui.i_reviewed_the_action_cooldown_fallback_and_understand_1bf9807d8c') }}</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-lucide-workflow class="icon" aria-hidden="true" /><span>{{ __('ui.save_guarded_rule_b5b05434db') }}</span></button>
                    </form>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading">
                        <div><p>{{ __('ui.current_rules_78da99d6a7') }}</p><h2>{{ __('ui.automation_register_755d812325') }}</h2></div>
                    </div>
                    <div class="device-rule-list">
                        @forelse ($automations as $automation)
                            <article>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3>{{ $automation['name'] }}</h3>
                                        <x-status-badge :label="$automation['status_label']" :tone="$automation['status_tone']" />
                                    </div>
                                    <p>{{ $automation['trigger'] }} → {{ $automation['action'] }}</p>
                                    <small>{{ $automation['condition'] }} · {{ $automation['max_runs'] }} · {{ $automation['cooldown'] }}</small>
                                </div>
                                <form method="POST" action="{{ route('devices.automations.test', [$device['slug'], $automation['id']]) }}">
                                    @csrf
                                    <button class="action" type="submit"><x-lucide-flask-conical class="icon" aria-hidden="true" /><span>{{ __('ui.simulate_66154a0841') }}</span></button>
                                </form>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_automations_yet_rules_never_run_until_explicitly_d134edd34f') }}</p>
                        @endforelse
                    </div>
                </section>

                @if ($device['type'] === 'gps-tracker')
                    <section id="safe-zone" class="device-form-section">
                        <div class="device-panel__heading">
                            <div><p>{{ __('ui.coordinates_encrypted_26a5cc1a74') }}</p><h2>{{ __('ui.add_a_safe_zone_0bfe6f8618') }}</h2></div>
                        </div>
                        <form method="POST" action="{{ route('devices.safe-zones.store', $device['slug']) }}" class="grid gap-4">
                            @csrf
                            <div class="device-form-grid">
                                <label>{{ __('ui.name_dcd1d5223f') }}<input name="name" required maxlength="120" placeholder="{{ __('ui.home_boundary_f85a717070') }}"></label>
                                <label>{{ __('ui.shape_e0e492707f') }}<select name="shape"><option value="circle">{{ __('ui.circle_b93d3bcecf') }}</option><option value="polygon">{{ __('ui.polygon_anchor_13e3f25961') }}</option></select></label>
                                <label>{{ __('ui.public_area_label_5d9b790916') }}<input name="public_area_label" required maxlength="160" placeholder="{{ __('ui.home_area_df8f366499') }}"></label>
                                <label>{{ __('ui.latitude_238a676da4') }}<input type="number" step="0.000001" name="latitude" min="-90" max="90" required></label>
                                <label>{{ __('ui.longitude_6d80458fd1') }}<input type="number" step="0.000001" name="longitude" min="-180" max="180" required></label>
                                <label>{{ __('ui.radius_meters_1b41d657bf') }}<input type="number" name="radius_meters" min="20" max="50000" value="120"></label>
                                <label>{{ __('ui.exit_confirmation_seconds_a0b5f5eaa1') }}<input type="number" name="exit_delay_seconds" min="0" max="900" value="45" required></label>
                                <label>{{ __('ui.accuracy_threshold_meters_16ed6d8c44') }}<input type="number" name="accuracy_threshold_meters" min="5" max="1000" value="35" required></label>
                            </div>
                            <div class="device-check-grid">
                                <label class="device-check"><input type="checkbox" name="is_home" value="1"><span>{{ __('ui.home_privacy_zone_1ccca16d37') }}</span></label>
                                <label class="device-check"><input type="checkbox" name="always_active" value="1"><span>{{ __('ui.always_active_8c559f48ea') }}</span></label>
                            </div>
                            <button class="action" type="submit"><x-lucide-map-pinned class="icon" aria-hidden="true" /><span>{{ __('ui.save_private_zone_1aae110811') }}</span></button>
                        </form>
                    </section>
                @endif
            </main>

            <aside class="grid min-w-0 content-start gap-5">
                <section id="access" class="device-form-section">
                    <div class="device-panel__heading">
                        <div><p>{{ __('ui.time_bound_and_revocable_a9be89ec6b') }}</p><h2>{{ __('ui.temporary_access_7059688673') }}</h2></div>
                    </div>
                    <form method="POST" action="{{ route('devices.access.store', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>{{ __('ui.recipient_51fac985e9') }}<input name="recipient_name" maxlength="120" required placeholder="{{ __('ui.alex_carter_805f38f620') }}"></label>
                        <label>
                            {{ __('ui.role_14736a2eb9') }}
                            <select name="recipient_role">
                                <option value="sitter">{{ __('ui.sitter_d26540f1d7') }}</option>
                                <option value="co-owner">{{ __('ui.co_owner_f3027e079c') }}</option>
                                <option value="veterinarian">{{ __('ui.veterinarian_38d6a38c0c') }}</option>
                                <option value="trainer">{{ __('ui.trainer_9f085ee951') }}</option>
                                <option value="support">{{ __('ui.technical_support_a9cea7b91c') }}</option>
                            </select>
                        </label>
                        <label>{{ __('ui.access_label_14b6448467') }}<input name="label" maxlength="140" required placeholder="{{ __('ui.weekend_care_6701d63cf6') }}"></label>
                        <fieldset>
                            <legend>{{ __('ui.permissions_abccc78cc9') }}</legend>
                            <div class="device-check-grid">
                                @forelse ($access_permission_options as $permission)
                                    <label class="device-check"><input type="checkbox" name="permissions[]" value="{{ $permission['value'] }}"><span>{{ $permission['label'] }}</span></label>
                                @empty
                                    <span>{{ __('ui.no_permissions_fbe77cb976') }}</span>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="device-check-grid">
                            <label class="device-check"><input type="checkbox" name="allow_location" value="1"><span>{{ __('ui.general_area_e0e1218303') }}</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_camera" value="1"><span>{{ __('ui.camera_status_f2aa3c9ff6') }}</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_commands" value="1"><span>{{ __('ui.commands_b269dc4e81') }}</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_audio" value="1"><span>{{ __('ui.audio_bc1b88907d') }}</span></label>
                        </div>
                        <div class="device-form-grid device-form-grid--compact">
                            <label>{{ __('ui.expires_in_hours_fd2774777e') }}<input type="number" name="expires_in_hours" min="1" max="720" value="48" required></label>
                            <label>{{ __('ui.maximum_opens_2a0d565dd4') }}<input type="number" name="max_views" min="1" max="100" value="20" required></label>
                        </div>
                        <label class="device-check device-check--boxed">
                            <input type="checkbox" name="privacy_acknowledged" value="1" required>
                            <span>{{ __('ui.i_reviewed_every_permission_and_will_share_the_3ea90ac56b') }}</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-lucide-key-round class="icon" aria-hidden="true" /><span>{{ __('ui.create_link_e6b850cff6') }}</span></button>
                    </form>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading"><div><p>{{ __('ui.active_and_historical_5202f01463') }}</p><h2>{{ __('ui.access_register_0d32891821') }}</h2></div></div>
                    <div class="device-access-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <div><h3>{{ $grant['recipient_name'] }}</h3><p>{{ $grant['recipient_role'] }} · {{ $grant['label'] }}</p></div>
                                    <x-status-badge :label="$grant['is_active'] ? __('ui.active_9234069589') : __('ui.expired_424a2551d3')" :tone="$grant['is_active'] ? 'success' : 'surface'" />
                                </div>
                                <small>{{ __('presentation.grant_permissions_expires_views', ['permissions' => implode(' · ', $grant['permissions']), 'expires' => $grant['expires_at'], 'views' => $grant['views']]) }}</small>
                                @if ($grant['is_active'])
                                    <form method="POST" action="{{ route('devices.access.revoke', [$device['slug'], $grant['id']]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="device-text-button device-text-button--danger" type="submit">{{ __('ui.revoke_now_581e15bd3f') }}</button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_temporary_links_created_4c6d25033f') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading"><div><p>{{ __('ui.no_real_commands_3e53eb8cb5') }}</p><h2>{{ __('ui.recent_simulations_573f2f057a') }}</h2></div></div>
                    <div class="device-compact-list">
                        @forelse ($automation_runs as $run)
                            <article>
                                <x-lucide-flask-conical class="size-4" aria-hidden="true" />
                                <div><h3>{{ $run['status'] }}</h3><p>{{ $run['result'] }}</p><small>{{ $run['started_at'] }}</small></div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_simulation_runs_5a3bd1cd58') }}</p>
                        @endforelse
                    </div>
                </section>

                <section id="audit" class="device-panel">
                    <div class="device-panel__heading"><div><p>{{ __('ui.owner_visible_history_759382a4db') }}</p><h2>{{ __('ui.audit_trail_c1ada08ce1') }}</h2></div></div>
                    <div class="device-audit-list">
                        @forelse ($audit as $item)
                            <article><span></span><div><h3>{{ $item['action'] }}</h3><p>{{ $item['actor'] }} · {{ $item['role'] }}</p><small>{{ $item['at'] }}</small></div></article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_audited_actions_yet_7e08fcb1f5') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
