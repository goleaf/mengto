const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });

        return;
    }

    callback();
};

const initializeComposer = () => {
    document.querySelectorAll('[data-message-composer]').forEach((form) => {
        const body = form.querySelector('[data-message-body]');
        const type = form.querySelector('[data-message-type]');
        const buttons = [...form.querySelectorAll('[data-message-type-button]')];
        const draftStatus = form.querySelector('[data-message-draft-status]');

        if (!(body instanceof HTMLTextAreaElement) || !(type instanceof HTMLInputElement)) {
            return;
        }

        const draftKey = body.dataset.draftKey;
        const savedDraft = draftKey ? window.localStorage.getItem(draftKey) : null;

        if (savedDraft && body.value.trim() === '') {
            body.value = savedDraft;
        }

        let draftTimer;
        body.addEventListener('input', () => {
            window.clearTimeout(draftTimer);

            if (draftStatus) {
                draftStatus.textContent = 'Saving draft…';
            }

            draftTimer = window.setTimeout(() => {
                if (draftKey) {
                    window.localStorage.setItem(draftKey, body.value);
                }

                if (draftStatus) {
                    draftStatus.textContent = 'Draft saved on this device';
                }
            }, 250);
        });

        body.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' || !event.ctrlKey) {
                return;
            }

            event.preventDefault();
            form.requestSubmit();
        });

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                const selectedType = button.dataset.messageTypeButton ?? 'text';
                const isActive = type.value === selectedType;

                type.value = isActive ? 'text' : selectedType;
                buttons.forEach((candidate) => {
                    candidate.setAttribute(
                        'aria-pressed',
                        candidate === button && !isActive ? 'true' : 'false',
                    );
                });

                const labels = {
                    audio: 'Audio message',
                    image: 'Photo for this conversation',
                    video: 'Video for this conversation',
                    file: 'Document for this conversation',
                    pet: 'Shared pet profile',
                    place: 'Shared place',
                    event: 'Shared event',
                    task: 'New shared task',
                };

                if (!isActive && body.value.trim() === '') {
                    body.value = labels[selectedType] ?? '';
                    body.dispatchEvent(new Event('input'));
                }

                body.focus();
            });
        });

        form.addEventListener('submit', () => {
            if (draftKey) {
                window.localStorage.removeItem(draftKey);
            }
        });
    });
};

const initializeAudio = () => {
    document.querySelectorAll('[data-audio-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const playing = button.getAttribute('aria-pressed') === 'true';
            button.setAttribute('aria-pressed', playing ? 'false' : 'true');
            button.setAttribute('aria-label', playing ? 'Play audio message' : 'Pause audio message');

            const icon = button.querySelector('svg');
            if (icon) {
                icon.style.opacity = playing ? '1' : '0.7';
            }
        });
    });
};

const initializeReplies = () => {
    const replyPanel = document.querySelector('[data-message-reply]');
    const replyValue = document.querySelector('[data-message-reply-value]');
    const replyBody = document.querySelector('[data-message-body]');

    if (!(replyPanel instanceof HTMLElement) || !(replyValue instanceof HTMLInputElement)) {
        return;
    }

    document.querySelectorAll('[data-message-reply-trigger]').forEach((button) => {
        button.addEventListener('click', () => {
            replyValue.value = button.dataset.messageReplyText ?? '';
            replyPanel.hidden = false;
            replyBody?.focus();
        });
    });

    document.querySelector('[data-message-reply-clear]')?.addEventListener('click', () => {
        replyValue.value = '';
        replyPanel.hidden = true;
        replyBody?.focus();
    });
};

const initializeCallStage = () => {
    const stage = document.querySelector('[data-call-stage]');

    if (!(stage instanceof HTMLElement) || stage.hidden) {
        return;
    }

    const preview = stage.querySelector('[data-call-preview]');
    const placeholder = stage.querySelector('[data-call-placeholder]');
    const status = stage.querySelector('[data-call-device-status]');
    const dialog = stage.querySelector('[role="dialog"]');
    const endForm = stage.querySelector('[data-call-end]');
    let stream = null;

    const stopStream = () => {
        stream?.getTracks().forEach((track) => track.stop());
        stream = null;
    };

    stage.querySelectorAll('[data-call-device]').forEach((button) => {
        button.addEventListener('click', async () => {
            if (!navigator.mediaDevices?.getUserMedia) {
                if (status) {
                    status.textContent = 'Device preview is unavailable in this browser.';
                }

                return;
            }

            const camera = button.dataset.callDevice === 'camera';

            try {
                stopStream();
                stream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                    video: camera,
                });

                if (status) {
                    status.textContent = camera
                        ? 'Camera and microphone preview active only on this device.'
                        : 'Microphone permission granted. Remote media is not connected.';
                }

                if (camera && preview instanceof HTMLVideoElement) {
                    preview.srcObject = stream;
                    preview.hidden = false;
                    placeholder?.setAttribute('hidden', '');
                    await preview.play();
                }
            } catch {
                if (status) {
                    status.textContent = 'Device permission was not granted. You can keep using text chat.';
                }
            }
        });
    });

    stage.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', stopStream);
    });

    if (dialog instanceof HTMLElement) {
        const focusable = [
            ...dialog.querySelectorAll(
                'a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), select:not([disabled]), textarea:not([disabled])',
            ),
        ].filter(
            (element) => element instanceof HTMLElement
                && !element.hidden
                && element.getClientRects().length > 0,
        );

        focusable[0]?.focus();

        dialog.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && endForm instanceof HTMLFormElement) {
                event.preventDefault();
                endForm.requestSubmit();

                return;
            }

            if (event.key !== 'Tab' || focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    window.addEventListener('beforeunload', stopStream, { once: true });
};

ready(() => {
    initializeComposer();
    initializeAudio();
    initializeReplies();
    initializeCallStage();
});
