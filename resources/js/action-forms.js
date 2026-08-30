const setPending = (form, pending) => {
    form.dataset.actionPending = pending ? 'true' : 'false';
    form.setAttribute('aria-busy', pending ? 'true' : 'false');

    form.querySelectorAll('[data-action-submit]').forEach((submitter) => {
        if (!(submitter instanceof HTMLButtonElement)) {
            return;
        }

        submitter.toggleAttribute('disabled', pending || submitter.dataset.initiallyDisabled === 'true');
        submitter.setAttribute('aria-disabled', submitter.disabled ? 'true' : 'false');

        const label = submitter.querySelector('[data-action-label]');
        const loadingLabel = submitter.querySelector('[data-action-loading-label]');

        if (label instanceof HTMLElement && loadingLabel instanceof HTMLElement) {
            label.hidden = pending;
            loadingLabel.hidden = !pending;
        }
    });
};

const initializeActionForms = () => {
    document.querySelectorAll('[data-action-form]').forEach((form) => {
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        form.querySelectorAll('[data-action-submit]').forEach((submitter) => {
            if (submitter instanceof HTMLButtonElement) {
                submitter.dataset.initiallyDisabled = submitter.disabled ? 'true' : 'false';
            }
        });

        form.addEventListener('submit', (event) => {
            if (form.dataset.actionPending === 'true') {
                event.preventDefault();

                return;
            }

            setPending(form, true);
        });
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeActionForms, { once: true });
} else {
    initializeActionForms();
}

window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-action-form]').forEach((form) => {
        if (form instanceof HTMLFormElement) {
            setPending(form, false);
        }
    });
});
