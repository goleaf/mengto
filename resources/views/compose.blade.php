<x-app-shell
    :owner="$owner"
    :title="$form['title'].' | PawCircle'"
    :active-section="$form['active_section']"
>
    <x-page-stack>
        <x-text-link :href="route($form['cancel_route'], $form['cancel_parameters'] ?? [])" icon="arrow-left" variant="back">
            Back
        </x-text-link>

        <x-composer-form :form="$form" />
    </x-page-stack>
</x-app-shell>
