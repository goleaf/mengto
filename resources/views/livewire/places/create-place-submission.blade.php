<x-page-stack data-section="place-submission-create">
    <x-page-header
        :eyebrow="__('places.submissions.create.eyebrow')"
        :title="__('places.submissions.create.title')"
        :description="__('places.submissions.create.description')"
        heading-id="place-submission-heading"
        :action-label="__('places.submissions.actions.back')"
        action-icon="arrow-left"
        :action-href="route('places.index')"
    />

    <form wire:submit="submit" class="panel stack" novalidate aria-labelledby="place-submission-heading">
        @if ($errors->any())
            <x-forum-error-summary :messages="$errors->getMessages()" :heading="__('places.submissions.validation.summary')" />
        @endif

        <div class="form-grid">
            <label class="stack" for="place-submission-name">
                <span>{{ __('places.submissions.fields.name') }}</span>
                <input id="place-submission-name" class="field" wire:model="form.name" type="text" maxlength="180" required>
                @error('form.name') <span class="field-error" aria-live="polite">{{ $message }}</span> @enderror
            </label>

            <label class="stack" for="place-submission-category">
                <span>{{ __('places.submissions.fields.category') }}</span>
                <select id="place-submission-category" class="field" wire:model="form.catalogCategory">
                    @forelse ($this->categoryOptions as $value => $label)
                        <option wire:key="place-category-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                    @empty
                        <option value="">{{ __('places.submissions.empty.options') }}</option>
                    @endforelse
                </select>
            </label>

            <label class="stack" for="place-submission-region">
                <span>{{ __('places.submissions.fields.region') }}</span>
                <input id="place-submission-region" class="field" wire:model="form.publicRegion" type="text" maxlength="160" required>
                @error('form.publicRegion') <span class="field-error" aria-live="polite">{{ $message }}</span> @enderror
            </label>

            <label class="stack" for="place-submission-precision">
                <span>{{ __('places.submissions.fields.location_precision') }}</span>
                <select id="place-submission-precision" class="field" wire:model.live="form.locationPrecision">
                    @forelse ($this->precisionOptions as $value => $label)
                        <option wire:key="place-precision-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                    @empty
                        <option value="public_region">{{ __('places.submissions.location_precision.public_region') }}</option>
                    @endforelse
                </select>
            </label>

            <label class="stack" for="place-submission-address">
                <span>{{ __('places.submissions.fields.public_address') }}</span>
                <input id="place-submission-address" class="field" wire:model="form.publicAddress" type="text" maxlength="500">
            </label>

            @if ($form->locationPrecision === 'public_point')
                <label class="stack" for="place-submission-latitude">
                    <span>{{ __('places.submissions.fields.public_latitude') }}</span>
                    <input id="place-submission-latitude" class="field" wire:model="form.publicLatitude" type="text" inputmode="decimal">
                </label>
                <label class="stack" for="place-submission-longitude">
                    <span>{{ __('places.submissions.fields.public_longitude') }}</span>
                    <input id="place-submission-longitude" class="field" wire:model="form.publicLongitude" type="text" inputmode="decimal">
                </label>
            @endif

            @if ($form->locationPrecision === 'private_exact')
                <label class="stack" for="place-submission-exact-address">
                    <span>{{ __('places.submissions.fields.exact_address') }}</span>
                    <input id="place-submission-exact-address" class="field" wire:model="form.exactAddress" type="text" maxlength="2000">
                    <small>{{ __('places.submissions.fields.exact_private_help') }}</small>
                </label>
                <label class="stack" for="place-submission-exact-latitude">
                    <span>{{ __('places.submissions.fields.exact_latitude') }}</span>
                    <input id="place-submission-exact-latitude" class="field" wire:model="form.exactLatitude" type="text" inputmode="decimal">
                </label>
                <label class="stack" for="place-submission-exact-longitude">
                    <span>{{ __('places.submissions.fields.exact_longitude') }}</span>
                    <input id="place-submission-exact-longitude" class="field" wire:model="form.exactLongitude" type="text" inputmode="decimal">
                </label>
            @endif

            <label class="stack" for="place-submission-source">
                <span>{{ __('places.submissions.fields.source') }}</span>
                <select id="place-submission-source" class="field" wire:model="form.source">
                    @forelse ($this->sourceOptions as $value => $label)
                        <option wire:key="place-source-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                    @empty
                        <option value="personal_visit">{{ __('places.submissions.sources.personal_visit') }}</option>
                    @endforelse
                </select>
            </label>

            <label class="stack" for="place-submission-source-reference">
                <span>{{ __('places.submissions.fields.source_reference') }}</span>
                <input id="place-submission-source-reference" class="field" wire:model="form.sourceReference" type="text" maxlength="2048">
            </label>

            <label class="stack" for="place-submission-relationship">
                <span>{{ __('places.submissions.fields.relationship') }}</span>
                <select id="place-submission-relationship" class="field" wire:model="form.relationshipToPlace">
                    @foreach (['visitor', 'customer', 'employee', 'owner', 'organization', 'public-observer'] as $relationship)
                        <option wire:key="place-relationship-{{ $relationship }}" value="{{ $relationship }}">{{ __('places.submissions.relationships.'.$relationship) }}</option>
                    @endforeach
                </select>
            </label>

            @foreach ([
                'publicPhone' => ['public_phone', 'tel'],
                'publicEmail' => ['public_email', 'email'],
                'publicWebsite' => ['public_website', 'url'],
                'observedAt' => ['observed_at', 'date'],
            ] as $property => [$field, $type])
                <label wire:key="place-field-{{ $field }}" class="stack" for="place-submission-{{ $field }}">
                    <span>{{ __('places.submissions.fields.'.$field) }}</span>
                    <input id="place-submission-{{ $field }}" class="field" wire:model="form.{{ $property }}" type="{{ $type }}">
                    @error('form.'.$property) <span class="field-error" aria-live="polite">{{ $message }}</span> @enderror
                </label>
            @endforeach
        </div>

        @foreach ([
            'summary' => 'summary',
            'hours' => 'hours',
            'services' => 'services',
            'rulesText' => 'rules',
            'features' => 'features',
        ] as $property => $field)
            <label wire:key="place-detail-{{ $field }}" class="stack" for="place-submission-{{ $field }}">
                <span>{{ __('places.submissions.fields.'.$field) }}</span>
                <textarea id="place-submission-{{ $field }}" class="field" wire:model="form.{{ $property }}" rows="3"></textarea>
            </label>
        @endforeach

        <label class="cluster" for="place-submission-consent">
            <input id="place-submission-consent" wire:model="form.consentGranted" type="checkbox" required>
            <span>{{ __('places.submissions.fields.consent') }}</span>
        </label>
        @error('form.consentGranted') <span class="field-error" aria-live="polite">{{ $message }}</span> @enderror

        <p wire:offline class="notice" role="status">{{ __('places.submissions.states.offline') }}</p>
        <button class="action action--primary action--regular" type="submit" wire:loading.attr="disabled" wire:target="submit">
            <span wire:loading.remove wire:target="submit">{{ __('places.submissions.actions.submit') }}</span>
            <span wire:loading wire:target="submit">{{ __('places.submissions.states.loading') }}</span>
        </button>
    </form>
</x-page-stack>
