@props([
    'action',
    'types',
    'idempotencyKey',
    'startedAt',
    'sourceType' => 'owner',
    'sourceName' => '',
    'submitLabel' => __('ui.record_care_action_b2937ac51f'),
    'compact' => false,
    'allowLocation' => true,
    'allowMedia' => true,
])

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    data-care-entry-offline-form
    data-offline-saved="{{ __('ui.care_entry_saved_offline') }}"
    data-offline-syncing="{{ __('ui.care_entries_syncing') }}"
    data-offline-synchronized="{{ __('ui.care_entries_synchronized') }}"
    data-offline-needs-review="{{ __('ui.care_entry_sync_needs_review') }}"
    data-offline-storage-unavailable="{{ __('ui.offline_storage_unavailable') }}"
    data-offline-media-too-large="{{ __('ui.offline_media_too_large') }}"
    {{ $attributes->class(['care-entry-form', 'care-entry-form--compact' => $compact]) }}
>
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">
    <input type="hidden" name="source_type" value="{{ $sourceType }}">
    <input type="hidden" name="source_name" value="{{ $sourceName }}">
    <input type="hidden" name="source_recorded_at" value="">
    <input type="hidden" name="source_timezone" value="">
    <input type="hidden" name="submitted_offline" value="0">

    <p class="care-sync-status" data-care-sync-status role="status" aria-live="polite" hidden></p>

    <div class="care-form-grid">
        <label>
            {{ __('ui.action_64cff1319d') }}
            <select name="entry_type" required>
                @forelse ($types as $type)
                    <option value="{{ $type['value'] }}" @selected(old('entry_type') === $type['value'])>{{ $type['label'] }}</option>
                @empty
                    <option value="">{{ __('ui.no_allowed_actions_0fb73607ff') }}</option>
                @endforelse
            </select>
        </label>
        <label>
            {{ __('ui.what_happened_483bd49023') }}
            <input name="title" value="{{ old('title') }}" maxlength="180" required placeholder="{{ __('ui.breakfast_park_walk_quiet_nap_1623873d92') }}">
        </label>
        <label>
            {{ __('ui.started_ecbc89cd37') }}
            <input type="datetime-local" name="started_at" value="{{ old('started_at', $startedAt) }}" required>
        </label>
        <label>
            {{ __('ui.outcome_4e80abb5b1') }}
            <select name="status" required>
                <option value="completed">{{ __('ui.completed_22a970d2e5') }}</option>
                <option value="partial">{{ __('ui.partially_completed_24421344ab') }}</option>
                <option value="in-progress">{{ __('ui.in_progress_c1f88e9d6c') }}</option>
                <option value="refused">{{ __('ui.pet_refused_323b25a990') }}</option>
                <option value="skipped">{{ __('ui.skipped_12698ce1ea') }}</option>
                <option value="needs-help">{{ __('ui.needs_help_102dd0fe0f') }}</option>
                <option value="needs-review">{{ __('ui.needs_review_07297fa94a') }}</option>
            </select>
        </label>
    </div>

    <details class="care-form-details">
        <summary><x-ui-icon name="sliders-horizontal" size="sm" /> {{ __('ui.structured_details_5a8ec19ecb') }}</summary>
        <div class="care-form-grid">
            <label>
                {{ __('ui.product_or_activity_cced0a8554') }}
                <input name="product_name" value="{{ old('product_name') }}" maxlength="180" placeholder="{{ __('ui.food_toy_grooming_product_b251812f21') }}">
            </label>
            <label>
                {{ __('ui.subtype_a26e25c373') }}
                <input name="subtype" value="{{ old('subtype') }}" maxlength="80" placeholder="{{ __('ui.breakfast_toilet_walk_brushing_a1434ee38b') }}">
            </label>
            <label>
                {{ __('ui.amount_49e96d7cdf') }}
                <input type="number" step="0.001" min="0" name="quantity_value" value="{{ old('quantity_value') }}">
            </label>
            <label>
                {{ __('ui.unit_4e545960f1') }}
                <select name="quantity_unit">
                    <option value="">{{ __('ui.not_measured_040f7884d3') }}</option>
                    @forelse (['g', 'kg', 'ml', 'l', 'oz', 'fl-oz', 'cups', 'pieces', 'packets', 'times', 'unknown'] as $unit)
                        <option value="{{ $unit }}" @selected(old('quantity_unit') === $unit)>{{ $unit }}</option>
                    @empty
                        <option value="">{{ __('ui.no_units_558efbe757') }}</option>
                    @endforelse
                </select>
            </label>
            <label>
                {{ __('ui.offered_95e7004b8d') }}
                <input name="amount_offered" value="{{ old('amount_offered') }}" maxlength="120" placeholder="{{ __('ui.150_g_4511515ef6') }}">
            </label>
            <label>
                {{ __('ui.consumed_847ba2a7cc') }}
                <input name="amount_consumed" value="{{ old('amount_consumed') }}" maxlength="120" placeholder="{{ __('ui.about_100_g_2f5362c946') }}">
            </label>
            <label>
                {{ __('ui.appetite_1b7037d5c7') }}
                <select name="appetite">
                    <option value="">{{ __('ui.not_assessed_025761aaeb') }}</option>
                    <option value="usual">{{ __('ui.usual_0f42435b74') }}</option>
                    <option value="good">{{ __('ui.good_c939327ca1') }}</option>
                    <option value="increased">{{ __('ui.increased_19513aa4f2') }}</option>
                    <option value="reduced">{{ __('ui.reduced_9f96285d77') }}</option>
                    <option value="strongly-reduced">{{ __('ui.strongly_reduced_5294990653') }}</option>
                    <option value="refused">{{ __('ui.refused_66b873543a') }}</option>
                    <option value="selective">{{ __('ui.selective_57999ad44c') }}</option>
                    <option value="unknown">{{ __('ui.unknown_b764cdc0ea') }}</option>
                </select>
            </label>
            <label>
                {{ __('ui.water_source_428868534e') }}
                <input name="water_source" value="{{ old('water_source') }}" maxlength="120" placeholder="{{ __('ui.kitchen_bowl_fountain_74d88c228f') }}">
            </label>
            <label>
                {{ __('ui.duration_minutes_c7d487ea6a') }}
                <input type="number" min="0" max="10080" name="duration_minutes" value="{{ old('duration_minutes') }}">
            </label>
            <label>
                {{ __('ui.distance_meters_e3792a8057') }}
                <input type="number" min="0" max="1000000" name="distance_meters" value="{{ old('distance_meters') }}">
            </label>
            <label>
                {{ __('ui.intensity_a6d01dcff3') }}
                <select name="intensity">
                    <option value="">{{ __('ui.not_assessed_025761aaeb') }}</option>
                    <option value="very-low">{{ __('ui.very_low_3090c619f6') }}</option>
                    <option value="low">{{ __('ui.low_f793de205e') }}</option>
                    <option value="moderate">{{ __('ui.moderate_5c42afc7a2') }}</option>
                    <option value="high">{{ __('ui.high_c4ebc6d4a5') }}</option>
                    <option value="very-high">{{ __('ui.very_high_0a84a4fa1d') }}</option>
                    <option value="unknown">{{ __('ui.unknown_b764cdc0ea') }}</option>
                </select>
            </label>
            <label>
                {{ __('ui.ended_7cdc804e69') }}
                <input type="datetime-local" name="ended_at" value="{{ old('ended_at') }}">
            </label>
            @if ($allowLocation)
                <label>
                    {{ __('ui.place_e9463dccf0') }}
                    <input name="location_label" value="{{ old('location_label') }}" maxlength="180" placeholder="{{ __('ui.general_place_only_620de8c87b') }}">
                </label>
                <label>
                    {{ __('ui.private_route_summary_a865ffd8dc') }}
                    <input name="route_summary" value="{{ old('route_summary') }}" maxlength="500" placeholder="{{ __('ui.route_remains_encrypted_and_private_f45bfdb29d') }}">
                </label>
            @endif
            <label>
                {{ __('ui.toilet_observation_8a4af0e376') }}
                <input name="toilet_quality" value="{{ old('toilet_quality') }}" maxlength="120" placeholder="{{ __('ui.formed_soft_attempt_only_95e9913b9d') }}">
            </label>
            <label>
                {{ __('ui.sleep_quality_646543197f') }}
                <input name="sleep_quality" value="{{ old('sleep_quality') }}" maxlength="120" placeholder="{{ __('ui.calm_interrupted_restless_07c0714096') }}">
            </label>
            <label>
                {{ __('ui.mood_780fe43f6e') }}
                <input name="mood" value="{{ old('mood') }}" maxlength="120" placeholder="{{ __('ui.calm_playful_anxious_a822629770') }}">
            </label>
            <label>
                {{ __('ui.environment_temperature_c_8432c9f4c8') }}
                <input type="number" step="0.1" min="-80" max="100" name="temperature_c" value="{{ old('temperature_c') }}">
            </label>
            <label>
                {{ __('ui.trigger_or_context_6417742b70') }}
                <textarea name="trigger" maxlength="500">{{ old('trigger') }}</textarea>
            </label>
            <label>
                {{ __('ui.result_6e7d50e84f') }}
                <textarea name="result" maxlength="500">{{ old('result') }}</textarea>
            </label>
        </div>
    </details>

    <label>
        {{ __('ui.private_note_8b95b48431') }}
        <textarea name="notes" maxlength="5000" placeholder="{{ __('ui.facts_reaction_what_changed_or_what_should_be_c1634f0ddf') }}">{{ old('notes') }}</textarea>
    </label>

    @if ($allowMedia)
        <div class="care-form-grid">
            <label>
                {{ __('ui.private_photo_or_short_video_5b5cc4cf72') }}
                <input type="file" name="media" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime">
            </label>
            <label>
                {{ __('ui.media_description_2e22cefe8c') }}
                <input name="media_alt" value="{{ old('media_alt') }}" maxlength="500" placeholder="{{ __('ui.describe_the_useful_visible_detail_4d73322126') }}">
            </label>
        </div>
    @endif

    <div class="care-checks">
        <label class="care-check">
            <input type="checkbox" name="is_unusual" value="1" @checked(old('is_unusual'))>
            <span>{{ __('ui.mark_as_an_unusual_observation_550fb3f38f') }}</span>
        </label>
        <label class="care-check">
            <input type="checkbox" name="confirm_duplicate" value="1" @checked(old('confirm_duplicate'))>
            <span>{{ __('ui.this_is_intentionally_separate_from_a_recent_feeding_aadfe2e861') }}</span>
        </label>
    </div>

    <button type="submit" class="action action--primary">
        <x-ui-icon name="check" />
        <span>{{ $submitLabel }}</span>
    </button>
</form>
