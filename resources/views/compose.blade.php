<x-layout.app-shell
    :owner="$owner"
    :title="$form['title'].' | PawCircle'"
    :active-section="$form['active_section']"
>
    <x-layout.page-stack>
        <x-ui.text-link :href="route($form['cancel_route'], $form['cancel_parameters'] ?? [])" icon="arrow-left" variant="back">
            Back
        </x-ui.text-link>

        <x-feature.composer-form :form="$form" />
    </x-layout.page-stack>
</x-layout.app-shell>
