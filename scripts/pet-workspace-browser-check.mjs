import { spawn } from 'node:child_process';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const baseUrl = (process.env.BROWSER_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const outputDirectory = process.env.BROWSER_OUTPUT_DIR ?? join(tmpdir(), 'mengto-pet-workspace-browser');
const verifyAutosave = process.argv.includes('--autosave');
const verifyBirth = process.argv.includes('--birth');
const verifyBreed = process.argv.includes('--breed');
const verifyNames = process.argv.includes('--names');
const allowDataMutation = process.env.BROWSER_ALLOW_DATA_MUTATION === '1';
const origin = new URL(baseUrl);
const chromeCandidates = [
    process.env.CHROME_BIN,
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
].filter(Boolean);

if (!['localhost', '127.0.0.1', '::1'].includes(origin.hostname)) {
    throw new Error('The pet workspace browser check only runs against a loopback URL.');
}

if ((verifyAutosave || verifyBirth || verifyBreed || verifyNames) && ! allowDataMutation) {
    throw new Error('--autosave, --birth, --breed, and --names require BROWSER_ALLOW_DATA_MUTATION=1 and a disposable database.');
}

const assert = (condition, message) => {
    if (!condition) throw new Error(message);
};

const chromeExecutable = async () => {
    for (const candidate of chromeCandidates) {
        try {
            await access(candidate);
            return candidate;
        } catch {
            // Continue through platform-specific candidates.
        }
    }

    throw new Error('Chrome was not found. Set CHROME_BIN.');
};

const waitForFile = async (path, timeout = 10_000) => {
    const deadline = Date.now() + timeout;

    while (Date.now() < deadline) {
        try {
            return await readFile(path, 'utf8');
        } catch {
            await delay(50);
        }
    }

    throw new Error(`Timed out waiting for ${path}.`);
};

class CdpClient {
    constructor(socket) {
        this.socket = socket;
        this.nextId = 0;
        this.pending = new Map();
        this.listeners = new Map();
        socket.addEventListener('message', ({ data }) => {
            const message = JSON.parse(data);

            if (message.id) {
                const pending = this.pending.get(message.id);
                this.pending.delete(message.id);
                message.error
                    ? pending?.reject(new Error(message.error.message))
                    : pending?.resolve(message.result);
                return;
            }

            for (const listener of this.listeners.get(message.method) ?? []) {
                if (listener.sessionId === undefined || listener.sessionId === message.sessionId) {
                    listener.callback(message.params ?? {});
                }
            }
        });
    }

    static async connect(url) {
        const socket = new WebSocket(url);
        await new Promise((resolve, reject) => {
            socket.addEventListener('open', resolve, { once: true });
            socket.addEventListener('error', reject, { once: true });
        });
        return new CdpClient(socket);
    }

    send(method, params = {}, sessionId = undefined) {
        const id = ++this.nextId;
        const message = { id, method, params };
        if (sessionId) message.sessionId = sessionId;
        this.socket.send(JSON.stringify(message));
        return new Promise((resolve, reject) => this.pending.set(id, { resolve, reject }));
    }

    on(method, callback, sessionId = undefined) {
        const listeners = this.listeners.get(method) ?? [];
        listeners.push({ callback, sessionId });
        this.listeners.set(method, listeners);
    }

    once(method, sessionId, timeout = 15_000) {
        return new Promise((resolve, reject) => {
            const listeners = this.listeners.get(method) ?? [];
            const listener = {
                sessionId,
                callback: (params) => {
                    clearTimeout(timer);
                    this.listeners.set(method, listeners.filter((entry) => entry !== listener));
                    resolve(params);
                },
            };
            const timer = setTimeout(() => {
                this.listeners.set(method, listeners.filter((entry) => entry !== listener));
                reject(new Error(`Timed out waiting for ${method}.`));
            }, timeout);
            listeners.push(listener);
            this.listeners.set(method, listeners);
        });
    }

    close() {
        this.socket.close();
    }
}

const evaluate = async (client, sessionId, expression) => {
    const result = await client.send('Runtime.evaluate', {
        expression,
        awaitPromise: true,
        returnByValue: true,
    }, sessionId);
    if (result.exceptionDetails) {
        throw new Error(
            result.exceptionDetails.exception?.description
                ?? result.exceptionDetails.text,
        );
    }
    return result.result.value;
};

const navigate = async (client, sessionId, url) => {
    const loaded = client.once('Page.loadEventFired', sessionId);
    await client.send('Page.navigate', { url }, sessionId);
    await loaded;
    await delay(300);
};

const waitUntil = async (callback, message, timeout = 15_000) => {
    const deadline = Date.now() + timeout;
    while (Date.now() < deadline) {
        if (await callback()) return;
        await delay(100);
    }
    throw new Error(message);
};

const login = async (client, sessionId) => {
    await navigate(client, sessionId, `${baseUrl}/login`);
    await evaluate(client, sessionId, `(() => {
        const setValue = (selector, value) => {
            const input = document.querySelector(selector);
            const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
            setter.call(input, value);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        setValue('#login-email', 'mia@example.test');
        setValue('#login-password', 'password');
        document.querySelector('[data-auth-page="login"] .auth-button--primary').click();
    })()`);
    await waitUntil(
        async () => !(await evaluate(client, sessionId, 'location.pathname')).includes('/login'),
        'Login did not complete.',
    );
};

const setLocale = async (client, sessionId, locale) => {
    await navigate(client, sessionId, `${baseUrl}/profile/settings`);
    const current = await evaluate(client, sessionId, 'document.querySelector("#profile-settings-locale")?.value');
    if (current === locale) return;
    await evaluate(client, sessionId, `((locale) => {
        const input = document.querySelector('#profile-settings-locale');
        input.value = locale;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        document.querySelector('form button[type="submit"]').click();
    })(${JSON.stringify(locale)})`);
    await waitUntil(
        async () => await evaluate(client, sessionId, `document.documentElement.lang === ${JSON.stringify(locale)}`),
        `Locale did not change to ${locale}.`,
    );
};

const auditExpression = `(() => {
    const visible = (element) => {
        const style = getComputedStyle(element);
        const box = element.getBoundingClientRect();
        return style.display !== 'none' && style.visibility !== 'hidden' && box.width > 0 && box.height > 0;
    };
    const controls = [...document.querySelectorAll('main a, main button, main input, main select')].filter(visible);
    const unnamed = controls.filter((element) => !(
        element.getAttribute('aria-label') || element.getAttribute('aria-labelledby')
        || element.labels?.length || element.textContent.trim() || element.title
    ));
    const smallTargets = controls.filter((element) => {
        const box = element.getBoundingClientRect();
        return box.width < 44 || box.height < 44;
    }).map((element) => ({ tag: element.tagName, text: element.textContent.trim().slice(0, 40), box: element.getBoundingClientRect().toJSON() }));
    const ids = [...document.querySelectorAll('[id]')].map((element) => element.id).filter(Boolean);
    return {
        language: document.documentElement.lang,
        title: document.title,
        h1Count: document.querySelectorAll('main h1').length,
        mainCount: document.querySelectorAll('main').length,
        workspaceCount: document.querySelectorAll('[data-section="pet-profile-workspace"]').length,
        cardCount: document.querySelectorAll('[data-pet-workspace-profile]').length,
        searchCount: document.querySelectorAll('#pet-workspace-search').length,
        invitationCount: document.querySelectorAll('[data-section="pet-workspace-invitations"]').length,
        followCount: [...document.querySelectorAll('main button, main a')].filter((element) => /^(Follow|Sekti|Подписаться)$/i.test(element.textContent.trim())).length,
        prototypeNames: ['Maple', 'Olive', 'Pico', 'Clover'].filter((name) => document.body.innerText.includes(name)),
        rawKeys: document.body.innerText.match(/\\b(?:pet_workspace|pet_profiles)\\.[a-z0-9_.-]+/gi) ?? [],
        privateLeak: /mia@example\\.test|discovery-demo-post-v1/.test(document.body.innerText),
        overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        unnamed,
        smallTargets,
        duplicateIds: [...new Set(ids.filter((id, index) => ids.indexOf(id) !== index))],
    };
})()`;

await mkdir(outputDirectory, { recursive: true });
const profileDirectory = await mkdtemp(join(tmpdir(), 'mengto-pet-workspace-chrome-'));
const browser = spawn(await chromeExecutable(), [
    '--headless=new', '--disable-background-networking', '--disable-default-apps', '--disable-extensions',
    '--disable-gpu', '--hide-scrollbars', '--no-first-run', '--remote-debugging-port=0',
    `--user-data-dir=${profileDirectory}`, 'about:blank',
], { stdio: ['ignore', 'ignore', 'pipe'] });
const browserExited = new Promise((resolve) => browser.once('exit', resolve));

let client;
let sessionId;
let originalLocale = 'en';

try {
    const [port, browserPath] = (await waitForFile(join(profileDirectory, 'DevToolsActivePort'))).trim().split(/\r?\n/);
    client = await CdpClient.connect(`ws://127.0.0.1:${port}${browserPath}`);
    const { targetId } = await client.send('Target.createTarget', { url: 'about:blank' });
    ({ sessionId } = await client.send('Target.attachToTarget', { targetId, flatten: true }));
    const consoleErrors = [];
    const livewireRequests = [];
    const livewireRequestIds = new Set();
    const failedLivewireRequests = [];
    client.on('Runtime.exceptionThrown', ({ exceptionDetails }) => consoleErrors.push(exceptionDetails.text), sessionId);
    client.on('Log.entryAdded', ({ entry }) => {
        if (entry.level === 'error') consoleErrors.push(entry.text);
    }, sessionId);
    client.on('Network.requestWillBeSent', ({ request, requestId }) => {
        const path = new URL(request.url).pathname;

        if (path.includes('/livewire-') && path.endsWith('/update')) {
            livewireRequests.push(request.url);
            livewireRequestIds.add(requestId);
        }
    }, sessionId);
    client.on('Network.loadingFailed', ({ errorText, requestId }) => {
        if (livewireRequestIds.has(requestId)) {
            failedLivewireRequests.push({ errorText, requestId });
        }
    }, sessionId);
    await Promise.all([
        client.send('Page.enable', {}, sessionId), client.send('Runtime.enable', {}, sessionId),
        client.send('Log.enable', {}, sessionId), client.send('Network.enable', {}, sessionId),
    ]);
    await client.send('Emulation.setEmulatedMedia', {
        features: [{ name: 'prefers-reduced-motion', value: 'reduce' }],
    }, sessionId);
    await login(client, sessionId);
    originalLocale = await evaluate(client, sessionId, 'document.documentElement.lang');
    const audits = {};

    for (const viewport of [
        { label: 'desktop-en', width: 1440, height: 900, mobile: false, locale: 'en' },
        { label: 'mobile-ru', width: 375, height: 812, mobile: true, locale: 'ru' },
        { label: 'mobile-lt-320', width: 320, height: 900, mobile: true, locale: 'lt' },
    ]) {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: viewport.width, height: viewport.height, deviceScaleFactor: 1,
            mobile: viewport.mobile, screenWidth: viewport.width, screenHeight: viewport.height,
        }, sessionId);
        await client.send('Emulation.setTouchEmulationEnabled', { enabled: viewport.mobile }, sessionId);
        await setLocale(client, sessionId, viewport.locale);
        await navigate(client, sessionId, `${baseUrl}/pets`);
        const audit = await evaluate(client, sessionId, auditExpression);
        assert(audit.language === viewport.locale, `${viewport.label}: locale mismatch.`);
        assert(audit.h1Count === 1 && audit.mainCount === 1, `${viewport.label}: invalid document landmarks.`);
        assert(audit.workspaceCount === 1, `${viewport.label}: workspace shell is missing.`);
        assert(audit.cardCount >= 2, `${viewport.label}: seeded managed profiles are missing.`);
        assert(audit.searchCount === 1, `${viewport.label}: canonical search is missing.`);
        assert(audit.invitationCount === 1, `${viewport.label}: pending invitation summary is missing.`);
        assert(audit.followCount === 0, `${viewport.label}: obsolete Follow action remains.`);
        assert(audit.prototypeNames.length === 0, `${viewport.label}: prototype pets remain ${audit.prototypeNames}.`);
        assert(audit.rawKeys.length === 0, `${viewport.label}: translation keys are visible.`);
        assert(!audit.privateLeak, `${viewport.label}: private account data leaked.`);
        assert(audit.overflow <= 1, `${viewport.label}: horizontal overflow is ${audit.overflow}px.`);
        assert(audit.unnamed.length === 0, `${viewport.label}: unnamed controls remain.`);
        assert(audit.duplicateIds.length === 0, `${viewport.label}: duplicate IDs remain.`);
        if (viewport.mobile) assert(audit.smallTargets.length === 0, `${viewport.label}: controls below 44px remain.`);
        audits[viewport.label] = audit;
        const screenshot = await client.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true }, sessionId);
        await writeFile(join(outputDirectory, `pets-${viewport.label}.png`), Buffer.from(screenshot.data, 'base64'));
    }

    let autosaveAudit = null;
    let birthAudit = null;
    let breedAudit = null;
    let nameAudit = null;

    if (verifyBirth) {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: 1440, height: 900, deviceScaleFactor: 1,
            mobile: false, screenWidth: 1440, screenHeight: 900,
        }, sessionId);
        await client.send('Emulation.setTouchEmulationEnabled', { enabled: false }, sessionId);
        await setLocale(client, sessionId, 'en');
        const manageUrl = `${baseUrl}/pets/manage/pet-scout?step=age-sex`;
        await navigate(client, sessionId, manageUrl);
        const initial = await evaluate(client, sessionId, `(() => ({
            pathname: location.pathname + location.search,
            heading: document.querySelector('main h1')?.textContent.trim() ?? null,
            precisionOptions: document.querySelectorAll('#managed-pet-birth-precision option').length,
            dateVisible: Boolean(document.querySelector('#managed-pet-birth-date')),
            yearVisible: Boolean(document.querySelector('#managed-pet-birth-year')),
        }))()`);
        assert(initial.precisionOptions === 6, `The birth precision selector does not expose all six modes: ${JSON.stringify(initial)}.`);
        assert(initial.dateVisible && ! initial.yearVisible, 'The exact-date mode shows the wrong conditional controls.');

        const requestsBeforeMode = livewireRequests.length;
        await evaluate(client, sessionId, `(() => {
            const precision = document.querySelector('#managed-pet-birth-precision');
            const setter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value').set;
            setter.call(precision, 'age-estimate');
            precision.dispatchEvent(new Event('change', { bubbles: true }));
        })()`);
        await waitUntil(
            async () => await evaluate(client, sessionId, `Boolean(
                document.querySelector('#managed-pet-estimated-age-years')
                && document.querySelector('#managed-pet-estimated-age-months')
            )`),
            'The estimated-age controls did not appear after changing precision.',
        );
        assert(livewireRequests.length - requestsBeforeMode === 1, 'Changing birth precision emitted an unexpected number of Livewire requests.');

        const requestsBeforeSave = livewireRequests.length;
        await evaluate(client, sessionId, `(() => {
            const setValue = (selector, value) => {
                const input = document.querySelector(selector);
                const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
                setter.call(input, value);
                input.dispatchEvent(new Event('input', { bubbles: true }));
            };
            setValue('#managed-pet-estimated-age-years', '3');
            setValue('#managed-pet-estimated-age-months', '4');
            setValue('#managed-pet-celebration-month', '8');
            setValue('#managed-pet-celebration-day', '3');
            document.querySelector('#managed-pet-estimated-age-years').closest('form').requestSubmit();
        })()`);
        await waitUntil(
            async () => await evaluate(client, sessionId, `document.body.innerText.includes('Age and sex details saved.')`),
            'The estimated age was not saved through Livewire.',
        );
        assert(livewireRequests.length - requestsBeforeSave >= 1, 'Saving birth details did not reach Livewire.');

        await navigate(client, sessionId, manageUrl);
        const restored = await evaluate(client, sessionId, `(() => ({
            precision: document.querySelector('#managed-pet-birth-precision')?.value,
            years: document.querySelector('#managed-pet-estimated-age-years')?.value,
            months: document.querySelector('#managed-pet-estimated-age-months')?.value,
            celebrationMonth: document.querySelector('#managed-pet-celebration-month')?.value,
            celebrationDay: document.querySelector('#managed-pet-celebration-day')?.value,
            currentAge: [...document.querySelectorAll('main [role="status"]')]
                .some((element) => element.textContent.includes('Approximately')),
        }))()`);
        assert(restored.precision === 'age-estimate', 'The estimated-age precision was not restored.');
        assert(restored.years === '3' && restored.months === '4', 'The estimated age was not restored.');
        assert(restored.celebrationMonth === '8' && restored.celebrationDay === '3', 'The celebration day was not restored.');
        assert(restored.currentAge, 'The automatically calculated approximate age is missing.');

        await navigate(client, sessionId, `${baseUrl}/pets/profile/pet-scout`);
        const publicProjection = await evaluate(client, sessionId, `(() => ({
            approximate: document.body.innerText.includes('Approximately'),
            celebration: document.body.innerText.includes('Celebration day')
                && document.body.innerText.includes('Aug 3'),
            rawKeys: document.body.innerText.match(/\\bpet_profiles\\.[a-z0-9_.-]+/gi) ?? [],
            overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        }))()`);
        assert(publicProjection.approximate, 'The public profile does not identify the age as approximate.');
        assert(publicProjection.celebration, 'The public profile does not show the separate celebration day.');
        assert(publicProjection.rawKeys.length === 0, 'The public birth projection exposes translation keys.');
        assert(publicProjection.overflow <= 1, `The public birth projection overflows by ${publicProjection.overflow}px.`);

        const responsive = {};
        for (const viewport of [
            { label: 'desktop', width: 1440, height: 900, mobile: false },
            { label: 'mobile', width: 375, height: 812, mobile: true },
            { label: 'mobile-320', width: 320, height: 900, mobile: true },
        ]) {
            await client.send('Emulation.setDeviceMetricsOverride', {
                width: viewport.width, height: viewport.height, deviceScaleFactor: 1,
                mobile: viewport.mobile, screenWidth: viewport.width, screenHeight: viewport.height,
            }, sessionId);
            await client.send('Emulation.setTouchEmulationEnabled', { enabled: viewport.mobile }, sessionId);
            await navigate(client, sessionId, manageUrl);
            const audit = await evaluate(client, sessionId, `(() => {
                const form = document.querySelector('[data-pet-profile-autosave-step="age-sex"]');
                const visible = (element) => {
                    const style = getComputedStyle(element);
                    const box = element.getBoundingClientRect();
                    return style.display !== 'none' && style.visibility !== 'hidden'
                        && box.width > 0 && box.height > 0;
                };
                const controls = [...(form?.querySelectorAll('button, input, select') ?? [])].filter(visible);
                const ids = [...document.querySelectorAll('[id]')].map((element) => element.id).filter(Boolean);

                return {
                    h1Count: document.querySelectorAll('main h1').length,
                    formCount: form ? 1 : 0,
                    precision: document.querySelector('#managed-pet-birth-precision')?.value,
                    estimateControls: document.querySelectorAll('#managed-pet-estimated-age-years, #managed-pet-estimated-age-months').length,
                    dateControls: document.querySelectorAll('#managed-pet-birth-date, #managed-pet-birth-month, #managed-pet-birth-year').length,
                    overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                    unnamed: controls.filter((element) => !(
                        element.getAttribute('aria-label') || element.getAttribute('aria-labelledby')
                        || element.labels?.length || element.textContent.trim() || element.title
                    )).length,
                    smallTargets: controls.filter((element) => {
                        const box = element.getBoundingClientRect();
                        return box.width < 44 || box.height < 44;
                    }).length,
                    duplicateIds: [...new Set(ids.filter((id, index) => ids.indexOf(id) !== index))],
                };
            })()`);
            assert(audit.h1Count === 1 && audit.formCount === 1, `${viewport.label}: invalid birth workspace landmarks.`);
            assert(audit.precision === 'age-estimate' && audit.estimateControls === 2, `${viewport.label}: estimated-age controls are missing.`);
            assert(audit.dateControls === 0, `${viewport.label}: inactive date controls remain visible.`);
            assert(audit.overflow <= 1, `${viewport.label}: birth workspace overflows by ${audit.overflow}px.`);
            assert(audit.unnamed === 0, `${viewport.label}: unnamed birth controls remain.`);
            assert(audit.duplicateIds.length === 0, `${viewport.label}: duplicate IDs remain.`);
            if (viewport.mobile) assert(audit.smallTargets === 0, `${viewport.label}: birth controls below 44px remain.`);
            responsive[viewport.label] = audit;
            const screenshot = await client.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true }, sessionId);
            await writeFile(join(outputDirectory, `pet-birth-${viewport.label}.png`), Buffer.from(screenshot.data, 'base64'));
        }

        birthAudit = {
            initial,
            restored,
            publicProjection,
            responsive,
        };
    }

    if (verifyNames) {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: 1440, height: 900, deviceScaleFactor: 1,
            mobile: false, screenWidth: 1440, screenHeight: 900,
        }, sessionId);
        await client.send('Emulation.setTouchEmulationEnabled', { enabled: false }, sessionId);
        await setLocale(client, sessionId, 'en');
        const nameValue = `Browser Moon ${Date.now()}`;
        const manageUrl = `${baseUrl}/pets/manage/pet-scout?step=basics`;
        await navigate(client, sessionId, manageUrl);
        const requestsBeforeName = livewireRequests.length;
        const nameForm = await evaluate(client, sessionId, `((nameValue) => {
            const name = document.querySelector('#managed-pet-alternative-name');
            const type = document.querySelector('#managed-pet-name-type');
            const visibility = document.querySelector('#managed-pet-name-visibility');
            const textSetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
            const selectSetter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value').set;
            textSetter.call(name, nameValue);
            name.dispatchEvent(new Event('input', { bubbles: true }));
            selectSetter.call(type, 'nickname');
            type.dispatchEvent(new Event('change', { bubbles: true }));
            selectSetter.call(visibility, 'public');
            visibility.dispatchEvent(new Event('change', { bubbles: true }));
            name.closest('form').requestSubmit();

            return {
                formCount: [...document.querySelectorAll('form')]
                    .filter((form) => form.getAttribute('wire:submit') === 'addAlternativeName').length,
                currentName: document.querySelector('#managed-pet-name')?.value ?? null,
            };
        })(${JSON.stringify(nameValue)})`);
        await waitUntil(
            async () => await evaluate(client, sessionId, `document.body.innerText.includes(${JSON.stringify(nameValue)})
                && document.body.innerText.includes('Alternative name added.')`),
            'The alternative pet name was not saved through Livewire.',
        );
        const nameRequestCount = livewireRequests.length - requestsBeforeName;
        assert(nameForm.formCount === 1, 'The alternative-name form is missing or duplicated.');
        assert(nameRequestCount >= 1, 'Adding an alternative name did not reach Livewire.');

        await navigate(client, sessionId, `${baseUrl}/pets/profile/pet-scout`);
        const publicProjection = await evaluate(client, sessionId, `(() => ({
            visible: document.body.innerText.includes(${JSON.stringify(nameValue)}),
            overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
            rawKeys: document.body.innerText.match(/\\bpet_profiles\\.[a-z0-9_.-]+/gi) ?? [],
        }))()`);
        assert(publicProjection.visible, 'The explicitly public alternative name is absent from the public profile.');
        assert(publicProjection.overflow <= 1, `The public pet name projection overflows by ${publicProjection.overflow}px.`);
        assert(publicProjection.rawKeys.length === 0, 'The public pet name projection exposes translation keys.');

        await navigate(client, sessionId, `${baseUrl}/pets?q=${encodeURIComponent(nameValue)}`);
        const searchProjection = await evaluate(client, sessionId, `(() => ({
            scoutVisible: document.body.innerText.includes('Scout'),
            resultCards: document.querySelectorAll('[data-pet-workspace-profile]').length,
        }))()`);
        assert(searchProjection.scoutVisible && searchProjection.resultCards === 1, 'Alternative-name search did not return the canonical current profile.');

        const responsive = {};
        for (const viewport of [
            { label: 'desktop', width: 1440, height: 900, mobile: false },
            { label: 'mobile', width: 375, height: 812, mobile: true },
            { label: 'mobile-320', width: 320, height: 900, mobile: true },
        ]) {
            await client.send('Emulation.setDeviceMetricsOverride', {
                width: viewport.width, height: viewport.height, deviceScaleFactor: 1,
                mobile: viewport.mobile, screenWidth: viewport.width, screenHeight: viewport.height,
            }, sessionId);
            await client.send('Emulation.setTouchEmulationEnabled', { enabled: viewport.mobile }, sessionId);
            await navigate(client, sessionId, manageUrl);
            const audit = await evaluate(client, sessionId, `(() => {
                const scope = document.querySelector('[data-section="pet-profile-management"]');
                const visible = (element) => {
                    const style = getComputedStyle(element);
                    const box = element.getBoundingClientRect();
                    return style.display !== 'none' && style.visibility !== 'hidden'
                        && box.width > 0 && box.height > 0;
                };
                const controls = [...(scope?.querySelectorAll('a, button, input, select, textarea') ?? [])]
                    .filter(visible);
                const ids = [...document.querySelectorAll('[id]')].map((element) => element.id).filter(Boolean);

                return {
                    h1Count: document.querySelectorAll('main h1').length,
                    formCount: [...document.querySelectorAll('form')]
                        .filter((form) => form.getAttribute('wire:submit') === 'addAlternativeName').length,
                    nameVisible: document.body.innerText.includes(${JSON.stringify(nameValue)}),
                    overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                    unnamed: controls.filter((element) => !(
                        element.getAttribute('aria-label') || element.getAttribute('aria-labelledby')
                        || element.labels?.length || element.textContent.trim() || element.title
                    )).length,
                    smallTargets: controls.filter((element) => {
                        const box = element.getBoundingClientRect();
                        return box.width < 44 || box.height < 44;
                    }).length,
                    duplicateIds: [...new Set(ids.filter((id, index) => ids.indexOf(id) !== index))],
                };
            })()`);
            assert(audit.h1Count === 1, `${viewport.label}: invalid pet-name heading hierarchy.`);
            assert(audit.formCount === 1 && audit.nameVisible, `${viewport.label}: pet-name controls or saved name are missing.`);
            assert(audit.overflow <= 1, `${viewport.label}: pet-name workspace overflows by ${audit.overflow}px.`);
            assert(audit.unnamed === 0, `${viewport.label}: unnamed pet-name controls remain.`);
            assert(audit.duplicateIds.length === 0, `${viewport.label}: duplicate IDs remain.`);
            if (viewport.mobile) assert(audit.smallTargets === 0, `${viewport.label}: pet-name controls below 44px remain.`);
            responsive[viewport.label] = audit;
            const screenshot = await client.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true }, sessionId);
            await writeFile(join(outputDirectory, `pet-names-${viewport.label}.png`), Buffer.from(screenshot.data, 'base64'));
        }

        nameAudit = {
            value: nameValue,
            nameRequestCount,
            publicProjection,
            searchProjection,
            responsive,
        };
    }

    if (verifyBreed) {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: 1440, height: 900, deviceScaleFactor: 1,
            mobile: false, screenWidth: 1440, screenHeight: 900,
        }, sessionId);
        await client.send('Emulation.setTouchEmulationEnabled', { enabled: false }, sessionId);
        await setLocale(client, sessionId, 'en');
        const manageUrl = `${baseUrl}/pets/manage/pet-scout?step=breed-origin`;
        const waitForBreedEntryCount = async (expectedCount, message) => {
            try {
                await waitUntil(
                    async () => await evaluate(
                        client,
                        sessionId,
                        `document.querySelectorAll('input[id^="managed-pet-breed-name-"]').length === ${expectedCount}`,
                    ),
                    message,
                );
            } catch {
                const diagnostic = await evaluate(client, sessionId, `(() => {
                    const addButton = [...document.querySelectorAll('button')]
                        .find((button) => button.getAttribute('wire:click') === 'addBreedOrigin');

                    return {
                        location: location.pathname + location.search,
                        entryCount: document.querySelectorAll('input[id^="managed-pet-breed-name-"]').length,
                        entries: [...document.querySelectorAll('input[id^="managed-pet-breed-name-"]')]
                            .map((input) => ({ id: input.id, value: input.value })),
                        type: document.querySelector('#managed-pet-breed-origin-type')?.value ?? null,
                        addButtonPresent: Boolean(addButton),
                        addButtonDisabled: addButton?.disabled ?? null,
                        alerts: [...document.querySelectorAll('[role="alert"]')]
                            .map((alert) => alert.textContent.trim())
                            .filter(Boolean),
                    };
                })()`);

                throw new Error(`${message} ${JSON.stringify(diagnostic)}`);
            }
        };
        await navigate(client, sessionId, manageUrl);
        const initial = await evaluate(client, sessionId, `(() => ({
            location: location.pathname + location.search,
            heading: document.querySelector('main h1')?.textContent.trim() ?? null,
            body: document.querySelector('main')?.innerText.slice(0, 500) ?? null,
            formCount: document.querySelectorAll('[data-pet-profile-autosave-step="breed-origin"]').length,
            typeOptions: document.querySelectorAll('#managed-pet-breed-origin-type option').length,
            trustNotice: document.body.innerText.includes('A photograph never changes'),
            entryCount: document.querySelectorAll('input[id^="managed-pet-breed-name-"]').length,
        }))()`);
        assert(initial.formCount === 1 && initial.typeOptions === 5, `The breed editor is incomplete: ${JSON.stringify(initial)}.`);
        assert(initial.trustNotice, 'The breed confidence and photo warning is missing.');

        const selectBreedOriginType = async (value) => await evaluate(client, sessionId, `((nextValue) => {
            const type = document.querySelector('#managed-pet-breed-origin-type');
            const setter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value').set;
            setter.call(type, nextValue);
            type.dispatchEvent(new Event('change', { bubbles: true }));
        })(${JSON.stringify(value)})`);
        await selectBreedOriginType('unknown');
        await waitUntil(
            async () => await evaluate(client, sessionId, `document.querySelector('#managed-pet-breed-origin-type')?.value === 'unknown'
                && document.querySelectorAll('input[id^="managed-pet-breed-name-"]').length === 0`),
            'The breed editor did not reset to an explicit unknown state.',
        );
        await delay(300);
        await selectBreedOriginType('mixed');
        await waitUntil(
            async () => await evaluate(client, sessionId, `document.querySelector('#managed-pet-breed-origin-type')?.value === 'mixed'
                && Boolean([...document.querySelectorAll('button')].find((button) => button.getAttribute('wire:click') === 'addBreedOrigin'))`),
            'The mixed-origin controls did not appear.',
        );
        await delay(1_000);
        await navigate(client, sessionId, manageUrl);
        const emptyMixedState = await evaluate(client, sessionId, `(() => ({
            type: document.querySelector('#managed-pet-breed-origin-type')?.value ?? null,
            entryCount: document.querySelectorAll('input[id^="managed-pet-breed-name-"]').length,
        }))()`);
        assert(
            emptyMixedState.type === 'mixed' && emptyMixedState.entryCount === 0,
            `The empty mixed-origin state was not restored cleanly: ${JSON.stringify(emptyMixedState)}.`,
        );
        await evaluate(client, sessionId, `[...document.querySelectorAll('button')]
            .find((button) => button.getAttribute('wire:click') === 'addBreedOrigin').click()`);
        await waitForBreedEntryCount(1, 'The first mixed-origin entry did not appear.');
        await evaluate(client, sessionId, `[...document.querySelectorAll('button')].find((button) => button.getAttribute('wire:click') === 'addBreedOrigin').click()`);
        await waitForBreedEntryCount(2, 'The second mixed-origin entry did not appear.');

        const requestsBeforeSave = livewireRequests.length;
        await evaluate(client, sessionId, `(() => {
            const inputs = [...document.querySelectorAll('input[id^="managed-pet-breed-name-"]')];
            const confidences = [...document.querySelectorAll('[id^="managed-pet-breed-confidence-"]')];
            const sources = [...document.querySelectorAll('[id^="managed-pet-breed-source-"]')];
            const shares = [...document.querySelectorAll('input[id^="managed-pet-breed-share-"]')];
            const inputSetter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
            const selectSetter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value').set;
            const setInput = (element, value) => {
                inputSetter.call(element, value);
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change', { bubbles: true }));
            };
            const setSelect = (element, value) => {
                selectSetter.call(element, value);
                element.dispatchEvent(new Event('change', { bubbles: true }));
            };
            setInput(inputs[0], 'Border Collie');
            setInput(inputs[1], 'Labrador Retriever');
            setSelect(confidences[0], 'confirmed');
            setSelect(confidences[1], 'owner-reported');
            setSelect(sources[0], 'genetic-test');
            setSelect(sources[1], 'owner-assumption');
            setInput(shares[0], '60');
            setInput(shares[1], '40');
        })()`);
        await delay(1_000);
        await evaluate(client, sessionId, `document.querySelector('[data-pet-profile-autosave-step="breed-origin"]').requestSubmit()`);
        await waitUntil(
            async () => await evaluate(client, sessionId, `document.body.innerText.includes('Breed and origin details saved.')`),
            'The mixed breed origin was not saved through Livewire.',
        );
        assert(livewireRequests.length - requestsBeforeSave >= 1, 'Saving breed origin did not reach Livewire.');

        await navigate(client, sessionId, manageUrl);
        const restored = await evaluate(client, sessionId, `(() => ({
            type: document.querySelector('#managed-pet-breed-origin-type')?.value,
            names: [...document.querySelectorAll('input[id^="managed-pet-breed-name-"]')].map((input) => input.value),
            sources: [...document.querySelectorAll('[id^="managed-pet-breed-source-"]')].map((input) => input.value),
            shares: [...document.querySelectorAll('input[id^="managed-pet-breed-share-"]')].map((input) => input.value),
        }))()`);
        assert(restored.type === 'mixed', 'The mixed origin type was not restored.');
        assert(JSON.stringify(restored.names) === JSON.stringify(['Border Collie', 'Labrador Retriever']), 'The mixed breed names were not restored.');
        assert(JSON.stringify(restored.sources) === JSON.stringify(['genetic-test', 'owner-assumption']), 'The separate breed sources were not restored.');
        assert(JSON.stringify(restored.shares) === JSON.stringify(['60', '40']), 'The optional breed shares were not restored.');

        await navigate(client, sessionId, `${baseUrl}/pets/profile/pet-scout`);
        const publicProjection = await evaluate(client, sessionId, `(() => ({
            namesVisible: document.body.innerText.includes('Border Collie')
                && document.body.innerText.includes('Labrador Retriever'),
            confidenceVisible: document.body.innerText.includes('Confirmed or documented')
                && document.body.innerText.includes('Owner-reported'),
            sourcesVisible: document.body.innerText.includes('Genetic test')
                && document.body.innerText.includes('Owner assumption'),
            noticeVisible: document.body.innerText.includes('does not predict character, health, or compatibility'),
            rawKeys: document.body.innerText.match(/\\bpet_profiles\\.[a-z0-9_.-]+/gi) ?? [],
            overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        }))()`);
        assert(publicProjection.namesVisible && publicProjection.confidenceVisible, 'The public breed confidence projection is incomplete.');
        assert(publicProjection.sourcesVisible && publicProjection.noticeVisible, 'The public breed source or non-discrimination notice is missing.');
        assert(publicProjection.rawKeys.length === 0, 'The public breed projection exposes translation keys.');
        assert(publicProjection.overflow <= 1, `The public breed projection overflows by ${publicProjection.overflow}px.`);

        const responsive = {};
        for (const viewport of [
            { label: 'desktop', width: 1440, height: 900, mobile: false },
            { label: 'mobile-390', width: 390, height: 844, mobile: true },
            { label: 'mobile-320', width: 320, height: 900, mobile: true },
        ]) {
            await client.send('Emulation.setDeviceMetricsOverride', {
                width: viewport.width, height: viewport.height, deviceScaleFactor: 1,
                mobile: viewport.mobile, screenWidth: viewport.width, screenHeight: viewport.height,
            }, sessionId);
            await client.send('Emulation.setTouchEmulationEnabled', { enabled: viewport.mobile }, sessionId);
            await navigate(client, sessionId, manageUrl);
            const audit = await evaluate(client, sessionId, `(() => {
                const form = document.querySelector('[data-pet-profile-autosave-step="breed-origin"]');
                const visible = (element) => {
                    const style = getComputedStyle(element);
                    const box = element.getBoundingClientRect();
                    return style.display !== 'none' && style.visibility !== 'hidden'
                        && box.width > 0 && box.height > 0;
                };
                const controls = [...(form?.querySelectorAll('button, input, select') ?? [])].filter(visible);
                const ids = [...document.querySelectorAll('[id]')].map((element) => element.id).filter(Boolean);
                return {
                    h1Count: document.querySelectorAll('main h1').length,
                    formCount: form ? 1 : 0,
                    entryCount: document.querySelectorAll('input[id^="managed-pet-breed-name-"]').length,
                    overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                    rawKeys: document.body.innerText.match(/\\bpet_profiles\\.[a-z0-9_.-]+/gi) ?? [],
                    unnamed: controls.filter((element) => !(
                        element.getAttribute('aria-label') || element.getAttribute('aria-labelledby')
                        || element.labels?.length || element.textContent.trim() || element.title
                    )).length,
                    smallTargets: controls.filter((element) => {
                        const box = element.getBoundingClientRect();
                        return box.width < 44 || box.height < 44;
                    }).length,
                    duplicateIds: [...new Set(ids.filter((id, index) => ids.indexOf(id) !== index))],
                };
            })()`);
            assert(audit.h1Count === 1 && audit.formCount === 1 && audit.entryCount === 2, `${viewport.label}: invalid breed workspace structure.`);
            assert(audit.overflow <= 1, `${viewport.label}: breed workspace overflows by ${audit.overflow}px.`);
            assert(audit.rawKeys.length === 0 && audit.unnamed === 0, `${viewport.label}: breed workspace exposes raw keys or unnamed controls.`);
            assert(audit.duplicateIds.length === 0, `${viewport.label}: duplicate breed control IDs remain.`);
            if (viewport.mobile) assert(audit.smallTargets === 0, `${viewport.label}: breed controls below 44px remain.`);
            responsive[viewport.label] = audit;
            const screenshot = await client.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true }, sessionId);
            await writeFile(join(outputDirectory, `pet-breed-${viewport.label}.png`), Buffer.from(screenshot.data, 'base64'));
        }

        breedAudit = { initial, restored, publicProjection, responsive };
    }

    if (verifyAutosave) {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: 1440, height: 900, deviceScaleFactor: 1,
            mobile: false, screenWidth: 1440, screenHeight: 900,
        }, sessionId);
        await client.send('Emulation.setTouchEmulationEnabled', { enabled: false }, sessionId);
        await setLocale(client, sessionId, 'en');
        const autosaveUrl = `${baseUrl}/pets/manage/pet-scout?step=appearance`;
        await navigate(client, sessionId, autosaveUrl);
        const originalAppearance = await evaluate(
            client,
            sessionId,
            'document.querySelector("#managed-pet-appearance")?.value',
        );
        const originalIdentifyingMarks = await evaluate(
            client,
            sessionId,
            'document.querySelector("#managed-pet-identifying-marks")?.value',
        );
        const autosaveValue = `Browser autosave verification ${Date.now()}`;
        const autosaveMarkup = await evaluate(client, sessionId, `(() => ({
            formWired: [...document.querySelectorAll('form')]
                .some((element) => element.getAttribute('wire:change')
                    === "autoSaveStep('appearance', $event.currentTarget.dataset.petProfileAutosaveRevision)"),
            statusRegion: [...document.querySelectorAll('[role="status"]')]
                .some((element) => [...element.querySelectorAll('*')]
                    .some((child) => child.getAttribute('wire:target') === 'autoSaveStep')),
            offlineNotice: [...document.querySelectorAll('main *')]
                .some((element) => element.hasAttribute('wire:offline')
                    && element.textContent.includes('You are offline')),
        }))()`);
        assert(autosaveMarkup.formWired, 'The appearance form is not wired to autosave.');
        assert(autosaveMarkup.statusRegion, 'The autosave status live region is missing.');
        assert(autosaveMarkup.offlineNotice, 'The pet workspace offline notice is missing.');

        const changeAppearance = async (value) => {
            await evaluate(client, sessionId, `((nextValue) => {
                const input = document.querySelector('#managed-pet-appearance');
                const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value').set;
                setter.call(input, nextValue);
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.blur();
            })(${JSON.stringify(value)})`);
            await waitUntil(
                async () => await evaluate(
                    client,
                    sessionId,
                    `document.body.innerText.includes('Appearance details saved.')`,
                ),
                'The appearance autosave did not receive server confirmation.',
            );
        };

        const requestsBeforeAutosave = livewireRequests.length;
        await changeAppearance(autosaveValue);
        const autosaveRequestCount = livewireRequests.length - requestsBeforeAutosave;
        assert(autosaveRequestCount === 1, `Autosave emitted ${autosaveRequestCount} Livewire requests instead of one.`);
        await navigate(client, sessionId, autosaveUrl);
        const restoredAppearance = await evaluate(
            client,
            sessionId,
            'document.querySelector("#managed-pet-appearance")?.value',
        );
        assert(restoredAppearance === autosaveValue, 'The autosaved appearance was not restored after reload.');

        const offlineIdentifyingMarks = [
            originalIdentifyingMarks,
            `Browser reconnect verification ${Date.now()}`,
        ].filter(Boolean).join(' ');
        const requestsBeforeOfflineEdit = livewireRequests.length;
        const failuresBeforeOfflineEdit = failedLivewireRequests.length;
        const errorsBeforeOfflineEdit = consoleErrors.length;
        await client.send('Network.emulateNetworkConditions', {
            offline: true,
            latency: 0,
            downloadThroughput: -1,
            uploadThroughput: -1,
        }, sessionId);
        const offlineAudit = await evaluate(client, sessionId, `((offlineValue) => {
            window.dispatchEvent(new Event('offline'));
            const input = document.querySelector('#managed-pet-identifying-marks');
            const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value').set;
            setter.call(input, offlineValue);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            const form = input.closest('form');
            const visible = (element) => {
                if (! element) return false;
                const style = getComputedStyle(element);
                const box = element.getBoundingClientRect();
                return style.display !== 'none' && style.visibility !== 'hidden' && box.height > 0;
            };
            const notice = [...document.querySelectorAll('main *')]
                .find((element) => element.hasAttribute('wire:offline') && visible(element));

            return {
                noticeVisible: Boolean(notice),
                statusText: document.querySelector('[data-pet-autosave-status]')?.textContent.trim() ?? '',
                pending: form?.hasAttribute('data-pet-profile-autosave-pending') ?? false,
            };
        })(${JSON.stringify(offlineIdentifyingMarks)})`);
        assert(offlineAudit.noticeVisible, 'The offline notice did not become visible.');
        assert(offlineAudit.statusText.includes('unsaved changes'), 'The offline edit was not identified as unsaved.');
        assert(offlineAudit.pending, 'The offline edit was not marked for reconnect recovery.');
        await waitUntil(
            async () => failedLivewireRequests.length > failuresBeforeOfflineEdit,
            'The offline autosave request did not fail at the network boundary.',
        );
        const offlineConsoleErrors = consoleErrors
            .slice(errorsBeforeOfflineEdit)
            .filter((error) => error.includes('net::ERR_INTERNET_DISCONNECTED'));
        assert(offlineConsoleErrors.length > 0, 'Chrome did not report the intentional offline network failure.');
        consoleErrors.splice(
            errorsBeforeOfflineEdit,
            consoleErrors.length - errorsBeforeOfflineEdit,
            ...consoleErrors
                .slice(errorsBeforeOfflineEdit)
                .filter((error) => !error.includes('net::ERR_INTERNET_DISCONNECTED')),
        );
        assert(
            livewireRequests.length - requestsBeforeOfflineEdit === 1,
            'The offline edit did not make exactly one failed Livewire attempt.',
        );

        const requestsBeforeReconnect = livewireRequests.length;
        await client.send('Network.emulateNetworkConditions', {
            offline: false,
            latency: 0,
            downloadThroughput: -1,
            uploadThroughput: -1,
        }, sessionId);
        await evaluate(client, sessionId, `(() => {
            window.dispatchEvent(new Event('online'));
        })()`);
        await waitUntil(
            async () => livewireRequests.length > requestsBeforeReconnect
                && await evaluate(
                    client,
                    sessionId,
                    `!document.querySelector('form[data-pet-profile-autosave-step="appearance"]')
                        ?.hasAttribute('data-pet-profile-autosave-pending')`,
                ),
            'The pending pet draft was not saved after reconnect.',
        );
        const reconnectRequestCount = livewireRequests.length - requestsBeforeReconnect;
        assert(reconnectRequestCount === 1, `Reconnect recovery emitted ${reconnectRequestCount} Livewire requests instead of one.`);

        await navigate(client, sessionId, autosaveUrl);
        const recoveredAfterReconnect = await evaluate(
            client,
            sessionId,
            'document.querySelector("#managed-pet-identifying-marks")?.value',
        );
        assert(recoveredAfterReconnect === offlineIdentifyingMarks, 'The reconnect draft was not restored from the server.');

        const requestsBeforeRestore = livewireRequests.length;
        await evaluate(client, sessionId, `((appearance, identifyingMarks) => {
            const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value').set;
            const appearanceInput = document.querySelector('#managed-pet-appearance');
            const marksInput = document.querySelector('#managed-pet-identifying-marks');
            setter.call(appearanceInput, appearance);
            setter.call(marksInput, identifyingMarks);
            appearanceInput.dispatchEvent(new Event('input', { bubbles: true }));
            marksInput.dispatchEvent(new Event('input', { bubbles: true }));
            marksInput.dispatchEvent(new Event('change', { bubbles: true }));
        })(${JSON.stringify(originalAppearance)}, ${JSON.stringify(originalIdentifyingMarks)})`);
        await waitUntil(
            async () => livewireRequests.length > requestsBeforeRestore
                && await evaluate(
                    client,
                    sessionId,
                    `!document.querySelector('form[data-pet-profile-autosave-step="appearance"]')
                        ?.hasAttribute('data-pet-profile-autosave-pending')`,
                ),
            'The browser check could not restore the original appearance values.',
        );
        await navigate(client, sessionId, autosaveUrl);
        const cleanupValues = await evaluate(client, sessionId, `(() => ({
            appearance: document.querySelector('#managed-pet-appearance')?.value,
            identifyingMarks: document.querySelector('#managed-pet-identifying-marks')?.value,
        }))()`);
        assert(cleanupValues.appearance === originalAppearance, 'The browser check did not restore the original appearance.');
        assert(cleanupValues.identifyingMarks === originalIdentifyingMarks, 'The browser check did not restore identifying marks.');

        await client.send('Emulation.setDeviceMetricsOverride', {
            width: 375, height: 812, deviceScaleFactor: 1,
            mobile: true, screenWidth: 375, screenHeight: 812,
        }, sessionId);
        await client.send('Emulation.setTouchEmulationEnabled', { enabled: true }, sessionId);
        await navigate(client, sessionId, autosaveUrl);
        const mobileAutosave = await evaluate(client, sessionId, `(() => {
            const status = document.querySelector('[data-pet-autosave-status]');
            const form = document.querySelector('#managed-pet-appearance')?.closest('form');
            const controls = [...(form?.querySelectorAll('textarea, button') ?? [])];

            return {
                h1Count: document.querySelectorAll('main h1').length,
                statusCount: status ? 1 : 0,
                overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
                smallTargets: controls.filter((element) => {
                    const box = element.getBoundingClientRect();
                    return box.width < 44 || box.height < 44;
                }).length,
            };
        })()`);
        assert(mobileAutosave.h1Count === 1, 'The mobile autosave page has an invalid heading hierarchy.');
        assert(mobileAutosave.statusCount === 1, 'The mobile autosave status is missing.');
        assert(mobileAutosave.overflow <= 1, `The mobile autosave page overflows by ${mobileAutosave.overflow}px.`);
        assert(mobileAutosave.smallTargets === 0, 'The mobile autosave controls are smaller than 44px.');
        const autosaveScreenshot = await client.send('Page.captureScreenshot', { format: 'png', captureBeyondViewport: true }, sessionId);
        await writeFile(join(outputDirectory, 'pet-autosave-mobile.png'), Buffer.from(autosaveScreenshot.data, 'base64'));

        autosaveAudit = {
            ...autosaveMarkup,
            autosaveRequestCount,
            persistedAfterReload: restoredAppearance === autosaveValue,
            offline: {
                ...offlineAudit,
                intentionalConsoleErrorCount: offlineConsoleErrors.length,
                failedRequestCount: failedLivewireRequests.length - failuresBeforeOfflineEdit,
                reconnectRequestCount,
                recoveredAfterReconnect: recoveredAfterReconnect === offlineIdentifyingMarks,
            },
            restoredOriginal: cleanupValues.appearance === originalAppearance
                && cleanupValues.identifyingMarks === originalIdentifyingMarks,
            mobile: mobileAutosave,
        };
    }

    assert(consoleErrors.length === 0, `Console errors: ${JSON.stringify(consoleErrors)}.`);
    const report = {
        baseUrl,
        outputDirectory,
        audits,
        verifyAutosave,
        autosaveAudit,
        verifyBirth,
        birthAudit,
        verifyBreed,
        breedAudit,
        verifyNames,
        nameAudit,
        consoleErrors,
    };
    await writeFile(join(outputDirectory, 'report.json'), `${JSON.stringify(report, null, 2)}\n`);
    process.stdout.write(`${JSON.stringify(report, null, 2)}\n`);
} finally {
    if (client && sessionId) {
        try { await setLocale(client, sessionId, originalLocale); } catch { /* Preserve the original failure. */ }
    }
    client?.close();
    if (browser.exitCode === null && browser.signalCode === null) {
        browser.kill('SIGTERM');
    }

    await Promise.race([browserExited, delay(5_000)]);

    if (browser.exitCode === null && browser.signalCode === null) {
        browser.kill('SIGKILL');
        await browserExited;
    }

    await rm(profileDirectory, { recursive: true, force: true });
}
