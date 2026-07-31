import PhotoSwipeLightbox from 'photoswipe/lightbox';

const gallerySelector = '[data-photo-gallery]';
const triggerSelector = '[data-photo-trigger]';
const photoParameter = 'photo';
let photoLightbox = null;

const updateLocation = (photoKey = null) => {
    const url = new URL(window.location.href);

    if (photoKey) {
        url.searchParams.set(photoParameter, photoKey);
    } else {
        url.searchParams.delete(photoParameter);
    }

    window.history.replaceState(window.history.state, '', url);
};

const panelTemplate = (trigger) => {
    const gallery = trigger?.closest(gallerySelector);
    const photoKey = trigger?.dataset.photoKey;

    if (!gallery || !photoKey) {
        return null;
    }

    return [...gallery.querySelectorAll('template[data-photo-panel-template]')]
        .find((template) => template.dataset.photoPanelTemplate === photoKey) ?? null;
};

const renderPanel = (panel, pswp) => {
    const trigger = pswp.currSlide?.data?.element;
    const template = panelTemplate(trigger);

    panel.replaceChildren();

    if (!template) {
        updateLocation();

        return;
    }

    panel.append(template.content.cloneNode(true));
    updateLocation(trigger.dataset.photoKey);
};

const openRequestedPhoto = (lightbox) => {
    const photoKey = new URL(window.location.href).searchParams.get(photoParameter);

    if (!photoKey) {
        return;
    }

    const trigger = [...document.querySelectorAll(triggerSelector)]
        .find((item) => item.dataset.photoKey === photoKey);
    const gallery = trigger?.closest(gallerySelector);

    if (!trigger || !gallery) {
        updateLocation();

        return;
    }

    const index = [...gallery.querySelectorAll(triggerSelector)].indexOf(trigger);

    if (index >= 0) {
        lightbox.loadAndOpen(index, { gallery });
    }
};

const initializePhotoViewer = () => {
    photoLightbox?.destroy();
    photoLightbox = null;

    const firstGallery = document.querySelector(gallerySelector);

    if (!firstGallery) {
        return;
    }

    const lightbox = new PhotoSwipeLightbox({
        gallery: gallerySelector,
        children: triggerSelector,
        pswpModule: () => import('photoswipe'),
        bgOpacity: 0.96,
        showHideAnimationType: 'zoom',
        wheelToZoom: true,
        closeTitle: firstGallery.dataset.photoClose,
        zoomTitle: firstGallery.dataset.photoZoom,
        arrowPrevTitle: firstGallery.dataset.photoPrevious,
        arrowNextTitle: firstGallery.dataset.photoNext,
        errorMsg: firstGallery.dataset.photoError,
        indexIndicatorSep: firstGallery.dataset.photoSeparator,
        paddingFn: (viewportSize) => {
            if (viewportSize.x >= 1024) {
                return { top: 16, bottom: 16, left: 16, right: 448 };
            }

            return {
                top: 16,
                bottom: Math.min(viewportSize.y * 0.46, 448) + 16,
                left: 16,
                right: 16,
            };
        },
    });

    lightbox.on('uiRegister', () => {
        lightbox.pswp.ui.registerElement({
            name: 'photo-social-panel',
            className: 'photo-viewer-panel',
            appendTo: 'root',
            onInit: (panel, pswp) => {
                pswp.element?.setAttribute('aria-modal', 'true');
                panel.setAttribute('role', 'complementary');
                panel.setAttribute('aria-label', document.querySelector(gallerySelector)?.getAttribute('aria-label') ?? '');
                pswp.on('change', () => renderPanel(panel, pswp));
                renderPanel(panel, pswp);
            },
        });
    });

    lightbox.on('close', () => updateLocation());
    lightbox.init();
    photoLightbox = lightbox;
    openRequestedPhoto(lightbox);
};

const destroyPhotoViewer = () => {
    photoLightbox?.destroy();
    photoLightbox = null;
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePhotoViewer, { once: true });
} else {
    initializePhotoViewer();
}

document.addEventListener('livewire:navigating', destroyPhotoViewer);
document.addEventListener('livewire:navigated', initializePhotoViewer);
