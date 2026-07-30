const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback, { once: true });

        return;
    }

    callback();
};

const selectMarker = (map, marker, focus = false) => {
    const key = marker.dataset.placeMarker;
    const markers = [...map.querySelectorAll('[data-place-marker]')];
    const cards = [...document.querySelectorAll('[data-place-card]')];
    const card = cards.find((candidate) => candidate.dataset.placeCard === key);
    const selection = map.querySelector('[data-place-selection]');
    const link = selection?.querySelector('[data-place-selection-link]');

    markers.forEach((candidate) => {
        const active = candidate === marker;
        candidate.classList.toggle('place-marker--selected', active);
        candidate.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    cards.forEach((candidate) => {
        candidate.classList.toggle('place-card--selected', candidate === card);
    });

    map.dataset.selectedPlace = key ?? '';

    if (card && selection) {
        const title = card.querySelector('.place-card__title');
        const location = card.querySelector('.place-card__location');
        const selectedTitle = selection.querySelector('strong');
        const selectedMeta = selection.querySelector('span');

        if (selectedTitle && title) {
            selectedTitle.textContent = title.textContent;
        }

        if (selectedMeta && location) {
            selectedMeta.textContent = location.textContent;
        }

        if (link && title instanceof HTMLAnchorElement) {
            link.href = title.href;
        }
    }

    if (focus) {
        marker.focus({ preventScroll: true });
    }
};

const initializeMaps = () => {
    document.querySelectorAll('[data-place-map]').forEach((map) => {
        const markers = [...map.querySelectorAll('[data-place-marker]')];
        const canvas = map.querySelector('[data-place-map-canvas]');
        const fullscreen = map.querySelector('[data-place-fullscreen]');
        let zoom = 1;

        markers.forEach((marker, index) => {
            marker.addEventListener('click', () => selectMarker(map, marker));
            marker.addEventListener('keydown', (event) => {
                const lastIndex = markers.length - 1;
                let targetIndex = null;

                if (['ArrowRight', 'ArrowDown'].includes(event.key)) {
                    targetIndex = index === lastIndex ? 0 : index + 1;
                } else if (['ArrowLeft', 'ArrowUp'].includes(event.key)) {
                    targetIndex = index === 0 ? lastIndex : index - 1;
                } else if (event.key === 'Home') {
                    targetIndex = 0;
                } else if (event.key === 'End') {
                    targetIndex = lastIndex;
                }

                if (targetIndex === null) {
                    return;
                }

                event.preventDefault();
                selectMarker(map, markers[targetIndex], true);
            });
        });

        map.querySelectorAll('[data-place-zoom]').forEach((control) => {
            control.addEventListener('click', () => {
                const direction = control.dataset.placeZoom;
                zoom = direction === 'in'
                    ? Math.min(1.2, Number((zoom + 0.1).toFixed(1)))
                    : Math.max(0.9, Number((zoom - 0.1).toFixed(1)));

                if (canvas instanceof HTMLElement) {
                    canvas.style.transform = `scale(${zoom})`;
                }
            });
        });

        fullscreen?.addEventListener('click', () => {
            const active = map.classList.toggle('place-map--fullscreen-active');
            fullscreen.setAttribute('aria-pressed', active ? 'true' : 'false');
            document.body.style.overflow = active ? 'hidden' : '';
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape' || !map.classList.contains('place-map--fullscreen-active')) {
                return;
            }

            map.classList.remove('place-map--fullscreen-active');
            fullscreen?.setAttribute('aria-pressed', 'false');
            document.body.style.overflow = '';
            fullscreen?.focus();
        });
    });
};

const initializeLocation = () => {
    document.querySelectorAll('[data-place-location-form]').forEach((form) => {
        const trigger = form.querySelector('[data-place-locate]');
        const latitude = form.querySelector('[data-place-latitude]');
        const longitude = form.querySelector('[data-place-longitude]');
        const status = form.querySelector('[data-place-location-status]');

        trigger?.addEventListener('click', () => {
            if (!navigator.geolocation) {
                if (status) {
                    status.textContent = 'Location is unavailable in this browser.';
                }

                return;
            }

            trigger.disabled = true;

            if (status) {
                status.textContent = 'Getting an approximate current area…';
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    if (latitude instanceof HTMLInputElement) {
                        latitude.value = String(position.coords.latitude);
                    }

                    if (longitude instanceof HTMLInputElement) {
                        longitude.value = String(position.coords.longitude);
                    }

                    if (status) {
                        status.textContent = 'Approximate area received.';
                    }

                    form.requestSubmit();
                },
                () => {
                    trigger.disabled = false;

                    if (status) {
                        status.textContent = 'Location permission was not granted. Manual area search remains available.';
                    }
                },
                {
                    enableHighAccuracy: false,
                    maximumAge: 300000,
                    timeout: 10000,
                },
            );
        });
    });
};

const initializeAutoSubmit = () => {
    document.querySelectorAll('[data-auto-submit]').forEach((control) => {
        control.addEventListener('change', () => {
            control.form?.requestSubmit();
        });
    });
};

ready(() => {
    initializeMaps();
    initializeLocation();
    initializeAutoSubmit();
});
