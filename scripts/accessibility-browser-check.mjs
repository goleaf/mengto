import { spawn } from 'node:child_process';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const baseUrl = (process.env.BROWSER_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const browserOrigin = new URL(baseUrl);
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

    const accountEntryAudits = {};
    const accountEntryViewports = [
        { label: 'English 320px account entry', locale: 'en', width: 320, height: 900, mobile: true },
        { label: 'Russian 375px account entry', locale: 'ru', width: 375, height: 812, mobile: true },
        { label: 'Lithuanian 768px account entry', locale: 'lt', width: 768, height: 1024, mobile: false },
        { label: 'Russian 1024px account entry', locale: 'ru', width: 1024, height: 900, mobile: false },
        { label: 'English 1440px account entry', locale: 'en', width: 1440, height: 900, mobile: false },
        { label: 'English 1920px account entry', locale: 'en', width: 1920, height: 1080, mobile: false },
    ];

    await navigate(client, sessionId, `${baseUrl}/`);

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
        await navigate(client, sessionId, `${baseUrl}/`);

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
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        const behavior = await evaluate(client, sessionId, `(() => ({
            eventSurfaceCount: document.querySelectorAll('[data-section="event-directory"], [data-section="event-workspace"]').length,
            rawTranslationKeys: document.body.innerText.match(/\\bforum_events\\.[a-z0-9_.-]+/gi) ?? [],
            privateLocationLeak: document.body.innerText.includes('Approved participant meeting point'),
        }))()`);
        assert(behavior.eventSurfaceCount === 1, `${label}: canonical event surface marker is missing.`);
        assert(
            behavior.rawTranslationKeys.length === 0,
            `${label}: raw event keys are visible: ${behavior.rawTranslationKeys.join(', ')}.`,
        );
        if (path === '/meetups') {
            assert(! behavior.privateLocationLeak, `${label}: an exact private location leaked into the directory.`);
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
    ]) {
        await navigate(client, sessionId, `${baseUrl}${path}`);
        const audit = await evaluate(client, sessionId, pageAuditExpression);
        assertPageAudit(audit, label);
        const smallTargets = await evaluate(client, sessionId, surfaceTouchTargetExpression);
        assert(smallTargets.length === 0, `${label}: controls below 44px ${JSON.stringify(smallTargets)}.`);
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
        eventAudits,
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
            join(outputDirectory, 'event-directory-desktop.png'),
            join(outputDirectory, 'event-directory-mobile.png'),
            join(outputDirectory, 'event-detail-desktop.png'),
            join(outputDirectory, 'event-detail-mobile.png'),
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
        ],
    };

    await writeFile(
        join(outputDirectory, 'report.json'),
        `${JSON.stringify(report, null, 2)}\n`,
    );
    console.log(JSON.stringify(report, null, 2));
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
    browser.kill('SIGTERM');
    await rm(profileDirectory, { recursive: true, force: true });
}
