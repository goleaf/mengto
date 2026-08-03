import { spawn } from 'node:child_process';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const baseUrl = (process.env.BROWSER_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const origin = new URL(baseUrl);
const outputDirectory = process.env.BROWSER_OUTPUT_DIR
    ?? join(tmpdir(), 'mengto-discovery-browser');
const chromeCandidates = [
    process.env.CHROME_BIN,
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/usr/bin/google-chrome',
    '/usr/bin/google-chrome-stable',
    '/usr/bin/chromium',
].filter(Boolean);

if (!['localhost', '127.0.0.1', '::1'].includes(origin.hostname)) {
    throw new Error('The discovery browser check only runs against a loopback URL.');
}

const assert = (condition, message) => {
    if (!condition) {
        throw new Error(message);
    }
};

const findChrome = async () => {
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

    while (Date.now() < deadline) {
        if (await callback()) {
            return;
        }

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
            input.blur();
        };

        setValue('#login-email', 'mia@example.test');
        setValue('#login-password', 'password');
        document.querySelector('[data-auth-page="login"] .auth-button--primary').click();
        return true;
    })()`);
    await waitUntil(
        async () => !(await evaluate(client, sessionId, 'location.pathname')).includes('/login'),
        'Login did not complete.',
    );
};

const setViewport = async (client, sessionId, width, height, mobile) => {
    await client.send('Emulation.setDeviceMetricsOverride', {
        width,
        height,
        deviceScaleFactor: 1,
        mobile,
        screenWidth: width,
        screenHeight: height,
    }, sessionId);
    await client.send('Emulation.setTouchEmulationEnabled', {
        enabled: mobile,
        ...(mobile ? { maxTouchPoints: 5 } : {}),
    }, sessionId);
};

const setLocale = async (client, sessionId, locale) => {
    await navigate(client, sessionId, `${baseUrl}/profile/settings`);
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

const auditExpression = `(() => {
    const visible = (element) => {
        const style = getComputedStyle(element);
        const box = element.getBoundingClientRect();

        return style.display !== 'none' && style.visibility !== 'hidden'
            && box.width > 0 && box.height > 0;
    };
    const controls = [...document.querySelectorAll(
        'main button, main input:not([type="hidden"]), main select, main textarea, main [role="button"]'
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
    const resultCards = [...document.querySelectorAll('[data-discover-result]')];

    return {
        url: location.href,
        language: document.documentElement.lang,
        title: document.title,
        h1Count: document.querySelectorAll('main h1').length,
        mainCount: document.querySelectorAll('main').length,
        overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        unnamedControls: unnamedControls.map((element) => element.outerHTML.slice(0, 180)),
        duplicateIds: [...new Set(duplicateIds)],
        invalidImageCount: [...document.images].filter((image) => !image.hasAttribute('alt')).length,
        brokenImageCount: [...document.images]
            .filter((image) => image.complete && image.naturalWidth === 0).length,
        directionCount: document.querySelectorAll('[data-section="discover-directions"] .discovery-direction').length,
        sectionCount: document.querySelectorAll('[data-discovery-section]').length,
        resultCount: resultCards.length,
        reasonCount: resultCards.filter((card) => card.querySelector('.discovery-result-card__reason')).length,
        oldPromoCount: [...document.querySelectorAll('main *')].filter((element) => (
            element.children.length === 0
            && /Local pulse|Trending nearby|Calm weekend walks/i.test(element.textContent)
        )).length,
        rawTranslationKeys: document.body.innerText.match(/\\bdiscovery\\.[a-z0-9_.-]+/gi) ?? [],
        privateLocationLeak: document.body.innerText.includes('Private gate code')
            || document.body.innerText.includes('Private service entrance'),
        smallActionTargets: [...document.querySelectorAll(
            'main .action, main .filter-chip, main input:not([type="hidden"])'
        )].filter(visible).map((element) => ({
            label: element.getAttribute('aria-label') || element.textContent.trim().slice(0, 60),
            width: Math.round(element.getBoundingClientRect().width),
            height: Math.round(element.getBoundingClientRect().height),
        })).filter((target) => target.width < 44 || target.height < 44),
    };
})()`;

const assertAudit = (audit, label, mobile) => {
    const expectedLanguage = label === 'lithuanian-320' ? 'lt' : 'en';

    assert(audit.language === expectedLanguage, `${label}: expected ${expectedLanguage}, found ${audit.language}.`);
    assert(audit.h1Count === 1, `${label}: expected one h1, found ${audit.h1Count}.`);
    assert(audit.mainCount === 1, `${label}: expected one main, found ${audit.mainCount}.`);
    assert(audit.overflow <= 1, `${label}: horizontal overflow is ${audit.overflow}px.`);
    assert(audit.unnamedControls.length === 0, `${label}: unnamed controls ${JSON.stringify(audit.unnamedControls)}.`);
    assert(audit.duplicateIds.length === 0, `${label}: duplicate ids ${audit.duplicateIds.join(', ')}.`);
    assert(audit.invalidImageCount === 0, `${label}: ${audit.invalidImageCount} image(s) lack alt text.`);
    assert(audit.brokenImageCount === 0, `${label}: ${audit.brokenImageCount} image(s) failed to load.`);
    assert(audit.directionCount === 5, `${label}: expected five discovery directions.`);
    assert(audit.sectionCount > 0, `${label}: discovery sections are missing.`);
    assert(audit.resultCount > 0, `${label}: seeded recommendations are missing.`);
    assert(audit.reasonCount === audit.resultCount, `${label}: every recommendation needs a reason.`);
    assert(audit.oldPromoCount === 0, `${label}: obsolete promotional discovery content remains.`);
    assert(audit.rawTranslationKeys.length === 0, `${label}: raw translation keys are visible.`);
    assert(!audit.privateLocationLeak, `${label}: private location content leaked.`);

    if (mobile) {
        assert(
            audit.smallActionTargets.length === 0,
            `${label}: action targets below 44px ${JSON.stringify(audit.smallActionTargets)}.`,
        );
    }
};

await mkdir(outputDirectory, { recursive: true });
const profileDirectory = await mkdtemp(join(tmpdir(), 'mengto-discovery-chrome-'));
const browser = spawn(await findChrome(), [
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

let client;
let sessionId;
let originalLocale;

try {
    const activePort = await waitForFile(join(profileDirectory, 'DevToolsActivePort'));
    const [port, browserPath] = activePort.trim().split(/\r?\n/);
    client = await CdpClient.connect(`ws://127.0.0.1:${port}${browserPath}`);
    const { targetId } = await client.send('Target.createTarget', { url: 'about:blank' });
    ({ sessionId } = await client.send('Target.attachToTarget', { targetId, flatten: true }));
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
    await client.send('Emulation.setEmulatedMedia', {
        features: [{ name: 'prefers-reduced-motion', value: 'reduce' }],
    }, sessionId);
    await setViewport(client, sessionId, 1440, 900, false);
    await login(client, sessionId);
    originalLocale = await evaluate(client, sessionId, 'document.documentElement.lang');
    const audits = {};

    for (const viewport of [
        { label: 'desktop', width: 1440, height: 900, mobile: false, locale: originalLocale },
        { label: 'mobile', width: 375, height: 812, mobile: true, locale: originalLocale },
        { label: 'lithuanian-320', width: 320, height: 900, mobile: true, locale: 'lt' },
    ]) {
        await setViewport(client, sessionId, viewport.width, viewport.height, viewport.mobile);
        await navigate(client, sessionId, `${baseUrl}/discover`);

        if (await evaluate(client, sessionId, 'document.documentElement.lang') !== viewport.locale) {
            await setLocale(client, sessionId, viewport.locale);
            await navigate(client, sessionId, `${baseUrl}/discover`);
        }

        await evaluate(client, sessionId, `(async () => {
            const cards = [...document.querySelectorAll('[data-discover-result]')];

            for (const card of cards) {
                card.scrollIntoView({ block: 'center' });
                await new Promise((resolve) => setTimeout(resolve, 80));
            }

            const images = [...document.querySelectorAll('[data-discover-result] img')];
            await Promise.all(images.map((image) => image.complete
                ? Promise.resolve()
                : new Promise((resolve) => {
                    image.addEventListener('load', resolve, { once: true });
                    image.addEventListener('error', resolve, { once: true });
                    setTimeout(resolve, 5000);
                })));
            scrollTo({ top: 0, behavior: 'instant' });
            await new Promise((resolve) => setTimeout(resolve, 200));
            return true;
        })()`);

        const audit = await evaluate(client, sessionId, auditExpression);
        assertAudit(audit, viewport.label, viewport.mobile);
        audits[viewport.label] = audit;

        const screenshot = await client.send('Page.captureScreenshot', {
            format: 'png',
            captureBeyondViewport: true,
        }, sessionId);
        await writeFile(
            join(outputDirectory, `discover-${viewport.label}.png`),
            Buffer.from(screenshot.data, 'base64'),
        );
    }

    if (await evaluate(client, sessionId, 'document.documentElement.lang') !== originalLocale) {
        await setLocale(client, sessionId, originalLocale);
    }

    assert(consoleErrors.length === 0, `Console errors: ${JSON.stringify(consoleErrors)}.`);
    const report = { baseUrl, outputDirectory, audits, consoleErrors };
    await writeFile(join(outputDirectory, 'report.json'), `${JSON.stringify(report, null, 2)}\n`);
    process.stdout.write(`${JSON.stringify(report, null, 2)}\n`);
} finally {
    if (client && sessionId && originalLocale) {
        try {
            await navigate(client, sessionId, `${baseUrl}/profile/settings`);
            const currentLocale = await evaluate(
                client,
                sessionId,
                `document.querySelector('#profile-settings-locale')?.value`,
            );

            if (currentLocale !== originalLocale) {
                await setLocale(client, sessionId, originalLocale);
            }
        } catch {
            // Cleanup remains best effort so the original browser failure is preserved.
        }
    }

    client?.close();
    browser.kill('SIGTERM');
    await rm(profileDirectory, { recursive: true, force: true });
}
