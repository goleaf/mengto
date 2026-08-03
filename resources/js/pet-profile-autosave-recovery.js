const AUTOSAVE_FORM_SELECTOR = 'form[data-pet-profile-autosave-step]';
const PENDING_ATTRIBUTE = 'data-pet-profile-autosave-pending';
const REVISION_ATTRIBUTE = 'data-pet-profile-autosave-revision';

let reconnectScheduled = false;

const autosaveFormFor = (target) => target.closest?.(AUTOSAVE_FORM_SELECTOR);

const markPending = (form) => {
    const currentRevision = Number.parseInt(form.getAttribute(REVISION_ATTRIBUTE) ?? '0', 10);
    const nextRevision = Number.isSafeInteger(currentRevision) ? currentRevision + 1 : 1;

    form.setAttribute(REVISION_ATTRIBUTE, String(nextRevision));
    form.setAttribute(PENDING_ATTRIBUTE, 'true');
};

const trackInput = (event) => {
    const form = autosaveFormFor(event.target);

    if (form) {
        markPending(form);
    }
};

const prepareAutosave = (event) => {
    const form = autosaveFormFor(event.target);

    if (!form) {
        return;
    }

    if (!form.hasAttribute(PENDING_ATTRIBUTE)) {
        markPending(form);
    }
};

const clearCompletedStep = (event) => {
    const completedStep = event.detail?.step;
    const completedRevision = event.detail?.revision;

    if (typeof completedStep !== 'string' || typeof completedRevision !== 'string') {
        return;
    }

    document.querySelectorAll(AUTOSAVE_FORM_SELECTOR).forEach((form) => {
        if (form.dataset.petProfileAutosaveStep === completedStep
            && form.getAttribute(REVISION_ATTRIBUTE) === completedRevision) {
            form.removeAttribute(PENDING_ATTRIBUTE);
        }
    });
};

const retryPendingForms = () => {
    if (reconnectScheduled) {
        return;
    }

    reconnectScheduled = true;

    window.setTimeout(() => {
        reconnectScheduled = false;

        document.querySelectorAll(`${AUTOSAVE_FORM_SELECTOR}[${PENDING_ATTRIBUTE}]`)
            .forEach((form) => form.dispatchEvent(new Event('change', { bubbles: true })));
    }, 0);
};

document.addEventListener('input', trackInput, true);
document.addEventListener('change', prepareAutosave, true);
window.addEventListener('pet-profile-autosave-completed', clearCompletedStep);
window.addEventListener('online', retryPendingForms);
