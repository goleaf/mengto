@props(['form'])

<section class="panel form-panel" aria-labelledby="composer-title">
    <x-ui.section-heading
        :eyebrow="$form['eyebrow']"
        :title="$form['title']"
        title-id="composer-title"
        size="directory"
        :level="1"
    />

    <p class="form-panel__description">{{ $form['description'] }}</p>

    @if ($errors->any())
        <div role="alert" class="form-errors">
            <x-lucide-circle-alert class="icon icon--sm" aria-hidden="true" />
            <p>Please review the highlighted fields.</p>
        </div>
    @endif

    <form method="POST" action="{{ route('actions.perform') }}" class="form-grid">
        @csrf
        <input type="hidden" name="action" value="{{ $form['action'] }}">

        @foreach ($form['payload'] ?? [] as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach

        @foreach ($form['fields'] as $field)
            <x-ui.form-field :field="$field" />
        @endforeach

        <x-ui.action-group class="form-actions">
            <x-ui.action-control
                :href="route($form['cancel_route'], $form['cancel_parameters'] ?? [])"
                label="Cancel"
                icon="x"
                variant="paper"
                size="regular"
            />
            @foreach ($form['secondary_actions'] ?? [] as $action)
                <x-ui.action-control
                    type="submit"
                    :label="$action['label']"
                    :icon="$action['icon']"
                    :name="$action['name']"
                    :value="$action['value']"
                    variant="paper"
                    size="regular"
                />
            @endforeach
            <x-ui.action-control
                type="submit"
                :label="$form['submit_label']"
                :icon="$form['submit_icon']"
                variant="primary"
                size="regular"
            />
        </x-ui.action-group>
    </form>
</section>
