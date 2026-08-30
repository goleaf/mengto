<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="care-manage-header">
            <div>
                <x-detail-navigation
                    :href="$care_journal['show_url']"
                    :label="__('presentation.pet_journal', ['pet' => $care_journal['pet_name']])"
                />
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.owner_workspace') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.plan_and_share_care') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.encrypted_private_data') }}" icon="lock-keyhole" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
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

        @if ($care_access_url)
            <section class="care-access-link" aria-labelledby="care-access-link-title">
                <div>
                    <p class="text-xs font-bold uppercase">{{ __('ui.shown_once') }}</p>
                    <h2 id="care-access-link-title" class="mt-1 font-bold">{{ __('ui.temporary_care_link') }}</h2>
                    <p class="mt-2 break-all text-sm">{{ $care_access_url }}</p>
                </div>
                <x-ui-icon name="link" size="xl" />
            </section>
        @endif

        <nav class="care-anchor-nav" aria-label="{{ __('ui.care_journal_management') }}">
            <a href="#tasks"><x-ui-icon name="list-checks" size="sm" /> {{ __('ui.tasks') }}</a>
            <a href="#routines"><x-ui-icon name="repeat-2" size="sm" /> {{ __('ui.routines') }}</a>
            <a href="#access"><x-ui-icon name="key-round" size="sm" /> {{ __('ui.access') }}</a>
            <a href="#audit"><x-ui-icon name="shield-check" size="sm" /> {{ __('ui.audit') }}</a>
        </nav>

        <div class="care-manage-grid">
            <div>
                <section id="tasks" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.household_work') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.schedule_a_task') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.tasks.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="care-form-grid">
                            <label>
                                {{ __('ui.task') }}
                                <input name="title" value="{{ old('title') }}" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.type') }}
                                <select name="type" required>
                                    @forelse ($entry_types as $type)
                                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.no_types') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.due') }}
                                <input type="datetime-local" name="due_at" value="{{ old('due_at', $form_defaults['task_due_at']) }}" required>
                            </label>
                            <label>
                                {{ __('ui.priority') }}
                                <select name="priority">
                                    <option value="normal">{{ __('ui.normal') }}</option>
                                    <option value="important">{{ __('ui.important') }}</option>
                                    <option value="urgent">{{ __('ui.urgent') }}</option>
                                    <option value="clinical">{{ __('ui.clinical_instruction') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.assignee_name') }}
                                <input name="assignee_name" value="{{ old('assignee_name', __('ui.mia_carter')) }}" maxlength="120">
                            </label>
                            <label>
                                {{ __('ui.repeat_rule') }}
                                <input name="repeat_rule" value="{{ old('repeat_rule') }}" maxlength="120" placeholder="{{ __('ui.daily_weekends_one_time') }}">
                            </label>
                            <label>
                                {{ __('ui.routine') }}
                                <select name="care_routine_id">
                                    <option value="">{{ __('ui.no_routine') }}</option>
                                    @forelse ($routines as $routine)
                                        <option value="{{ $routine['id'] }}">{{ $routine['name'] }}</option>
                                    @empty
                                        <option value="" disabled>{{ __('ui.create_a_routine_below') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.private_instruction') }}
                                <textarea name="instructions" maxlength="3000">{{ old('instructions') }}</textarea>
                            </label>
                        </div>
                        <label class="care-check">
                            <input type="checkbox" name="requires_individual_confirmation" value="1">
                            <span>{{ __('ui.require_an_individual_confirmation_for_this_task') }}</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-ui-icon name="plus" /><span>{{ __('ui.add_task') }}</span></button>
                    </form>
                </section>

                <section id="routines" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.versioned_plan') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.create_a_routine') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.routines.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="care-form-grid">
                            <label>
                                {{ __('ui.routine_name') }}
                                <input name="name" maxlength="160" required placeholder="{{ __('ui.weekday_morning') }}">
                            </label>
                            <label>
                                {{ __('ui.period') }}
                                <select name="period" required>
                                    <option value="daily">{{ __('ui.daily') }}</option>
                                    <option value="weekdays">{{ __('ui.weekdays') }}</option>
                                    <option value="weekends">{{ __('ui.weekends') }}</option>
                                    <option value="weekly">{{ __('ui.weekly') }}</option>
                                    <option value="temporary">{{ __('ui.temporary') }}</option>
                                    <option value="custom">{{ __('ui.custom') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.starts') }}
                                <input type="date" name="starts_on" value="{{ $form_defaults['routine_starts_on'] }}" required>
                            </label>
                            <label>
                                {{ __('ui.ends') }}
                                <input type="date" name="ends_on">
                            </label>
                            <label>
                                {{ __('ui.preferred_time') }}
                                <input type="time" name="start_time">
                            </label>
                            <label>
                                {{ __('ui.private_instructions') }}
                                <textarea name="instructions" maxlength="3000"></textarea>
                            </label>
                        </div>
                        <button class="action action--primary" type="submit"><x-ui-icon name="repeat-2" /><span>{{ __('ui.save_routine') }}</span></button>
                    </form>
                </section>
            </div>

            <aside>
                <section id="access" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.least_privilege') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.temporary_access') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.access.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>
                            {{ __('ui.recipient') }}
                            <input name="recipient_name" maxlength="160" required placeholder="{{ __('ui.alex_carter') }}">
                        </label>
                        <label>
                            {{ __('ui.role') }}
                            <select name="recipient_role" required>
                                <option value="sitter">{{ __('ui.sitter') }}</option>
                                <option value="family">{{ __('ui.family') }}</option>
                                <option value="co-owner">{{ __('ui.co_owner') }}</option>
                                <option value="veterinarian">{{ __('ui.veterinarian') }}</option>
                                <option value="trainer">{{ __('ui.trainer') }}</option>
                                <option value="groomer">{{ __('ui.groomer') }}</option>
                                <option value="specialist">{{ __('ui.specialist') }}</option>
                                <option value="shelter">{{ __('ui.shelter') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.access_label') }}
                            <input name="label" maxlength="180" required placeholder="{{ __('ui.weekend_care') }}">
                        </label>
                        <fieldset>
                            <legend>{{ __('ui.visible_sections') }}</legend>
                            <div class="care-check-grid">
                                @forelse ($access_section_options as $section)
                                    <label class="care-check">
                                        <input type="checkbox" name="sections[]" value="{{ $section['value'] }}">
                                        <span>{{ $section['label'] }}</span>
                                    </label>
                                @empty
                                    <span>{{ __('ui.no_sections') }}</span>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="care-form-grid">
                            <label>
                                {{ __('ui.expires_in_hours') }}
                                <input type="number" name="expires_in_hours" min="1" max="720" value="72" required>
                            </label>
                            <label>
                                {{ __('ui.maximum_opens') }}
                                <input type="number" name="max_views" min="1" max="200" value="30" required>
                            </label>
                        </div>
                        <div class="care-checks">
                            <label class="care-check"><input type="checkbox" name="allow_add" value="1"><span>{{ __('ui.may_add_reports') }}</span></label>
                            <label class="care-check"><input type="checkbox" name="allow_location" value="1"><span>{{ __('ui.may_see_add_locations') }}</span></label>
                            <label class="care-check"><input type="checkbox" name="allow_media" value="1"><span>{{ __('ui.may_add_media') }}</span></label>
                            <label class="care-check"><input type="checkbox" name="privacy_acknowledged" value="1" required><span>{{ __('ui.i_reviewed_the_selected_access') }}</span></label>
                        </div>
                        <button class="action action--primary" type="submit"><x-ui-icon name="key-round" /><span>{{ __('ui.create_link') }}</span></button>
                    </form>

                    <div class="care-access-list">
                        @forelse ($access_grants as $grant)
                            <article>
                                <div class="flex min-w-0 items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h3 class="font-bold">{{ $grant['recipient_name'] }}</h3>
                                        <p class="mt-1 text-sm text-paw-muted">{{ __('presentation.role_views', ['role' => $grant['recipient_role'], 'views' => $grant['views']]) }}</p>
                                        <p class="mt-1 text-xs text-paw-muted">{{ implode(', ', $grant['sections']) }}</p>
                                    </div>
                                    <x-status-badge :label="$grant['status']" :icon="$grant['active'] ? 'link' : 'link-2-off'" :tone="$grant['active'] ? 'success' : 'surface'" />
                                </div>
                                @if ($grant['active'])
                                    <form method="POST" action="{{ route('care-journals.access.revoke', [$care_journal['slug'], $grant['id']]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action action--compact"><x-ui-icon name="link-2-off" size="sm" /><span>{{ __('ui.revoke') }}</span></button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_temporary_access_links') }}</p>
                        @endforelse
                    </div>
                </section>

                <section id="audit" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.recent_access_trail') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.audit') }}</h2>
                        </div>
                    </div>
                    <div class="care-audit-list">
                        @forelse ($audits as $audit)
                            <article>
                                <x-ui-icon name="shield-check" size="sm" />
                                <div>
                                    <strong>{{ $audit['action'] }}</strong>
                                    <p>{{ $audit['actor'] }} · {{ $audit['time'] }}</p>
                                </div>
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_recent_journal_access_events') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
