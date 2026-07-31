const editor = document.querySelector('[data-forum-editor]');

if (editor instanceof HTMLFormElement) {
    const storageKey = `forum-draft:${editor.dataset.draftKey ?? 'new'}`;
    const title = editor.querySelector('[name="title"]');
    const category = editor.querySelector('[name="category"]');
    const similar = editor.querySelector('[data-similar-topics]');
    const endpoint = editor.dataset.similarEndpoint;
    const photos = editor.querySelector('[data-forum-photos]');
    const video = editor.querySelector('[data-forum-video]');
    const mediaDescription = editor.querySelector('[data-forum-media-description]');
    const videoTranscript = editor.querySelector('[data-forum-video-transcript]');
    const captions = editor.querySelector('[data-forum-caption]');
    const captionLocale = editor.querySelector('[data-forum-caption-locale]');
    const language = editor.querySelector('[name="language"]');
    let debounce;

    const fields = () => Array.from(editor.elements).filter(
        (field) => field instanceof HTMLInputElement
            || field instanceof HTMLTextAreaElement
            || field instanceof HTMLSelectElement,
    );

    try {
        const saved = JSON.parse(localStorage.getItem(storageKey) ?? '{}');

        fields().forEach((field) => {
            if (!field.name || field.name.startsWith('_') || field.type === 'file' || field.value !== '') {
                return;
            }

            if (field.type === 'checkbox') {
                field.checked = Boolean(saved[field.name]);
            } else if (typeof saved[field.name] === 'string') {
                field.value = saved[field.name];
            }
        });
    } catch {
        localStorage.removeItem(storageKey);
    }

    const saveDraft = () => {
        const payload = {};

        fields().forEach((field) => {
            if (!field.name || field.name.startsWith('_') || field.type === 'file') {
                return;
            }

            payload[field.name] = field.type === 'checkbox' ? field.checked : field.value;
        });

        localStorage.setItem(storageKey, JSON.stringify(payload));
    };

    editor.addEventListener('input', saveDraft);
    editor.addEventListener('change', saveDraft);
    editor.addEventListener('submit', () => {
        localStorage.removeItem(storageKey);

        window.setTimeout(() => {
            editor.querySelectorAll('button[type="submit"]').forEach((button) => {
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
            });
        });
    });

    const syncMediaRequirements = () => {
        const hasPhotos = photos instanceof HTMLInputElement && (photos.files?.length ?? 0) > 0;
        const hasVideo = video instanceof HTMLInputElement && (video.files?.length ?? 0) > 0;
        const hasCaptions = captions instanceof HTMLInputElement && (captions.files?.length ?? 0) > 0;

        if (mediaDescription instanceof HTMLInputElement) {
            mediaDescription.required = hasPhotos || hasVideo;
        }

        if (videoTranscript instanceof HTMLTextAreaElement) {
            videoTranscript.required = hasVideo;
        }

        if (captionLocale instanceof HTMLSelectElement) {
            captionLocale.required = hasCaptions;

            if (
                hasCaptions
                && !captionLocale.value
                && language instanceof HTMLSelectElement
            ) {
                captionLocale.value = language.value;
            }
        }
    };

    photos?.addEventListener('change', syncMediaRequirements);
    video?.addEventListener('change', syncMediaRequirements);
    captions?.addEventListener('change', syncMediaRequirements);
    syncMediaRequirements();

    const findSimilar = () => {
        if (!(title instanceof HTMLInputElement) || !endpoint || title.value.trim().length < 20) {
            if (similar) {
                similar.replaceChildren();
            }

            return;
        }

        const url = new URL(endpoint, window.location.origin);
        url.searchParams.set('q', title.value.trim());

        if (category instanceof HTMLSelectElement && category.value) {
            url.searchParams.set('category', category.value);
        }

        fetch(url, { headers: { Accept: 'application/json' } })
            .then((response) => response.ok ? response.json() : Promise.reject(response))
            .then((payload) => {
                if (!similar) {
                    return;
                }

                similar.replaceChildren();

                payload.data.forEach((topic) => {
                    const link = document.createElement('a');
                    const heading = document.createElement('span');
                    const meta = document.createElement('small');

                    link.href = topic.url;
                    link.target = '_blank';
                    link.rel = 'noopener';
                    heading.textContent = topic.title;
                    meta.textContent = `${topic.category} / ${topic.status} / ${topic.answers} answers`;
                    link.append(heading, meta);
                    similar.append(link);
                });
            })
            .catch(() => {
                if (similar) {
                    similar.replaceChildren();
                }
            });
    };

    title?.addEventListener('input', () => {
        window.clearTimeout(debounce);
        debounce = window.setTimeout(findSimilar, 350);
    });
}
