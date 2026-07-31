let generatedId = 0;

const nextId = (prefix) => {
    generatedId += 1;

    return `${prefix}-${generatedId}`;
};

const modeledField = (control, field) => Array.from(control.attributes).some(
    (attribute) => attribute.name.startsWith('wire:model') && attribute.value === field,
);

const fieldControl = (summary, field) => {
    const livewireRoot = summary.closest('[wire\\:id]');
    const scope = livewireRoot ?? document;

    return Array.from(scope.querySelectorAll('input, select, textarea')).find(
        (control) => control.name === field || modeledField(control, field),
    );
};

const associate = (control, error) => {
    control.id ||= nextId('forum-field');
    error.id ||= nextId('forum-error');
    error.dataset.forumErrorMessage = 'true';
    control.setAttribute('aria-invalid', 'true');

    const describedBy = new Set(
        (control.getAttribute('aria-describedby') ?? '').split(/\s+/).filter(Boolean),
    );
    const errorIds = new Set(
        (control.dataset.forumErrorIds ?? '').split(/\s+/).filter(Boolean),
    );

    describedBy.add(error.id);
    errorIds.add(error.id);
    control.setAttribute('aria-describedby', Array.from(describedBy).join(' '));
    control.dataset.forumErrorIds = Array.from(errorIds).join(' ');
};

const clearStaleAssociations = (root = document) => {
    root.querySelectorAll('[data-forum-error-ids]').forEach((control) => {
        const errorIds = new Set(
            (control.dataset.forumErrorIds ?? '').split(/\s+/).filter(Boolean),
        );
        const remainingErrorIds = Array.from(errorIds).filter(
            (id) => document.getElementById(id)?.dataset.forumErrorMessage === 'true',
        );
        const describedBy = (control.getAttribute('aria-describedby') ?? '')
            .split(/\s+/)
            .filter((id) => id && (!errorIds.has(id) || remainingErrorIds.includes(id)));

        if (describedBy.length > 0) {
            control.setAttribute('aria-describedby', describedBy.join(' '));
        } else {
            control.removeAttribute('aria-describedby');
        }

        if (remainingErrorIds.length > 0) {
            control.dataset.forumErrorIds = remainingErrorIds.join(' ');
        } else {
            control.removeAttribute('aria-invalid');
            delete control.dataset.forumErrorIds;
        }
    });
};

const prepareSummary = (summary) => {
    summary.querySelectorAll('[data-error-field]').forEach((error) => {
        const field = error.dataset.errorField;
        const control = field ? fieldControl(summary, field) : null;

        if (control instanceof HTMLElement) {
            associate(control, error);
        }
    });

    if (summary.dataset.focusApplied !== 'true') {
        summary.dataset.focusApplied = 'true';
        summary.focus();
    }
};

const prepareInlineError = (error) => {
    if (error.closest('[data-forum-error-summary]')) {
        return;
    }

    const field = error.closest('label, .forum-form__field');
    const control = field?.querySelector('input, select, textarea');

    if (control instanceof HTMLElement) {
        associate(control, error);
    }
};

const initializeForumAccessibility = (root = document) => {
    clearStaleAssociations(root);
    root.querySelectorAll('[data-forum-error-summary]').forEach(prepareSummary);
    root.querySelectorAll('.forum-page [role="alert"], .forum-form [role="alert"]')
        .forEach(prepareInlineError);
};

const containsValidation = (nodes) => Array.from(nodes).some(
    (node) => node instanceof Element && (
        node.matches('[data-forum-error-summary], [role="alert"]')
        || node.querySelector('[data-forum-error-summary], [role="alert"]')
    ),
);

const observer = new MutationObserver((mutations) => {
    const containsValidationChange = mutations.some(
        ({ addedNodes, removedNodes }) => containsValidation(addedNodes)
            || containsValidation(removedNodes),
    );

    if (containsValidationChange) {
        queueMicrotask(() => initializeForumAccessibility());
    }
});

initializeForumAccessibility();
observer.observe(document.documentElement, { childList: true, subtree: true });
document.addEventListener('livewire:navigated', () => initializeForumAccessibility());
