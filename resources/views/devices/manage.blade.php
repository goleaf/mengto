<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="device-detail-header">
            <div>
                <a href="{{ $device['show_url'] }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                    {{ $device['name'] }}
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">Owner controls</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Rules, access, and audit</h1>
            </div>
            <x-status-badge label="Least privilege" icon="shield-check" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="device-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>The change was not saved</strong>
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

        @if (session('device_access_url'))
            <section class="device-access-link" aria-labelledby="device-access-link-title">
                <div>
                    <p>Shown once</p>
                    <h2 id="device-access-link-title">Temporary device link</h2>
                    <code>{{ session('device_access_url') }}</code>
                </div>
                <x-lucide-link class="size-6" aria-hidden="true" />
            </section>
        @endif

        <nav class="device-anchor-nav" aria-label="Device management">
            <a href="#automations"><x-lucide-workflow class="size-4" aria-hidden="true" /> Automations</a>
            <a href="#access"><x-lucide-key-round class="size-4" aria-hidden="true" /> Access</a>
            <a href="#audit"><x-lucide-shield-check class="size-4" aria-hidden="true" /> Audit</a>
            @if ($device['type'] === 'gps-tracker')
                <a href="#safe-zone"><x-lucide-map-pinned class="size-4" aria-hidden="true" /> Safe zone</a>
            @endif
        </nav>

        <div class="device-manage-grid">
            <main class="grid min-w-0 content-start gap-5">
                <section id="automations" class="device-form-section">
                    <div class="device-panel__heading">
                        <div>
                            <p>Test before enabling</p>
                            <h2>Create an automation</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('devices.automations.store', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="device-form-grid">
                            <label>Name<input name="name" maxlength="140" required placeholder="Notify when device goes offline"></label>
                            <label>
                                Trigger
                                <select name="trigger_type" required>
                                    @forelse (['safe-zone-exit', 'battery-low', 'device-offline', 'feeding-failed', 'water-low', 'temperature-high', 'temperature-low', 'door-open', 'leak-detected'] as $trigger)
                                        <option value="{{ $trigger }}">{{ str($trigger)->headline() }}</option>
                                    @empty
                                        <option value="">No triggers</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>Threshold<input type="number" step="any" name="trigger_value"></label>
                            <label>
                                Home mode
                                <select name="condition_mode">
                                    @forelse (['any', 'home', 'away', 'sitter', 'night', 'lost-mode'] as $mode)
                                        <option value="{{ $mode }}">{{ str($mode)->headline() }}</option>
                                    @empty
                                        <option value="">Any</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Action
                                <select name="action_type">
                                    @forelse (['send-notification', 'create-task', 'lock-door', 'stop-water-pump', 'enable-lost-mode'] as $action)
                                        <option value="{{ $action }}">{{ str($action)->headline() }}</option>
                                    @empty
                                        <option value="">No actions</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Priority
                                <select name="priority">
                                    @forelse (['normal', 'important', 'urgent', 'critical'] as $priority)
                                        <option value="{{ $priority }}">{{ str($priority)->headline() }}</option>
                                    @empty
                                        <option value="">Normal</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Initial status
                                <select name="status"><option value="draft">Draft</option><option value="enabled">Enabled</option></select>
                            </label>
                            <label>Maximum runs / hour<input type="number" name="max_runs_per_hour" min="1" max="12" value="2" required></label>
                            <label>Cooldown seconds<input type="number" name="cooldown_seconds" min="30" max="86400" value="300" required></label>
                        </div>
                        <label class="device-check device-check--boxed">
                            <input type="checkbox" name="safety_acknowledged" value="1" required>
                            <span>I reviewed the action, cooldown, fallback, and understand that prohibited commands cannot be automated.</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-lucide-workflow class="icon" aria-hidden="true" /><span>Save guarded rule</span></button>
                    </form>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading">
                        <div><p>Current rules</p><h2>Automation register</h2></div>
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
                                    <button class="action" type="submit"><x-lucide-flask-conical class="icon" aria-hidden="true" /><span>Simulate</span></button>
                                </form>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No automations yet. Rules never run until explicitly enabled.</p>
                        @endforelse
                    </div>
                </section>

                @if ($device['type'] === 'gps-tracker')
                    <section id="safe-zone" class="device-form-section">
                        <div class="device-panel__heading">
                            <div><p>Coordinates encrypted</p><h2>Add a safe zone</h2></div>
                        </div>
                        <form method="POST" action="{{ route('devices.safe-zones.store', $device['slug']) }}" class="grid gap-4">
                            @csrf
                            <div class="device-form-grid">
                                <label>Name<input name="name" required maxlength="120" placeholder="Home boundary"></label>
                                <label>Shape<select name="shape"><option value="circle">Circle</option><option value="polygon">Polygon anchor</option></select></label>
                                <label>Public area label<input name="public_area_label" required maxlength="160" placeholder="Home area"></label>
                                <label>Latitude<input type="number" step="0.000001" name="latitude" min="-90" max="90" required></label>
                                <label>Longitude<input type="number" step="0.000001" name="longitude" min="-180" max="180" required></label>
                                <label>Radius meters<input type="number" name="radius_meters" min="20" max="50000" value="120"></label>
                                <label>Exit confirmation seconds<input type="number" name="exit_delay_seconds" min="0" max="900" value="45" required></label>
                                <label>Accuracy threshold meters<input type="number" name="accuracy_threshold_meters" min="5" max="1000" value="35" required></label>
                            </div>
                            <div class="device-check-grid">
                                <label class="device-check"><input type="checkbox" name="is_home" value="1"><span>Home privacy zone</span></label>
                                <label class="device-check"><input type="checkbox" name="always_active" value="1"><span>Always active</span></label>
                            </div>
                            <button class="action" type="submit"><x-lucide-map-pinned class="icon" aria-hidden="true" /><span>Save private zone</span></button>
                        </form>
                    </section>
                @endif
            </main>

            <aside class="grid min-w-0 content-start gap-5">
                <section id="access" class="device-form-section">
                    <div class="device-panel__heading">
                        <div><p>Time-bound and revocable</p><h2>Temporary access</h2></div>
                    </div>
                    <form method="POST" action="{{ route('devices.access.store', $device['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>Recipient<input name="recipient_name" maxlength="120" required placeholder="Alex Carter"></label>
                        <label>
                            Role
                            <select name="recipient_role">
                                <option value="sitter">Sitter</option>
                                <option value="co-owner">Co-owner</option>
                                <option value="veterinarian">Veterinarian</option>
                                <option value="trainer">Trainer</option>
                                <option value="support">Technical support</option>
                            </select>
                        </label>
                        <label>Access label<input name="label" maxlength="140" required placeholder="Weekend care"></label>
                        <fieldset>
                            <legend>Permissions</legend>
                            <div class="device-check-grid">
                                @forelse (['view-status', 'view-readings', 'view-events', 'control'] as $permission)
                                    <label class="device-check"><input type="checkbox" name="permissions[]" value="{{ $permission }}"><span>{{ str($permission)->headline() }}</span></label>
                                @empty
                                    <span>No permissions.</span>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="device-check-grid">
                            <label class="device-check"><input type="checkbox" name="allow_location" value="1"><span>General area</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_camera" value="1"><span>Camera status</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_commands" value="1"><span>Commands</span></label>
                            <label class="device-check"><input type="checkbox" name="allow_audio" value="1"><span>Audio</span></label>
                        </div>
                        <div class="device-form-grid device-form-grid--compact">
                            <label>Expires in hours<input type="number" name="expires_in_hours" min="1" max="720" value="48" required></label>
                            <label>Maximum opens<input type="number" name="max_views" min="1" max="100" value="20" required></label>
                        </div>
                        <label class="device-check device-check--boxed">
                            <input type="checkbox" name="privacy_acknowledged" value="1" required>
                            <span>I reviewed every permission and will share the one-time link securely.</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-lucide-key-round class="icon" aria-hidden="true" /><span>Create link</span></button>
                    </form>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading"><div><p>Active and historical</p><h2>Access register</h2></div></div>
                    <div class="device-access-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex items-start justify-between gap-2">
                                    <div><h3>{{ $grant['recipient_name'] }}</h3><p>{{ $grant['recipient_role'] }} · {{ $grant['label'] }}</p></div>
                                    <x-status-badge :label="$grant['is_active'] ? 'Active' : 'Expired'" :tone="$grant['is_active'] ? 'success' : 'surface'" />
                                </div>
                                <small>{{ implode(' · ', $grant['permissions']) }}<br>Expires {{ $grant['expires_at'] }} · views {{ $grant['views'] }}</small>
                                @if ($grant['is_active'])
                                    <form method="POST" action="{{ route('devices.access.revoke', [$device['slug'], $grant['id']]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="device-text-button device-text-button--danger" type="submit">Revoke now</button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No temporary links created.</p>
                        @endforelse
                    </div>
                </section>

                <section class="device-panel">
                    <div class="device-panel__heading"><div><p>No real commands</p><h2>Recent simulations</h2></div></div>
                    <div class="device-compact-list">
                        @forelse ($automation_runs as $run)
                            <article>
                                <x-lucide-flask-conical class="size-4" aria-hidden="true" />
                                <div><h3>{{ $run['status'] }}</h3><p>{{ $run['result'] }}</p><small>{{ $run['started_at'] }}</small></div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No simulation runs.</p>
                        @endforelse
                    </div>
                </section>

                <section id="audit" class="device-panel">
                    <div class="device-panel__heading"><div><p>Owner-visible history</p><h2>Audit trail</h2></div></div>
                    <div class="device-audit-list">
                        @forelse ($audit as $item)
                            <article><span></span><div><h3>{{ $item['action'] }}</h3><p>{{ $item['actor'] }} · {{ $item['role'] }}</p><small>{{ $item['at'] }}</small></div></article>
                        @empty
                            <p class="text-sm text-paw-muted">No audited actions yet.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
