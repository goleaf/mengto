<x-app-shell
    :owner="$owner"
    :title="__('presentation.brand_title', ['title' => $form['title']])"
    :active-section="$form['active_section']"
>
    <x-page-stack>
        <x-page-header
            :eyebrow="$form['eyebrow']"
            :title="$form['title']"
            :description="$form['description']"
            heading-id="composer-title"
            :action-label="__('ui.back')"
            action-icon="arrow-left"
            :action-href="route($form['cancel_route'], $form['cancel_parameters'] ?? [])"
            action-variant="paper"
            data-section="composer-header"
        />

        <x-composer-form :form="$form" />
    </x-page-stack>
</x-app-shell>
