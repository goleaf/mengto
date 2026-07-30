@props([
    'action',
    'types',
    'idempotencyKey',
    'startedAt',
    'sourceType' => 'owner',
    'sourceName' => '',
    'submitLabel' => 'Record care action',
    'compact' => false,
    'allowLocation' => true,
    'allowMedia' => true,
])

<form
    method="POST"
    action="{{ $action }}"
    enctype="multipart/form-data"
    {{ $attributes->class(['care-entry-form', 'care-entry-form--compact' => $compact]) }}
>
    @csrf
    <input type="hidden" name="idempotency_key" value="{{ old('idempotency_key', $idempotencyKey) }}">
    <input type="hidden" name="source_type" value="{{ $sourceType }}">
    <input type="hidden" name="source_name" value="{{ $sourceName }}">

    <div class="care-form-grid">
        <label>
            Action
            <select name="entry_type" required>
                @forelse ($types as $type)
                    <option value="{{ $type['value'] }}" @selected(old('entry_type') === $type['value'])>{{ $type['label'] }}</option>
                @empty
                    <option value="">No allowed actions</option>
                @endforelse
            </select>
        </label>
        <label>
            What happened
            <input name="title" value="{{ old('title') }}" maxlength="180" required placeholder="Breakfast, park walk, quiet nap">
        </label>
        <label>
            Started
            <input type="datetime-local" name="started_at" value="{{ old('started_at', $startedAt) }}" required>
        </label>
        <label>
            Outcome
            <select name="status" required>
                <option value="completed">Completed</option>
                <option value="partial">Partially completed</option>
                <option value="in-progress">In progress</option>
                <option value="refused">Pet refused</option>
                <option value="skipped">Skipped</option>
                <option value="needs-help">Needs help</option>
                <option value="needs-review">Needs review</option>
            </select>
        </label>
    </div>

    <details class="care-form-details">
        <summary><x-lucide-sliders-horizontal class="size-4" aria-hidden="true" /> Structured details</summary>
        <div class="care-form-grid">
            <label>
                Product or activity
                <input name="product_name" value="{{ old('product_name') }}" maxlength="180" placeholder="Food, toy, grooming product">
            </label>
            <label>
                Subtype
                <input name="subtype" value="{{ old('subtype') }}" maxlength="80" placeholder="Breakfast, toilet walk, brushing">
            </label>
            <label>
                Amount
                <input type="number" step="0.001" min="0" name="quantity_value" value="{{ old('quantity_value') }}">
            </label>
            <label>
                Unit
                <select name="quantity_unit">
                    <option value="">Not measured</option>
                    @forelse (['g', 'kg', 'ml', 'l', 'oz', 'fl-oz', 'cups', 'pieces', 'packets', 'times', 'unknown'] as $unit)
                        <option value="{{ $unit }}" @selected(old('quantity_unit') === $unit)>{{ $unit }}</option>
                    @empty
                        <option value="">No units</option>
                    @endforelse
                </select>
            </label>
            <label>
                Offered
                <input name="amount_offered" value="{{ old('amount_offered') }}" maxlength="120" placeholder="150 g">
            </label>
            <label>
                Consumed
                <input name="amount_consumed" value="{{ old('amount_consumed') }}" maxlength="120" placeholder="About 100 g">
            </label>
            <label>
                Appetite
                <select name="appetite">
                    <option value="">Not assessed</option>
                    <option value="usual">Usual</option>
                    <option value="good">Good</option>
                    <option value="increased">Increased</option>
                    <option value="reduced">Reduced</option>
                    <option value="strongly-reduced">Strongly reduced</option>
                    <option value="refused">Refused</option>
                    <option value="selective">Selective</option>
                    <option value="unknown">Unknown</option>
                </select>
            </label>
            <label>
                Water source
                <input name="water_source" value="{{ old('water_source') }}" maxlength="120" placeholder="Kitchen bowl, fountain">
            </label>
            <label>
                Duration, minutes
                <input type="number" min="0" max="10080" name="duration_minutes" value="{{ old('duration_minutes') }}">
            </label>
            <label>
                Distance, meters
                <input type="number" min="0" max="1000000" name="distance_meters" value="{{ old('distance_meters') }}">
            </label>
            <label>
                Intensity
                <select name="intensity">
                    <option value="">Not assessed</option>
                    <option value="very-low">Very low</option>
                    <option value="low">Low</option>
                    <option value="moderate">Moderate</option>
                    <option value="high">High</option>
                    <option value="very-high">Very high</option>
                    <option value="unknown">Unknown</option>
                </select>
            </label>
            <label>
                Ended
                <input type="datetime-local" name="ended_at" value="{{ old('ended_at') }}">
            </label>
            @if ($allowLocation)
                <label>
                    Place
                    <input name="location_label" value="{{ old('location_label') }}" maxlength="180" placeholder="General place only">
                </label>
                <label>
                    Private route summary
                    <input name="route_summary" value="{{ old('route_summary') }}" maxlength="500" placeholder="Route remains encrypted and private">
                </label>
            @endif
            <label>
                Toilet observation
                <input name="toilet_quality" value="{{ old('toilet_quality') }}" maxlength="120" placeholder="Formed, soft, attempt only">
            </label>
            <label>
                Sleep quality
                <input name="sleep_quality" value="{{ old('sleep_quality') }}" maxlength="120" placeholder="Calm, interrupted, restless">
            </label>
            <label>
                Mood
                <input name="mood" value="{{ old('mood') }}" maxlength="120" placeholder="Calm, playful, anxious">
            </label>
            <label>
                Environment temperature, C
                <input type="number" step="0.1" min="-80" max="100" name="temperature_c" value="{{ old('temperature_c') }}">
            </label>
            <label>
                Trigger or context
                <textarea name="trigger" maxlength="500">{{ old('trigger') }}</textarea>
            </label>
            <label>
                Result
                <textarea name="result" maxlength="500">{{ old('result') }}</textarea>
            </label>
        </div>
    </details>

    <label>
        Private note
        <textarea name="notes" maxlength="5000" placeholder="Facts, reaction, what changed, or what should be watched">{{ old('notes') }}</textarea>
    </label>

    @if ($allowMedia)
        <div class="care-form-grid">
            <label>
                Private photo or short video
                <input type="file" name="media" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime">
            </label>
            <label>
                Media description
                <input name="media_alt" value="{{ old('media_alt') }}" maxlength="500" placeholder="Describe the useful visible detail">
            </label>
        </div>
    @endif

    <div class="care-checks">
        <label class="care-check">
            <input type="checkbox" name="is_unusual" value="1" @checked(old('is_unusual'))>
            <span>Mark as an unusual observation</span>
        </label>
        <label class="care-check">
            <input type="checkbox" name="confirm_duplicate" value="1" @checked(old('confirm_duplicate'))>
            <span>This is intentionally separate from a recent feeding</span>
        </label>
    </div>

    <button type="submit" class="action action--primary">
        <x-lucide-check class="icon" aria-hidden="true" />
        <span>{{ $submitLabel }}</span>
    </button>
</form>
