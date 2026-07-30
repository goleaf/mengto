const DATABASE_NAME = 'care-journal-offline-v1';
const DATABASE_VERSION = 1;
const STORE_NAME = 'pending-entries';
const MAX_OFFLINE_MEDIA_BYTES = 15 * 1024 * 1024;

let activeSynchronization = null;

const forms = () => [...document.querySelectorAll('[data-care-entry-offline-form]')];

const messageFor = (form, key) => form.dataset[key] ?? '';

const announce = (form, message) => {
    const status = form.querySelector('[data-care-sync-status]');

    if (!status || !message) {
        return;
    }

    status.textContent = message;
    status.hidden = false;
};

const currentTimezone = () => {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
    } catch {
        return 'UTC';
    }
};

const openDatabase = () => new Promise((resolve, reject) => {
    if (!('indexedDB' in window)) {
        reject(new Error('indexed-db-unavailable'));

        return;
    }

    const request = indexedDB.open(DATABASE_NAME, DATABASE_VERSION);

    request.onupgradeneeded = () => {
        const database = request.result;

        if (!database.objectStoreNames.contains(STORE_NAME)) {
            database.createObjectStore(STORE_NAME, { keyPath: 'idempotencyKey' });
        }
    };
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
});

const useStore = async (mode, operation) => {
    const database = await openDatabase();

    try {
        return await new Promise((resolve, reject) => {
            const transaction = database.transaction(STORE_NAME, mode);
            const store = transaction.objectStore(STORE_NAME);
            const request = operation(store);

            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
            transaction.onabort = () => reject(transaction.error);
        });
    } finally {
        database.close();
    }
};

const saveEntry = (entry) => useStore('readwrite', (store) => store.put(entry));
const removeEntry = (key) => useStore('readwrite', (store) => store.delete(key));
const pendingEntries = () => useStore('readonly', (store) => store.getAll());

const updateEntry = (entry) => useStore('readwrite', (store) => store.put(entry));

const stampSourceMetadata = (form, submittedOffline) => {
    form.elements.source_recorded_at.value = new Date().toISOString();
    form.elements.source_timezone.value = currentTimezone();
    form.elements.submitted_offline.value = submittedOffline ? '1' : '0';
};

const serializableFields = (formData) => [...formData.entries()]
    .filter(([name]) => name !== '_token');

const idempotencyKeyFor = (form) => form.elements.idempotency_key.value;

const prepareNextSubmission = (form) => {
    form.reset();
    form.elements.idempotency_key.value = crypto.randomUUID();
    stampSourceMetadata(form, false);
};

const queueOfflineSubmission = async (form) => {
    stampSourceMetadata(form, true);

    const formData = new FormData(form);
    const media = formData.get('media');

    if (media instanceof File && media.size > MAX_OFFLINE_MEDIA_BYTES) {
        announce(form, messageFor(form, 'offlineMediaTooLarge'));

        return;
    }

    const idempotencyKey = idempotencyKeyFor(form);

    try {
        await saveEntry({
            idempotencyKey,
            action: form.action,
            fields: serializableFields(formData),
            queuedAt: new Date().toISOString(),
            state: 'pending',
        });
        prepareNextSubmission(form);
        announce(form, messageFor(form, 'offlineSaved'));
    } catch {
        announce(form, messageFor(form, 'offlineStorageUnavailable'));
    }
};

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const formDataFor = (entry) => {
    const formData = new FormData();

    entry.fields.forEach(([name, value]) => formData.append(name, value));
    formData.set('_token', csrfToken());
    formData.set('submitted_offline', '1');

    return formData;
};

const isMatchingConfirmation = (payload, entry) => (
    payload?.data?.idempotency_key === entry.idempotencyKey
);

const synchronizeEntry = async (entry) => {
    const response = await fetch(entry.action, {
        method: 'POST',
        body: formDataFor(entry),
        credentials: 'same-origin',
        redirect: 'follow',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
    });

    if (response.status === 422) {
        await updateEntry({ ...entry, state: 'needs-review' });

        return 'needs-review';
    }

    if ([401, 403, 404, 410].includes(response.status)) {
        await removeEntry(entry.idempotencyKey);

        return 'access-ended';
    }

    if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
        return 'retry-later';
    }

    const payload = await response.json();

    if (!isMatchingConfirmation(payload, entry)) {
        return 'retry-later';
    }

    await removeEntry(entry.idempotencyKey);

    return 'synchronized';
};

const performSynchronization = async () => {
    const visibleForms = forms();
    let synchronized = 0;
    let needsReview = false;

    try {
        const entries = (await pendingEntries())
            .filter((entry) => entry.state === 'pending')
            .sort((left, right) => left.queuedAt.localeCompare(right.queuedAt));

        if (entries.length === 0) {
            return;
        }

        visibleForms.forEach((form) => announce(form, messageFor(form, 'offlineSyncing')));

        for (const entry of entries) {
            const result = await synchronizeEntry(entry);

            if (result === 'synchronized') {
                synchronized += 1;
            } else if (result === 'needs-review') {
                needsReview = true;
            } else if (result === 'retry-later') {
                break;
            }
        }
    } catch {
        return;
    }

    visibleForms.forEach((form) => {
        if (needsReview) {
            announce(form, messageFor(form, 'offlineNeedsReview'));
        } else if (synchronized > 0) {
            announce(form, messageFor(form, 'offlineSynchronized'));
        }
    });
};

const synchronizePendingEntries = () => {
    if (!navigator.onLine || activeSynchronization) {
        return activeSynchronization;
    }

    const synchronize = () => performSynchronization();
    activeSynchronization = ('locks' in navigator)
        ? navigator.locks.request('care-journal-sync-v1', synchronize)
        : synchronize();

    activeSynchronization
        .catch(() => undefined)
        .finally(() => {
            activeSynchronization = null;
        });

    return activeSynchronization;
};

document.addEventListener('submit', (event) => {
    const form = event.target.closest?.('[data-care-entry-offline-form]');

    if (!form) {
        return;
    }

    stampSourceMetadata(form, !navigator.onLine);

    if (navigator.onLine) {
        return;
    }

    event.preventDefault();
    void queueOfflineSubmission(form);
});

window.addEventListener('online', () => {
    void synchronizePendingEntries();
});

if (navigator.onLine) {
    void synchronizePendingEntries();
}
