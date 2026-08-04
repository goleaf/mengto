import { spawn } from 'node:child_process';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const baseUrl = (process.env.BROWSER_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const browserOrigin = new URL(baseUrl);
const groupsOnly = process.argv.includes('--groups-only');
const placesOnly = process.argv.includes('--places-only');
const pageIdentityOnly = process.argv.includes('--page-identity-only');
const outputDirectory = process.env.BROWSER_OUTPUT_DIR
    ?? join(tmpdir(), 'mengto-accessibility-browser');
const chromeCandidates = [
    process.env.CHROME_BIN,
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/usr/bin/google-chrome',
    '/usr/bin/google-chrome-stable',
    '/usr/bin/chromium',
    '/usr/bin/chromium-browser',
].filter(Boolean);

if (!['localhost', '127.0.0.1', '::1'].includes(browserOrigin.hostname)) {
    throw new Error('The accessibility browser check only runs against a loopback application URL.');
}

const assert = (condition, message) => {
    if (!condition) {
        throw new Error(message);
    }
};

const chromeExecutable = async () => {
    for (const candidate of chromeCandidates) {
        try {
            await access(candidate);

            return candidate;
        } catch {
            // Try the next platform-specific location.
        }
    }

    throw new Error('Chrome was not found. Set CHROME_BIN to a Chromium-compatible executable.');
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

                if (message.error) {
                    pending?.reject(new Error(message.error.message));
                } else {
                    pending?.resolve(message.result);
                }

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

        if (sessionId) {
            message.sessionId = sessionId;
        }

        this.socket.send(JSON.stringify(message));

        return new Promise((resolve, reject) => {
            this.pending.set(id, { resolve, reject });
        });
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
        throw new Error(result.exceptionDetails.text);
    }

    return result.result.value;
};

const navigate = async (client, sessionId, url) => {
    const loaded = client.once('Page.loadEventFired', sessionId);
    await client.send('Page.navigate', { url }, sessionId);
    await loaded;
    await delay(350);
};

const waitUntil = async (callback, message, timeout = 15_000) => {
    const deadline = Date.now() + timeout;
    let lastError;

    while (Date.now() < deadline) {
        try {
            if (await callback()) {
                return;
            }
        } catch (error) {
            lastError = error;
        }

        await delay(100);
    }

    const detail = lastError instanceof Error ? ` Last error: ${lastError.message}` : '';

    throw new Error(`${message}${detail}`);
};

const login = async (client, sessionId, email) => {
    await navigate(client, sessionId, `${baseUrl}/login`);
    await evaluate(client, sessionId, `((email) => {
        const setValue = (selector, value) => {
            const input = document.querySelector(selector);
            const setter = Object.getOwnPropertyDescriptor(
                HTMLInputElement.prototype,
                'value',
            ).set;
            setter.call(input, value);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            input.blur();
        };

        setValue('#login-email', email);
        setValue('#login-password', 'password');
        document.querySelector('[data-auth-page="login"] .auth-button--primary').click();
        return true;
    })(${JSON.stringify(email)})`);
    await waitUntil(
        async () => !(await evaluate(client, sessionId, 'location.pathname')).includes('/login'),
        `Login did not complete for ${email}.`,
    );
};

const setProfileLocale = async (client, sessionId, locale) => {
    await navigate(client, sessionId, `${baseUrl}/profile/settings`);
    const currentLocale = await evaluate(
        client,
        sessionId,
        `document.querySelector('#profile-settings-locale')?.value`,
    );

    if (currentLocale === locale) {
        return;
    }

    await evaluate(client, sessionId, `((localeValue) => {
        const input = document.querySelector('#profile-settings-locale');
        input.value = localeValue;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        input.blur();
        document.querySelector('form button[type="submit"]').click();
        return true;
    })(${JSON.stringify(locale)})`);
    await waitUntil(
        async () => await evaluate(
            client,
            sessionId,
            `document.documentElement.lang === ${JSON.stringify(locale)}`,
        ),
        `Profile locale did not change to ${locale}.`,
    );
};

const pageAuditExpression = `(() => {
    const visible = (element) => {
        const style = getComputedStyle(element);
        const box = element.getBoundingClientRect();

        return style.display !== 'none' && style.visibility !== 'hidden'
            && box.width > 0 && box.height > 0;
    };
    const controls = [...document.querySelectorAll(
        'button, input:not([type="hidden"]), select, textarea, [role="button"]'
    )].filter(visible);
    const unnamedControls = controls.filter((element) => !(
        element.getAttribute('aria-label')
        || element.getAttribute('aria-labelledby')
        || element.labels?.length
        || element.textContent.trim()
        || element.title
    ));
    const duplicateIds = [...document.querySelectorAll('[id]')]
        .map((element) => element.id)
        .filter((id, index, values) => id && values.indexOf(id) !== index);
    const invalidTables = [...document.querySelectorAll('.forum-page table')]
        .filter((table) => !table.querySelector('caption') || !table.querySelector('th[scope="col"]'));
    const invalidImages = [...document.images].filter((image) => !image.hasAttribute('alt'));

    return {
        url: location.href,
        title: document.title,
        h1Count: document.querySelectorAll('h1').length,
        mainCount: document.querySelectorAll('main').length,
        overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        unnamedControlCount: unnamedControls.length,
        unnamedControls: unnamedControls.map((element) => element.outerHTML.slice(0, 160)),
        duplicateIds: [...new Set(duplicateIds)],
        invalidTableCount: invalidTables.length,
        invalidImageCount: invalidImages.length,
        skipTargetExists: Boolean(document.querySelector('a[href="#main-content"]')
            && document.querySelector('#main-content')),
    };
})()`;

const assertPageAudit = (audit, label) => {
    assert(audit.h1Count === 1, `${label}: expected one h1, found ${audit.h1Count}.`);
    assert(audit.mainCount === 1, `${label}: expected one main, found ${audit.mainCount}.`);
    assert(audit.overflow <= 1, `${label}: horizontal overflow is ${audit.overflow}px.`);
    assert(audit.unnamedControlCount === 0, `${label}: unnamed controls ${JSON.stringify(audit.unnamedControls)}.`);
    assert(audit.duplicateIds.length === 0, `${label}: duplicate ids ${audit.duplicateIds.join(', ')}.`);
    assert(audit.invalidTableCount === 0, `${label}: a forum table lacks caption or scoped headers.`);
    assert(audit.invalidImageCount === 0, `${label}: an image lacks an alt attribute.`);
    assert(audit.skipTargetExists, `${label}: skip link target is missing.`);
};

const surfaceTouchTargetExpression = `(() => [...document.querySelectorAll(
    '.forum-page button, .forum-page input:not([type="hidden"]), .forum-page select, '
    + '.forum-page textarea, .forum-page .forum-button'
)].filter((element) => {
    const style = getComputedStyle(element);
    const box = element.getBoundingClientRect();
    return style.display !== 'none' && style.visibility !== 'hidden'
        && box.width > 0 && box.height > 0;
}).map((element) => ({
    label: element.getAttribute('aria-label') || element.textContent.trim().slice(0, 60)
        || element.getAttribute('name'),
    width: Math.round(element.getBoundingClientRect().width),
    height: Math.round(element.getBoundingClientRect().height),
})).filter((target) => target.width < 44 || target.height < 44))()`;

const placeTouchTargetExpression = `(() => [...document.querySelectorAll(
    'main button, main input:not([type="hidden"]), main select, main textarea, '
    + 'main a.action, main .icon-button, main [role="button"]'
)].filter((element) => {
    const style = getComputedStyle(element);
    const box = element.getBoundingClientRect();
    return style.display !== 'none' && style.visibility !== 'hidden'
        && box.width > 0 && box.height > 0;
}).map((element) => ({
    label: element.getAttribute('aria-label') || element.textContent.trim().slice(0, 60)
        || element.getAttribute('name'),
    width: Math.round(element.getBoundingClientRect().width),
    height: Math.round(element.getBoundingClientRect().height),
})).filter((target) => target.width < 44 || target.height < 44))()`;

await mkdir(outputDirectory, { recursive: true });
const profileDirectory = await mkdtemp(join(tmpdir(), 'mengto-chrome-'));
const chrome = await chromeExecutable();
const chromeOutput = [];
const browser = spawn(chrome, [
    '--headless=new',
    '--disable-background-networking',
    '--disable-default-apps',
    '--disable-extensions',
    '--disable-features=Translate,MediaRouter',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-default-browser-check',
    '--no-first-run',
    '--remote-debugging-port=0',
    `--user-data-dir=${profileDirectory}`,
    'about:blank',
], { stdio: ['ignore', 'ignore', 'pipe'] });

browser.stderr.on('data', (chunk) => chromeOutput.push(chunk.toString()));

let client;
let sessionId;
let originalProfileLocale;

