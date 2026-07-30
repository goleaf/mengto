<x-app-shell
    :owner="$owner"
    :title="__('presentation.brand_title', ['title' => $form['title']])"
    :active-section="$form['active_section']"
>
    <x-page-stack>
        <x-text-link :href="route($form['cancel_route'], $form['cancel_parameters'] ?? [])" icon="arrow-left" variant="back">
            {{ __('ui.back_76900f1bfd') }}
        </x-text-link>

        <x-composer-form :form="$form" />
    </x-page-stack>
</x-app-shell>
