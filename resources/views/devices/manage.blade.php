<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="device-detail-header">
            <div>
                <x-detail-navigation :href="$device['show_url']" :label="$device['name']" />
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.owner_controls') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.rules_access_and_audit') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.least_privilege') }}" icon="shield-check" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
                <div>
                    <strong>{{ __('ui.the_change_was_not_saved') }}</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>{{ __('ui.validation_failed') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        @if (session('device_access_url'))
            <section class="device-access-link" aria-labelledby="device-access-link-title">
                <div>
                    <p>{{ __('ui.shown_once') }}</p>
                    <h2 id="device-access-link-title">{{ __('ui.temporary_device_link') }}</h2>
                    <code>{{ session('device_access_url') }}</code>
                </div>
                <x-ui-icon name="link" size="xl" />
            </section>
        @endif

        <nav class="device-anchor-nav" aria-label="{{ __('ui.device_management') }}">
            <a href="#retention"><x-ui-icon name="history" size="sm" /> {{ __('devices.retention.heading') }}</a>
            <a href="#lifecycle"><x-ui-icon name="wrench" size="sm" /> {{ __('devices.lifecycle.heading') }}</a>
            <a href="#automations"><x-ui-icon name="workflow" size="sm" /> {{ __('ui.automations') }}</a>
            <a href="#access"><x-ui-icon name="key-round" size="sm" /> {{ __('ui.access') }}</a>
            <a href="#audit"><x-ui-icon name="shield-check" size="sm" /> {{ __('ui.audit') }}</a>
            @if ($device['type'] === 'gps-tracker')
                <a href="#safe-zone"><x-ui-icon name="map-pinned" size="sm" /> {{ __('ui.safe_zone') }}</a>
            @endif
        </nav>

        <div class="device-manage-grid">
            <div class="grid min-w-0 content-start gap-5">
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
                                        <option value="">{{ __('ui.unavailable') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.retention.media') }}
                                <select name="media_retention_days" required>
                                    @forelse ($retention_options['media'] as $option)
                                        <option value="{{ $option['value'] }}" @selected((int) old('media_retention_days', $device['media_retention_days']) === $option['value'])>{{ $option['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.retention.telemetry') }}
                                <select name="telemetry_retention_days" required>
                                    @forelse ($retention_options['telemetry'] as $option)
                                        <option value="{{ $option['value'] }}" @selected((int) old('telemetry_retention_days', $device['telemetry_retention_days']) === $option['value'])>{{ $option['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable') }}</option>
                                    @endforelse
                                </select>
                            </label>
                        </div>
                        <button class="action" type="submit">
                            <x-ui-icon name="save" />
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
                                        <option value="">{{ __('ui.unavailable') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.lifecycle.status_label') }}
                                <select name="status" required>
                                    @forelse ($lifecycle_statuses as $status)
                                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('devices.lifecycle.severity') }}
                                <select name="severity" required>
                                    @forelse ($lifecycle_severities as $severity)
                                        <option value="{{ $severity['value'] }}">{{ $severity['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.unavailable') }}</option>
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
                            <x-ui-icon name="clipboard-check" />
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
                            <p>{{ __('ui.test_before_enabling') }}</p>
                            <h2>{{ __('ui.create_an_automation') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('devices.automations.store', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="device-form-grid">
                            <label>{{ __('ui.name') }}<input name="name" maxlength="140" required placeholder="{{ __('ui.notify_when_device_goes_offline') }}"></label>
                            <label>
                                {{ __('ui.trigger') }}
                                <select name="trigger_type" required>
                                    @forelse ($automation_triggers as $trigger)
                                        <option value="{{ $trigger['value'] }}">{{ $trigger['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.no_triggers') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>{{ __('ui.threshold') }}<input type="number" step="any" name="trigger_value"></label>
                            <label>
                                {{ __('ui.home_mode') }}
                                <select name="condition_mode">
                                    @forelse ($automation_modes as $mode)
                                        <option value="{{ $mode['value'] }}">{{ $mode['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.any') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.action') }}
                                <select name="action_type">
                                    @forelse ($automation_actions as $action)
                                        <option value="{{ $action['value'] }}">{{ $action['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.no_actions') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.priority') }}
                                <select name="priority">
                                    @forelse ($automation_priorities as $priority)
                                        <option value="{{ $priority['value'] }}">{{ $priority['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.normal') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.initial_status') }}
                                <select name="status"><option value="draft">{{ __('ui.draft') }}</option><option value="enabled">{{ __('ui.enabled') }}</option></select>
                            </label>
                            <label>{{ __('ui.maximum_runs_hour') }}<input type="number" name="max_runs_per_hour" min="1" max="12" value="2" required></label>
                            <label>{{ __('ui.cooldown_seconds') }}<input type="number" name="cooldown_seconds" min="30" max="86400" value="300" required></label>
                        </div>
                        <label class="device-check device-check--boxed">
                            <input type="checkbox" name="safety_acknowledged" value="1" required>
                            <span>{{ __('ui.i_reviewed_the_action_cooldown_fallback_and_understand_that_prohibited_commands_cannot_be_automated') }}</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-ui-icon name="workflow" /><span>{{ __('ui.save_guarded_rule') }}</span></button>
                    </form>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading">
                        <div><p>{{ __('ui.current_rules') }}</p><h2>{{ __('ui.automation_register') }}</h2></div>
                    </div>
                    <div class="device-rule-list">
                        @forelse ($automations as $automation)
                            <article>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3>{{ $automation['name'] }}</h3>
                                        <x-status-badge :label="$automation['status_label']" :tone="$automation['status_tone']" />
                                    </div>
                                    <p class="device-rule-list__flow">
                                        <span>{{ $automation['trigger'] }}</span>
                                        <x-ui-icon name="arrow-right" size="sm" />
                                        <span>{{ $automation['action'] }}</span>
                                    </p>
                                    <small>{{ $automation['condition'] }} · {{ $automation['max_runs'] }} · {{ $automation['cooldown'] }}</small>
                                </div>
                                <form method="POST" action="{{ route('devices.automations.test', [$device['slug'], $automation['id']]) }}">
                                    @csrf
                                    <button class="action" type="submit"><x-ui-icon name="flask-conical" /><span>{{ __('ui.simulate') }}</span></button>
                                </form>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_automations_yet_rules_never_run_until_explicitly_enabled') }}</p>
                        @endforelse
                    </div>
                </section>

                @if ($device['type'] === 'gps-tracker')
                    <section id="safe-zone" class="device-form-section">
                        <div class="device-panel__heading">
                            <div><p>{{ __('ui.coordinates_encrypted') }}</p><h2>{{ __('ui.add_a_safe_zone') }}</h2></div>
                        </div>
                        <form method="POST" action="{{ route('devices.safe-zones.store', $device['slug']) }}" class="grid gap-4">
                            @csrf
                            <div class="device-form-grid">
                                <label>{{ __('ui.name') }}<input name="name" required maxlength="120" placeholder="{{ __('ui.home_boundary') }}"></label>
                                <label>{{ __('ui.shape') }}<select name="shape"><option value="circle">{{ __('ui.circle') }}</option><option value="polygon">{{ __('ui.polygon_anchor') }}</option></select></label>
                                <label>{{ __('ui.public_area_label') }}<input name="public_area_label" required maxlength="160" placeholder="{{ __('ui.home_area') }}"></label>
                                <label>{{ __('ui.latitude') }}<input type="number" step="0.000001" name="latitude" min="-90" max="90" required></label>
                                <label>{{ __('ui.longitude') }}<input type="number" step="0.000001" name="longitude" min="-180" max="180" required></label>
                                <label>{{ __('ui.radius_meters') }}<input type="number" name="radius_meters" min="20" max="50000" value="120"></label>
                                <label>{{ __('ui.exit_confirmation_seconds') }}<input type="number" name="exit_delay_seconds" min="0" max="900" value="45" required></label>
                                <label>{{ __('ui.accuracy_threshold_meters') }}<input type="number" name="accuracy_threshold_meters" min="5" max="1000" value="35" required></label>
                            </div>
                            <div class="device-check-grid">
                                <label class="device-check"><input type="checkbox" name="is_home" value="1"><span>{{ __('ui.home_privacy_zone') }}</span></label>
                                <label class="device-check"><input type="checkbox" name="always_active" value="1"><span>{{ __('ui.always_active') }}</span></label>
                            </div>
                            <button class="action" type="submit"><x-ui-icon name="map-pinned" /><span>{{ __('ui.save_private_zone') }}</span></button>
                        </form>
                    </section>
                @endif
            </div>

            <aside class="grid min-w-0 content-start gap-5">
                <section id="access" class="device-form-section">
                    <div class="device-panel__heading">
                        <div><p>{{ __('ui.time_bound_and_revocable') }}</p><h2>{{ __('ui.temporary_access') }}</h2></div>
                    </div>
                    <form method="POST" action="{{ route('devices.access.store', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>{{ __('ui.recipient') }}<input name="recipient_name" maxlength="120" required placeholder="{{ __('ui.alex_carter') }}"></label>
                        <label>
                            {{ __('ui.role') }}
                            <select name="recipient_role">
                                <option value="sitter">{{ __('ui.sitter') }}</option>
                                <option value="co-owner">{{ __('ui.co_owner') }}</option>
                                <option value="veterinarian">{{ __('ui.veterinarian') }}</option>
                                <option value="trainer">{{ __('ui.trainer') }}</option>
                                <option value="support">{{ __('ui.technical_support') }}</option>
                            </select>
                        </label>
                        <label>{{ __('ui.access_label') }}<input name="label" maxlength="140" required placeholder="{{ __('ui.weekend_care') }}"></label>
                        <fieldset>
                            <legend>{{ __('ui.permissions') }}</legend>
                            <div class="device-check-grid">
                                @forelse ($access_permission_options as $permission)
                                    <label class="device-check"><input type="checkbox" name="permissions[]" value="{{ $permission['value'] }}"><span>{{ $permission['label'] }}</span></label>
                                @empty
                                    <span>{{ __('ui.no_permissions') }}</span>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="device-check-grid">
                            <label class="device-check"><input type="checkbox" name="allow_location" value="1"><span>{{ __('ui.general_area') }}</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_camera" value="1"><span>{{ __('ui.camera_status') }}</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_commands" value="1"><span>{{ __('ui.commands') }}</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_audio" value="1"><span>{{ __('ui.audio') }}</span></label>
                        </div>
                        <div class="device-form-grid device-form-grid--compact">
                            <label>{{ __('ui.expires_in_hours') }}<input type="number" name="expires_in_hours" min="1" max="720" value="48" required></label>
                            <label>{{ __('ui.maximum_opens') }}<input type="number" name="max_views" min="1" max="100" value="20" required></label>
                        </div>
                        <label class="device-check device-check--boxed">
                            <input type="checkbox" name="privacy_acknowledged" value="1" required>
                            <span>{{ __('ui.i_reviewed_every_permission_and_will_share_the_one_time_link_securely') }}</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-ui-icon name="key-round" /><span>{{ __('ui.create_link') }}</span></button>
                    </form>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading"><div><p>{{ __('ui.active_and_historical') }}</p><h2>{{ __('ui.access_register') }}</h2></div></div>
                    <div class="device-access-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <div><h3>{{ $grant['recipient_name'] }}</h3><p>{{ $grant['recipient_role'] }} · {{ $grant['label'] }}</p></div>
                                    <x-status-badge :label="$grant['is_active'] ? __('ui.active') : __('ui.expired')" :tone="$grant['is_active'] ? 'success' : 'surface'" />
                                </div>
                                <small>{{ __('presentation.grant_permissions_expires_views', ['permissions' => implode(' · ', $grant['permissions']), 'expires' => $grant['expires_at'], 'views' => $grant['views']]) }}</small>
                                @if ($grant['is_active'])
                                    <form method="POST" action="{{ route('devices.access.revoke', [$device['slug'], $grant['id']]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="device-text-button device-text-button--danger" type="submit">
                                            <x-ui-icon name="ban" size="sm" />
                                            <span>{{ __('ui.revoke_now') }}</span>
                                        </button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_temporary_links_created') }}</p>
                        @endforelse
                    </div>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading"><div><p>{{ __('ui.no_real_commands') }}</p><h2>{{ __('ui.recent_simulations') }}</h2></div></div>
                    <div class="device-compact-list">
                        @forelse ($automation_runs as $run)
                            <article>
                                <x-ui-icon name="flask-conical" size="sm" />
                                <div><h3>{{ $run['status'] }}</h3><p>{{ $run['result'] }}</p><small>{{ $run['started_at'] }}</small></div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_simulation_runs') }}</p>
                        @endforelse
                    </div>
                </section>

                <section id="audit" class="device-panel">
                    <div class="device-panel__heading"><div><p>{{ __('ui.owner_visible_history') }}</p><h2>{{ __('ui.audit_trail') }}</h2></div></div>
                    <div class="device-audit-list">
                        @forelse ($audit as $item)
                            <article><span></span><div><h3>{{ $item['action'] }}</h3><p>{{ $item['actor'] }} · {{ $item['role'] }}</p><small>{{ $item['at'] }}</small></div></article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_audited_actions_yet') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