try {
    const activePort = await waitForFile(join(profileDirectory, 'DevToolsActivePort'));
    const [port, browserPath] = activePort.trim().split(/\r?\n/);
    client = await CdpClient.connect(`ws://127.0.0.1:${port}${browserPath}`);

    const { targetId } = await client.send('Target.createTarget', { url: 'about:blank' });
    ({ sessionId } = await client.send('Target.attachToTarget', {
        targetId,
        flatten: true,
    }));
    const consoleErrors = [];

    client.on('Runtime.exceptionThrown', ({ exceptionDetails }) => {
        consoleErrors.push(exceptionDetails.text);
    }, sessionId);
    client.on('Log.entryAdded', ({ entry }) => {
        if (entry.level === 'error') {
            consoleErrors.push(entry.text);
        }
    }, sessionId);

    await Promise.all([
        client.send('Page.enable', {}, sessionId),
        client.send('Runtime.enable', {}, sessionId),
        client.send('Network.enable', {}, sessionId),
        client.send('Log.enable', {}, sessionId),
    ]);
    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    }, sessionId);

    auditRun: {
    const accountEntryAudits = {};
    const accountEntryViewports = [
        { label: 'English 320px account entry', locale: 'en', width: 320, height: 900, mobile: true },
        { label: 'Russian 375px account entry', locale: 'ru', width: 375, height: 812, mobile: true },
        { label: 'Lithuanian 768px account entry', locale: 'lt', width: 768, height: 1024, mobile: false },
        { label: 'Russian 1024px account entry', locale: 'ru', width: 1024, height: 900, mobile: false },
        { label: 'English 1440px account entry', locale: 'en', width: 1440, height: 900, mobile: false },
        { label: 'English 1920px account entry', locale: 'en', width: 1920, height: 1080, mobile: false },
    ];

    await navigate(client, sessionId, `${baseUrl}/login`);

    for (const viewport of accountEntryViewports) {
        const currentLocale = await evaluate(client, sessionId, 'document.documentElement.lang');

        if (currentLocale !== viewport.locale) {
            const loaded = client.once('Page.loadEventFired', sessionId);
            await evaluate(
                client,
                sessionId,
                `document.querySelector('button[name="locale"][value="${viewport.locale}"]').click(); true`,
            );
            await loaded;
            await delay(350);
        }

        await client.send('Emulation.setDeviceMetricsOverride', {
            width: viewport.width,
            height: viewport.height,
            deviceScaleFactor: 1,
            mobile: viewport.mobile,
            screenWidth: viewport.width,
            screenHeight: viewport.height,
        }, sessionId);
        await navigate(client, sessionId, `${baseUrl}/login`);

        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, viewport.label);

        const behavior = await evaluate(client, sessionId, `(() => {
            const visible = (element) => {
                const style = getComputedStyle(element);
                const box = element.getBoundingClientRect();

                return style.display !== 'none' && style.visibility !== 'hidden'
                    && box.width > 0 && box.height > 0;
            };
            const targets = [...document.querySelectorAll(
                '.auth-button, .auth-text-link, .auth-switch__link, .auth-locale button, .auth-brand'
            )].filter(visible).map((element) => ({
                label: element.textContent.trim().slice(0, 60),
                width: Math.round(element.getBoundingClientRect().width),
                height: Math.round(element.getBoundingClientRect().height),
            })).filter((target) => target.width < 44 || target.height < 44);

            return {
                documentLanguage: document.documentElement.lang,
                authShellCount: document.querySelectorAll('[data-auth-shell]').length,
                loginPageCount: document.querySelectorAll('[data-auth-page="login"]').length,
                productContentCount: document.querySelectorAll('[data-section], .forum-page').length,
                memberHeaderCount: document.querySelectorAll('[data-site-header]').length,
                externalImageCount: [...document.images]
                    .filter((image) => new URL(image.currentSrc || image.src).origin !== location.origin).length,
                smallTargets: targets,
            };
        })()`);
        assert(
            behavior.documentLanguage === viewport.locale,
            `${viewport.label}: expected ${viewport.locale}, found ${behavior.documentLanguage}.`,
        );
        assert(behavior.authShellCount === 1, `${viewport.label}: auth shell marker is missing.`);
        assert(behavior.loginPageCount === 1, `${viewport.label}: root did not resolve to login.`);
        assert(behavior.productContentCount === 0, `${viewport.label}: product content leaked into account entry.`);
        assert(behavior.memberHeaderCount === 0, `${viewport.label}: member header leaked into account entry.`);
        assert(behavior.externalImageCount === 0, `${viewport.label}: external image dependency found.`);
        assert(
            behavior.smallTargets.length === 0,
            `${viewport.label}: controls below 44px ${JSON.stringify(behavior.smallTargets)}.`,
        );

        accountEntryAudits[viewport.label] = { ...audit, ...behavior };

        if (viewport.width === 320) {
            const accountEntryMobileScreenshot = await client.send('Page.captureScreenshot', {
                format: 'png',
                captureBeyondViewport: true,
            }, sessionId);
            await writeFile(
                join(outputDirectory, 'account-entry-mobile.png'),
                Buffer.from(accountEntryMobileScreenshot.data, 'base64'),
            );
        }
    }

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
        screenWidth: 1440,
        screenHeight: 900,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/`);
    await evaluate(client, sessionId, `(() => {
        document.querySelector('a[href="#main-content"]')?.focus();

        return true;
    })()`);
    const accountEntrySkipFocus = await evaluate(client, sessionId, `(() => {
        const element = document.activeElement;
        const style = getComputedStyle(element);

        return {
            isSkipLink: element?.matches('a[href="#main-content"]') ?? false,
            activeElement: element?.outerHTML?.slice(0, 240) ?? null,
            outlineStyle: style.outlineStyle,
            outlineWidth: style.outlineWidth,
            boxShadow: style.boxShadow,
        };
    })()`);
    assert(accountEntrySkipFocus.isSkipLink, 'The account entry skip link could not receive focus.');
    assert(
        (accountEntrySkipFocus.outlineStyle !== 'none' && accountEntrySkipFocus.outlineWidth !== '0px')
            || accountEntrySkipFocus.boxShadow !== 'none',
        'Account entry skip link focus is not visible.',
    );
    await evaluate(client, sessionId, 'document.activeElement.blur(); true');

    const accountEntryDesktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'account-entry-desktop.png'),
        Buffer.from(accountEntryDesktopScreenshot.data, 'base64'),
    );

    await login(client, sessionId, 'mia@example.test');

    if (pageIdentityOnly) {
        const pageIdentityRoutes = [
            { path: '/pets', slug: 'pets', label: 'pets' },
            { path: '/medical-records', slug: 'medical-records', label: 'medical records' },
            { path: '/care-journals', slug: 'care-journals', label: 'care journals' },
            { path: '/meetups', slug: 'meetups', label: 'meetups' },
            { path: '/places', slug: 'places', label: 'places' },
            { path: '/lost-found', slug: 'lost-found', label: 'lost and found' },
            { path: '/marketplace', slug: 'marketplace', label: 'marketplace' },
            { path: '/experts', slug: 'experts', label: 'experts' },
            { path: '/forum', slug: 'forum', label: 'forum' },
            { path: '/groups', slug: 'groups', label: 'groups' },
            { path: '/neighbors', slug: 'neighbors', label: 'neighbors' },
            { path: '/discover', slug: 'discover', label: 'discover' },
            { path: '/messages', slug: 'messages', label: 'messages' },
        ];
        const pageIdentityViewports = [
            { label: '320-en', locale: 'en', width: 320, height: 900, mobile: true },
            { label: '375-ru', locale: 'ru', width: 375, height: 812, mobile: true },
            { label: '768-lt', locale: 'lt', width: 768, height: 1024, mobile: false },
            { label: '1024-ru-forced-colors', locale: 'ru', width: 1024, height: 900, mobile: false, forcedColors: true },
            { label: '1280-en-200-percent', locale: 'en', width: 640, height: 450, screenWidth: 1280, screenHeight: 900, zoom: 2, mobile: false },
            { label: '1440-en', locale: 'en', width: 1440, height: 900, mobile: false },
            { label: '1920-lt', locale: 'lt', width: 1920, height: 1080, mobile: false },
        ];
        const pageIdentityAudits = {};
        const pageIdentityScreenshots = [];
        const englishIdentityCopy = new Map();
        let englishNavigationCopy = null;
        let englishMedicalRecordCopy = null;
        let englishCareJournalCopy = null;
        let englishLostFoundCopy = null;
        let englishMarketplaceCopy = null;
        let englishExpertCopy = null;
        let englishGroupCopy = null;
        let englishNeighborCopy = null;
        let englishMessagingCopy = null;
        let englishCallStageCopy = null;
        let englishMessageDetailsCopy = null;
        let englishShareCopy = null;
        let englishPlaceCopy = null;
        let canonicalTitleFont = null;

        const setProfileLocale = async (locale) => {
            await navigate(client, sessionId, `${baseUrl}/profile/settings`);
            const currentLocale = await evaluate(client, sessionId, 'document.documentElement.lang');

            if (currentLocale === locale) {
                return;
            }

            await evaluate(client, sessionId, `((locale) => {
                const select = document.querySelector('#profile-settings-locale');
                select.value = locale;
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
                select.blur();
                document.querySelector('form button[type="submit"]').click();

                return true;
            })(${JSON.stringify(locale)})`);
            await waitUntil(
                async () => await evaluate(
                    client,
                    sessionId,
                    `document.documentElement.lang === ${JSON.stringify(locale)}`,
                ),
                `The page-identity matrix did not switch to ${locale}.`,
            );
        };

        await navigate(client, sessionId, `${baseUrl}/profile/settings`);
        originalProfileLocale = await evaluate(
            client,
            sessionId,
            "document.querySelector('#profile-settings-locale').value",
        );

        for (const viewport of pageIdentityViewports) {
            await setProfileLocale(viewport.locale);
            await client.send('Emulation.setDeviceMetricsOverride', {
                width: viewport.width,
                height: viewport.height,
                deviceScaleFactor: viewport.zoom ?? 1,
                mobile: viewport.mobile,
                screenWidth: viewport.screenWidth ?? viewport.width,
                screenHeight: viewport.screenHeight ?? viewport.height,
            }, sessionId);
            await client.send('Emulation.setTouchEmulationEnabled', {
                enabled: viewport.mobile,
                maxTouchPoints: viewport.mobile ? 5 : 1,
            }, sessionId);
            await client.send('Emulation.setEmulatedMedia', {
                features: [
                    { name: 'prefers-reduced-motion', value: 'reduce' },
                    { name: 'forced-colors', value: viewport.forcedColors ? 'active' : 'none' },
                ],
            }, sessionId);

            for (const route of pageIdentityRoutes) {
                const label = `${viewport.label} ${route.label}`;
                const routeUrl = route.path === '/messages' && viewport.width < 832
                    ? `${baseUrl}${route.path}?conversation=ari`
                    : `${baseUrl}${route.path}`;
                await navigate(client, sessionId, routeUrl);
                const pageAudit = await evaluate(client, sessionId, pageAuditExpression);
                assertPageAudit(pageAudit, label);
                const behavior = await evaluate(client, sessionId, `(() => {
                    const header = document.querySelector('main [data-page-identity="canonical"]');
                    const heading = header?.querySelector('h1');
                    const copy = header?.querySelector('.page-header__copy');
                    const description = header?.querySelector('.page-header__description');
                    const aside = header?.querySelector('.page-header__aside');
                    const actions = header?.querySelector('.page-header__actions');
                    const style = heading ? getComputedStyle(heading) : null;
                    const visible = (element) => {
                        const elementStyle = getComputedStyle(element);
                        const box = element.getBoundingClientRect();

                        return elementStyle.display !== 'none'
                            && elementStyle.visibility !== 'hidden'
                            && box.width > 0
                            && box.height > 0;
                    };
                    const directText = (element) => [...(element?.childNodes ?? [])]
                        .filter((node) => node.nodeType === Node.TEXT_NODE)
                        .map((node) => node.textContent.trim())
                        .filter(Boolean)
                        .join(' ');
                    const smallTargets = header
                        ? [...header.querySelectorAll('a, button, input, select, textarea, [role="button"]')]
                            .filter(visible)
                            .map((element) => ({
                                label: element.getAttribute('aria-label')
                                    || element.textContent.trim().slice(0, 60)
                                    || element.getAttribute('name'),
                                width: Math.round(element.getBoundingClientRect().width),
                                height: Math.round(element.getBoundingClientRect().height),
                            }))
                            .filter((target) => target.width < 44 || target.height < 44)
                        : [];
                    const clippedRegions = [header, copy, heading, description, aside, actions]
                        .filter(Boolean)
                        .filter((element) => element.scrollWidth > element.clientWidth + 1)
                        .map((element) => element.className || element.tagName);
                    const copyBox = copy?.getBoundingClientRect();
                    const actionsBox = actions?.getBoundingClientRect();
                    const actionsOverlapCopy = Boolean(
                        copyBox && actionsBox
                        && copyBox.left < actionsBox.right - 1
                        && copyBox.right > actionsBox.left + 1
                        && copyBox.top < actionsBox.bottom - 1
                        && copyBox.bottom > actionsBox.top + 1
                    );
                    const firstAction = header?.querySelector('a, button');
                    const focusBefore = firstAction ? getComputedStyle(firstAction) : null;
                    const borderColorBefore = focusBefore?.borderColor ?? null;
                    firstAction?.focus();
                    const focusStyle = firstAction ? getComputedStyle(firstAction) : null;
                    const medicalCard = document.querySelector('.medical-record-card');
                    const medicalMedia = medicalCard?.querySelector('.medical-record-card__media');
                    const medicalBody = medicalCard?.querySelector('.medical-record-card__body');
                    const medicalCardBox = medicalCard?.getBoundingClientRect();
                    const medicalMediaBox = medicalMedia?.getBoundingClientRect();
                    const medicalBodyBox = medicalBody?.getBoundingClientRect();
                    const careCard = document.querySelector('.care-journal-card');
                    const careMedia = careCard?.querySelector('.care-journal-card__media');
                    const careBody = careCard?.querySelector('.care-journal-card__body');
                    const careCardBox = careCard?.getBoundingClientRect();
                    const careMediaBox = careMedia?.getBoundingClientRect();
                    const careBodyBox = careBody?.getBoundingClientRect();
                    const lostFoundStats = document.querySelector('[data-lost-found-stats]');
                    const lostFoundStatItems = [...document.querySelectorAll('[data-lost-found-stat]')];
                    const lostFoundFilters = document.querySelector('form[role="search"]');
                    const searchCaseCard = document.querySelector('[data-search-case-card]');
                    const searchMap = document.querySelector('[data-search-map]');
                    const lostFoundStatsBox = lostFoundStats?.getBoundingClientRect();
                    const lostFoundLastStatBox = lostFoundStatItems.at(-1)?.getBoundingClientRect();
                    const marketplaceStats = document.querySelector('[data-marketplace-stats]');
                    const marketplaceStatItems = [...document.querySelectorAll('[data-marketplace-stat]')];
                    const marketplaceFilters = document.querySelector('[data-marketplace-filters]');
                    const listingCard = document.querySelector('[data-listing-card]');
                    const expertStats = document.querySelector('[data-expert-stats]');
                    const expertStatItems = [...document.querySelectorAll('[data-expert-stat]')];
                    const expertFilters = document.querySelector('[data-expert-filters]');
                    const expertCard = document.querySelector('[data-expert-card]');
                    const groupSummary = document.querySelector('[data-group-summary]');
                    const groupFilters = document.querySelector('[data-group-filters]');
                    const groupCard = document.querySelector('[data-group-card]');
                    const neighborSummary = document.querySelector('[data-neighbor-summary]');
                    const neighborFilters = document.querySelector('[data-neighbor-filters]');
                    const neighborResults = document.querySelector('[data-neighbor-results]');
                    const neighborCards = [...document.querySelectorAll('[data-neighbor-card]')];
                    const neighborCard = neighborCards[0];
                    const messagingFolders = document.querySelector('[data-messaging-folders]');
                    const messagingInbox = document.querySelector('[data-messaging-inbox]');
                    const messagingThread = document.querySelector('[data-messaging-thread-header]');
                    const messagingMessageList = document.querySelector('[data-messaging-message-list]');
                    const messagingComposer = document.querySelector('[data-messaging-composer]');
                    const messagingMessages = [...document.querySelectorAll('[data-messaging-message]')];
                    const firstMessagingMessage = messagingMessages[0];
                    const messagingThreadRegion = document.querySelector('.messaging-thread');
                    const messagingContext = document.querySelector('[data-messaging-context]');
                    const placeSummary = document.querySelector('[data-section="places-summary"]');
                    const placeSearch = document.querySelector('.place-search');
                    const placeControls = document.querySelector('[data-place-controls]');
                    const placeMap = document.querySelector('[data-place-map]');
                    const placeResults = document.querySelector('.place-results');
                    const placeCard = document.querySelector('[data-place-card]');
                    const placeComparison = document.querySelector('.place-comparison');
                    const placeRegions = [placeSummary, placeSearch, placeControls, placeMap, placeResults, placeComparison]
                        .filter(Boolean);
                    const placeTargets = placeRegions.flatMap((region) => [...region.querySelectorAll(
                        'a, button, input:not([type="hidden"]), select, summary, [role="button"]'
                    )]).filter(visible);

                    return {
                        documentLanguage: document.documentElement.lang,
                        documentTitle: document.title,
                        headerCount: document.querySelectorAll('main [data-page-identity="canonical"]').length,
                        h1Count: document.querySelectorAll('main h1').length,
                        legacyHeaderCount: document.querySelectorAll(
                            'main .forum-header, main .care-directory-header, main .messaging-page__header'
                        ).length,
                        headingId: heading?.id ?? null,
                        labelledBy: header?.getAttribute('aria-labelledby') ?? null,
                        eyebrowText: header?.querySelector('.page-header__eyebrow')?.textContent.trim() ?? null,
                        headingText: heading?.textContent.trim() ?? null,
                        descriptionText: description?.textContent.trim() ?? null,
                        titleFontFamily: style?.fontFamily ?? null,
                        titleFontSize: Number.parseFloat(style?.fontSize ?? '0'),
                        titleFontWeight: style?.fontWeight ?? null,
                        titleLineHeight: Number.parseFloat(style?.lineHeight ?? '0'),
                        clippedRegions,
                        actionsOverlapCopy,
                        smallTargets,
                        reducedMotion: matchMedia('(prefers-reduced-motion: reduce)').matches,
                        forcedColors: matchMedia('(forced-colors: active)').matches,
                        devicePixelRatio: window.devicePixelRatio,
                        innerWidth: window.innerWidth,
                        screenWidth: window.screen.width,
                        focusVisible: ! firstAction || Boolean(
                            focusStyle
                            && (
                                (
                                    focusStyle.outlineStyle !== 'none'
                                    && Number.parseFloat(focusStyle.outlineWidth) > 0
                                )
                                || focusStyle.boxShadow !== 'none'
                                || focusStyle.borderColor !== borderColorBefore
                            )
                        ),
                        rawTranslationKeys: document.body.innerText.match(
                            /\\b(?:ui|messages|forum|navigation|pet_profiles|places|place_directory|experts|groups|neighbors|messaging)\\.[a-z0-9_.-]+/gi
                        ) ?? [],
                        navigationCopy: {
                            utility: {
                                brandHomeLabel: document.querySelector('.brand-link')
                                    ?.getAttribute('aria-label') ?? null,
                                searchLabel: document.querySelector('[data-header-link="discover"]')
                                    ?.getAttribute('aria-label') ?? null,
                                searchPlaceholder: document.querySelector('[data-header-link="discover"] span')
                                    ?.textContent.trim() ?? null,
                                actionLabels: ['circle', 'notifications', 'messages', 'profile']
                                    .map((name) => document.querySelector('[data-header-link="' + name + '"]')
                                        ?.getAttribute('aria-label') ?? null),
                            },
                            desktopLabel: document.querySelector(
                                'nav[data-navigation-variant="desktop"]'
                            )?.getAttribute('aria-label') ?? null,
                            desktopItems: [...document.querySelectorAll(
                                'nav[data-navigation-variant="desktop"] a[data-nav-item] span'
                            )].map((element) => element.textContent.trim()),
                            mobileLabel: document.querySelector(
                                'nav[data-navigation-variant="mobile"]'
                            )?.getAttribute('aria-label') ?? null,
                            mobileItems: [...document.querySelectorAll(
                                'nav[data-navigation-variant="mobile"] a[data-nav-item] span'
                            )].map((element) => element.textContent.trim()),
                        },
                        medicalRecordCopy: {
                            privacyLabel: document.querySelector('.medical-privacy-strip')
                                ?.getAttribute('aria-label') ?? null,
                            privacyTitle: document.querySelector('.medical-privacy-strip strong')
                                ?.textContent.trim() ?? null,
                            privacyDescription: document.querySelector('.medical-privacy-strip span')
                                ?.textContent.trim() ?? null,
                            sectionTitle: document.querySelector('#health-record-list-title')
                                ?.textContent.trim() ?? null,
                            cardEyebrow: document.querySelector('.medical-record-card__body > div p')
                                ?.textContent.trim() ?? null,
                            cardBadge: document.querySelector('.medical-record-card .status-badge span:last-child')
                                ?.textContent.trim() ?? null,
                            statLabels: [...(
                                document.querySelector('.medical-record-card')
                                    ?.querySelectorAll('.medical-record-card__stats dt') ?? []
                            )]
                                .map((element) => element.textContent.trim()),
                            actionLabel: document.querySelector('.medical-record-card__body > div:last-child a')
                                ?.textContent.trim() ?? null,
                            imageAlt: document.querySelector('.medical-record-card img')?.alt ?? null,
                        },
                        medicalRecordLayout: medicalCardBox && medicalMediaBox && medicalBodyBox
                            ? {
                                cardWidth: Math.round(medicalCardBox.width),
                                mediaWidth: Math.round(medicalMediaBox.width),
                                mediaBottom: Math.round(medicalMediaBox.bottom),
                                bodyTop: Math.round(medicalBodyBox.top),
                            }
                            : null,
                        careJournalCopy: {
                            familyLabel: document.querySelector('.care-family-strip')
                                ?.getAttribute('aria-label') ?? null,
                            familyTitle: document.querySelector('.care-family-strip strong')
                                ?.textContent.trim() ?? null,
                            familyDescription: document.querySelector('.care-family-strip p')
                                ?.textContent.trim() ?? null,
                            directoryLabel: document.querySelector('.care-directory-grid')
                                ?.getAttribute('aria-label') ?? null,
                            species: document.querySelector('.care-journal-card__body > div p')
                                ?.textContent.trim() ?? null,
                            cardBadge: document.querySelector('.care-journal-card .status-badge span:last-child')
                                ?.textContent.trim() ?? null,
                            statLabels: [...(
                                document.querySelector('.care-journal-card')
                                    ?.querySelectorAll('.care-journal-card__stats dt') ?? []
                            )]
                                .map((element) => element.textContent.trim()),
                            statValues: [...(
                                document.querySelector('.care-journal-card')
                                    ?.querySelectorAll('.care-journal-card__stats dd') ?? []
                            )]
                                .map((element) => element.textContent.trim()),
                            actionLabels: [...(
                                document.querySelector('.care-journal-card')
                                    ?.querySelectorAll('.care-journal-card__body > div:last-child .action') ?? []
                            )]
                                .map((element) => element.textContent.trim()),
                            mediaLabel: document.querySelector('.care-journal-card__media')
                                ?.getAttribute('aria-label') ?? null,
                        },
                        careJournalLayout: careCardBox && careMediaBox && careBodyBox
                            ? {
                                cardWidth: Math.round(careCardBox.width),
                                mediaWidth: Math.round(careMediaBox.width),
                                mediaBottom: Math.round(careMediaBox.bottom),
                                bodyTop: Math.round(careBodyBox.top),
                            }
                            : null,
                        lostFoundCopy: {
                            statsLabel: lostFoundStats?.getAttribute('aria-label') ?? null,
                            statsLabels: lostFoundStatItems.map(
                                (item) => item.querySelector('div span')?.textContent.trim() ?? null,
                            ),
                            filterLabels: [...(lostFoundFilters?.querySelectorAll('label') ?? [])]
                                .map(directText),
                            filterDefaults: [...(lostFoundFilters?.querySelectorAll('select') ?? [])]
                                .map((select) => select.selectedOptions[0]?.textContent.trim() ?? null),
                            searchPlaceholder: lostFoundFilters?.querySelector('input[name="q"]')
                                ?.getAttribute('placeholder') ?? null,
                            applyLabel: lostFoundFilters?.querySelector('button[type="submit"] span')
                                ?.textContent.trim() ?? null,
                            clearLabel: lostFoundFilters?.querySelector('a[title]')
                                ?.getAttribute('title') ?? null,
                            resultsTitle: document.querySelector('#search-results-title')
                                ?.textContent.trim() ?? null,
                            resultsOrder: document.querySelector('[data-lost-found-results-order]')
                                ?.textContent.trim() ?? null,
                            cardType: searchCaseCard?.querySelector('[data-search-case-type]')
                                ?.textContent.trim() ?? null,
                            cardSpecies: searchCaseCard?.querySelector('[data-search-case-species]')
                                ?.textContent.trim() ?? null,
                            cardStatus: searchCaseCard?.querySelector('[data-search-case-status]')
                                ?.textContent.trim() ?? null,
                            cardCounts: [...(searchCaseCard?.querySelectorAll(
                                '[data-search-case-counts] span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            areaLabel: searchCaseCard?.querySelector('[data-search-case-area-label]')
                                ?.textContent.trim() ?? null,
                            lastSeenLabel: searchCaseCard?.querySelector(
                                '[data-search-case-last-seen-label]'
                            )?.textContent.trim() ?? null,
                            mapTitle: searchMap?.querySelector('#search-map-title')
                                ?.textContent.trim() ?? null,
                            mapPrivacy: searchMap?.querySelector('[data-search-map-privacy]')
                                ?.textContent.trim() ?? null,
                            mapListLabel: searchMap?.querySelector('ol')
                                ?.getAttribute('aria-label') ?? null,
                            guidanceTitle: document.querySelector('[data-lost-found-guidance-title]')
                                ?.textContent.trim() ?? null,
                            guidanceCopy: document.querySelector('[data-lost-found-guidance-copy]')
                                ?.textContent.trim() ?? null,
                        },
                        lostFoundLayout: lostFoundStatsBox && lostFoundLastStatBox
                            ? {
                                itemCount: lostFoundStatItems.length,
                                statsWidth: Math.round(lostFoundStatsBox.width),
                                lastStatWidth: Math.round(lostFoundLastStatBox.width),
                            }
                            : null,
                        marketplaceCopy: {
                            statsLabel: marketplaceStats?.getAttribute('aria-label') ?? null,
                            statsLabels: marketplaceStatItems.map(
                                (item) => item.querySelector('div span')?.textContent.trim() ?? null,
                            ),
                            filterLabels: [...(marketplaceFilters?.querySelectorAll('label') ?? [])]
                                .map(directText),
                            filterDefaults: [...(marketplaceFilters?.querySelectorAll('select') ?? [])]
                                .map((select) => select.selectedOptions[0]?.textContent.trim() ?? null),
                            searchPlaceholder: marketplaceFilters?.querySelector('input[name="q"]')
                                ?.getAttribute('placeholder') ?? null,
                            applyLabel: marketplaceFilters?.querySelector('button[type="submit"] span')
                                ?.textContent.trim() ?? null,
                            clearLabel: marketplaceFilters?.querySelector('a[title]')
                                ?.getAttribute('title') ?? null,
                            resultsTitle: document.querySelector('[data-marketplace-results-title]')
                                ?.textContent.trim() ?? null,
                            resultsDescription: document.querySelector(
                                '[data-marketplace-results-description]'
                            )?.textContent.trim() ?? null,
                            resultsPrivacy: document.querySelector('[data-marketplace-results-privacy]')
                                ?.textContent.trim() ?? null,
                            cardType: listingCard?.querySelector('[data-listing-type]')
                                ?.textContent.trim() ?? null,
                            cardCategory: listingCard?.querySelector('[data-listing-category]')
                                ?.textContent.trim() ?? null,
                            cardSpecies: listingCard?.querySelector('[data-listing-species]')
                                ?.textContent.trim() ?? null,
                            cardLocationLabel: listingCard?.querySelector('[data-listing-location-label]')
                                ?.textContent.trim() ?? null,
                            cardAvailabilityLabel: listingCard?.querySelector(
                                '[data-listing-availability-label]'
                            )?.textContent.trim() ?? null,
                            cardSellerType: directText(
                                listingCard?.querySelector('[data-listing-seller-type]')
                            ),
                            cardSaveLabel: listingCard?.querySelector('[data-listing-save] span')
                                ?.textContent.trim() ?? null,
                            cardViewLabel: listingCard?.querySelector('[data-listing-view] span')
                                ?.textContent.trim() ?? null,
                        },
                        expertCopy: {
                            statsLabel: expertStats?.getAttribute('aria-label') ?? null,
                            statsLabels: expertStatItems.map(
                                (item) => item.querySelector('div span')?.textContent.trim() ?? null,
                            ),
                            filterLabels: [...(expertFilters?.querySelectorAll('label') ?? [])]
                                .map(directText),
                            filterDefaults: [...(expertFilters?.querySelectorAll('select') ?? [])]
                                .map((select) => select.selectedOptions[0]?.textContent.trim() ?? null),
                            searchPlaceholder: expertFilters?.querySelector('input[name="q"]')
                                ?.getAttribute('placeholder') ?? null,
                            applyLabel: expertFilters?.querySelector('button[type="submit"] span')
                                ?.textContent.trim() ?? null,
                            clearLabel: expertFilters?.querySelector('a[title]')
                                ?.getAttribute('title') ?? null,
                            resultsTitle: document.querySelector('[data-expert-results-title]')
                                ?.textContent.trim() ?? null,
                            resultsDescription: document.querySelector('[data-expert-results-description]')
                                ?.textContent.trim() ?? null,
                            urgentLabel: document.querySelector('[data-expert-urgent]')
                                ?.textContent.trim() ?? null,
                            cardBadge: expertCard?.querySelector('[data-expert-card-badge] span:last-child')
                                ?.textContent.trim() ?? null,
                            cardType: expertCard?.querySelector('[data-expert-card-type]')
                                ?.textContent.trim() ?? null,
                            cardSpecializations: expertCard?.querySelector('[data-expert-card-specializations]')
                                ?.textContent.trim() ?? null,
                            cardFactLabels: [...(expertCard?.querySelectorAll(
                                '[data-expert-card-facts] dt'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            cardViewLabel: expertCard?.querySelector('[data-expert-card-view] span')
                                ?.textContent.trim() ?? null,
                            cardBookLabel: expertCard?.querySelector('[data-expert-card-book] span')
                                ?.textContent.trim() ?? null,
                        },
                        groupCopy: {
                            summaryLabel: groupSummary?.getAttribute('aria-label') ?? null,
                            summaryLabels: [...(groupSummary?.querySelectorAll('.summary-stat__label span') ?? [])]
                                .map((element) => element.textContent.trim()),
                            summaryDetails: [...(groupSummary?.querySelectorAll('.summary-stat__detail') ?? [])]
                                .map((element) => element.textContent.trim()),
                            toolbarLabel: groupFilters?.getAttribute('aria-label') ?? null,
                            filterGroupLabel: groupFilters?.querySelector('[role="group"]')
                                ?.getAttribute('aria-label') ?? null,
                            filterLabels: [...(groupFilters?.querySelectorAll('[role="group"] button') ?? [])]
                                .map((element) => element.textContent.trim()),
                            sortLabel: groupFilters?.querySelector('label[for="group-filters-sort"]')
                                ?.textContent.trim() ?? null,
                            sortOptions: [...(groupFilters?.querySelectorAll('select[name="sort"] option') ?? [])]
                                .map((element) => element.textContent.trim()),
                            searchLabel: groupFilters?.querySelector('label[for="group-search"]')
                                ?.textContent.trim() ?? null,
                            searchPlaceholder: groupFilters?.querySelector('input[name="q"]')
                                ?.getAttribute('placeholder') ?? null,
                            resultsTitle: document.querySelector('[data-group-results-title]')
                                ?.getAttribute('data-group-results-title') ?? null,
                            cardCategory: groupCard?.querySelector('[data-group-card-category] span:last-child')
                                ?.textContent.trim() ?? null,
                            cardPrivacy: groupCard?.querySelector('[data-group-card-privacy] span:last-child')
                                ?.textContent.trim() ?? null,
                            cardReasonLabel: groupCard?.querySelector('[data-group-card-reason] .recommendation-reason__label')
                                ?.textContent.trim() ?? null,
                            cardReason: groupCard?.querySelector('[data-group-card-reason] .recommendation-reason__copy p:last-child')
                                ?.textContent.trim() ?? null,
                            cardDescription: groupCard?.querySelector('[data-group-card-description]')
                                ?.textContent.trim() ?? null,
                            cardTags: [...(groupCard?.querySelectorAll('[data-group-card-tags] .tag') ?? [])]
                                .map((element) => element.textContent.trim()),
                            cardMetricLabels: [...(groupCard?.querySelectorAll('[data-group-card-metrics] dt span') ?? [])]
                                .map((element) => element.textContent.trim()),
                        },
                        neighborCopy: {
                            actionLabel: header?.querySelector('.page-header__actions .action span')
                                ?.textContent.trim() ?? null,
                            summaryLabel: neighborSummary?.getAttribute('aria-label') ?? null,
                            summaryLabels: [...(neighborSummary?.querySelectorAll('.summary-stat__label span') ?? [])]
                                .map((element) => element.textContent.trim()),
                            summaryValues: [...(neighborSummary?.querySelectorAll('.summary-stat__value') ?? [])]
                                .map((element) => element.textContent.trim()),
                            summaryDetails: [...(neighborSummary?.querySelectorAll('.summary-stat__detail') ?? [])]
                                .map((element) => element.textContent.trim()),
                            toolbarLabel: neighborFilters?.getAttribute('aria-label') ?? null,
                            filterGroupLabel: neighborFilters?.querySelector('[role="group"]')
                                ?.getAttribute('aria-label') ?? null,
                            filterLabels: [...(neighborFilters?.querySelectorAll('[role="group"] button') ?? [])]
                                .map((element) => element.textContent.trim()),
                            sortLabel: neighborFilters?.querySelector('label[for="neighbor-filters-sort"]')
                                ?.textContent.trim() ?? null,
                            sortOptions: [...(neighborFilters?.querySelectorAll('select[name="sort"] option') ?? [])]
                                .map((element) => element.textContent.trim()),
                            searchLabel: neighborFilters?.querySelector('label[for="neighbor-search"]')
                                ?.textContent.trim() ?? null,
                            searchPlaceholder: neighborFilters?.querySelector('input[name="q"]')
                                ?.getAttribute('placeholder') ?? null,
                            resultsTitle: neighborResults?.closest('section')?.querySelector('h2')
                                ?.textContent.trim() ?? null,
                            cardCategory: neighborCard?.querySelector('[data-neighbor-card-category] span:last-child')
                                ?.textContent.trim() ?? null,
                            cardDistance: neighborCard?.querySelector('[data-neighbor-card-distance] span:last-child')
                                ?.textContent.trim() ?? null,
                            cardPet: neighborCard?.querySelector('[data-neighbor-card-pet]')
                                ?.textContent.trim() ?? null,
                            cardStatus: neighborCard?.querySelector('[data-neighbor-card-status]')
                                ?.textContent.trim() ?? null,
                            cardTags: [...(neighborCard?.querySelectorAll('[data-neighbor-card-interests] .tag') ?? [])]
                                .map((element) => element.textContent.trim()),
                            cardFollowLabel: neighborCard?.querySelector('[data-neighbor-card-follow] span:last-child')
                                ?.textContent.trim() ?? null,
                            cardImageAlt: neighborCard?.querySelector('img')?.getAttribute('alt') ?? null,
                            remainingCardCategories: neighborCards.slice(1).map(
                                (card) => card.querySelector('[data-neighbor-card-category] span:last-child')
                                    ?.textContent.trim() ?? null,
                            ),
                            remainingCardDistances: neighborCards.slice(1).map(
                                (card) => card.querySelector('[data-neighbor-card-distance] span:last-child')
                                    ?.textContent.trim() ?? null,
                            ),
                            remainingCardStatuses: neighborCards.slice(1).map(
                                (card) => card.querySelector('[data-neighbor-card-status]')
                                    ?.textContent.trim() ?? null,
                            ),
                            cardPets: neighborCards.map(
                                (card) => card.querySelector('[data-neighbor-card-pet]')
                                    ?.textContent.trim() ?? null,
                            ),
                        },
                        placeCopy: {
                            actionLabel: header?.querySelector('.page-header__actions .action span')
                                ?.textContent.trim() ?? null,
                            summaryLabel: placeSummary?.getAttribute('aria-label') ?? null,
                            summaryLabels: [...(placeSummary?.querySelectorAll('.summary-stat__label span') ?? [])]
                                .map((element) => element.textContent.trim()),
                            summaryDetails: [...(placeSummary?.querySelectorAll('.summary-stat__detail') ?? [])]
                                .map((element) => element.textContent.trim()),
                            localizedSummaryValues: [...(placeSummary?.querySelectorAll('.summary-stat__value') ?? [])]
                                .slice(2)
                                .map((element) => element.textContent.trim()),
                            searchEyebrow: placeSearch?.querySelector('.place-search__eyebrow')
                                ?.textContent.trim() ?? null,
                            emergencyAction: placeSearch?.querySelector('.place-search__heading .action span')
                                ?.textContent.trim() ?? null,
                            primaryLabels: [
                                placeSearch?.querySelector('label[for="place-search"]')
                                    ?.textContent.trim() ?? null,
                                ...(placeSearch?.querySelectorAll(
                                    '.place-search__primary .field-group__label'
                                ) ?? []),
                            ].map((element) => typeof element === 'string'
                                ? element
                                : element?.textContent.trim() ?? null),
                            searchPlaceholder: placeSearch?.querySelector('#place-search')
                                ?.getAttribute('placeholder') ?? null,
                            noPetOption: placeSearch?.querySelector('#place-pet option[value="none"]')
                                ?.textContent.trim() ?? null,
                            searchAction: placeSearch?.querySelector('.place-search__submit span')
                                ?.textContent.trim() ?? null,
                            categoriesLabel: placeSearch?.querySelector('[data-place-categories]')
                                ?.getAttribute('aria-label') ?? null,
                            categoryLabels: [...(placeSearch?.querySelectorAll(
                                '[data-place-categories] button span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            moreFilters: placeSearch?.querySelector('.place-search__filters > summary')
                                ?.textContent.trim() ?? null,
                            filterLabels: [...(placeSearch?.querySelectorAll(
                                '.place-search__filter-grid .field-group__label'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            filterDefaults: [...(placeSearch?.querySelectorAll(
                                '.place-search__filter-grid select'
                            ) ?? [])].map((select) => select.selectedOptions[0]?.textContent.trim() ?? null),
                            openNow: placeSearch?.querySelector('.place-search__toggle span')
                                ?.textContent.trim() ?? null,
                            filterActions: [...(placeSearch?.querySelectorAll(
                                '.place-search__filter-actions .action span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            locationTitle: placeSearch?.querySelector('.place-location__copy strong')
                                ?.textContent.trim() ?? null,
                            locationDescription: placeSearch?.querySelector('.place-location__copy span')
                                ?.textContent.trim() ?? null,
                            locationAction: placeSearch?.querySelector('[data-place-locate] span')
                                ?.textContent.trim() ?? null,
                            controlsLabel: placeControls?.getAttribute('aria-label') ?? null,
                            modeLabels: [...(placeControls?.querySelectorAll(
                                '.place-directory__modes a span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            viewLabel: placeControls?.querySelector('.place-directory__views')
                                ?.getAttribute('aria-label') ?? null,
                            viewLabels: [...(placeControls?.querySelectorAll(
                                '.place-directory__views a span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            sortLabel: placeControls?.querySelector('label[for="place-sort"]')
                                ?.textContent.trim() ?? null,
                            sortOptions: [...(placeControls?.querySelectorAll('#place-sort option') ?? [])]
                                .map((element) => element.textContent.trim()),
                            sortAction: placeControls?.querySelector('.place-directory__sort button')
                                ?.getAttribute('aria-label') ?? null,
                            mapEyebrow: placeMap?.querySelector('.place-map__eyebrow')
                                ?.textContent.trim() ?? null,
                            mapTitle: placeMap?.querySelector('#place-map-title')
                                ?.textContent.trim() ?? null,
                            mapControlsLabel: placeMap?.querySelector('.place-map__controls')
                                ?.getAttribute('aria-label') ?? null,
                            mapControlLabels: [...(placeMap?.querySelectorAll('.place-map__controls button') ?? [])]
                                .map((element) => element.getAttribute('aria-label')),
                            oldTown: placeMap?.querySelector('.place-map__district--old-town')
                                ?.textContent.trim() ?? null,
                            mapAlternative: placeMap?.querySelector('ol')
                                ?.getAttribute('aria-label') ?? null,
                            resultsEyebrow: placeResults?.querySelector('.place-results__heading p')
                                ?.textContent.trim() ?? null,
                            resultsHeading: placeResults?.querySelector('#place-results-title')
                                ?.textContent.trim() ?? null,
                            layerLabel: placeResults?.querySelector('[data-place-layers]')
                                ?.getAttribute('aria-label') ?? null,
                            layerLabels: [...(placeResults?.querySelectorAll(
                                '[data-place-layers] a span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            cardHighlights: placeCard?.querySelector('.place-card__facts')
                                ?.getAttribute('aria-label') ?? null,
                            cardActions: [...(placeCard?.querySelectorAll('.place-card__actions .action span') ?? [])]
                                .map((element) => element.textContent.trim()),
                            comparisonEyebrow: placeComparison?.querySelector(':scope > header p')
                                ?.textContent.trim() ?? null,
                            comparisonTitle: placeComparison?.querySelector(':scope > header h2')
                                ?.textContent.trim() ?? null,
                            comparisonLabels: [...(placeComparison?.querySelectorAll(
                                '.place-comparison__card:first-child dt'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            comparisonAccess: placeComparison?.querySelector(
                                '.place-comparison__card:first-child dl > div:nth-child(4) dd'
                            )?.textContent.trim() ?? null,
                        },
                        placeLayout: {
                            clippedLabels: placeRegions.flatMap((region) => [...region.querySelectorAll(
                                '.summary-stat__label span, .summary-stat__detail, '
                                    + '.place-search__categories button span, .field-group__label, '
                                    + '.place-search__toggle span, .place-directory__modes a span, '
                                    + '.place-directory__views a span, .place-results__layers a span, '
                                    + '.place-map__header h2, .place-comparison dt, .place-comparison > header h2'
                            )]).filter((element) => (
                                element.scrollWidth > element.clientWidth + 1
                                || element.scrollHeight > element.clientHeight + 1
                            )).map((element) => ({
                                text: element.textContent.trim(),
                                clientWidth: element.clientWidth,
                                scrollWidth: element.scrollWidth,
                                clientHeight: element.clientHeight,
                                scrollHeight: element.scrollHeight,
                            })),
                            smallTargets: placeTargets.map((element) => ({
                                label: element.getAttribute('aria-label')
                                    || element.textContent.trim().slice(0, 80)
                                    || element.getAttribute('placeholder')
                                    || element.getAttribute('name'),
                                width: Math.round(element.getBoundingClientRect().width),
                                height: Math.round(element.getBoundingClientRect().height),
                            })).filter((target) => target.width < 44 || target.height < 44),
                        },
                        messagingCopy: {
                            actionLabel: header?.querySelector('.page-header__actions .action span')
                                ?.textContent.trim() ?? null,
                            metaLabel: header?.querySelector('.page-header__meta')
                                ?.getAttribute('aria-label') ?? null,
                            foldersLabel: messagingFolders?.getAttribute('aria-label') ?? null,
                            folderLabels: [...(messagingFolders?.querySelectorAll('.messaging-filter > span') ?? [])]
                                .map((element) => element.textContent.trim()),
                            inboxLabel: messagingInbox?.getAttribute('aria-label') ?? null,
                            searchLabel: messagingInbox?.querySelector('label[for="message-conversation-search"]')
                                ?.textContent.trim() ?? null,
                            searchPlaceholder: messagingInbox?.querySelector('#message-conversation-search')
                                ?.getAttribute('placeholder') ?? null,
                            searchAction: messagingInbox?.querySelector('.messaging-inbox__search button')
                                ?.getAttribute('aria-label') ?? null,
                            conversationsLabel: messagingInbox?.querySelector('.messaging-inbox__list')
                                ?.getAttribute('aria-label') ?? null,
                            shownCount: messagingInbox?.querySelector('[data-messaging-inbox-meta] strong')
                                ?.textContent.trim() ?? null,
                            summary: messagingInbox?.querySelector('[data-messaging-inbox-meta] span')
                                ?.textContent.trim() ?? null,
                            conversationTypes: [...(messagingInbox?.querySelectorAll(
                                '[data-messaging-conversation-type]'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            relativeTimes: [...(messagingInbox?.querySelectorAll(
                                '[data-messaging-conversation-time]'
                            ) ?? [])].slice(2).map((element) => element.textContent.trim()),
                            threadBack: messagingThread?.querySelector('.messaging-thread-header__back')
                                ?.getAttribute('aria-label') ?? null,
                            threadActionTitles: [...(messagingThread?.querySelectorAll(
                                '.messaging-thread-header__actions [title]'
                            ) ?? [])].map((element) => element.getAttribute('title')),
                            threadActionLabels: [...(messagingThread?.querySelectorAll(
                                '.messaging-thread-header__actions .sr-only'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            messageListLabel: messagingMessageList?.getAttribute('aria-label') ?? null,
                            messageDate: messagingMessageList?.querySelector('.messaging-date-divider time')
                                ?.textContent.trim() ?? null,
                            composerLabel: messagingComposer?.getAttribute('aria-label') ?? null,
                            composerReply: messagingComposer?.querySelector('[data-message-reply] span')
                                ?.textContent.trim() ?? null,
                            composerCancelReply: messagingComposer?.querySelector('[data-message-reply-clear]')
                                ?.getAttribute('aria-label') ?? null,
                            composerDraftSaving: messagingComposer?.querySelector('[data-message-composer]')
                                ?.getAttribute('data-draft-saving') ?? null,
                            composerDraftSaved: messagingComposer?.querySelector('[data-message-composer]')
                                ?.getAttribute('data-draft-saved') ?? null,
                            composerMessageType: messagingComposer?.querySelector('.messaging-composer__tools')
                                ?.getAttribute('aria-label') ?? null,
                            composerToolTitles: [...(messagingComposer?.querySelectorAll(
                                '[data-message-type-button]'
                            ) ?? [])].map((element) => element.getAttribute('title')),
                            composerToolActionLabels: [...(messagingComposer?.querySelectorAll(
                                '[data-message-type-button]'
                            ) ?? [])].map((element) => element.getAttribute('aria-label')),
                            composerRecipient: messagingComposer?.querySelector('label[for^="message-body-"]')
                                ?.textContent.trim() ?? null,
                            composerPlaceholder: messagingComposer?.querySelector('[data-message-body]')
                                ?.getAttribute('placeholder') ?? null,
                            composerQuiet: messagingComposer?.querySelector(
                                '.messaging-composer__footer label span'
                            )?.textContent.trim() ?? null,
                            composerDraftStatus: messagingComposer?.querySelector('[data-message-draft-status]')
                                ?.textContent.trim() ?? null,
                            composerSend: messagingComposer?.querySelector('.action--primary span')
                                ?.textContent.trim() ?? null,
                            composerSchedule: messagingComposer?.querySelector(
                                '.messaging-composer__schedule summary'
                            )?.textContent.trim() ?? null,
                            composerSendAt: messagingComposer?.querySelector(
                                '.messaging-composer__schedule label'
                            )?.textContent.trim() ?? null,
                            composerScheduleHelp: messagingComposer?.querySelector(
                                '.messaging-composer__schedule p'
                            )?.textContent.trim() ?? null,
                            composerPrivacy: messagingComposer?.querySelector('.messaging-composer__privacy')
                                ?.textContent.trim() ?? null,
                            structuredMessageTypes: messagingMessages.filter(
                                (element) => ['place', 'audio'].includes(
                                    element.getAttribute('data-messaging-message-type')
                                )
                            ).map((element) => element.querySelector('.messaging-message__media small')
                                ?.textContent.trim() ?? null),
                            messageStatuses: messagingMessages.map((element) => element.querySelector(
                                '[data-messaging-message-status]'
                            )?.textContent.trim() ?? null),
                            messageStatusCodes: messagingMessages.map((element) => element.querySelector(
                                '[data-messaging-message-status]'
                            )?.getAttribute('data-messaging-message-status-code') ?? null),
                            messageActionsLabel: firstMessagingMessage?.querySelector(
                                '.messaging-message-menu summary'
                            )?.getAttribute('aria-label') ?? null,
                            messageActionLabels: [...(firstMessagingMessage?.querySelectorAll(
                                '.messaging-message-menu > div button'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            audioActionLabel: messagingMessageList?.querySelector('[data-audio-toggle]')
                                ?.getAttribute('aria-label') ?? null,
                            contextLabel: messagingContext?.getAttribute('aria-label') ?? null,
                            contextPurpose: messagingContext?.querySelector('[data-messaging-context-purpose]')
                                ?.textContent.trim() ?? null,
                            contextPrivacy: messagingContext?.querySelector('[data-messaging-context-privacy]')
                                ?.textContent.trim() ?? null,
                            contextIdentityNote: messagingContext?.querySelector('[data-messaging-context-identity-note]')
                                ?.textContent.trim() ?? null,
                            contextSearchLabel: messagingContext?.querySelector('label[for="message-history-search"]')
                                ?.textContent.trim() ?? null,
                            contextSearchPlaceholder: messagingContext?.querySelector('#message-history-search')
                                ?.getAttribute('placeholder') ?? null,
                            contextControlsLabel: messagingContext?.querySelector('[data-messaging-context-controls]')
                                ?.getAttribute('aria-label') ?? null,
                            contextControlLabels: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-controls] button span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextMembersTitle: messagingContext?.querySelector(
                                '[data-messaging-context-members] summary strong'
                            )?.textContent.trim() ?? null,
                            contextSharedTitle: messagingContext?.querySelector(
                                '[data-messaging-context-shared] summary strong'
                            )?.textContent.trim() ?? null,
                            contextSharedLabels: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-shared] .messaging-shared-grid strong'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextSharedValues: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-shared] .messaging-shared-grid small'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextSafetyTitle: messagingContext?.querySelector(
                                '[data-messaging-context-safety] summary strong'
                            )?.textContent.trim() ?? null,
                            contextSafetyTitles: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-safety] .messaging-safety strong'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextSafetyDescriptions: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-safety] .messaging-safety p span'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextSafetyActionLabels: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-safety] .messaging-danger-actions button'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextBoundaryTitle: messagingContext?.querySelector(
                                '[data-messaging-context-boundary] summary strong'
                            )?.textContent.trim() ?? null,
                            contextBoundaryLabels: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-boundary] dt'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextBoundaryValues: [...(messagingContext?.querySelectorAll(
                                '[data-messaging-context-boundary] dd'
                            ) ?? [])].map((element) => element.textContent.trim()),
                            contextActionCodes: [...(messagingContext?.querySelectorAll(
                                'form input[name="action"]'
                            ) ?? [])].map((element) => element.value),
                            contextActionIcons: [...(messagingContext?.querySelectorAll(
                                'form:has(input[name="action"]) [data-ui-icon]'
                            ) ?? [])].map((element) => element.getAttribute('data-ui-icon')),
                        },
                        messagingLayout: {
                            clippedFolderLabels: [...(messagingFolders?.querySelectorAll(
                                '.messaging-filter > span'
                            ) ?? [])].filter(
                                (element) => element.scrollWidth > element.clientWidth + 1
                                    || element.scrollHeight > element.clientHeight + 1,
                            ).map((element) => element.textContent.trim()),
                            smallTargets: [...[
                                ...(messagingFolders?.querySelectorAll('a') ?? []),
                                ...(messagingInbox?.querySelectorAll(
                                    'input:not([type="hidden"]), button'
                                ) ?? []),
                                ...(messagingThreadRegion?.querySelectorAll(
                                    'a, button, input:not([type="hidden"]), select, summary, textarea'
                                ) ?? []),
                                ...(messagingContext?.querySelectorAll(
                                    'a, button, input:not([type="hidden"]), select, summary, textarea'
                                ) ?? []),
                            ]].filter(visible).map((element) => {
                                const target = element.matches('input[type="checkbox"], input[type="radio"]')
                                    ? element.closest('label') ?? element
                                    : element;

                                return {
                                    label: element.getAttribute('aria-label')
                                        || element.textContent.trim()
                                        || element.getAttribute('placeholder'),
                                    width: Math.round(target.getBoundingClientRect().width),
                                    height: Math.round(target.getBoundingClientRect().height),
                                };
                            }).filter((target) => target.width < 44 || target.height < 44),
                        },
                    };
                })()`);
                const expectedTitleSize = viewport.width >= 640 ? 30 : 24;
                const expectedLineHeight = expectedTitleSize * 1.2;

                canonicalTitleFont ??= behavior.titleFontFamily;
                const englishCopy = englishIdentityCopy.get(route.path);

                if (viewport.locale === 'en') {
                    if (englishCopy === undefined) {
                        englishIdentityCopy.set(route.path, {
                            documentTitle: behavior.documentTitle,
                            eyebrowText: behavior.eyebrowText,
                            headingText: behavior.headingText,
                            descriptionText: behavior.descriptionText,
                        });
                    } else {
                        assert(behavior.documentTitle === englishCopy.documentTitle, `${label}: English document title changed across viewports.`);
                        assert(behavior.eyebrowText === englishCopy.eyebrowText, `${label}: English eyebrow changed across viewports.`);
                        assert(behavior.headingText === englishCopy.headingText, `${label}: English heading changed across viewports.`);
                        assert(behavior.descriptionText === englishCopy.descriptionText, `${label}: English description changed across viewports.`);
                    }
                } else {
                    assert(englishCopy !== undefined, `${label}: English identity baseline is missing.`);
                    assert(behavior.documentTitle !== englishCopy.documentTitle, `${label}: document title was not localized.`);
                    assert(behavior.eyebrowText !== englishCopy.eyebrowText, `${label}: eyebrow was not localized.`);
                    assert(behavior.headingText !== englishCopy.headingText, `${label}: heading was not localized.`);
                    assert(behavior.descriptionText !== englishCopy.descriptionText, `${label}: description was not localized.`);
                }

                const navigationCopy = [
                    behavior.navigationCopy.utility.brandHomeLabel,
                    behavior.navigationCopy.utility.searchLabel,
                    behavior.navigationCopy.utility.searchPlaceholder,
                    ...behavior.navigationCopy.utility.actionLabels,
                    behavior.navigationCopy.desktopLabel,
                    ...behavior.navigationCopy.desktopItems,
                    behavior.navigationCopy.mobileLabel,
                    ...behavior.navigationCopy.mobileItems,
                ];
                assert(
                    behavior.navigationCopy.utility.actionLabels.length === 4
                        && behavior.navigationCopy.desktopItems.length === 13
                        && behavior.navigationCopy.mobileItems.length === 11
                        && navigationCopy.every((value) => value?.length > 0),
                    `${label}: the global navigation localization surface is incomplete ${JSON.stringify(behavior.navigationCopy)}.`,
                );

                if (viewport.locale === 'en') {
                    englishNavigationCopy ??= navigationCopy;
                    assert(
                        navigationCopy.every((value, index) => value === englishNavigationCopy[index]),
                        `${label}: English navigation changed across routes or viewports.`,
                    );
                } else {
                    assert(englishNavigationCopy !== null, `${label}: English navigation baseline is missing.`);
                    assert(
                        navigationCopy.every((value, index) => value !== englishNavigationCopy[index]),
                        `${label}: English global navigation fallback remains.`,
                    );
                }

                if (route.path === '/medical-records') {
                    const medicalCopy = [
                        behavior.medicalRecordCopy.privacyLabel,
                        behavior.medicalRecordCopy.privacyTitle,
                        behavior.medicalRecordCopy.privacyDescription,
                        behavior.medicalRecordCopy.sectionTitle,
                        behavior.medicalRecordCopy.cardEyebrow,
                        behavior.medicalRecordCopy.cardBadge,
                        ...behavior.medicalRecordCopy.statLabels,
                        behavior.medicalRecordCopy.actionLabel,
                        behavior.medicalRecordCopy.imageAlt,
                    ];

                    assert(
                        medicalCopy.length === 11 && medicalCopy.every((value) => value?.length > 0),
                        `${label}: the medical record localization surface is incomplete ${JSON.stringify(behavior.medicalRecordCopy)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishMedicalRecordCopy ??= medicalCopy;
                    } else {
                        assert(englishMedicalRecordCopy !== null, `${label}: English medical copy baseline is missing.`);
                        assert(
                            medicalCopy.every((value, index) => value !== englishMedicalRecordCopy[index]),
                            `${label}: English medical body fallback remains.`,
                        );
                    }

                    if (viewport.width <= 375) {
                        assert(behavior.medicalRecordLayout !== null, `${label}: medical card geometry is missing.`);
                        assert(
                            Math.abs(
                                behavior.medicalRecordLayout.mediaWidth
                                - behavior.medicalRecordLayout.cardWidth
                            ) <= 2,
                            `${label}: medical card media is not full width.`,
                        );
                        assert(
                            behavior.medicalRecordLayout.mediaBottom
                                <= behavior.medicalRecordLayout.bodyTop + 1,
                            `${label}: medical card media and body are not stacked.`,
                        );
                    }
                }

                if (route.path === '/care-journals') {
                    const careCopy = [
                        behavior.careJournalCopy.familyLabel,
                        behavior.careJournalCopy.familyTitle,
                        behavior.careJournalCopy.familyDescription,
                        behavior.careJournalCopy.directoryLabel,
                        behavior.careJournalCopy.species,
                        behavior.careJournalCopy.cardBadge,
                        ...behavior.careJournalCopy.statLabels,
                        behavior.careJournalCopy.statValues[0],
                        behavior.careJournalCopy.statValues[2],
                        behavior.careJournalCopy.statValues[3],
                        ...behavior.careJournalCopy.actionLabels,
                        behavior.careJournalCopy.mediaLabel,
                    ];

                    assert(
                        careCopy.length === 16 && careCopy.every((value) => value?.length > 0),
                        `${label}: the care journal localization surface is incomplete ${JSON.stringify(behavior.careJournalCopy)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishCareJournalCopy ??= careCopy;
                    } else {
                        assert(englishCareJournalCopy !== null, `${label}: English care copy baseline is missing.`);
                        assert(
                            careCopy.every((value, index) => value !== englishCareJournalCopy[index]),
                            `${label}: English care body fallback remains.`,
                        );
                    }

                    if (viewport.width <= 375) {
                        assert(behavior.careJournalLayout !== null, `${label}: care journal card geometry is missing.`);
                        assert(
                            Math.abs(
                                behavior.careJournalLayout.mediaWidth
                                - behavior.careJournalLayout.cardWidth
                            ) <= 2,
                            `${label}: care journal media is not full width.`,
                        );
                        assert(
                            behavior.careJournalLayout.mediaBottom
                                <= behavior.careJournalLayout.bodyTop + 1,
                            `${label}: care journal media and body are not stacked.`,
                        );
                    }
                }

                if (route.path === '/lost-found') {
                    const lostFoundCopy = [
                        behavior.lostFoundCopy.statsLabel,
                        ...behavior.lostFoundCopy.statsLabels,
                        ...behavior.lostFoundCopy.filterLabels,
                        ...behavior.lostFoundCopy.filterDefaults,
                        behavior.lostFoundCopy.searchPlaceholder,
                        behavior.lostFoundCopy.applyLabel,
                        behavior.lostFoundCopy.clearLabel,
                        behavior.lostFoundCopy.resultsTitle,
                        behavior.lostFoundCopy.resultsOrder,
                        behavior.lostFoundCopy.cardType,
                        behavior.lostFoundCopy.cardSpecies,
                        behavior.lostFoundCopy.cardStatus,
                        ...behavior.lostFoundCopy.cardCounts,
                        behavior.lostFoundCopy.areaLabel,
                        behavior.lostFoundCopy.lastSeenLabel,
                        behavior.lostFoundCopy.mapTitle,
                        behavior.lostFoundCopy.mapPrivacy,
                        behavior.lostFoundCopy.mapListLabel,
                        behavior.lostFoundCopy.guidanceTitle,
                        behavior.lostFoundCopy.guidanceCopy,
                    ];

                    assert(
                        lostFoundCopy.length === 34
                            && lostFoundCopy.every((value) => value?.length > 0),
                        `${label}: the lost-and-found localization surface is incomplete ${JSON.stringify(behavior.lostFoundCopy)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishLostFoundCopy ??= lostFoundCopy;
                    } else {
                        assert(englishLostFoundCopy !== null, `${label}: English lost-and-found baseline is missing.`);
                        assert(
                            lostFoundCopy.every((value, index) => value !== englishLostFoundCopy[index]),
                            `${label}: English lost-and-found body fallback remains.`,
                        );
                    }

                    if (viewport.width <= 375) {
                        assert(behavior.lostFoundLayout !== null, `${label}: search statistics geometry is missing.`);
                        assert(behavior.lostFoundLayout.itemCount === 5, `${label}: search statistics item count changed.`);
                        assert(
                            Math.abs(
                                behavior.lostFoundLayout.lastStatWidth
                                - behavior.lostFoundLayout.statsWidth
                            ) <= 2,
                            `${label}: the final search statistic leaves an empty mobile grid cell.`,
                        );
                    }
                }

                if (route.path === '/marketplace') {
                    const marketplaceCopy = [
                        behavior.marketplaceCopy.statsLabel,
                        ...behavior.marketplaceCopy.statsLabels,
                        ...behavior.marketplaceCopy.filterLabels,
                        ...behavior.marketplaceCopy.filterDefaults,
                        behavior.marketplaceCopy.searchPlaceholder,
                        behavior.marketplaceCopy.applyLabel,
                        behavior.marketplaceCopy.clearLabel,
                        behavior.marketplaceCopy.resultsTitle,
                        behavior.marketplaceCopy.resultsDescription,
                        behavior.marketplaceCopy.resultsPrivacy,
                        behavior.marketplaceCopy.cardType,
                        behavior.marketplaceCopy.cardCategory,
                        behavior.marketplaceCopy.cardSpecies,
                        behavior.marketplaceCopy.cardLocationLabel,
                        behavior.marketplaceCopy.cardAvailabilityLabel,
                        behavior.marketplaceCopy.cardSellerType,
                        behavior.marketplaceCopy.cardSaveLabel,
                        behavior.marketplaceCopy.cardViewLabel,
                    ];

                    assert(
                        marketplaceCopy.length === 41
                            && marketplaceCopy.every((value) => value?.length > 0),
                        `${label}: the marketplace localization surface is incomplete ${JSON.stringify(behavior.marketplaceCopy)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishMarketplaceCopy ??= marketplaceCopy;
                    } else {
                        assert(englishMarketplaceCopy !== null, `${label}: English marketplace baseline is missing.`);
                        assert(
                            marketplaceCopy.every((value, index) => value !== englishMarketplaceCopy[index]),
                            `${label}: English marketplace body fallback remains. Current ${JSON.stringify(marketplaceCopy)}; English ${JSON.stringify(englishMarketplaceCopy)}.`,
                        );
                    }
                }

                if (route.path === '/experts') {
                    const expertCopy = [
                        behavior.expertCopy.statsLabel,
                        ...behavior.expertCopy.statsLabels,
                        ...behavior.expertCopy.filterLabels,
                        ...behavior.expertCopy.filterDefaults,
                        behavior.expertCopy.searchPlaceholder,
                        behavior.expertCopy.applyLabel,
                        behavior.expertCopy.clearLabel,
                        behavior.expertCopy.resultsTitle,
                        behavior.expertCopy.resultsDescription,
                        behavior.expertCopy.urgentLabel,
                        behavior.expertCopy.cardBadge,
                        behavior.expertCopy.cardType,
                        behavior.expertCopy.cardSpecializations,
                        ...behavior.expertCopy.cardFactLabels,
                        behavior.expertCopy.cardViewLabel,
                        behavior.expertCopy.cardBookLabel,
                    ];

                    assert(
                        expertCopy.length === 37
                            && expertCopy.every((value) => value?.length > 0),
                        `${label}: the expert localization surface is incomplete ${JSON.stringify(behavior.expertCopy)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishExpertCopy ??= expertCopy;
                    } else {
                        assert(englishExpertCopy !== null, `${label}: English expert baseline is missing.`);
                        assert(
                            expertCopy.every((value, index) => value !== englishExpertCopy[index]),
                            `${label}: English expert body fallback remains. Current ${JSON.stringify(expertCopy)}; English ${JSON.stringify(englishExpertCopy)}.`,
                        );
                    }
                }

                if (route.path === '/groups') {
                    const groupCopy = [
                        behavior.groupCopy.summaryLabel,
                        ...behavior.groupCopy.summaryLabels,
                        ...behavior.groupCopy.summaryDetails,
                        behavior.groupCopy.toolbarLabel,
                        behavior.groupCopy.filterGroupLabel,
                        ...behavior.groupCopy.filterLabels,
                        behavior.groupCopy.sortLabel,
                        ...behavior.groupCopy.sortOptions,
                        behavior.groupCopy.searchLabel,
                        behavior.groupCopy.searchPlaceholder,
                        behavior.groupCopy.resultsTitle,
                        behavior.groupCopy.cardCategory,
                        behavior.groupCopy.cardPrivacy,
                        behavior.groupCopy.cardReasonLabel,
                        behavior.groupCopy.cardReason,
                        behavior.groupCopy.cardDescription,
                        ...behavior.groupCopy.cardTags,
                        ...behavior.groupCopy.cardMetricLabels,
                    ];

                    assert(
                        groupCopy.length === 32
                            && groupCopy.every((value) => value?.length > 0),
                        `${label}: the group localization surface is incomplete ${JSON.stringify(behavior.groupCopy)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishGroupCopy ??= groupCopy;
                    } else {
                        assert(englishGroupCopy !== null, `${label}: English group baseline is missing.`);
                        assert(
                            groupCopy.every((value, index) => value !== englishGroupCopy[index]),
                            `${label}: English group body fallback remains. Current ${JSON.stringify(groupCopy)}; English ${JSON.stringify(englishGroupCopy)}.`,
                        );
                    }
                }

                if (route.path === '/neighbors') {
                    const neighborCopy = [
                        behavior.neighborCopy.actionLabel,
                        behavior.neighborCopy.summaryLabel,
                        ...behavior.neighborCopy.summaryLabels,
                        ...behavior.neighborCopy.summaryValues,
                        ...behavior.neighborCopy.summaryDetails.slice(1),
                        behavior.neighborCopy.toolbarLabel,
                        behavior.neighborCopy.filterGroupLabel,
                        ...behavior.neighborCopy.filterLabels,
                        behavior.neighborCopy.sortLabel,
                        ...behavior.neighborCopy.sortOptions,
                        behavior.neighborCopy.searchLabel,
                        behavior.neighborCopy.searchPlaceholder,
                        behavior.neighborCopy.resultsTitle,
                        behavior.neighborCopy.cardCategory,
                        behavior.neighborCopy.cardDistance,
                        behavior.neighborCopy.cardPet,
                        behavior.neighborCopy.cardStatus,
                        ...behavior.neighborCopy.cardTags,
                        behavior.neighborCopy.cardFollowLabel,
                        behavior.neighborCopy.cardImageAlt,
                        ...behavior.neighborCopy.remainingCardCategories,
                        ...behavior.neighborCopy.remainingCardDistances,
                        ...behavior.neighborCopy.remainingCardStatuses,
                        ...behavior.neighborCopy.cardPets,
                    ];

                    assert(
                        neighborCopy.length === 43
                            && neighborCopy.every((value) => value?.length > 0),
                        `${label}: the neighbor localization surface is incomplete ${JSON.stringify(behavior.neighborCopy)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishNeighborCopy ??= neighborCopy;
                    } else {
                        assert(englishNeighborCopy !== null, `${label}: English neighbor baseline is missing.`);
                        assert(
                            neighborCopy.every((value, index) => value !== englishNeighborCopy[index]),
                            `${label}: English neighbor body fallback remains. Current ${JSON.stringify(neighborCopy)}; English ${JSON.stringify(englishNeighborCopy)}.`,
                        );
                    }
                }

                if (route.path === '/places') {
                    const placeCopy = [
                        behavior.placeCopy.actionLabel,
                        behavior.placeCopy.summaryLabel,
                        ...behavior.placeCopy.summaryLabels,
                        ...behavior.placeCopy.summaryDetails,
                        ...behavior.placeCopy.localizedSummaryValues,
                        behavior.placeCopy.searchEyebrow,
                        behavior.placeCopy.emergencyAction,
                        ...behavior.placeCopy.primaryLabels,
                        behavior.placeCopy.searchPlaceholder,
                        behavior.placeCopy.noPetOption,
                        behavior.placeCopy.searchAction,
                        behavior.placeCopy.categoriesLabel,
                        ...behavior.placeCopy.categoryLabels,
                        behavior.placeCopy.moreFilters,
                        ...behavior.placeCopy.filterLabels,
                        ...behavior.placeCopy.filterDefaults,
                        behavior.placeCopy.openNow,
                        ...behavior.placeCopy.filterActions,
                        behavior.placeCopy.locationTitle,
                        behavior.placeCopy.locationDescription,
                        behavior.placeCopy.locationAction,
                        behavior.placeCopy.controlsLabel,
                        ...behavior.placeCopy.modeLabels,
                        behavior.placeCopy.viewLabel,
                        ...behavior.placeCopy.viewLabels,
                        behavior.placeCopy.sortLabel,
                        ...behavior.placeCopy.sortOptions,
                        behavior.placeCopy.sortAction,
                        behavior.placeCopy.mapEyebrow,
                        behavior.placeCopy.mapTitle,
                        behavior.placeCopy.mapControlsLabel,
                        ...behavior.placeCopy.mapControlLabels,
                        behavior.placeCopy.oldTown,
                        behavior.placeCopy.mapAlternative,
                        behavior.placeCopy.resultsEyebrow,
                        behavior.placeCopy.resultsHeading,
                        behavior.placeCopy.layerLabel,
                        ...behavior.placeCopy.layerLabels,
                        behavior.placeCopy.cardHighlights,
                        ...behavior.placeCopy.cardActions,
                        behavior.placeCopy.comparisonEyebrow,
                        behavior.placeCopy.comparisonTitle,
                        ...behavior.placeCopy.comparisonLabels,
                        behavior.placeCopy.comparisonAccess,
                    ];

                    assert(
                        placeCopy.length === 113
                            && placeCopy.every((value) => value?.length > 0),
                        label + ': the place-directory localization surface is incomplete '
                            + JSON.stringify(behavior.placeCopy) + '.',
                    );
                    assert(
                        behavior.placeCopy.categoryLabels.length === 10
                            && behavior.placeCopy.modeLabels.length === 6
                            && behavior.placeCopy.viewLabels.length === 5
                            && behavior.placeCopy.layerLabels.length === 6,
                        label + ': place-directory stable option counts changed '
                            + JSON.stringify(behavior.placeCopy) + '.',
                    );
                    assert(
                        behavior.placeLayout.clippedLabels.length === 0,
                        label + ': place-directory labels are clipped '
                            + JSON.stringify(behavior.placeLayout.clippedLabels) + '.',
                    );
                    assert(
                        behavior.placeLayout.smallTargets.length === 0,
                        label + ': place-directory controls below 44px '
                            + JSON.stringify(behavior.placeLayout.smallTargets) + '.',
                    );

                    if (viewport.locale === 'en') {
                        englishPlaceCopy ??= placeCopy;
                    } else {
                        assert(englishPlaceCopy !== null, label + ': English place-directory baseline is missing.');
                        assert(
                            placeCopy.every((value, index) => value !== englishPlaceCopy[index]),
                            label + ': English place-directory system fallback remains. Current '
                                + JSON.stringify(placeCopy) + '; English '
                                + JSON.stringify(englishPlaceCopy) + '.',
                        );
                    }
                }

                if (route.path === '/messages') {
                    const messagingCopy = [
                        behavior.messagingCopy.actionLabel,
                        behavior.messagingCopy.metaLabel,
                        behavior.messagingCopy.foldersLabel,
                        ...behavior.messagingCopy.folderLabels,
                        behavior.messagingCopy.inboxLabel,
                        behavior.messagingCopy.searchLabel,
                        behavior.messagingCopy.searchPlaceholder,
                        behavior.messagingCopy.searchAction,
                        behavior.messagingCopy.conversationsLabel,
                        behavior.messagingCopy.shownCount,
                        behavior.messagingCopy.summary,
                        ...behavior.messagingCopy.conversationTypes,
                        ...behavior.messagingCopy.relativeTimes,
                        behavior.messagingCopy.threadBack,
                        ...behavior.messagingCopy.threadActionTitles,
                        ...behavior.messagingCopy.threadActionLabels,
                        behavior.messagingCopy.messageListLabel,
                        behavior.messagingCopy.messageDate,
                        behavior.messagingCopy.composerLabel,
                        behavior.messagingCopy.composerReply,
                        behavior.messagingCopy.composerCancelReply,
                        behavior.messagingCopy.composerDraftSaving,
                        behavior.messagingCopy.composerDraftSaved,
                        behavior.messagingCopy.composerMessageType,
                        ...behavior.messagingCopy.composerToolTitles,
                        ...behavior.messagingCopy.composerToolActionLabels,
                        behavior.messagingCopy.composerRecipient,
                        behavior.messagingCopy.composerPlaceholder,
                        behavior.messagingCopy.composerQuiet,
                        behavior.messagingCopy.composerDraftStatus,
                        behavior.messagingCopy.composerSend,
                        behavior.messagingCopy.composerSchedule,
                        behavior.messagingCopy.composerSendAt,
                        behavior.messagingCopy.composerScheduleHelp,
                        behavior.messagingCopy.composerPrivacy,
                        ...behavior.messagingCopy.structuredMessageTypes,
                        ...behavior.messagingCopy.messageStatuses,
                        behavior.messagingCopy.messageActionsLabel,
                        ...behavior.messagingCopy.messageActionLabels,
                        behavior.messagingCopy.audioActionLabel,
                        behavior.messagingCopy.contextLabel,
                        behavior.messagingCopy.contextPurpose,
                        behavior.messagingCopy.contextPrivacy,
                        behavior.messagingCopy.contextIdentityNote,
                        behavior.messagingCopy.contextSearchLabel,
                        behavior.messagingCopy.contextSearchPlaceholder,
                        behavior.messagingCopy.contextControlsLabel,
                        ...behavior.messagingCopy.contextControlLabels,
                        behavior.messagingCopy.contextMembersTitle,
                        behavior.messagingCopy.contextSharedTitle,
                        ...behavior.messagingCopy.contextSharedLabels,
                        ...behavior.messagingCopy.contextSharedValues,
                        behavior.messagingCopy.contextSafetyTitle,
                        ...behavior.messagingCopy.contextSafetyTitles,
                        ...behavior.messagingCopy.contextSafetyDescriptions,
                        ...behavior.messagingCopy.contextSafetyActionLabels,
                        behavior.messagingCopy.contextBoundaryTitle,
                        ...behavior.messagingCopy.contextBoundaryLabels,
                        ...behavior.messagingCopy.contextBoundaryValues,
                    ];

                    assert(
                        messagingCopy.length === 127
                            && messagingCopy.every((value) => value?.length > 0),
                        `${label}: the messaging localization surface is incomplete ${JSON.stringify(behavior.messagingCopy)}.`,
                    );
                    assert(
                        JSON.stringify(behavior.messagingCopy.messageStatusCodes)
                            === JSON.stringify(['delivered', 'read', 'delivered', 'delivered']),
                        `${label}: messaging delivery codes drifted ${JSON.stringify(behavior.messagingCopy.messageStatusCodes)}.`,
                    );
                    assert(
                        JSON.stringify(behavior.messagingCopy.contextActionCodes)
                            === JSON.stringify([
                                'pin-conversation',
                                'mute-conversation',
                                'archive-conversation',
                                'mark-conversation-unread',
                                'restrict-conversation',
                                'block-conversation',
                                'export-conversation',
                            ]),
                        `${label}: messaging context action codes drifted ${JSON.stringify(behavior.messagingCopy.contextActionCodes)}.`,
                    );
                    assert(
                        JSON.stringify(behavior.messagingCopy.contextActionIcons)
                            === JSON.stringify(['pin', 'bell-off', 'archive', 'mail', 'shield-minus', 'ban', 'download']),
                        `${label}: messaging context icons drifted ${JSON.stringify(behavior.messagingCopy.contextActionIcons)}.`,
                    );
                    assert(
                        behavior.messagingLayout.clippedFolderLabels.length === 0,
                        `${label}: messaging folder labels are clipped ${JSON.stringify(behavior.messagingLayout.clippedFolderLabels)}.`,
                    );
                    assert(
                        behavior.messagingLayout.smallTargets.length === 0,
                        `${label}: messaging controls below 44px ${JSON.stringify(behavior.messagingLayout.smallTargets)}.`,
                    );

                    if (viewport.locale === 'en') {
                        englishMessagingCopy ??= messagingCopy;
                    } else {
                        assert(englishMessagingCopy !== null, `${label}: English messaging baseline is missing.`);
                        assert(
                            messagingCopy.every((value, index) => value !== englishMessagingCopy[index]),
                            `${label}: English messaging system fallback remains. Current ${JSON.stringify(messagingCopy)}; English ${JSON.stringify(englishMessagingCopy)}.`,
                        );
                    }
                }

                assert(behavior.documentLanguage === viewport.locale, `${label}: wrong document language.`);
                assert(behavior.headerCount === 1, `${label}: expected one canonical page header.`);
                assert(behavior.h1Count === 1, `${label}: expected one main h1.`);
                assert(behavior.legacyHeaderCount === 0, `${label}: legacy header family remains.`);
                assert(behavior.headingId !== null, `${label}: heading id is missing.`);
                assert(behavior.labelledBy === behavior.headingId, `${label}: aria-labelledby is not synchronized.`);
                assert(behavior.headingText?.length > 0, `${label}: heading text is empty.`);
                assert(behavior.documentTitle.length > 0, `${label}: document title is empty.`);
                assert(behavior.titleFontFamily === canonicalTitleFont, `${label}: title font family drifted.`);
                assert(
                    Math.abs(behavior.titleFontSize - expectedTitleSize) <= 0.2,
                    `${label}: title size is ${behavior.titleFontSize}px; expected ${expectedTitleSize}px.`,
                );
                assert(
                    Math.abs(behavior.titleLineHeight - expectedLineHeight) <= 0.3,
                    `${label}: line height is ${behavior.titleLineHeight}px; expected ${expectedLineHeight}px.`,
                );
                assert(behavior.titleFontWeight === '600', `${label}: title weight is ${behavior.titleFontWeight}.`);
                assert(behavior.clippedRegions.length === 0, `${label}: clipped header regions ${JSON.stringify(behavior.clippedRegions)}.`);
                assert(! behavior.actionsOverlapCopy, `${label}: header actions overlap copy.`);
                assert(behavior.smallTargets.length === 0, `${label}: header controls below 44px ${JSON.stringify(behavior.smallTargets)}.`);
                assert(behavior.reducedMotion, `${label}: reduced-motion emulation is inactive.`);
                assert(behavior.forcedColors === Boolean(viewport.forcedColors), `${label}: forced-colors state is wrong.`);
                assert(behavior.devicePixelRatio === (viewport.zoom ?? 1), `${label}: 200% zoom emulation is inactive.`);
                assert(behavior.innerWidth === viewport.width, `${label}: effective viewport width is wrong.`);
                assert(behavior.screenWidth === (viewport.screenWidth ?? viewport.width), `${label}: physical screen width is wrong.`);
                assert(behavior.focusVisible, `${label}: focused header action has no visible focus treatment.`);
                assert(behavior.rawTranslationKeys.length === 0, `${label}: raw translation keys ${behavior.rawTranslationKeys.join(', ')}.`);

                pageIdentityAudits[label] = { ...pageAudit, ...behavior };

                if (route.path === '/messages') {
                    const callLoaded = client.once('Page.loadEventFired', sessionId);
                    await evaluate(client, sessionId, `(() => {
                        const form = document.querySelector(
                            'form:has(input[name="action"][value="start-message-call"])'
                                + ':has(input[name="call_type"][value="video"])'
                        );
                        form?.requestSubmit();

                        return Boolean(form);
                    })()`);
                    await callLoaded;
                    await delay(350);

                    const callAudit = await evaluate(client, sessionId, `(() => {
                        const stage = document.querySelector('[data-call-stage]');
                        const dialog = stage?.querySelector('[role="dialog"]');
                        const visible = (element) => {
                            const style = getComputedStyle(element);
                            const box = element.getBoundingClientRect();

                            return style.display !== 'none' && style.visibility !== 'hidden'
                                && box.width > 0 && box.height > 0;
                        };
                        const targets = [...(stage?.querySelectorAll('button') ?? [])]
                            .filter(visible)
                            .map((element) => ({
                                label: element.getAttribute('aria-label')
                                    || element.getAttribute('title')
                                    || element.textContent.trim(),
                                width: Math.round(element.getBoundingClientRect().width),
                                height: Math.round(element.getBoundingClientRect().height),
                            }))
                            .filter((target) => target.width < 44 || target.height < 44);
                        const dialogBox = dialog?.getBoundingClientRect();
                        const clippedControls = [...(stage?.querySelectorAll(
                            '.messaging-call-stage__controls button span'
                        ) ?? [])].filter(
                            (element) => element.scrollWidth > element.clientWidth + 1
                                || element.scrollHeight > element.clientHeight + 1,
                        ).map((element) => element.textContent.trim());
                        const previousScrollTop = dialog?.scrollTop ?? 0;
                        if (dialog) {
                            dialog.scrollTop = dialog.scrollHeight;
                        }
                        const footerBox = stage?.querySelector('footer')?.getBoundingClientRect();
                        const footerReachable = Boolean(
                            dialogBox
                            && footerBox
                            && footerBox.top >= dialogBox.top - 1
                            && footerBox.bottom <= dialogBox.bottom + 1
                        );
                        if (dialog) {
                            dialog.scrollTop = previousScrollTop;
                        }

                        return {
                            visible: Boolean(stage && ! stage.hidden && visible(stage) && visible(dialog)),
                            typeCode: stage?.dataset.callTypeCode ?? null,
                            statusCode: stage?.dataset.callStatusCode ?? null,
                            qualityCode: stage?.dataset.callQualityCode ?? null,
                            copy: {
                                statusLine: stage?.querySelector('[data-call-status-line]')?.textContent.trim() ?? null,
                                quality: stage?.querySelector('[data-call-quality]')?.textContent.trim() ?? null,
                                deviceStatus: stage?.querySelector('[data-call-device-status]')?.textContent.trim() ?? null,
                                recording: stage?.querySelector('.messaging-call-stage__recording')?.textContent.trim() ?? null,
                                checks: [...(stage?.querySelectorAll('.messaging-call-stage__checks > *') ?? [])]
                                    .map((element) => element.textContent.trim()),
                                consentTitle: stage?.querySelector('.messaging-call-stage__notice strong')
                                    ?.textContent.trim() ?? null,
                                transport: stage?.querySelector('[data-call-boundary-transport]')
                                    ?.textContent.trim() ?? null,
                                recordingBoundary: stage?.querySelector('[data-call-boundary-recording]')
                                    ?.textContent.trim() ?? null,
                                emergency: stage?.querySelector('[data-call-boundary-emergency]')
                                    ?.textContent.trim() ?? null,
                                controlsLabel: stage?.querySelector('.messaging-call-stage__controls')
                                    ?.getAttribute('aria-label') ?? null,
                                controlLabels: [...(stage?.querySelectorAll(
                                    '.messaging-call-stage__controls button span'
                                ) ?? [])].map((element) => element.textContent.trim()),
                                close: stage?.querySelector('form[data-call-end] button')
                                    ?.getAttribute('title') ?? null,
                                join: stage?.querySelector('footer .action--primary span')
                                    ?.textContent.trim() ?? null,
                                deviceUnavailable: stage?.dataset.deviceUnavailable ?? null,
                                cameraActive: stage?.dataset.cameraActive ?? null,
                                microphoneActive: stage?.dataset.microphoneActive ?? null,
                                deviceDenied: stage?.dataset.deviceDenied ?? null,
                            },
                            controlCodes: [...(stage?.querySelectorAll(
                                '.messaging-call-stage__controls input[name="call_control"]'
                            ) ?? [])].map((element) => element.value),
                            controlIcons: [...(stage?.querySelectorAll(
                                '.messaging-call-stage__controls [data-ui-icon]'
                            ) ?? [])].map((element) => element.getAttribute('data-ui-icon')),
                            firstFocusIsClose: document.activeElement?.matches(
                                '[data-call-stage] form[data-call-end] button'
                            ) ?? false,
                            smallTargets: targets,
                            clippedControls,
                            footerReachable,
                            dialogWithinViewport: Boolean(
                                dialogBox
                                && dialogBox.left >= -1
                                && dialogBox.top >= -1
                                && dialogBox.right <= innerWidth + 1
                                && dialogBox.bottom <= innerHeight + 1
                            ),
                            dialogOverflow: dialog
                                ? Math.max(0, dialog.scrollWidth - dialog.clientWidth)
                                : null,
                        };
                    })()`);
                    const callCopy = [
                        callAudit.copy.statusLine,
                        callAudit.copy.quality,
                        callAudit.copy.deviceStatus,
                        callAudit.copy.recording,
                        ...callAudit.copy.checks,
                        callAudit.copy.consentTitle,
                        callAudit.copy.transport,
                        callAudit.copy.recordingBoundary,
                        callAudit.copy.emergency,
                        callAudit.copy.controlsLabel,
                        ...callAudit.copy.controlLabels,
                        callAudit.copy.close,
                        callAudit.copy.join,
                        callAudit.copy.deviceUnavailable,
                        callAudit.copy.cameraActive,
                        callAudit.copy.microphoneActive,
                        callAudit.copy.deviceDenied,
                    ];

                    assert(callAudit.visible, `${label}: call stage is not visible after submitting video preflight.`);
                    assert(
                        callCopy.length === 22 && callCopy.every((value) => value?.length > 0),
                        `${label}: call-stage localization surface is incomplete ${JSON.stringify(callAudit.copy)}.`,
                    );
                    assert(
                        JSON.stringify([callAudit.typeCode, callAudit.statusCode, callAudit.qualityCode])
                            === JSON.stringify(['video', 'preflight', 'checking']),
                        `${label}: call-stage state codes drifted.`,
                    );
                    assert(
                        JSON.stringify(callAudit.controlCodes)
                            === JSON.stringify(['microphone', 'camera', 'captions', 'audio-only']),
                        `${label}: call-stage control codes drifted ${JSON.stringify(callAudit.controlCodes)}.`,
                    );
                    assert(
                        JSON.stringify(callAudit.controlIcons)
                            === JSON.stringify(['mic', 'video', 'captions', 'phone']),
                        `${label}: call-stage icons drifted ${JSON.stringify(callAudit.controlIcons)}.`,
                    );
                    assert(callAudit.firstFocusIsClose, `${label}: call dialog did not focus its first close control.`);
                    assert(callAudit.smallTargets.length === 0, `${label}: call controls below 44px ${JSON.stringify(callAudit.smallTargets)}.`);
                    assert(callAudit.clippedControls.length === 0, `${label}: call control labels are clipped ${JSON.stringify(callAudit.clippedControls)}.`);
                    assert(callAudit.footerReachable, `${label}: call footer is not reachable by dialog scrolling.`);
                    assert(callAudit.dialogWithinViewport, `${label}: call dialog escapes the viewport.`);
                    assert(callAudit.dialogOverflow <= 1, `${label}: call dialog has horizontal overflow.`);

                    if (viewport.locale === 'en') {
                        englishCallStageCopy ??= callCopy;
                        assert(
                            callCopy.every((value, index) => value === englishCallStageCopy[index]),
                            `${label}: English call-stage copy changed across viewports.`,
                        );
                    } else {
                        assert(englishCallStageCopy !== null, `${label}: English call-stage baseline is missing.`);
                        assert(
                            callCopy.every((value, index) => value !== englishCallStageCopy[index]),
                            `${label}: English call-stage fallback remains. Current ${JSON.stringify(callCopy)}; English ${JSON.stringify(englishCallStageCopy)}.`,
                        );
                    }

                    pageIdentityAudits[label].callStage = callAudit;

                    if ([375, 1440].includes(viewport.width)) {
                        const callScreenshotPath = join(
                            outputDirectory,
                            `page-identity-messages-call-${viewport.width}.png`,
                        );
                        const callScreenshotData = await client.send('Page.captureScreenshot', {
                            format: 'png',
                            captureBeyondViewport: true,
                        }, sessionId);
                        await writeFile(callScreenshotPath, Buffer.from(callScreenshotData.data, 'base64'));
                        pageIdentityScreenshots.push(callScreenshotPath);
                    }

                    const callEnded = client.once('Page.loadEventFired', sessionId);
                    await evaluate(client, sessionId, `(() => {
                        const form = document.querySelector('[data-call-stage] form[data-call-end]');
                        form?.requestSubmit();

                        return Boolean(form);
                    })()`);
                    await callEnded;
                    await delay(350);

                    await navigate(client, sessionId, `${baseUrl}/messages/ari/details`);
                    const detailsPageAudit = await evaluate(client, sessionId, pageAuditExpression);
                    assertPageAudit(detailsPageAudit, `${label} details`);
                    const detailsAudit = await evaluate(client, sessionId, `(() => {
                        const center = document.querySelector('[data-messaging-center]');
                        const context = document.querySelector('[data-messaging-context]');
                        const inbox = document.querySelector('[data-messaging-inbox]');
                        const thread = document.querySelector('.messaging-thread');
                        const folders = document.querySelector('[data-messaging-folders]');
                        const back = context?.querySelector('.messaging-context__back a');
                        const visible = (element) => {
                            const style = getComputedStyle(element);
                            const box = element.getBoundingClientRect();

                            return style.display !== 'none' && style.visibility !== 'hidden'
                                && box.width > 0 && box.height > 0;
                        };
                        const targets = [...(context?.querySelectorAll(
                            'a, button, input:not([type="hidden"]), select, summary, textarea'
                        ) ?? [])].filter(visible).map((element) => {
                            const target = element.matches('input[type="checkbox"], input[type="radio"]')
                                ? element.closest('label') ?? element
                                : element;

                            return {
                                label: element.getAttribute('aria-label')
                                    || element.textContent.trim()
                                    || element.getAttribute('placeholder'),
                                width: Math.round(target.getBoundingClientRect().width),
                                height: Math.round(target.getBoundingClientRect().height),
                            };
                        }).filter((target) => target.width < 44 || target.height < 44);

                        return {
                            path: location.pathname,
                            detailsMode: center?.hasAttribute('data-details-open') ?? false,
                            contextVisible: context ? visible(context) : false,
                            inboxVisible: inbox ? visible(inbox) : false,
                            threadVisible: thread ? visible(thread) : false,
                            foldersVisible: folders ? visible(folders) : false,
                            backVisible: back ? visible(back) : false,
                            callStageHidden: document.querySelector('[data-call-stage]')?.hidden ?? false,
                            copy: {
                                label: context?.getAttribute('aria-label') ?? null,
                                back: back?.textContent.trim() ?? null,
                                identityNote: context?.querySelector('[data-messaging-context-identity-note]')
                                    ?.textContent.trim() ?? null,
                            },
                            smallTargets: targets,
                            overflow: document.documentElement.scrollWidth
                                - document.documentElement.clientWidth,
                        };
                    })()`);
                    const detailsCopy = [
                        detailsAudit.copy.label,
                        detailsAudit.copy.back,
                        detailsAudit.copy.identityNote,
                    ];
                    const compactDetails = viewport.width < 1200;

                    assert(detailsAudit.path === '/messages/ari/details', `${label}: details route path drifted.`);
                    assert(detailsAudit.detailsMode, `${label}: details route marker is missing.`);
                    assert(detailsAudit.contextVisible, `${label}: conversation details are hidden.`);
                    assert(detailsAudit.callStageHidden, `${label}: inactive call stage leaked into details.`);
                    assert(
                        detailsAudit.inboxVisible === ! compactDetails
                            && detailsAudit.threadVisible === ! compactDetails
                            && detailsAudit.foldersVisible === ! compactDetails,
                        `${label}: details responsive visibility is wrong ${JSON.stringify(detailsAudit)}.`,
                    );
                    assert(
                        detailsAudit.backVisible === compactDetails,
                        `${label}: details back control visibility is wrong.`,
                    );
                    assert(
                        detailsCopy.length === 3 && detailsCopy.every((value) => value?.length > 0),
                        `${label}: details localization surface is incomplete ${JSON.stringify(detailsAudit.copy)}.`,
                    );
                    assert(detailsAudit.smallTargets.length === 0, `${label}: details controls below 44px ${JSON.stringify(detailsAudit.smallTargets)}.`);
                    assert(detailsAudit.overflow <= 1, `${label}: details route overflows horizontally.`);

                    if (viewport.locale === 'en') {
                        englishMessageDetailsCopy ??= detailsCopy;
                    } else {
                        assert(englishMessageDetailsCopy !== null, `${label}: English details baseline is missing.`);
                        assert(
                            detailsCopy.every((value, index) => value !== englishMessageDetailsCopy[index]),
                            `${label}: English details fallback remains.`,
                        );
                    }

                    pageIdentityAudits[label].messageDetails = {
                        ...detailsPageAudit,
                        ...detailsAudit,
                    };

                    if ([375, 1440].includes(viewport.width)) {
                        const detailsScreenshotPath = join(
                            outputDirectory,
                            `page-identity-messages-details-${viewport.width}.png`,
                        );
                        const detailsScreenshotData = await client.send('Page.captureScreenshot', {
                            format: 'png',
                            captureBeyondViewport: true,
                        }, sessionId);
                        await writeFile(detailsScreenshotPath, Buffer.from(detailsScreenshotData.data, 'base64'));
                        pageIdentityScreenshots.push(detailsScreenshotPath);
                    }

                    await navigate(client, sessionId, routeUrl);
                }

                if ([375, 1440].includes(viewport.width)) {
                    const screenshotPath = join(
                        outputDirectory,
                        `page-identity-${route.slug}-${viewport.width}.png`,
                    );
                    const screenshotData = await client.send('Page.captureScreenshot', {
                        format: 'png',
                        captureBeyondViewport: true,
                    }, sessionId);
                    await writeFile(screenshotPath, Buffer.from(screenshotData.data, 'base64'));
                    pageIdentityScreenshots.push(screenshotPath);
                }
            }

            const shareLabel = `${viewport.label} share`;
            await navigate(client, sessionId, `${baseUrl}/share/apartment-pets`);
            const sharePageAudit = await evaluate(client, sessionId, pageAuditExpression);
            assertPageAudit(sharePageAudit, shareLabel);
            const shareAudit = await evaluate(client, sessionId, `(() => {
                const page = document.querySelector('[data-share-page]');
                const channelsPanel = page?.querySelector('[data-share-channels-panel]');
                const neighborsPanel = page?.querySelector('[data-share-neighbors-panel]');
                const detailsPanel = page?.querySelector('[data-share-details]');
                const privacy = page?.querySelector('[data-share-privacy]');
                const visible = (element) => {
                    const style = getComputedStyle(element);
                    const box = element.getBoundingClientRect();

                    return style.display !== 'none' && style.visibility !== 'hidden'
                        && box.width > 0 && box.height > 0;
                };
                const targets = [...(page?.querySelectorAll(
                    'a, button, input:not([type="hidden"]), select, textarea, [role="button"]'
                ) ?? [])].filter(visible).map((element) => ({
                    label: element.getAttribute('aria-label')
                        || element.textContent.trim().slice(0, 80)
                        || element.getAttribute('name'),
                    width: Math.round(element.getBoundingClientRect().width),
                    height: Math.round(element.getBoundingClientRect().height),
                })).filter((target) => target.width < 44 || target.height < 44);
                const clippedRegions = [
                    page,
                    page?.querySelector('.context-hero'),
                    page?.querySelector('.context-hero__copy'),
                    channelsPanel,
                    neighborsPanel,
                    detailsPanel,
                    privacy,
                    ...page?.querySelectorAll('.share-channel, .share-recipient') ?? [],
                ].filter(Boolean).filter(
                    (element) => element.scrollWidth > element.clientWidth + 1,
                ).map((element) => element.className || element.dataset.section || element.tagName);

                return {
                    path: location.pathname,
                    pageVisible: page ? visible(page) : false,
                    copy: {
                        documentTitle: document.title,
                        back: page?.querySelector(':scope > .text-link span')?.textContent.trim() ?? null,
                        heroEyebrow: page?.querySelector('.context-hero__eyebrow')?.textContent.trim() ?? null,
                        openOriginal: page?.querySelector('[data-share-open-original] span')?.textContent.trim() ?? null,
                        channels: {
                            eyebrow: channelsPanel?.querySelector('.section-heading__eyebrow')?.textContent.trim() ?? null,
                            title: channelsPanel?.querySelector('.section-heading__title')?.textContent.trim() ?? null,
                            count: channelsPanel?.querySelector('.panel-heading__meta')?.textContent.trim() ?? null,
                            titles: [...(channelsPanel?.querySelectorAll('.share-channel__title') ?? [])]
                                .map((element) => element.textContent.trim()),
                            descriptions: [...(channelsPanel?.querySelectorAll('.share-channel__description') ?? [])]
                                .map((element) => element.textContent.trim()),
                            actions: [...(channelsPanel?.querySelectorAll('[data-share-channel-action] span') ?? [])]
                                .map((element) => element.textContent.trim()),
                        },
                        neighbors: {
                            eyebrow: neighborsPanel?.querySelector('.section-heading__eyebrow')?.textContent.trim() ?? null,
                            title: neighborsPanel?.querySelector('.section-heading__title')?.textContent.trim() ?? null,
                            count: neighborsPanel?.querySelector('.panel-heading__meta')?.textContent.trim() ?? null,
                            actions: [...(neighborsPanel?.querySelectorAll('[data-share-recipient-action] span') ?? [])]
                                .map((element) => element.textContent.trim()),
                        },
                        details: {
                            title: detailsPanel?.querySelector('.panel-heading__title')?.textContent.trim() ?? null,
                            labels: [...(detailsPanel?.querySelectorAll('.definition-list__term') ?? [])]
                                .map((element) => element.textContent.trim()),
                            targetType: detailsPanel?.querySelector('.definition-list__value')?.textContent.trim() ?? null,
                        },
                        privacy: {
                            title: privacy?.querySelector('.notice__title')?.textContent.trim() ?? null,
                            description: privacy?.querySelector('.notice__description')?.textContent.trim() ?? null,
                        },
                    },
                    channelCodes: [...(page?.querySelectorAll('[data-share-channel]') ?? [])]
                        .map((element) => element.dataset.shareChannel),
                    channelIcons: [...(channelsPanel?.querySelectorAll('[data-ui-icon]') ?? [])]
                        .map((element) => element.getAttribute('data-ui-icon')),
                    recipientIcons: [...(neighborsPanel?.querySelectorAll('[data-share-recipient-action] [data-ui-icon]') ?? [])]
                        .map((element) => element.getAttribute('data-ui-icon')),
                    pageIcons: [...(page?.querySelectorAll('[data-ui-icon]') ?? [])]
                        .map((element) => element.getAttribute('data-ui-icon')),
                    smallTargets: targets,
                    clippedRegions,
                    overflow: document.documentElement.scrollWidth
                        - document.documentElement.clientWidth,
                    rawTranslationKeys: document.body.innerText.match(
                        /\bsharing\.[a-z0-9_.-]+/gi
                    ) ?? [],
                };
            })()`);
            const shareCopy = [
                shareAudit.copy.documentTitle,
                shareAudit.copy.back,
                shareAudit.copy.heroEyebrow,
                shareAudit.copy.openOriginal,
                shareAudit.copy.channels.eyebrow,
                shareAudit.copy.channels.title,
                shareAudit.copy.channels.count,
                ...shareAudit.copy.channels.titles,
                ...shareAudit.copy.channels.descriptions,
                ...shareAudit.copy.channels.actions,
                shareAudit.copy.neighbors.eyebrow,
                shareAudit.copy.neighbors.title,
                shareAudit.copy.neighbors.count,
                ...shareAudit.copy.neighbors.actions,
                shareAudit.copy.details.title,
                ...shareAudit.copy.details.labels,
                shareAudit.copy.details.targetType,
                shareAudit.copy.privacy.title,
                shareAudit.copy.privacy.description,
            ];
            const expectedChannelIcons = ['mail', 'arrow-up-right', 'message-square-text', 'arrow-up-right', 'external-link', 'arrow-up-right'];

            assert(shareAudit.path === '/share/apartment-pets', `${shareLabel}: share route path drifted.`);
            assert(shareAudit.pageVisible, `${shareLabel}: share page is hidden.`);
            assert(
                shareCopy.length === 30 && shareCopy.every((value) => value?.length > 0),
                `${shareLabel}: share localization surface is incomplete ${JSON.stringify(shareAudit.copy)}.`,
            );
            assert(
                JSON.stringify(shareAudit.channelCodes) === JSON.stringify(['email', 'text', 'original']),
                `${shareLabel}: share channel codes drifted ${JSON.stringify(shareAudit.channelCodes)}.`,
            );
            assert(
                JSON.stringify(shareAudit.channelIcons) === JSON.stringify(expectedChannelIcons),
                `${shareLabel}: share channel icons drifted ${JSON.stringify(shareAudit.channelIcons)}.`,
            );
            assert(
                shareAudit.recipientIcons.length === 4
                    && shareAudit.recipientIcons.every((icon) => icon === 'send'),
                `${shareLabel}: recipient action icons drifted ${JSON.stringify(shareAudit.recipientIcons)}.`,
            );
            assert(
                JSON.stringify(shareAudit.pageIcons) === JSON.stringify([
                    'arrow-left',
                    'external-link',
                    ...expectedChannelIcons,
                    'send',
                    'send',
                    'send',
                    'send',
                    'shield-check',
                ]),
                `${shareLabel}: share page icon order drifted ${JSON.stringify(shareAudit.pageIcons)}.`,
            );
            assert(shareAudit.smallTargets.length === 0, `${shareLabel}: share controls below 44px ${JSON.stringify(shareAudit.smallTargets)}.`);
            assert(shareAudit.clippedRegions.length === 0, `${shareLabel}: share content is clipped ${JSON.stringify(shareAudit.clippedRegions)}.`);
            assert(shareAudit.overflow <= 1, `${shareLabel}: share page overflows horizontally.`);
            assert(shareAudit.rawTranslationKeys.length === 0, `${shareLabel}: raw share translation keys remain.`);

            if (viewport.locale === 'en') {
                englishShareCopy ??= shareCopy;
                assert(
                    shareCopy.every((value, index) => value === englishShareCopy[index]),
                    `${shareLabel}: English share copy changed across viewports.`,
                );
            } else {
                assert(englishShareCopy !== null, `${shareLabel}: English share baseline is missing.`);
                assert(
                    shareCopy.every((value, index) => value !== englishShareCopy[index]),
                    `${shareLabel}: English share fallback remains. Current ${JSON.stringify(shareCopy)}; English ${JSON.stringify(englishShareCopy)}.`,
                );
            }

            pageIdentityAudits[shareLabel] = {
                ...sharePageAudit,
                ...shareAudit,
            };

            if ([375, 1440].includes(viewport.width)) {
                const shareScreenshotPath = join(
                    outputDirectory,
                    `page-identity-share-${viewport.width}.png`,
                );
                const shareScreenshotData = await client.send('Page.captureScreenshot', {
                    format: 'png',
                    captureBeyondViewport: true,
                }, sessionId);
                await writeFile(shareScreenshotPath, Buffer.from(shareScreenshotData.data, 'base64'));
                pageIdentityScreenshots.push(shareScreenshotPath);
            }
        }

        await setProfileLocale(originalProfileLocale);
        assert(consoleErrors.length === 0, `Browser console errors: ${consoleErrors.join(' | ')}`);

        const report = {
            scope: 'page-identity',
            baseUrl,
            checkedAt: new Date().toISOString(),
            routes: pageIdentityRoutes,
            viewports: pageIdentityViewports,
            audits: pageIdentityAudits,
            consoleErrors,
            screenshots: pageIdentityScreenshots,
        };

        await writeFile(
            join(outputDirectory, 'page-identity-report.json'),
            `${JSON.stringify(report, null, 2)}\n`,
        );
        console.log(JSON.stringify(report, null, 2));

        break auditRun;
    }

    await setProfileLocale(client, sessionId, 'en');
    await navigate(client, sessionId, `${baseUrl}/forum`);
    const desktopAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(desktopAudit, 'desktop forum');

    await evaluate(client, sessionId, `(() => {
        document.querySelector('a[href="#main-content"]')?.focus();

        return true;
    })()`);
    const skipFocus = await evaluate(client, sessionId, `(() => {
        const element = document.activeElement;
        const style = getComputedStyle(element);

        return {
            isSkipLink: element?.matches('a[href="#main-content"]') ?? false,
            activeElement: element?.outerHTML?.slice(0, 240) ?? null,
            outlineStyle: style.outlineStyle,
            outlineWidth: style.outlineWidth,
            boxShadow: style.boxShadow,
        };
    })()`);
    assert(
        skipFocus.isSkipLink,
        `The forum skip link could not receive focus: ${skipFocus.activeElement}.`,
    );
    assert(
        (skipFocus.outlineStyle !== 'none' && skipFocus.outlineWidth !== '0px')
            || skipFocus.boxShadow !== 'none',
        'Skip link focus is not visible.',
    );

    const desktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(join(outputDirectory, 'forum-desktop.png'), Buffer.from(desktopScreenshot.data, 'base64'));

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);
    await client.send('Emulation.setTouchEmulationEnabled', {
        enabled: true,
        maxTouchPoints: 5,
    }, sessionId);
    await client.send('Emulation.setEmulatedMedia', {
        features: [{ name: 'prefers-reduced-motion', value: 'reduce' }],
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/forum`);
    const mobileAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(mobileAudit, 'mobile forum');

    const mobileTargets = await evaluate(client, sessionId, `(() => [...document.querySelectorAll(
        '.forum-page button, .forum-page input:not([type="hidden"]), .forum-page select, '
        + '.forum-page textarea, .forum-page .forum-button'
    )].filter((element) => {
        const style = getComputedStyle(element);
        const box = element.getBoundingClientRect();
        return style.display !== 'none' && style.visibility !== 'hidden' && box.width > 0 && box.height > 0;
    }).map((element) => ({
        label: element.getAttribute('aria-label') || element.textContent.trim().slice(0, 60)
            || element.getAttribute('name'),
        width: Math.round(element.getBoundingClientRect().width),
        height: Math.round(element.getBoundingClientRect().height),
    })).filter((target) => target.width < 44 || target.height < 44))()`);
    assert(mobileTargets.length === 0, `Mobile controls below 44px: ${JSON.stringify(mobileTargets)}.`);

    const mobileScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(join(outputDirectory, 'forum-mobile.png'), Buffer.from(mobileScreenshot.data, 'base64'));

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 320,
        height: 900,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 320,
        screenHeight: 900,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/forum`);
    const zoomAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(zoomAudit, '320px reflow');

    const oneHealthAudits = {};

    for (const viewport of [
        { label: 'desktop', width: 1440, height: 900, mobile: false },
        { label: 'mobile', width: 375, height: 812, mobile: true },
    ]) {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: viewport.width,
            height: viewport.height,
            deviceScaleFactor: 1,
            mobile: viewport.mobile,
            screenWidth: viewport.width,
            screenHeight: viewport.height,
        }, sessionId);
        const label = `${viewport.label} one-health category`;
        await navigate(
            client,
            sessionId,
            `${baseUrl}/forum?category=one-health-human-safety`,
        );
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        const behavior = await evaluate(client, sessionId, `(() => ({
            boundaryCount: document.querySelectorAll(
                '[data-section="one-health-professional-boundary"][role="note"]'
            ).length,
            subcategoryCount: document.querySelectorAll(
                '[data-category-child^="one-health-human-safety/"]'
            ).length,
            professionalBoundaryVisible: document.body.innerText.includes(
                'These discussions do not replace a physician, veterinarian, public-health authority, or emergency service.'
            ),
            rawTranslationKeys: document.body.innerText.match(/\\bforum_categories\\.[a-z0-9_.-]+/gi) ?? [],
        }))()`);
        assert(behavior.boundaryCount === 1, `${label}: professional boundary is missing or duplicated.`);
        assert(behavior.subcategoryCount === 42, `${label}: expected 42 source subcategories.`);
        assert(behavior.professionalBoundaryVisible, `${label}: localized professional boundary is not visible.`);
        assert(
            behavior.rawTranslationKeys.length === 0,
            `${label}: raw category keys are visible: ${behavior.rawTranslationKeys.join(', ')}.`,
        );
        const smallTargets = viewport.mobile
            ? await evaluate(client, sessionId, surfaceTouchTargetExpression)
            : [];
        assert(
            smallTargets.length === 0,
            `${label}: controls below 44px ${JSON.stringify(smallTargets)}.`,
        );
        oneHealthAudits[label] = { ...audit, ...behavior, smallTargets };

        const screenshotData = await client.send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
        }, sessionId);
        await writeFile(
            join(outputDirectory, `forum-one-health-${viewport.label}.png`),
            Buffer.from(screenshotData.data, 'base64'),
        );
    }

    const groupCardAudits = {};
    const groupDetailAudits = {};
    const groupTabAudits = {};
    let englishGroupDetailCopy = null;
    const englishGroupTabCopy = {};
    await navigate(client, sessionId, `${baseUrl}/profile/settings`);
    const originalGroupLocale = await evaluate(
        client,
        sessionId,
        `document.querySelector('#profile-settings-locale')?.value`,
    );
    const groupViewports = [
        {
            locale: 'en', label: '1440px', width: 1440, height: 900, mobile: false,
            screenshot: 'group-directory-desktop.png',
        },
        { locale: 'en', label: '1920px', width: 1920, height: 1080, mobile: false },
        {
            locale: 'lt', label: '320px', width: 320, height: 900, mobile: true,
            screenshot: 'group-directory-lt-mobile.png',
        },
        { locale: 'lt', label: '768px', width: 768, height: 1024, mobile: false },
        {
            locale: 'ru', label: '375px', width: 375, height: 812, mobile: true,
            screenshot: 'group-directory-mobile.png',
        },
        { locale: 'ru', label: '1024px', width: 1024, height: 900, mobile: false },
    ];

    try {
        for (const viewport of groupViewports) {
            await setProfileLocale(client, sessionId, viewport.locale);
            await client.send('Emulation.setDeviceMetricsOverride', {
                width: viewport.width,
                height: viewport.height,
                deviceScaleFactor: 1,
                mobile: viewport.mobile,
                screenWidth: viewport.width,
                screenHeight: viewport.height,
            }, sessionId);
            const label = `${viewport.locale} ${viewport.label} group directory cards`;
            await navigate(client, sessionId, `${baseUrl}/groups`);
            const audit = await evaluate(client, sessionId, pageAuditExpression);
            assertPageAudit(audit, label);
            const behavior = await evaluate(client, sessionId, `(() => {
                const cards = [...document.querySelectorAll('[data-group-card][data-ui-card]')];
                const layouts = cards.map((card) => {
                    const media = card.querySelector(':scope > [data-card-region="media"]');
                    const body = card.querySelector(':scope > [data-card-region="body"]');
                    const footer = body?.querySelector(':scope > [data-card-region="footer"]');
                    const image = media?.querySelector('img');
                    const heading = body?.querySelector('[data-card-heading]');
                    const description = body?.querySelector('[data-card-description]');
                    const cardBox = card.getBoundingClientRect();
                    const mediaBox = media?.getBoundingClientRect();
                    const bodyBox = body?.getBoundingClientRect();
                    const imageBox = image?.getBoundingClientRect();
                    const mediaStyle = media ? getComputedStyle(media) : null;
                    const bodyStyle = body ? getComputedStyle(body) : null;

                    return {
                        cardTop: Math.round(cardBox.top),
                        cardHeight: Math.round(cardBox.height),
                        hasMedia: Boolean(media),
                        hasBody: Boolean(body),
                        hasFooter: Boolean(footer),
                        hasHeading: Boolean(heading),
                        hasDescription: Boolean(description),
                        descriptionLength: description?.textContent.trim().length ?? 0,
                        copyClipped: [heading, description].filter(Boolean).some(
                            (element) => element.scrollHeight > element.clientHeight + 1
                                || element.scrollWidth > element.clientWidth + 1,
                        ),
                        boundaryGap: mediaBox && bodyBox
                            ? Math.round((bodyBox.top - mediaBox.bottom) * 100) / 100
                            : null,
                        separatorWidth: Number.parseFloat(mediaStyle?.borderBottomWidth ?? '0'),
                        mediaOverflow: mediaStyle?.overflow,
                        bodyBackground: bodyStyle?.backgroundColor,
                        bodyPaddingTop: Number.parseFloat(bodyStyle?.paddingTop ?? '0'),
                        imageEscapesMedia: Boolean(
                            mediaBox && imageBox
                            && (
                                imageBox.left < mediaBox.left - 1
                                || imageBox.right > mediaBox.right + 1
                                || imageBox.top < mediaBox.top - 1
                                || imageBox.bottom > mediaBox.bottom + 1
                            )
                        ),
                    };
                });
                const rows = Object.values(layouts.reduce((grouped, layout) => {
                    grouped[layout.cardTop] ??= [];
                    grouped[layout.cardTop].push(layout.cardHeight);

                    return grouped;
                }, {}));
                const actionCount = (name, pressed) => document.querySelectorAll(
                    '[data-group-card] form:has(input[name="action"][value="' + name + '"]) '
                        + 'button[aria-pressed="' + pressed + '"]',
                ).length;
                const visibleActionTargets = [...document.querySelectorAll(
                    '[data-group-card] .group-card__actions button, '
                        + '[data-group-card] .group-card__actions a',
                )].filter((element) => {
                    const style = getComputedStyle(element);
                    const box = element.getBoundingClientRect();

                    return style.display !== 'none' && style.visibility !== 'hidden'
                        && box.width > 0 && box.height > 0;
                });

                return {
                    documentLanguage: document.documentElement.lang,
                    cardCount: cards.length,
                    layouts,
                    maximumDescriptionLength: Math.max(...layouts.map(({ descriptionLength }) => descriptionLength)),
                    rowHeightSpreads: rows.map((heights) => Math.max(...heights) - Math.min(...heights)),
                    membershipActions: {
                        joined: actionCount('leave-group', 'true'),
                        pending: actionCount('cancel-group-request', 'false'),
                        unjoined: actionCount('join-group', 'false'),
                    },
                    secondaryActionCount: document.querySelectorAll(
                        '[data-group-card] form:has(input[name="action"][value="dismiss-group-recommendation"]) '
                            + 'button[aria-label]',
                    ).length,
                    smallActionTargets: visibleActionTargets.map((element) => ({
                        label: element.getAttribute('aria-label') || element.textContent.trim().slice(0, 60),
                        width: Math.round(element.getBoundingClientRect().width),
                        height: Math.round(element.getBoundingClientRect().height),
                    })).filter((target) => target.width < 44 || target.height < 44),
                    rawTranslationKeys: document.body.innerText.match(/\\b(?:messages|ui|presentation)\\.[a-z0-9_.-]+/gi) ?? [],
                };
            })()`);
            assert(
                behavior.documentLanguage === viewport.locale,
                `${label}: expected ${viewport.locale}, found ${behavior.documentLanguage}.`,
            );
            assert(behavior.cardCount === 6, `${label}: expected six compatibility group cards.`);
            assert(
                behavior.layouts.every((layout) => layout.hasMedia && layout.hasBody && layout.hasFooter),
                `${label}: shared media, body, or footer region is missing.`,
            );
            assert(
                behavior.layouts.every(
                    (layout) => layout.hasHeading && layout.hasDescription && ! layout.copyClipped,
                ),
                `${label}: shared copy is missing or clipped.`,
            );
            assert(
                behavior.maximumDescriptionLength >= 100,
                `${label}: the long-content fixture is unexpectedly short.`,
            );
            assert(
                behavior.layouts.every((layout) => Math.abs(layout.boundaryGap) <= 1),
                `${label}: media and body overlap or separate unpredictably.`,
            );
            assert(
                behavior.layouts.every((layout) => layout.separatorWidth >= 1),
                `${label}: visible media/body separator is missing.`,
            );
            assert(
                behavior.layouts.every(
                    (layout) => layout.mediaOverflow === 'hidden'
                        && layout.bodyBackground !== 'rgba(0, 0, 0, 0)'
                        && layout.bodyPaddingTop >= 16
                        && ! layout.imageEscapesMedia,
                ),
                `${label}: shared card containment or body spacing is invalid.`,
            );
            assert(
                behavior.rowHeightSpreads.every((spread) => spread <= 1),
                `${label}: cards in the same grid row do not share a stable height.`,
            );
            assert(
                JSON.stringify(behavior.membershipActions)
                    === JSON.stringify({ joined: 2, pending: 1, unjoined: 3 }),
                `${label}: membership action states are incomplete ${JSON.stringify(behavior.membershipActions)}.`,
            );
            assert(
                behavior.secondaryActionCount === 6,
                `${label}: expected six accessible secondary recommendation actions.`,
            );
            assert(
                behavior.smallActionTargets.length === 0,
                `${label}: controls below 44px ${JSON.stringify(behavior.smallActionTargets)}.`,
            );
            assert(
                behavior.rawTranslationKeys.length === 0,
                `${label}: raw translation keys are visible: ${behavior.rawTranslationKeys.join(', ')}.`,
            );
            groupCardAudits[label] = { ...audit, ...behavior };

            if (viewport.screenshot) {
                const loadedImageCount = await evaluate(client, sessionId, `(async () => {
                    const images = [...document.querySelectorAll('[data-group-card] [data-ui-card-media] img')];

                    for (const image of images) {
                        image.scrollIntoView({ block: 'center' });
                        await new Promise((resolve) => setTimeout(resolve, 100));
                    }

                    await Promise.all(images.map((image) => {
                        if (image.complete) {
                            return Promise.resolve();
                        }

                        return new Promise((resolve) => {
                            image.addEventListener('load', resolve, { once: true });
                            image.addEventListener('error', resolve, { once: true });
                        });
                    }));
                    window.scrollTo(0, 0);
                    await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                    return images.filter((image) => image.complete && image.naturalWidth > 0).length;
                })()`);
                assert(loadedImageCount === 6, `${label}: expected all six card images to load before capture.`);
                groupCardAudits[label].loadedImageCount = loadedImageCount;
                const screenshotData = await client.send('Page.captureScreenshot', {
                    format: 'png',
                    captureBeyondViewport: true,
                }, sessionId);
                await writeFile(
                    join(outputDirectory, viewport.screenshot),
                    Buffer.from(screenshotData.data, 'base64'),
                );
            }

            const detailLabel = `${viewport.locale} ${viewport.label} group detail`;
            await navigate(client, sessionId, `${baseUrl}/groups/trail-tails`);
            const detailAudit = await evaluate(client, sessionId, pageAuditExpression);
            assertPageAudit(detailAudit, detailLabel);
            const detailBehavior = await evaluate(client, sessionId, `(() => {
                const hero = document.querySelector('[data-group-detail-hero]');
                const dashboard = document.querySelector('[data-group-detail-dashboard]');
                const panelCopy = (section) => {
                    const panel = document.querySelector('[data-section="' + section + '"]');

                    return [
                        panel?.querySelector('.section-heading__eyebrow')?.textContent.trim() ?? null,
                        panel?.querySelector('.section-heading__title, .panel-heading__title')?.textContent.trim() ?? null,
                    ];
                };
                const actionLabels = [...(hero?.querySelectorAll('.group-hero__commands a, .group-hero__commands button') ?? [])]
                    .map((element) => element.getAttribute('aria-label') || element.textContent.trim());
                const tabLabels = [...document.querySelectorAll('.group-tabs .tabs__item > span:not(.tabs__count)')]
                    .map((element) => element.textContent.trim());
                const principles = panelCopy('group-principles');
                const overviewPosts = [
                    document.querySelector('#overview-posts-title')
                        ?.closest('.section-heading')?.querySelector('.section-heading__eyebrow')?.textContent.trim() ?? null,
                    document.querySelector('#overview-posts-title')?.textContent.trim() ?? null,
                ];
                const overviewEvents = panelCopy('overview-events');
                const poll = document.querySelector('[data-section="group-poll"]');
                const chat = document.querySelector('[data-section="group-chat"]');
                const notifications = document.querySelector('[data-section="group-notifications"]');
                const groupDetailCopy = [
                    document.querySelector('[data-group-detail-back]')?.textContent.trim() ?? null,
                    hero?.querySelector('.group-hero__meta')?.getAttribute('aria-label') ?? null,
                    hero?.querySelector('.group-hero__summary')?.getAttribute('aria-label') ?? null,
                    hero?.querySelector('.group-hero__badges .status-badge:nth-child(2) span:last-child')?.textContent.trim() ?? null,
                    ...actionLabels,
                    ...tabLabels,
                    hero?.querySelector('.group-hero__eyebrow')?.textContent.trim() ?? null,
                    hero?.querySelector('.group-hero__description')?.textContent.trim() ?? null,
                    hero?.querySelector('.group-hero__image')?.getAttribute('alt') ?? null,
                    ...principles,
                    ...overviewPosts,
                    ...overviewEvents,
                    poll?.querySelector('.section-heading__eyebrow')?.textContent.trim() ?? null,
                    chat?.querySelector('.section-heading__eyebrow')?.textContent.trim() ?? null,
                    chat?.querySelector('.section-heading__title')?.textContent.trim() ?? null,
                    chat?.querySelector('.chat-preview')?.getAttribute('aria-label') ?? null,
                    chat?.querySelector('.action span')?.textContent.trim() ?? null,
                    document.querySelector('[data-section="group-team"] .panel-heading__title')?.textContent.trim() ?? null,
                    notifications?.querySelector('.panel-heading__title')?.textContent.trim() ?? null,
                    notifications?.querySelector('.panel-heading__meta')?.textContent.trim() ?? null,
                    notifications?.querySelector('.notification-options')?.getAttribute('aria-label') ?? null,
                    ...[...(notifications?.querySelectorAll('.notification-options .action span') ?? [])]
                        .map((element) => element.textContent.trim()),
                ];

                return {
                    documentLanguage: document.documentElement.lang,
                    heroCount: document.querySelectorAll('[data-group-detail-hero]').length,
                    dashboardCount: document.querySelectorAll('[data-group-detail-dashboard]').length,
                    actionCount: actionLabels.length,
                    tabCount: tabLabels.length,
                    groupDetailCopy,
                    rawTranslationKeys: document.body.innerText.match(/\b(?:messages|ui|groups)\.[a-z0-9_.-]+/gi) ?? [],
                };
            })()`);
            assert(detailBehavior.documentLanguage === viewport.locale, `${detailLabel}: wrong locale.`);
            assert(detailBehavior.heroCount === 1, `${detailLabel}: detail hero is missing or duplicated.`);
            assert(detailBehavior.dashboardCount === 1, `${detailLabel}: detail dashboard is missing or duplicated.`);
            assert(detailBehavior.actionCount === 3, `${detailLabel}: expected three hero actions.`);
            assert(detailBehavior.tabCount === 8, `${detailLabel}: expected eight detail tabs.`);
            assert(
                detailBehavior.groupDetailCopy.length === 39
                    && detailBehavior.groupDetailCopy.every((value) => value?.length > 0),
                `${detailLabel}: group detail localization surface is incomplete ${JSON.stringify(detailBehavior.groupDetailCopy)}.`,
            );

            if (viewport.locale === 'en') {
                englishGroupDetailCopy ??= detailBehavior.groupDetailCopy;
                assert(
                    detailBehavior.groupDetailCopy.every((value, index) => value === englishGroupDetailCopy[index]),
                    `${detailLabel}: English group detail chrome changed across viewports.`,
                );
            } else {
                assert(englishGroupDetailCopy !== null, `${detailLabel}: English detail baseline is missing.`);
                assert(
                    detailBehavior.groupDetailCopy.every((value, index) => value !== englishGroupDetailCopy[index]),
                    `${detailLabel}: English group detail chrome fallback remains.`,
                );
            }

            assert(
                detailBehavior.rawTranslationKeys.length === 0,
                `${detailLabel}: raw translation keys are visible: ${detailBehavior.rawTranslationKeys.join(', ')}.`,
            );
            const detailSmallTargets = viewport.mobile
                ? await evaluate(client, sessionId, surfaceTouchTargetExpression)
                : [];
            assert(
                detailSmallTargets.length === 0,
                `${detailLabel}: controls below 44px ${JSON.stringify(detailSmallTargets)}.`,
            );
            groupDetailAudits[detailLabel] = { ...detailAudit, ...detailBehavior, smallTargets: detailSmallTargets };

            if (viewport.screenshot) {
                const screenshotData = await client.send('Page.captureScreenshot', {
                    format: 'png',
                    captureBeyondViewport: true,
                }, sessionId);
                await writeFile(
                    join(outputDirectory, viewport.screenshot.replace('group-directory', 'group-detail')),
                    Buffer.from(screenshotData.data, 'base64'),
                );
            }

            if (viewport.screenshot) {
                const tabViewportLabel = viewport.locale + ' ' + viewport.label + ' group tabs';
                groupTabAudits[tabViewportLabel] = {};

                for (const tab of ['overview', 'posts', 'discussions', 'events', 'members', 'pets', 'resources', 'rules']) {
                    await navigate(client, sessionId, baseUrl + '/groups/trail-tails?tab=' + tab);
                    const tabAudit = await evaluate(client, sessionId, pageAuditExpression);
                    const tabLabel = tabViewportLabel + ' ' + tab;
                    assertPageAudit(tabAudit, tabLabel);
                    const tabBehavior = await evaluate(client, sessionId, String.raw`(() => {
                        const dashboard = document.querySelector('[data-group-detail-dashboard]');
                        const activeTab = document.querySelector(
                            '.group-tabs .tabs__item[aria-current="page"] > span:not(.tabs__count)',
                        );

                        return {
                            documentLanguage: document.documentElement.lang,
                            activeTab: activeTab?.textContent.trim() ?? null,
                            dashboardText: dashboard?.innerText.replace(/\s+/g, ' ').trim() ?? null,
                            rawTranslationKeys: dashboard?.innerText.match(
                                /\b(?:messages|ui|groups)\.[a-z0-9_.-]+/gi,
                            ) ?? [],
                        };
                    })()`);
                    assert(tabBehavior.documentLanguage === viewport.locale, tabLabel + ': wrong locale.');
                    assert(tabBehavior.activeTab?.length > 0, tabLabel + ': active tab is missing.');
                    assert(
                        tabBehavior.dashboardText?.length >= 100,
                        tabLabel + ': localized group content is unexpectedly short.',
                    );
                    assert(
                        tabBehavior.rawTranslationKeys.length === 0,
                        tabLabel + ': raw translation keys are visible: ' + tabBehavior.rawTranslationKeys.join(', ') + '.',
                    );

                    if (viewport.locale === 'en') {
                        englishGroupTabCopy[tab] = tabBehavior.dashboardText;
                    } else {
                        assert(
                            englishGroupTabCopy[tab]?.length > 0,
                            tabLabel + ': English group tab baseline is missing.',
                        );
                        assert(
                            tabBehavior.dashboardText !== englishGroupTabCopy[tab],
                            tabLabel + ': English group tab content fallback remains.',
                        );
                    }

                    const tabSmallTargets = viewport.mobile
                        ? await evaluate(client, sessionId, surfaceTouchTargetExpression)
                        : [];
                    assert(
                        tabSmallTargets.length === 0,
                        tabLabel + ': controls below 44px ' + JSON.stringify(tabSmallTargets) + '.',
                    );
                    groupTabAudits[tabViewportLabel][tab] = {
                        ...tabAudit,
                        ...tabBehavior,
                        smallTargets: tabSmallTargets,
                    };
                }
            }
        }
    } finally {
        await setProfileLocale(client, sessionId, originalGroupLocale);
    }

    if (groupsOnly) {
        assert(consoleErrors.length === 0, `Browser console errors: ${consoleErrors.join(' | ')}`);

        const report = {
            scope: 'groups',
            baseUrl,
            checkedAt: new Date().toISOString(),
            groupCardAudits,
            groupDetailAudits,
            groupTabAudits,
            consoleErrors,
            screenshots: [
                join(outputDirectory, 'group-directory-desktop.png'),
                join(outputDirectory, 'group-directory-mobile.png'),
                join(outputDirectory, 'group-directory-lt-mobile.png'),
                join(outputDirectory, 'group-detail-desktop.png'),
                join(outputDirectory, 'group-detail-mobile.png'),
                join(outputDirectory, 'group-detail-lt-mobile.png'),
            ],
        };

        await writeFile(
            join(outputDirectory, 'group-directory-report.json'),
            `${JSON.stringify(report, null, 2)}\n`,
        );
        console.log(JSON.stringify(report, null, 2));

        break auditRun;
    }

    const eventAudits = {};
    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
        screenWidth: 1440,
        screenHeight: 900,
    }, sessionId);

    for (const [path, label, screenshot] of [
        ['/meetups', 'desktop event directory', 'event-directory-desktop.png'],
        [
            '/meetups/demo-point13-weekly-group-walk',
            'desktop recurring event detail',
            'event-detail-desktop.png',
        ],
        [
            '/meetups/demo-point13-care-conference',
            'desktop conference schedule',
            'event-schedule-desktop.png',
        ],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        const behavior = await evaluate(client, sessionId, `(() => ({
            eventSurfaceCount: document.querySelectorAll('[data-section="event-directory"], [data-section="event-workspace"]').length,
            rawTranslationKeys: document.body.innerText.match(/\\bforum_events\\.[a-z0-9_.-]+/gi) ?? [],
            privateLocationLeak: document.body.innerText.includes('Approved participant meeting point'),
            scheduleSessionCount: document.querySelectorAll('.event-schedule article').length,
        }))()`);
        assert(behavior.eventSurfaceCount === 1, `${label}: canonical event surface marker is missing.`);
        assert(
            behavior.rawTranslationKeys.length === 0,
            `${label}: raw event keys are visible: ${behavior.rawTranslationKeys.join(', ')}.`,
        );
        if (path === '/meetups') {
            assert(! behavior.privateLocationLeak, `${label}: an exact private location leaked into the directory.`);
        }
        if (path.includes('care-conference')) {
            assert(behavior.scheduleSessionCount === 3, `${label}: expected three seeded sessions.`);
            assert(! behavior.privateLocationLeak, `${label}: an exact private location leaked into the schedule.`);
        }
        eventAudits[label] = { ...audit, ...behavior };

        const screenshotData = await client.send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
        }, sessionId);
        await writeFile(
            join(outputDirectory, screenshot),
            Buffer.from(screenshotData.data, 'base64'),
        );
    }

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);

    for (const [path, label, screenshot] of [
        ['/meetups', 'mobile event directory', 'event-directory-mobile.png'],
        [
            '/meetups/demo-point13-weekly-group-walk',
            'mobile recurring event detail',
            'event-detail-mobile.png',
        ],
        [
            '/meetups/demo-point13-care-conference',
            'mobile conference schedule',
            'event-schedule-mobile.png',
        ],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        const smallTargets = await evaluate(client, sessionId, surfaceTouchTargetExpression);
        assert(smallTargets.length === 0, `${label}: controls below 44px ${JSON.stringify(smallTargets)}.`);
        if (path.includes('care-conference')) {
            const scheduleSessionCount = await evaluate(
                client,
                sessionId,
                "document.querySelectorAll('.event-schedule article').length",
            );
            assert(scheduleSessionCount === 3, `${label}: expected three seeded sessions.`);
        }
        eventAudits[label] = { ...audit, smallTargets };

        const screenshotData = await client.send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
        }, sessionId);
        await writeFile(
            join(outputDirectory, screenshot),
            Buffer.from(screenshotData.data, 'base64'),
        );
    }

    const placeAudits = {};

    for (const viewport of [
        { label: 'desktop', width: 1440, height: 900, mobile: false },
        { label: 'mobile', width: 375, height: 812, mobile: true },
    ]) {
        await client.send('Emulation.setDeviceMetricsOverride', {
            width: viewport.width,
            height: viewport.height,
            deviceScaleFactor: 1,
            mobile: viewport.mobile,
            screenWidth: viewport.width,
            screenHeight: viewport.height,
        }, sessionId);

        for (const [path, surface, screenshot] of [
            ['/places', 'place directory', `place-directory-${viewport.label}.png`],
            [
                '/places/vingis-quiet-loop',
                'place detail',
                `place-detail-${viewport.label}.png`,
            ],
        ]) {
            const label = `${viewport.label} ${surface}`;
            await navigate(client, sessionId, `${baseUrl}${path}`);
            let loadedPlaceImageCount = null;

            if (path === '/places') {
                loadedPlaceImageCount = await evaluate(client, sessionId, `(async () => {
                    const images = [...document.querySelectorAll('[data-place-card] [data-ui-card-media] img')];

                    for (const image of images) {
                        image.scrollIntoView({ block: 'center' });
                        await new Promise((resolve) => setTimeout(resolve, 100));
                    }

                    await Promise.all(images.map((image) => {
                        if (image.complete) {
                            return Promise.resolve();
                        }

                        return new Promise((resolve) => {
                            image.addEventListener('load', resolve, { once: true });
                            image.addEventListener('error', resolve, { once: true });
                        });
                    }));
                    window.scrollTo(0, 0);
                    await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)));

                    return images.filter((image) => image.complete && image.naturalWidth > 0).length;
                })()`);
                assert(loadedPlaceImageCount === 6, `${label}: expected all six place images to load.`);
            }

            const audit = await evaluate(client, sessionId, pageAuditExpression);
            assertPageAudit(audit, label);
            const behavior = await evaluate(client, sessionId, `(() => {
                const cards = [...document.querySelectorAll('[data-place-card]')];
                const cardLayouts = cards.map((card) => {
                    const media = card.querySelector('[data-ui-card-media]');
                    const body = card.querySelector('.place-card__body');
                    const heading = card.querySelector('[data-card-heading]');
                    const description = card.querySelector('[data-card-description]');
                    const image = media?.querySelector('img');
                    const mediaBox = media?.getBoundingClientRect();
                    const imageBox = image?.getBoundingClientRect();
                    const mediaLink = media instanceof HTMLAnchorElement ? media : null;
                    const headingLink = heading?.querySelector('a');

                    return {
                        cardHeight: Math.round(card.getBoundingClientRect().height),
                        mediaHeight: Math.round(mediaBox?.height ?? 0),
                        bodyHeight: Math.round(body?.getBoundingClientRect().height ?? 0),
                        hasSharedMedia: Boolean(media),
                        hasSharedHeading: Boolean(heading),
                        hasSharedDescription: Boolean(description),
                        copyClipped: [heading, description].filter(Boolean).some(
                            (element) => element.scrollHeight > element.clientHeight + 1
                                || element.scrollWidth > element.clientWidth + 1,
                        ),
                        destinationsMatch: Boolean(
                            mediaLink && headingLink && mediaLink.href === headingLink.href,
                        ),
                        imageEscapesMedia: Boolean(
                            mediaBox && imageBox
                            && (
                                imageBox.left < mediaBox.left - 1
                                || imageBox.right > mediaBox.right + 1
                                || imageBox.top < mediaBox.top - 1
                                || imageBox.bottom > mediaBox.bottom + 1
                            )
                        ),
                    };
                });
                const firstCard = cards[0];
                const firstKey = firstCard?.dataset.placeCard;
                const firstMarker = firstKey
                    ? document.querySelector('[data-place-marker="' + CSS.escape(firstKey) + '"]')
                    : null;
                firstMarker?.click();
                const selectedHeading = firstCard?.querySelector('[data-card-heading]');
                const selectedHeadingLink = selectedHeading?.querySelector('a');
                const selection = document.querySelector('[data-place-selection]');

                return {
                    surfaceCount: document.querySelectorAll('[data-section="places-summary"], .place-dashboard').length,
                    placeCardCount: cards.length,
                    cardLayouts,
                    mapSelectionSynced: ! firstCard || Boolean(
                        selection?.querySelector('strong')?.textContent.trim()
                            === selectedHeading?.textContent.trim()
                        && selection?.querySelector('[data-place-selection-link]')?.href
                            === selectedHeadingLink?.href,
                    ),
                    rawTranslationKeys: document.body.innerText.match(/\\bplaces\\.[a-z0-9_.-]+/gi) ?? [],
                    privateLocationLeak: document.body.innerText.includes('Protected foster entrance'),
                };
            })()`);
            assert(behavior.surfaceCount === 1, `${label}: canonical place surface marker is missing.`);
            assert(
                behavior.rawTranslationKeys.length === 0,
                `${label}: raw place keys are visible: ${behavior.rawTranslationKeys.join(', ')}.`,
            );
            assert(! behavior.privateLocationLeak, `${label}: a protected exact location leaked.`);
            if (path === '/places') {
                assert(behavior.placeCardCount > 0, `${label}: no persisted place cards were rendered.`);
                assert(
                    behavior.cardLayouts.every(
                        (card) => card.hasSharedMedia
                            && card.hasSharedHeading
                            && card.hasSharedDescription
                            && card.destinationsMatch
                            && ! card.copyClipped
                            && ! card.imageEscapesMedia,
                    ),
                    `${label}: shared place-card composition or containment is invalid.`,
                );
                assert(behavior.mapSelectionSynced, `${label}: marker selection did not update from shared heading hooks.`);
                const maximumCardHeight = Math.max(
                    ...behavior.cardLayouts.map(({ cardHeight }) => cardHeight),
                );
                const allowedCardHeight = viewport.mobile ? 720 : 480;
                assert(
                    maximumCardHeight <= allowedCardHeight,
                    `${label}: a card is ${maximumCardHeight}px tall; expected at most ${allowedCardHeight}px.`,
                );
            }

            const smallTargets = viewport.mobile
                ? await evaluate(client, sessionId, placeTouchTargetExpression)
                : [];
            assert(
                smallTargets.length === 0,
                `${label}: controls below 44px ${JSON.stringify(smallTargets)}.`,
            );
            placeAudits[label] = { ...audit, ...behavior, smallTargets, loadedPlaceImageCount };

            const screenshotData = await client.send('Page.captureScreenshot', {
                format: 'png',
                captureBeyondViewport: true,
            }, sessionId);
            await writeFile(
                join(outputDirectory, screenshot),
                Buffer.from(screenshotData.data, 'base64'),
            );
        }
    }

    if (placesOnly) {
        assert(consoleErrors.length === 0, `Browser console errors: ${consoleErrors.join(' | ')}`);

        const report = {
            scope: 'places',
            baseUrl,
            checkedAt: new Date().toISOString(),
            placeAudits,
            consoleErrors,
            screenshots: [
                join(outputDirectory, 'place-directory-desktop.png'),
                join(outputDirectory, 'place-directory-mobile.png'),
                join(outputDirectory, 'place-detail-desktop.png'),
                join(outputDirectory, 'place-detail-mobile.png'),
            ],
        };

        await writeFile(
            join(outputDirectory, 'place-card-report.json'),
            `${JSON.stringify(report, null, 2)}\n`,
        );
        console.log(JSON.stringify(report, null, 2));

        break auditRun;
    }

    const organizationAudits = {};
    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
        screenWidth: 1440,
        screenHeight: 900,
    }, sessionId);

    for (const [path, label, screenshot, marker] of [
        [
            '/organizations',
            'desktop organization directory',
            'organization-directory-desktop.png',
            'organization-directory',
        ],
        [
            '/organizations/vilnius-animal-welfare-network',
            'desktop organization workspace',
            'organization-workspace-desktop.png',
            'organization-workspace',
        ],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        const behavior = await evaluate(client, sessionId, `((sectionMarker) => ({
            organizationSurfaceCount: document.querySelectorAll('[data-section="' + sectionMarker + '"]').length,
            rawTranslationKeys: document.body.innerText.match(/\\borganizations\\.[a-z0-9_.-]+/gi) ?? [],
            privateDataLeak: document.body.innerText.includes('demo-independent-registry'),
        }))(${JSON.stringify(marker)})`);
        assert(
            behavior.organizationSurfaceCount === 1,
            `${label}: canonical organization surface marker is missing.`,
        );
        assert(
            behavior.rawTranslationKeys.length === 0,
            `${label}: raw organization keys are visible: ${behavior.rawTranslationKeys.join(', ')}.`,
        );
        assert(! behavior.privateDataLeak, `${label}: private organization evidence leaked.`);
        organizationAudits[label] = { ...audit, ...behavior };

        const screenshotData = await client.send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
        }, sessionId);
        await writeFile(
            join(outputDirectory, screenshot),
            Buffer.from(screenshotData.data, 'base64'),
        );
    }

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);

    for (const [path, label, screenshot, marker] of [
        [
            '/organizations',
            'mobile organization directory',
            'organization-directory-mobile.png',
            'organization-directory',
        ],
        [
            '/organizations/vilnius-animal-welfare-network',
            'mobile organization workspace',
            'organization-workspace-mobile.png',
            'organization-workspace',
        ],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        const smallTargets = await evaluate(client, sessionId, surfaceTouchTargetExpression);
        assert(smallTargets.length === 0, `${label}: controls below 44px ${JSON.stringify(smallTargets)}.`);
        const behavior = await evaluate(client, sessionId, `((sectionMarker) => ({
            organizationSurfaceCount: document.querySelectorAll('[data-section="' + sectionMarker + '"]').length,
            rawTranslationKeys: document.body.innerText.match(/\\borganizations\\.[a-z0-9_.-]+/gi) ?? [],
        }))(${JSON.stringify(marker)})`);
        assert(
            behavior.organizationSurfaceCount === 1,
            `${label}: canonical organization surface marker is missing.`,
        );
        assert(
            behavior.rawTranslationKeys.length === 0,
            `${label}: raw organization keys are visible: ${behavior.rawTranslationKeys.join(', ')}.`,
        );
        organizationAudits[label] = { ...audit, ...behavior, smallTargets };

        const screenshotData = await client.send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
        }, sessionId);
        await writeFile(
            join(outputDirectory, screenshot),
            Buffer.from(screenshotData.data, 'base64'),
        );
    }

    const contentAudits = {};
    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/content`);
    const contentDesktopAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(contentDesktopAudit, 'desktop content feed');
    contentAudits['desktop content feed'] = contentDesktopAudit;

    const contentDesktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'content-feed-desktop.png'),
        Buffer.from(contentDesktopScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/content`);
    const contentMobileAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(contentMobileAudit, 'mobile content feed');
    contentAudits['mobile content feed'] = contentMobileAudit;

    const contentMobileScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'content-feed-mobile.png'),
        Buffer.from(contentMobileScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 320,
        height: 900,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 320,
        screenHeight: 900,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/content`);
    const contentZoomAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(contentZoomAudit, '320px content feed reflow');
    contentAudits['320px content feed reflow'] = contentZoomAudit;

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    }, sessionId);
    const communityAudits = {};
    await navigate(client, sessionId, `${baseUrl}/forum/groups/apartment-pets`);
    const communityDesktopAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(communityDesktopAudit, 'desktop community workspace');
    communityAudits['desktop community workspace'] = communityDesktopAudit;
    const communityProfileBehavior = await evaluate(client, sessionId, `(() => {
        const profileSelect = [...document.querySelectorAll('select')]
            .find((element) => element.getAttribute('wire:model.live') === 'selectedActorKey');
        const pageText = document.body.innerText;

        return {
            actorOptionCount: profileSelect?.options.length ?? 0,
            hasRulesVersion: /Accepted rules version:\\s*\\d+/i.test(pageText),
            hasAccountabilityNotice: pageText.includes('real account'),
            rawTranslationKeys: pageText.match(/\\bforum_groups\\.[a-z0-9_.-]+/gi) ?? [],
        };
    })()`);
    assert(
        communityProfileBehavior.actorOptionCount >= 2,
        'The community profile selector did not expose the personal and pet profiles.',
    );
    assert(
        communityProfileBehavior.hasRulesVersion,
        'The accepted community rules version is not visible.',
    );
    assert(
        communityProfileBehavior.hasAccountabilityNotice,
        'The community real-account accountability notice is missing.',
    );
    assert(
        communityProfileBehavior.rawTranslationKeys.length === 0,
        `Raw community keys are visible: ${communityProfileBehavior.rawTranslationKeys.join(', ')}.`,
    );

    const communityDesktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'community-workspace-desktop.png'),
        Buffer.from(communityDesktopScreenshot.data, 'base64'),
    );

    const medicalAudits = {};

    for (const [path, label] of [
        ['/medical-records', 'desktop medical record directory'],
        ['/medical-records/new', 'desktop medical record creation'],
        ['/medical-records/scout-health', 'desktop medical record detail'],
        ['/medical-records/scout-health/manage', 'desktop medical record management'],
        ['/medical-records/nori-health/emergency', 'desktop emergency medical card'],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        medicalAudits[label] = audit;
    }

    const medicalBehavior = await evaluate(client, sessionId, `(() => {
        const pageText = document.body.innerText;

        return {
            documentLanguage: document.documentElement.lang,
            noneKnownVisible: pageText.includes('No known items in the available history'),
            lastUpdatedVisible: pageText.includes('Last updated'),
            rawTranslationKeys: pageText.match(/\\bmedical\\.[a-z0-9_.-]+/gi) ?? [],
        };
    })()`);
    assert(medicalBehavior.documentLanguage === 'en', 'The medical card document language is not English.');
    assert(medicalBehavior.noneKnownVisible, 'The explicit no-known-items medical status is missing.');
    assert(medicalBehavior.lastUpdatedVisible, 'The emergency card freshness label is missing.');
    assert(
        medicalBehavior.rawTranslationKeys.length === 0,
        `Raw medical translation keys are visible: ${medicalBehavior.rawTranslationKeys.join(', ')}.`,
    );

    const medicalDesktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'medical-emergency-desktop.png'),
        Buffer.from(medicalDesktopScreenshot.data, 'base64'),
    );

    const petAudits = {};

    for (const [path, label] of [
        ['/pets/profile/pet-scout', 'desktop public pet profile'],
        ['/pets/manage/new', 'desktop pet creation'],
        ['/pets/manage/invitations', 'desktop pet invitations'],
        ['/pets/manage/pet-scout', 'desktop pet management'],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        petAudits[label] = audit;
    }

    const petDesktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'pet-profile-manage-desktop.png'),
        Buffer.from(petDesktopScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/forum/groups/apartment-pets`);
    const communityMobileAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(communityMobileAudit, 'mobile community workspace');
    communityAudits['mobile community workspace'] = communityMobileAudit;
    const communityMobileTargets = await evaluate(client, sessionId, surfaceTouchTargetExpression);
    assert(
        communityMobileTargets.length === 0,
        `Mobile community controls below 44px: ${JSON.stringify(communityMobileTargets)}.`,
    );

    const communityMobileScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'community-workspace-mobile.png'),
        Buffer.from(communityMobileScreenshot.data, 'base64'),
    );

    for (const [path, label] of [
        ['/medical-records', 'mobile medical record directory'],
        ['/medical-records/scout-health', 'mobile medical record detail'],
        ['/medical-records/scout-health/manage', 'mobile medical record management'],
        ['/medical-records/nori-health/emergency', 'mobile emergency medical card'],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        medicalAudits[label] = audit;
    }

    const medicalMobileScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'medical-emergency-mobile.png'),
        Buffer.from(medicalMobileScreenshot.data, 'base64'),
    );

    for (const [path, label] of [
        ['/pets/profile/pet-scout', 'mobile public pet profile'],
        ['/pets/manage/new', 'mobile pet creation'],
        ['/pets/manage/pet-scout', 'mobile pet management'],
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        const smallTouchTargets = await evaluate(client, sessionId, surfaceTouchTargetExpression);
        assertPageAudit(audit, label);
        assert(
            smallTouchTargets.length === 0,
            `${label}: controls below 44px ${JSON.stringify(smallTouchTargets)}.`,
        );
        petAudits[label] = { ...audit, smallTouchTargets };
    }

    const petMobileScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'pet-profile-manage-mobile.png'),
        Buffer.from(petMobileScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 320,
        height: 900,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 320,
        screenHeight: 900,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/pets/manage/pet-scout`);
    const petZoomAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(petZoomAudit, '320px pet management reflow');
    petAudits['320px pet management reflow'] = petZoomAudit;

    await navigate(client, sessionId, `${baseUrl}/medical-records/scout-health/manage`);
    const medicalZoomAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(medicalZoomAudit, '320px medical record management reflow');
    medicalAudits['320px medical record management reflow'] = medicalZoomAudit;

    const socialAudits = {};
    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/circle/social`);
    const socialDesktopAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(socialDesktopAudit, 'desktop social relationships');
    socialAudits['desktop social relationships'] = socialDesktopAudit;

    const socialDesktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'social-relationships-desktop.png'),
        Buffer.from(socialDesktopScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/circle/social`);
    const socialMobileAudit = await evaluate(client, sessionId, pageAuditExpression);
    const socialSmallTouchTargets = await evaluate(
        client,
        sessionId,
        surfaceTouchTargetExpression,
    );
    assertPageAudit(socialMobileAudit, 'mobile social relationships');
    assert(
        socialSmallTouchTargets.length === 0,
        `mobile social relationships: controls below 44px ${JSON.stringify(socialSmallTouchTargets)}.`,
    );
    socialAudits['mobile social relationships'] = {
        ...socialMobileAudit,
        smallTouchTargets: socialSmallTouchTargets,
    };

    const socialMobileScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'social-relationships-mobile.png'),
        Buffer.from(socialMobileScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 320,
        height: 900,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 320,
        screenHeight: 900,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/circle/social`);
    const socialZoomAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(socialZoomAudit, '320px social relationships reflow');
    socialAudits['320px social relationships reflow'] = socialZoomAudit;

    await client.send('Network.clearBrowserCookies', {}, sessionId);
    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    }, sessionId);
    await login(client, sessionId, 'administrator@example.test');

    await navigate(client, sessionId, `${baseUrl}/profile/settings`);
    const profileSettingsAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(profileSettingsAudit, 'profile settings');
    originalProfileLocale = await evaluate(
        client,
        sessionId,
        `document.querySelector('#profile-settings-locale')?.value`,
    );
    await evaluate(client, sessionId, `(() => {
        const locale = document.querySelector('#profile-settings-locale');
        locale.value = 'lt';
        locale.dispatchEvent(new Event('input', { bubbles: true }));
        locale.dispatchEvent(new Event('change', { bubbles: true }));
        locale.blur();
        document.querySelector('form button[type="submit"]').click();
        return true;
    })()`);
    await waitUntil(
        async () => await evaluate(
            client,
            sessionId,
            `document.body.innerText.includes('Profilio nustatymai išsaugoti.')`,
        ),
        'Profile locale update did not complete in Lithuanian.',
    );
    const profileLocaleAudit = await evaluate(client, sessionId, `({
        documentLanguage: document.documentElement.lang,
        selectedLocale: document.querySelector('#profile-settings-locale')?.value,
        savedMessageVisible: document.body.innerText.includes('Profilio nustatymai išsaugoti.'),
    })`);
    assert(profileLocaleAudit.documentLanguage === 'lt', 'The document language did not change to Lithuanian.');
    assert(profileLocaleAudit.selectedLocale === 'lt', 'The Lithuanian profile locale was not selected.');
    assert(profileLocaleAudit.savedMessageVisible, 'The localized profile success message is missing.');

    const translationUrl = `${baseUrl}/knowledge/dog-travel-documents-lithuania-to-poland/translations/new`;
    await navigate(client, sessionId, translationUrl);
    const translationEditorAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(translationEditorAudit, 'knowledge translation editor');
    const translationBehavior = await evaluate(client, sessionId, `(() => {
        const language = document.querySelector('select[wire\\\\:model="form.language"]');
        const title = document.querySelector('input[wire\\\\:model="form.title"]');
        const summary = document.querySelector('textarea[wire\\\\:model="form.summary"]');
        const body = document.querySelector('textarea[wire\\\\:model="form.body"]');
        const rawKeyPattern = /\\b(?:auth|forum_categories|knowledge)\\.[a-z0-9_.-]+/gi;

        return {
            documentLanguage: document.documentElement.lang,
            sourceNoticeVisible: document.body.innerText.includes('Vertimo šaltinis'),
            sourceTitleVisible: document.body.innerText.includes(
                'Dog travel documents: Lithuania to Poland',
            ),
            originalPreservedNoticeVisible: document.body.innerText.includes(
                'Originalas lieka nepakeistas',
            ),
            targetLocale: language?.value,
            availableLocales: [...(language?.options ?? [])].map((option) => option.value),
            translatedProseStartsEmpty: [title, summary, body].every(
                (field) => field?.value === '',
            ),
            rawTranslationKeys: document.body.innerText.match(rawKeyPattern) ?? [],
        };
    })()`);
    assert(translationBehavior.documentLanguage === 'lt', 'Translation editor locale is not Lithuanian.');
    assert(translationBehavior.sourceNoticeVisible, 'The localized translation-source notice is missing.');
    assert(translationBehavior.sourceTitleVisible, 'The source guide title is not visible to the translator.');
    assert(
        translationBehavior.originalPreservedNoticeVisible,
        'The localized original-preservation notice is missing.',
    );
    assert(translationBehavior.targetLocale === 'lt', 'The first available target locale is not selected.');
    assert(
        !translationBehavior.availableLocales.includes('en'),
        'The source locale remains selectable as a translation target.',
    );
    assert(
        translationBehavior.translatedProseStartsEmpty,
        'Source prose was copied into the human translation draft.',
    );
    assert(
        translationBehavior.rawTranslationKeys.length === 0,
        `Raw translation keys are visible: ${translationBehavior.rawTranslationKeys.join(', ')}.`,
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);
    await navigate(client, sessionId, translationUrl);
    const translationMobileAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(translationMobileAudit, 'mobile knowledge translation editor');
    const translationScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'knowledge-translation-mobile.png'),
        Buffer.from(translationScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/forum/ask`);
    const editorAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(editorAudit, 'topic editor');
    const editorMedia = await evaluate(client, sessionId, `({
        mediaDescription: Boolean(document.querySelector('[data-forum-media-description]')),
        transcript: Boolean(document.querySelector('[data-forum-video-transcript]')),
        captions: Boolean(document.querySelector('[data-forum-caption]')),
    })`);
    assert(Object.values(editorMedia).every(Boolean), 'Accessible media authoring controls are incomplete.');

    const editorLayoutExpression = `(() => {
        const shell = document.querySelector('[data-forum-editor-shell]');
        const guidance = document.querySelector('[data-forum-publishing-guidance]');
        const form = document.querySelector('[data-forum-editor]');
        const sections = [...document.querySelectorAll('[data-forum-editor-section]')];
        const shellWidth = shell?.getBoundingClientRect().width ?? 0;
        const formWidth = form?.getBoundingClientRect().width ?? 0;
        const touchTargets = [...(shell?.querySelectorAll(
            'button, input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), '
            + 'select, textarea, .forum-form__checks label',
        ) ?? [])].filter((element) => {
            const style = getComputedStyle(element);
            const box = element.getBoundingClientRect();

            return style.display !== 'none' && style.visibility !== 'hidden'
                && box.width > 0 && box.height > 0;
        });

        return {
            shellCount: document.querySelectorAll('[data-forum-editor-shell]').length,
            guidanceBeforeForm: guidance?.nextElementSibling === form,
            guidanceItems: guidance?.querySelectorAll('ul > li').length ?? 0,
            sidebarCount: document.querySelectorAll('.forum-sidebar').length,
            sectionKeys: sections.map((section) => section.dataset.forumEditorSection),
            shellWidth,
            formWidth,
            guidanceColumns: guidance
                ? getComputedStyle(guidance).gridTemplateColumns.split(' ').length
                : 0,
            smallTargets: touchTargets.map((element) => ({
                label: element.getAttribute('aria-label')
                    || element.textContent.trim().slice(0, 60)
                    || element.getAttribute('name'),
                width: Math.round(element.getBoundingClientRect().width),
                height: Math.round(element.getBoundingClientRect().height),
            })).filter((target) => target.width < 44 || target.height < 44),
            rawTranslationKeys: document.body.innerText.match(/\\bforum\\.editor\\.[a-z0-9_.-]+/gi) ?? [],
        };
    })()`;
    const editorLayout = await evaluate(client, sessionId, editorLayoutExpression);
    assert(editorLayout.shellCount === 1, 'Topic editor does not render one unified shell.');
    assert(editorLayout.guidanceBeforeForm, 'Publishing guidance is not immediately above the topic form.');
    assert(editorLayout.guidanceItems === 5, 'Publishing guidance is incomplete.');
    assert(editorLayout.sidebarCount === 0, 'The legacy topic-editor sidebar is still rendered.');
    assert(
        JSON.stringify(editorLayout.sectionKeys) === JSON.stringify(['context', 'response', 'media']),
        'Topic editor sections are incomplete or out of order.',
    );
    assert(
        editorLayout.formWidth >= editorLayout.shellWidth - 2,
        'Topic form does not occupy the unified editor shell.',
    );
    assert(
        editorLayout.smallTargets.length === 0,
        `Topic editor has undersized targets: ${JSON.stringify(editorLayout.smallTargets)}.`,
    );
    assert(editorLayout.rawTranslationKeys.length === 0, 'Topic editor exposes raw translation keys.');
    const editorDesktopScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'forum-topic-editor-desktop.png'),
        Buffer.from(editorDesktopScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 375,
        height: 812,
        deviceScaleFactor: 1,
        mobile: true,
        screenWidth: 375,
        screenHeight: 812,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/forum/ask`);
    const editorMobileAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(editorMobileAudit, 'mobile topic editor');
    const editorMobileLayout = await evaluate(client, sessionId, editorLayoutExpression);
    assert(editorMobileLayout.guidanceBeforeForm, 'Mobile publishing guidance is not above the form.');
    assert(editorMobileLayout.guidanceColumns === 1, 'Mobile publishing guidance does not reflow to one column.');
    assert(
        editorMobileLayout.formWidth >= editorMobileLayout.shellWidth - 2,
        'Mobile topic form does not occupy the unified editor shell.',
    );
    assert(
        editorMobileLayout.smallTargets.length === 0,
        `Mobile topic editor has undersized targets: ${JSON.stringify(editorMobileLayout.smallTargets)}.`,
    );
    const editorMobileScreenshot = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'forum-topic-editor-mobile.png'),
        Buffer.from(editorMobileScreenshot.data, 'base64'),
    );

    await client.send('Emulation.setDeviceMetricsOverride', {
        width: 1440,
        height: 900,
        deviceScaleFactor: 1,
        mobile: false,
    }, sessionId);
    await navigate(client, sessionId, `${baseUrl}/forum/ask`);

    const reloaded = client.once('Page.loadEventFired', sessionId);
    await evaluate(client, sessionId, `(() => {
        const form = document.querySelector('[data-forum-editor]');
        form.noValidate = true;
        form.querySelector('button[type="submit"]').click();
        return true;
    })()`);
    await reloaded;
    await waitUntil(
        async () => await evaluate(client, sessionId, 'Boolean(document.querySelector("[data-forum-error-summary]"))'),
        'Validation summary did not render.',
    );
    const validationAudit = await evaluate(client, sessionId, `(() => {
        const summary = document.querySelector('[data-forum-error-summary]');
        const title = document.querySelector('[name="title"]');

        return {
            summaryFocused: document.activeElement === summary,
            summaryRole: summary?.getAttribute('role'),
            summaryLive: summary?.getAttribute('aria-live'),
            titleInvalid: title?.getAttribute('aria-invalid'),
            titleDescribedBy: title?.getAttribute('aria-describedby'),
            titleErrorLinked: Boolean(title?.getAttribute('aria-describedby')
                && document.getElementById(title.getAttribute('aria-describedby').split(/\\s+/).at(-1))),
        };
    })()`);
    assert(validationAudit.summaryFocused, 'Validation summary did not receive focus.');
    assert(validationAudit.summaryRole === 'alert' && validationAudit.summaryLive === 'assertive', 'Validation summary is not announced.');
    assert(validationAudit.titleInvalid === 'true' && validationAudit.titleErrorLinked, 'Invalid title is not associated with its error.');
    await evaluate(client, sessionId, `document.querySelector('[data-forum-error-summary]').remove(); true`);
    await waitUntil(
        async () => await evaluate(client, sessionId, 'document.querySelector(\'[name="title"]\').getAttribute("aria-invalid") === null'),
        'Corrected field retained stale generated error semantics.',
    );

    await navigate(client, sessionId, `${baseUrl}/admin/forum`);
    const adminAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(adminAudit, 'forum administration');
    const tableAudit = await evaluate(client, sessionId, `(() => [...document.querySelectorAll('table')].map(
        (table) => ({
            caption: table.querySelector('caption')?.textContent.trim() ?? '',
            scopedHeaders: table.querySelectorAll('th[scope="col"]').length,
        }),
    ))()`);
    assert(tableAudit.length > 0, 'Forum administration did not render its data tables.');
    assert(tableAudit.every((table) => table.caption && table.scopedHeaders > 0), 'An admin table is not semantically labeled.');

    await navigate(client, sessionId, `${baseUrl}/profile/settings`);
    await evaluate(client, sessionId, `(() => {
        const locale = document.querySelector('#profile-settings-locale');
        locale.value = 'ru';
        locale.dispatchEvent(new Event('input', { bubbles: true }));
        locale.dispatchEvent(new Event('change', { bubbles: true }));
        locale.blur();
        document.querySelector('form button[type="submit"]').click();
        return true;
    })()`);
    await waitUntil(
        async () => await evaluate(client, sessionId, `document.documentElement.lang === 'ru'`),
        'Profile locale update did not complete in Russian.',
    );
    await navigate(client, sessionId, `${baseUrl}/forum`);
    const russianForumAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(russianForumAudit, 'Russian forum');
    const russianBehavior = await evaluate(client, sessionId, `(() => ({
        documentLanguage: document.documentElement.lang,
        rawTranslationKeys: document.body.innerText.match(
            /\\b(?:auth|forum|forum_categories|knowledge)\\.[a-z0-9_.-]+/gi,
        ) ?? [],
    }))()`);
    assert(russianBehavior.documentLanguage === 'ru', 'The forum document language is not Russian.');
    assert(
        russianBehavior.rawTranslationKeys.length === 0,
        `Raw Russian translation keys are visible: ${russianBehavior.rawTranslationKeys.join(', ')}.`,
    );

    await navigate(client, sessionId, `${baseUrl}/content`);
    const russianContentAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(russianContentAudit, 'Russian content feed');
    const russianContentBehavior = await evaluate(client, sessionId, `(() => ({
        documentLanguage: document.documentElement.lang,
        rawTranslationKeys: document.body.innerText.match(
            /\b(?:content)\.[a-z0-9_.-]+/gi,
        ) ?? [],
    }))()`);
    assert(
        russianContentBehavior.documentLanguage === 'ru',
        'The content feed document language is not Russian.',
    );
    assert(
        russianContentBehavior.rawTranslationKeys.length === 0,
        `Raw Russian content keys are visible: ${russianContentBehavior.rawTranslationKeys.join(', ')}.`,
    );

    await navigate(client, sessionId, `${baseUrl}/circle/social`);
    const russianSocialAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(russianSocialAudit, 'Russian social relationships');
    const russianSocialBehavior = await evaluate(client, sessionId, `(() => ({
        documentLanguage: document.documentElement.lang,
        rawTranslationKeys: document.body.innerText.match(
            /\b(?:social_relationships)\.[a-z0-9_.-]+/gi,
        ) ?? [],
    }))()`);
    assert(
        russianSocialBehavior.documentLanguage === 'ru',
        'The social relationships document language is not Russian.',
    );
    assert(
        russianSocialBehavior.rawTranslationKeys.length === 0,
        `Raw Russian social keys are visible: ${russianSocialBehavior.rawTranslationKeys.join(', ')}.`,
    );

    await navigate(client, sessionId, `${baseUrl}/forum/groups/apartment-pets`);
    const russianCommunityAudit = await evaluate(client, sessionId, pageAuditExpression);
    assertPageAudit(russianCommunityAudit, 'Russian community workspace');
    const russianCommunityBehavior = await evaluate(client, sessionId, `(() => ({
        documentLanguage: document.documentElement.lang,
        actorOptionCount: [...document.querySelectorAll('select')]
            .find((element) => element.getAttribute('wire:model.live') === 'selectedActorKey')
            ?.options.length ?? 0,
        rawTranslationKeys: document.body.innerText.match(
            /\\b(?:forum_groups)\\.[a-z0-9_.-]+/gi,
        ) ?? [],
    }))()`);
    assert(
        russianCommunityBehavior.documentLanguage === 'ru',
        'The community workspace document language is not Russian.',
    );
    assert(
        russianCommunityBehavior.actorOptionCount >= 1,
        'The Russian community profile selector is missing.',
    );
    assert(
        russianCommunityBehavior.rawTranslationKeys.length === 0,
        `Raw Russian community keys are visible: ${russianCommunityBehavior.rawTranslationKeys.join(', ')}.`,
    );

    await navigate(client, sessionId, `${baseUrl}/profile/settings`);
    await evaluate(client, sessionId, `((originalLocale) => {
        const locale = document.querySelector('#profile-settings-locale');
        locale.value = originalLocale;
        locale.dispatchEvent(new Event('input', { bubbles: true }));
        locale.dispatchEvent(new Event('change', { bubbles: true }));
        locale.blur();
        document.querySelector('form button[type="submit"]').click();
        return true;
    })(${JSON.stringify(originalProfileLocale)})`);
    await waitUntil(
        async () => await evaluate(
            client,
            sessionId,
            `document.documentElement.lang === ${JSON.stringify(originalProfileLocale)}`,
        ),
        'The browser check did not restore the original profile locale.',
    );

    assert(consoleErrors.length === 0, `Browser console errors: ${consoleErrors.join(' | ')}`);

    const report = {
        baseUrl,
        checkedAt: new Date().toISOString(),
        accountEntryAudits,
        accountEntrySkipFocus,
        desktopAudit,
        mobileAudit,
        zoomAudit,
        oneHealthAudits,
        groupCardAudits,
        eventAudits,
        placeAudits,
        organizationAudits,
        contentAudits,
        petAudits,
        medicalAudits,
        medicalBehavior,
        socialAudits,
        communityAudits,
        communityProfileBehavior,
        skipFocus,
        profileSettingsAudit,
        originalProfileLocale,
        profileLocaleAudit,
        translationEditorAudit,
        translationBehavior,
        translationMobileAudit,
        editorAudit,
        editorMedia,
        editorLayout,
        editorMobileAudit,
        editorMobileLayout,
        validationAudit,
        adminTables: tableAudit,
        russianForumAudit,
        russianBehavior,
        russianContentAudit,
        russianContentBehavior,
        russianSocialAudit,
        russianSocialBehavior,
        russianCommunityAudit,
        russianCommunityBehavior,
        consoleErrors,
        screenshots: [
            join(outputDirectory, 'account-entry-desktop.png'),
            join(outputDirectory, 'account-entry-mobile.png'),
            join(outputDirectory, 'forum-desktop.png'),
            join(outputDirectory, 'forum-mobile.png'),
            join(outputDirectory, 'forum-one-health-desktop.png'),
            join(outputDirectory, 'forum-one-health-mobile.png'),
            join(outputDirectory, 'group-directory-desktop.png'),
            join(outputDirectory, 'group-directory-mobile.png'),
            join(outputDirectory, 'group-directory-lt-mobile.png'),
            join(outputDirectory, 'event-directory-desktop.png'),
            join(outputDirectory, 'event-directory-mobile.png'),
            join(outputDirectory, 'event-detail-desktop.png'),
            join(outputDirectory, 'event-detail-mobile.png'),
            join(outputDirectory, 'event-schedule-desktop.png'),
            join(outputDirectory, 'event-schedule-mobile.png'),
            join(outputDirectory, 'place-directory-desktop.png'),
            join(outputDirectory, 'place-directory-mobile.png'),
            join(outputDirectory, 'place-detail-desktop.png'),
            join(outputDirectory, 'place-detail-mobile.png'),
            join(outputDirectory, 'organization-directory-desktop.png'),
            join(outputDirectory, 'organization-directory-mobile.png'),
            join(outputDirectory, 'organization-workspace-desktop.png'),
            join(outputDirectory, 'organization-workspace-mobile.png'),
            join(outputDirectory, 'content-feed-desktop.png'),
            join(outputDirectory, 'content-feed-mobile.png'),
            join(outputDirectory, 'pet-profile-manage-desktop.png'),
            join(outputDirectory, 'pet-profile-manage-mobile.png'),
            join(outputDirectory, 'medical-emergency-desktop.png'),
            join(outputDirectory, 'medical-emergency-mobile.png'),
            join(outputDirectory, 'social-relationships-desktop.png'),
            join(outputDirectory, 'social-relationships-mobile.png'),
            join(outputDirectory, 'community-workspace-desktop.png'),
            join(outputDirectory, 'community-workspace-mobile.png'),
            join(outputDirectory, 'knowledge-translation-mobile.png'),
            join(outputDirectory, 'forum-topic-editor-desktop.png'),
            join(outputDirectory, 'forum-topic-editor-mobile.png'),
        ],
    };

    await writeFile(
        join(outputDirectory, 'report.json'),
        `${JSON.stringify(report, null, 2)}\n`,
    );
    console.log(JSON.stringify(report, null, 2));
    }
} finally {
    if (client && sessionId && originalProfileLocale) {
        try {
            await navigate(client, sessionId, `${baseUrl}/profile/settings`);
            const currentLocale = await evaluate(
                client,
                sessionId,
                `document.querySelector('#profile-settings-locale')?.value`,
            );

            if (currentLocale !== originalProfileLocale) {
                await evaluate(client, sessionId, `((localeValue) => {
                    const locale = document.querySelector('#profile-settings-locale');
                    locale.value = localeValue;
                    locale.dispatchEvent(new Event('input', { bubbles: true }));
                    locale.dispatchEvent(new Event('change', { bubbles: true }));
                    locale.blur();
                    document.querySelector('form button[type="submit"]').click();
                    return true;
                })(${JSON.stringify(originalProfileLocale)})`);
                await waitUntil(
                    async () => await evaluate(
                        client,
                        sessionId,
                        `document.documentElement.lang === ${JSON.stringify(originalProfileLocale)}`,
                    ),
                    'The browser cleanup did not restore the original profile locale.',
                );
            }
        } catch {
            // Preserve the original audit result when best-effort cleanup cannot reconnect.
        }
    }

    client?.close();
    const browserExit = new Promise((resolve) => {
        if (browser.exitCode !== null || browser.signalCode !== null) {
            resolve();

            return;
        }

        browser.once('exit', resolve);
    });
    browser.kill('SIGTERM');
    await Promise.race([browserExit, delay(5_000)]);
    await rm(profileDirectory, {
        recursive: true,
        force: true,
        maxRetries: 5,
        retryDelay: 100,
    });
}
