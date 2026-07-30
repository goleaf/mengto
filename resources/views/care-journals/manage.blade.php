<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="care-manage-header">
            <div>
                <a href="{{ $care_journal['show_url'] }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                    {{ $care_journal['pet_name'] }}'s journal
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">Owner workspace</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Plan and share care</h1>
            </div>
            <x-status-badge label="Encrypted private data" icon="lock-keyhole" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
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

        @if ($care_access_url)
            <section class="care-access-link" aria-labelledby="care-access-link-title">
                <div>
                    <p class="text-xs font-bold uppercase">Shown once</p>
                    <h2 id="care-access-link-title" class="mt-1 font-bold">Temporary care link</h2>
                    <p class="mt-2 break-all text-sm">{{ $care_access_url }}</p>
                </div>
                <x-lucide-link class="size-6" aria-hidden="true" />
            </section>
        @endif

        <nav class="care-anchor-nav" aria-label="Care journal management">
            <a href="#tasks"><x-lucide-list-checks class="size-4" aria-hidden="true" /> Tasks</a>
            <a href="#routines"><x-lucide-repeat-2 class="size-4" aria-hidden="true" /> Routines</a>
            <a href="#access"><x-lucide-key-round class="size-4" aria-hidden="true" /> Access</a>
            <a href="#audit"><x-lucide-shield-check class="size-4" aria-hidden="true" /> Audit</a>
        </nav>

        <div class="care-manage-grid">
            <div>
                <section id="tasks" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Household work</p>
                            <h2 class="mt-1 text-xl font-bold">Schedule a task</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.tasks.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="care-form-grid">
                            <label>
                                Task
                                <input name="title" value="{{ old('title') }}" maxlength="180" required>
                            </label>
                            <label>
                                Type
                                <select name="type" required>
                                    @forelse ($entry_types as $type)
                                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                    @empty
                                        <option value="">No types</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Due
                                <input type="datetime-local" name="due_at" value="{{ old('due_at', $form_defaults['task_due_at']) }}" required>
                            </label>
                            <label>
                                Priority
                                <select name="priority">
                                    <option value="normal">Normal</option>
                                    <option value="important">Important</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="clinical">Clinical instruction</option>
                                </select>
                            </label>
                            <label>
                                Assignee name
                                <input name="assignee_name" value="{{ old('assignee_name', 'Mia Carter') }}" maxlength="120">
                            </label>
                            <label>
                                Repeat rule
                                <input name="repeat_rule" value="{{ old('repeat_rule') }}" maxlength="120" placeholder="Daily, weekends, one time">
                            </label>
                            <label>
                                Routine
                                <select name="care_routine_id">
                                    <option value="">No routine</option>
                                    @forelse ($routines as $routine)
                                        <option value="{{ $routine['id'] }}">{{ $routine['name'] }}</option>
                                    @empty
                                        <option value="" disabled>Create a routine below</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                Private instruction
                                <textarea name="instructions" maxlength="3000">{{ old('instructions') }}</textarea>
                            </label>
                        </div>
                        <label class="care-check">
                            <input type="checkbox" name="requires_individual_confirmation" value="1">
                            <span>Require an individual confirmation for this task</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-lucide-plus class="icon" aria-hidden="true" /><span>Add task</span></button>
                    </form>
                </section>

                <section id="routines" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Versioned plan</p>
                            <h2 class="mt-1 text-xl font-bold">Create a routine</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.routines.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="care-form-grid">
                            <label>
                                Routine name
                                <input name="name" maxlength="160" required placeholder="Weekday morning">
                            </label>
                            <label>
                                Period
                                <select name="period" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekdays">Weekdays</option>
                                    <option value="weekends">Weekends</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="temporary">Temporary</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </label>
                            <label>
                                Starts
                                <input type="date" name="starts_on" value="{{ $form_defaults['routine_starts_on'] }}" required>
                            </label>
                            <label>
                                Ends
                                <input type="date" name="ends_on">
                            </label>
                            <label>
                                Preferred time
                                <input type="time" name="start_time">
                            </label>
                            <label>
                                Private instructions
                                <textarea name="instructions" maxlength="3000"></textarea>
                            </label>
                        </div>
                        <button class="action action--primary" type="submit"><x-lucide-repeat-2 class="icon" aria-hidden="true" /><span>Save routine</span></button>
                    </form>
                </section>
            </div>

            <aside>
                <section id="access" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Least privilege</p>
                            <h2 class="mt-1 text-xl font-bold">Temporary access</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.access.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>
                            Recipient
                            <input name="recipient_name" maxlength="160" required placeholder="Alex Carter">
                        </label>
                        <label>
                            Role
                            <select name="recipient_role" required>
                                <option value="sitter">Sitter</option>
                                <option value="family">Family</option>
                                <option value="co-owner">Co-owner</option>
                                <option value="veterinarian">Veterinarian</option>
                                <option value="trainer">Trainer</option>
                                <option value="groomer">Groomer</option>
                                <option value="specialist">Specialist</option>
                                <option value="shelter">Shelter</option>
                            </select>
                        </label>
                        <label>
                            Access label
                            <input name="label" maxlength="180" required placeholder="Weekend care">
                        </label>
                        <fieldset>
                            <legend>Visible sections</legend>
                            <div class="care-check-grid">
                                @forelse (['summary', 'feeding', 'water', 'walks', 'toilet', 'sleep', 'activity', 'care', 'observations', 'tasks'] as $section)
                                    <label class="care-check">
                                        <input type="checkbox" name="sections[]" value="{{ $section }}">
                                        <span>{{ str($section)->headline() }}</span>
                                    </label>
                                @empty
                                    <span>No sections.</span>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="care-form-grid">
                            <label>
                                Expires in hours
                                <input type="number" name="expires_in_hours" min="1" max="720" value="72" required>
                            </label>
                            <label>
                                Maximum opens
                                <input type="number" name="max_views" min="1" max="200" value="30" required>
                            </label>
                        </div>
                        <div class="care-checks">
                            <label class="care-check"><input type="checkbox" name="allow_add" value="1"><span>May add reports</span></label>
                            <label class="care-check"><input type="checkbox" name="allow_location" value="1"><span>May see/add locations</span></label>
                            <label class="care-check"><input type="checkbox" name="allow_media" value="1"><span>May add media</span></label>
                            <label class="care-check"><input type="checkbox" name="privacy_acknowledged" value="1" required><span>I reviewed the selected access</span></label>
                        </div>
                        <button class="action action--primary" type="submit"><x-lucide-key-round class="icon" aria-hidden="true" /><span>Create link</span></button>
                    </form>

                    <div class="care-access-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex min-w-0 items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="font-bold">{{ $grant['recipient_name'] }}</h3>
                                        <p class="mt-1 text-sm text-paw-muted">{{ $grant['recipient_role'] }} · {{ $grant['views'] }} opens</p>
                                        <p class="mt-1 text-xs text-paw-muted">{{ implode(', ', $grant['sections']) }}</p>
                                    </div>
                                    <x-status-badge :label="$grant['status']" :icon="$grant['active'] ? 'link' : 'link-2-off'" :tone="$grant['active'] ? 'success' : 'surface'" />
                                </div>
                                @if ($grant['active'])
                                    <form method="POST" action="{{ route('care-journals.access.revoke', [$care_journal['slug'], $grant['id']]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action action--compact"><x-lucide-link-2-off class="icon icon--sm" aria-hidden="true" /><span>Revoke</span></button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No temporary access links.</p>
                        @endforelse
                    </div>
                </section>

                <section id="audit" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">Recent access trail</p>
                            <h2 class="mt-1 text-xl font-bold">Audit</h2>
                        </div>
                    </div>
                    <div class="care-audit-list">
                        @forelse ($audits as $audit)
                            <article>
                                <x-lucide-shield-check class="size-4" aria-hidden="true" />
                                <div>
                                    <strong>{{ $audit['action'] }}</strong>
                                    <p>{{ $audit['actor'] }} · {{ $audit['time'] }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">No recent journal access events.</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
