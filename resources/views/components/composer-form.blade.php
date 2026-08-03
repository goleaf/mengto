@props(['form'])

<section class="panel form-panel" aria-labelledby="composer-title">
    @if ($errors->any())
        <div role="alert" class="form-errors">
            <x-lucide-circle-alert class="icon icon--sm" aria-hidden="true" />
            <p>{{ __('ui.please_review_the_highlighted_fields_9941c0ee3c') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('actions.perform') }}" class="form-grid">
        @csrf
        <input type="hidden" name="action" value="{{ $form['action'] }}">

        @foreach ($form['payload'] ?? [] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        @foreach ($form['fields'] as $field)
            <x-form-field :field="$field" />
        @endforeach

        <x-action-group class="form-actions">
            <x-action-control
                :href="route($form['cancel_route'], $form['cancel_parameters'] ?? [])"
                label="{{ __('ui.cancel_19766ed6cc') }}"
                icon="x"
                variant="paper"
                size="regular"
            />
            @foreach ($form['secondary_actions'] ?? [] as $action)
                <x-action-control
                    type="submit"
                    :label="$action['label']"
                    :icon="$action['icon']"
                    :name="$action['name']"
                    :value="$action['value']"
                    variant="paper"
                    size="regular"
                />
            @endforeach
            <x-action-control
                type="submit"
                :label="$form['submit_label']"
                :icon="$form['submit_icon']"
                variant="primary"
                size="regular"
            />
        </x-action-group>
    </form>
</section>
