@props([
    'action',
    'types',
    'idempotencyKey',
    'startedAt',
    'sourceType' => 'owner',
    'sourceName' => '',
    'submitLabel' => __('ui.record_care_action'),
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
            {{ __('ui.action') }}
            <select name="entry_type" required>
                @forelse ($types as $type)
                    <option value="{{ $type['value'] }}" @selected(old('entry_type') === $type['value'])>{{ $type['label'] }}</option>
                @empty
                    <option value="">{{ __('ui.no_allowed_actions') }}</option>
                @endforelse
            </select>
        </label>
        <label>
            {{ __('ui.what_happened') }}
            <input name="title" value="{{ old('title') }}" maxlength="180" required placeholder="{{ __('ui.breakfast_park_walk_quiet_nap') }}">
        </label>
        <label>
            {{ __('ui.started') }}
            <input type="datetime-local" name="started_at" value="{{ old('started_at', $startedAt) }}" required>
        </label>
        <label>
            {{ __('ui.outcome') }}
            <select name="status" required>
                <option value="completed">{{ __('ui.completed') }}</option>
                <option value="partial">{{ __('ui.partially_completed') }}</option>
                <option value="in-progress">{{ __('ui.in_progress') }}</option>
                <option value="refused">{{ __('ui.pet_refused') }}</option>
                <option value="skipped">{{ __('ui.skipped') }}</option>
                <option value="needs-help">{{ __('ui.needs_help') }}</option>
                <option value="needs-review">{{ __('ui.needs_review') }}</option>
            </select>
        </label>
    </div>

    <details class="care-form-details">
        <summary><x-ui-icon name="sliders-horizontal" size="sm" /> {{ __('ui.structured_details') }}</summary>
        <div class="care-form-grid">
            <label>
                {{ __('ui.product_or_activity') }}
                <input name="product_name" value="{{ old('product_name') }}" maxlength="180" placeholder="{{ __('ui.food_toy_grooming_product') }}">
            </label>
            <label>
                {{ __('ui.subtype') }}
                <input name="subtype" value="{{ old('subtype') }}" maxlength="80" placeholder="{{ __('ui.breakfast_toilet_walk_brushing') }}">
            </label>
            <label>
                {{ __('ui.amount') }}
                <input type="number" step="0.001" min="0" name="quantity_value" value="{{ old('quantity_value') }}">
            </label>
            <label>
                {{ __('ui.unit') }}
                <select name="quantity_unit">
                    <option value="">{{ __('ui.not_measured') }}</option>
                    @forelse (['g', 'kg', 'ml', 'l', 'oz', 'fl-oz', 'cups', 'pieces', 'packets', 'times', 'unknown'] as $unit)
                        <option value="{{ $unit }}" @selected(old('quantity_unit') === $unit)>{{ $unit }}</option>
                    @empty
                        <option value="">{{ __('ui.no_units') }}</option>
                    @endforelse
                </select>
            </label>
            <label>
                {{ __('ui.offered') }}
                <input name="amount_offered" value="{{ old('amount_offered') }}" maxlength="120" placeholder="{{ __('ui.150_g') }}">
            </label>
            <label>
                {{ __('ui.consumed') }}
                <input name="amount_consumed" value="{{ old('amount_consumed') }}" maxlength="120" placeholder="{{ __('ui.about_100_g') }}">
            </label>
            <label>
                {{ __('ui.appetite') }}
                <select name="appetite">
                    <option value="">{{ __('ui.not_assessed') }}</option>
                    <option value="usual">{{ __('ui.usual') }}</option>
                    <option value="good">{{ __('ui.good') }}</option>
                    <option value="increased">{{ __('ui.increased') }}</option>
                    <option value="reduced">{{ __('ui.reduced') }}</option>
                    <option value="strongly-reduced">{{ __('ui.strongly_reduced') }}</option>
                    <option value="refused">{{ __('ui.refused') }}</option>
                    <option value="selective">{{ __('ui.selective') }}</option>
                    <option value="unknown">{{ __('ui.unknown') }}</option>
                </select>
            </label>
            <label>
                {{ __('ui.water_source') }}
                <input name="water_source" value="{{ old('water_source') }}" maxlength="120" placeholder="{{ __('ui.kitchen_bowl_fountain') }}">
            </label>
            <label>
                {{ __('ui.duration_minutes') }}
                <input type="number" min="0" max="10080" name="duration_minutes" value="{{ old('duration_minutes') }}">
            </label>
            <label>
                {{ __('ui.distance_meters') }}
                <input type="number" min="0" max="1000000" name="distance_meters" value="{{ old('distance_meters') }}">
            </label>
            <label>
                {{ __('ui.intensity') }}
                <select name="intensity">
                    <option value="">{{ __('ui.not_assessed') }}</option>
                    <option value="very-low">{{ __('ui.very_low') }}</option>
                    <option value="low">{{ __('ui.low') }}</option>
                    <option value="moderate">{{ __('ui.moderate') }}</option>
                    <option value="high">{{ __('ui.high') }}</option>
                    <option value="very-high">{{ __('ui.very_high') }}</option>
                    <option value="unknown">{{ __('ui.unknown') }}</option>
                </select>
            </label>
            <label>
                {{ __('ui.ended') }}
                <input type="datetime-local" name="ended_at" value="{{ old('ended_at') }}">
            </label>
            @if ($allowLocation)
                <label>
                    {{ __('ui.place') }}
                    <input name="location_label" value="{{ old('location_label') }}" maxlength="180" placeholder="{{ __('ui.general_place_only') }}">
                </label>
                <label>
                    {{ __('ui.private_route_summary') }}
                    <input name="route_summary" value="{{ old('route_summary') }}" maxlength="500" placeholder="{{ __('ui.route_remains_encrypted_and_private') }}">
                </label>
            @endif
            <label>
                {{ __('ui.toilet_observation') }}
                <input name="toilet_quality" value="{{ old('toilet_quality') }}" maxlength="120" placeholder="{{ __('ui.formed_soft_attempt_only') }}">
            </label>
            <label>
                {{ __('ui.sleep_quality') }}
                <input name="sleep_quality" value="{{ old('sleep_quality') }}" maxlength="120" placeholder="{{ __('ui.calm_interrupted_restless') }}">
            </label>
            <label>
                {{ __('ui.mood') }}
                <input name="mood" value="{{ old('mood') }}" maxlength="120" placeholder="{{ __('ui.calm_playful_anxious') }}">
            </label>
            <label>
                {{ __('ui.environment_temperature_c') }}
                <input type="number" step="0.1" min="-80" max="100" name="temperature_c" value="{{ old('temperature_c') }}">
            </label>
            <label>
                {{ __('ui.trigger_or_context') }}
                <textarea name="trigger" maxlength="500">{{ old('trigger') }}</textarea>
            </label>
            <label>
                {{ __('ui.result') }}
                <textarea name="result" maxlength="500">{{ old('result') }}</textarea>
            </label>
        </div>
    </details>

    <label>
        {{ __('ui.private_note') }}
        <textarea name="notes" maxlength="5000" placeholder="{{ __('ui.facts_reaction_what_changed_or_what_should_be_watched') }}">{{ old('notes') }}</textarea>
    </label>

    @if ($allowMedia)
        <div class="care-form-grid">
            <label>
                {{ __('ui.private_photo_or_short_video') }}
                <input type="file" name="media" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime">
            </label>
            <label>
                {{ __('ui.media_description') }}
                <input name="media_alt" value="{{ old('media_alt') }}" maxlength="500" placeholder="{{ __('ui.describe_the_useful_visible_detail') }}">
            </label>
        </div>
    @endif

    <div class="care-checks">
        <label class="care-check">
            <input type="checkbox" name="is_unusual" value="1" @checked(old('is_unusual'))>
            <span>{{ __('ui.mark_as_an_unusual_observation') }}</span>
        </label>
        <label class="care-check">
            <input type="checkbox" name="confirm_duplicate" value="1" @checked(old('confirm_duplicate'))>
            <span>{{ __('ui.this_is_intentionally_separate_from_a_recent_feeding') }}</span>
        </label>
    </div>

    <button type="submit" class="action action--primary">
        <x-ui-icon name="check" />
        <span>{{ $submitLabel }}</span>
    </button>
</form>
