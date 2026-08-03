<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="grid gap-7">
        <header class="care-manage-header">
            <div>
                <a href="{{ $care_journal['show_url'] }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                    <x-ui-icon name="arrow-left" size="sm" />
                    {{ __('presentation.pet_journal', ['pet' => $care_journal['pet_name']]) }}
                </a>
                <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">{{ __('ui.owner_workspace_cefa8e8061') }}</p>
                <h1 class="mt-2 text-3xl font-bold sm:text-4xl">{{ __('ui.plan_and_share_care_487b4f79e4') }}</h1>
            </div>
            <x-status-badge label="{{ __('ui.encrypted_private_data_232feb3171') }}" icon="lock-keyhole" tone="success" size="regular" />
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-ui-icon name="circle-alert" size="lg" />
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

        @if ($care_access_url)
            <section class="care-access-link" aria-labelledby="care-access-link-title">
                <div>
                    <p class="text-xs font-bold uppercase">{{ __('ui.shown_once_22548d041f') }}</p>
                    <h2 id="care-access-link-title" class="mt-1 font-bold">{{ __('ui.temporary_care_link_3673c20654') }}</h2>
                    <p class="mt-2 break-all text-sm">{{ $care_access_url }}</p>
                </div>
                <x-ui-icon name="link" size="xl" />
            </section>
        @endif

        <nav class="care-anchor-nav" aria-label="{{ __('ui.care_journal_management_5e769cd112') }}">
            <a href="#tasks"><x-ui-icon name="list-checks" size="sm" /> {{ __('ui.tasks_b3a60e61a5') }}</a>
            <a href="#routines"><x-ui-icon name="repeat-2" size="sm" /> {{ __('ui.routines_61b7bb44e2') }}</a>
            <a href="#access"><x-ui-icon name="key-round" size="sm" /> {{ __('ui.access_ec5ba0abb7') }}</a>
            <a href="#audit"><x-ui-icon name="shield-check" size="sm" /> {{ __('ui.audit_bb6aea2873') }}</a>
        </nav>

        <div class="care-manage-grid">
            <div>
                <section id="tasks" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.household_work_2da76ab5a1') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.schedule_a_task_13159a79d3') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.tasks.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="care-form-grid">
                            <label>
                                {{ __('ui.task_4bc74b2135') }}
                                <input name="title" value="{{ old('title') }}" maxlength="180" required>
                            </label>
                            <label>
                                {{ __('ui.type_baaddf70fb') }}
                                <select name="type" required>
                                    @forelse ($entry_types as $type)
                                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                    @empty
                                        <option value="">{{ __('ui.no_types_c57cc94337') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.due_9071738f14') }}
                                <input type="datetime-local" name="due_at" value="{{ old('due_at', $form_defaults['task_due_at']) }}" required>
                            </label>
                            <label>
                                {{ __('ui.priority_d60dbba079') }}
                                <select name="priority">
                                    <option value="normal">{{ __('ui.normal_a7248eeb45') }}</option>
                                    <option value="important">{{ __('ui.important_ddca9a57e6') }}</option>
                                    <option value="urgent">{{ __('ui.urgent_1b015904cc') }}</option>
                                    <option value="clinical">{{ __('ui.clinical_instruction_0aaef55e5f') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.assignee_name_6b5a493908') }}
                                <input name="assignee_name" value="{{ old('assignee_name', __('ui.mia_carter_0e5b29cc3b')) }}" maxlength="120">
                            </label>
                            <label>
                                {{ __('ui.repeat_rule_bc4359c4d5') }}
                                <input name="repeat_rule" value="{{ old('repeat_rule') }}" maxlength="120" placeholder="{{ __('ui.daily_weekends_one_time_53004857cd') }}">
                            </label>
                            <label>
                                {{ __('ui.routine_0b5baf3098') }}
                                <select name="care_routine_id">
                                    <option value="">{{ __('ui.no_routine_5372d6774b') }}</option>
                                    @forelse ($routines as $routine)
                                        <option value="{{ $routine['id'] }}">{{ $routine['name'] }}</option>
                                    @empty
                                        <option value="" disabled>{{ __('ui.create_a_routine_below_466eb12b1f') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>
                                {{ __('ui.private_instruction_656a930cbc') }}
                                <textarea name="instructions" maxlength="3000">{{ old('instructions') }}</textarea>
                            </label>
                        </div>
                        <label class="care-check">
                            <input type="checkbox" name="requires_individual_confirmation" value="1">
                            <span>{{ __('ui.require_an_individual_confirmation_for_this_task_fde2e621db') }}</span>
                        </label>
                        <button class="action action--primary" type="submit"><x-ui-icon name="plus" /><span>{{ __('ui.add_task_ba423a4640') }}</span></button>
                    </form>
                </section>

                <section id="routines" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.versioned_plan_97de20f964') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.create_a_routine_8fcd077d42') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.routines.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <div class="care-form-grid">
                            <label>
                                {{ __('ui.routine_name_4ea82889e5') }}
                                <input name="name" maxlength="160" required placeholder="{{ __('ui.weekday_morning_35c77bb822') }}">
                            </label>
                            <label>
                                {{ __('ui.period_6e795d4d3c') }}
                                <select name="period" required>
                                    <option value="daily">{{ __('ui.daily_b36c2611dc') }}</option>
                                    <option value="weekdays">{{ __('ui.weekdays_6f4b602bb5') }}</option>
                                    <option value="weekends">{{ __('ui.weekends_93dcf1d1d2') }}</option>
                                    <option value="weekly">{{ __('ui.weekly_2975132481') }}</option>
                                    <option value="temporary">{{ __('ui.temporary_a29c13b958') }}</option>
                                    <option value="custom">{{ __('ui.custom_494ca78f73') }}</option>
                                </select>
                            </label>
                            <label>
                                {{ __('ui.starts_96dbedeca7') }}
                                <input type="date" name="starts_on" value="{{ $form_defaults['routine_starts_on'] }}" required>
                            </label>
                            <label>
                                {{ __('ui.ends_e98982c9f2') }}
                                <input type="date" name="ends_on">
                            </label>
                            <label>
                                {{ __('ui.preferred_time_13ae4c193d') }}
                                <input type="time" name="start_time">
                            </label>
                            <label>
                                {{ __('ui.private_instructions_10fceb6ef9') }}
                                <textarea name="instructions" maxlength="3000"></textarea>
                            </label>
                        </div>
                        <button class="action action--primary" type="submit"><x-ui-icon name="repeat-2" /><span>{{ __('ui.save_routine_2eb6ea92d5') }}</span></button>
                    </form>
                </section>
            </div>

            <aside>
                <section id="access" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.least_privilege_bdec4748d6') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.temporary_access_7059688673') }}</h2>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('care-journals.access.store', $care_journal['slug']) }}" class="grid gap-4">
                        @csrf
                        <label>
                            {{ __('ui.recipient_51fac985e9') }}
                            <input name="recipient_name" maxlength="160" required placeholder="{{ __('ui.alex_carter_805f38f620') }}">
                        </label>
                        <label>
                            {{ __('ui.role_14736a2eb9') }}
                            <select name="recipient_role" required>
                                <option value="sitter">{{ __('ui.sitter_d26540f1d7') }}</option>
                                <option value="family">{{ __('ui.family_bd2d677b2e') }}</option>
                                <option value="co-owner">{{ __('ui.co_owner_f3027e079c') }}</option>
                                <option value="veterinarian">{{ __('ui.veterinarian_38d6a38c0c') }}</option>
                                <option value="trainer">{{ __('ui.trainer_9f085ee951') }}</option>
                                <option value="groomer">{{ __('ui.groomer_1f4df5ea23') }}</option>
                                <option value="specialist">{{ __('ui.specialist_8302f971b5') }}</option>
                                <option value="shelter">{{ __('ui.shelter_cfcd1f3d6a') }}</option>
                            </select>
                        </label>
                        <label>
                            {{ __('ui.access_label_14b6448467') }}
                            <input name="label" maxlength="180" required placeholder="{{ __('ui.weekend_care_6701d63cf6') }}">
                        </label>
                        <fieldset>
                            <legend>{{ __('ui.visible_sections_da3cd28fb0') }}</legend>
                            <div class="care-check-grid">
                                @forelse ($access_section_options as $section)
                                    <label class="care-check">
                                        <input type="checkbox" name="sections[]" value="{{ $section['value'] }}">
                                        <span>{{ $section['label'] }}</span>
                                    </label>
                                @empty
                                    <span>{{ __('ui.no_sections_a767e0e78c') }}</span>
                                @endforelse
                            </div>
                        </fieldset>
                        <div class="care-form-grid">
                            <label>
                                {{ __('ui.expires_in_hours_fd2774777e') }}
                                <input type="number" name="expires_in_hours" min="1" max="720" value="72" required>
                            </label>
                            <label>
                                {{ __('ui.maximum_opens_2a0d565dd4') }}
                                <input type="number" name="max_views" min="1" max="200" value="30" required>
                            </label>
                        </div>
                        <div class="care-checks">
                            <label class="care-check"><input type="checkbox" name="allow_add" value="1"><span>{{ __('ui.may_add_reports_f263f8dca1') }}</span></label>
                            <label class="care-check"><input type="checkbox" name="allow_location" value="1"><span>{{ __('ui.may_see_add_locations_8cd64a616f') }}</span></label>
                            <label class="care-check"><input type="checkbox" name="allow_media" value="1"><span>{{ __('ui.may_add_media_0591ec1f5d') }}</span></label>
                            <label class="care-check"><input type="checkbox" name="privacy_acknowledged" value="1" required><span>{{ __('ui.i_reviewed_the_selected_access_c59f495ae2') }}</span></label>
                        </div>
                        <button class="action action--primary" type="submit"><x-ui-icon name="key-round" /><span>{{ __('ui.create_link_e6b850cff6') }}</span></button>
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
                                        <button type="submit" class="action action--compact"><x-ui-icon name="link-2-off" size="sm" /><span>{{ __('ui.revoke_87e6d00bbf') }}</span></button>
                                    </form>
                                @endif
                            </article>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_temporary_access_links_905ba29b53') }}</p>
                        @endforelse
                    </div>
                </section>

                <section id="audit" class="care-form-section">
                    <div class="care-section__heading">
                        <div>
                            <p class="text-xs font-bold uppercase text-paw-leaf">{{ __('ui.recent_access_trail_0c35ec219a') }}</p>
                            <h2 class="mt-1 text-xl font-bold">{{ __('ui.audit_bb6aea2873') }}</h2>
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
                            <p class="text-sm text-paw-muted">{{ __('ui.no_recent_journal_access_events_1e5d3aa22d') }}</p>
                        @endforelse
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-app-shell>
