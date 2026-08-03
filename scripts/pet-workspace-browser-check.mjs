import { spawn } from 'node:child_process';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const baseUrl = (process.env.BROWSER_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const outputDirectory = process.env.BROWSER_OUTPUT_DIR ?? join(tmpdir(), 'mengto-pet-workspace-browser');
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
    if (result.exceptionDetails) throw new Error(result.exceptionDetails.text);
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

let client;
let sessionId;
let originalLocale = 'en';

try {
    const [port, browserPath] = (await waitForFile(join(profileDirectory, 'DevToolsActivePort'))).trim().split(/\r?\n/);
    client = await CdpClient.connect(`ws://127.0.0.1:${port}${browserPath}`);
    const { targetId } = await client.send('Target.createTarget', { url: 'about:blank' });
    ({ sessionId } = await client.send('Target.attachToTarget', { targetId, flatten: true }));
    const consoleErrors = [];
    client.on('Runtime.exceptionThrown', ({ exceptionDetails }) => consoleErrors.push(exceptionDetails.text), sessionId);
    client.on('Log.entryAdded', ({ entry }) => {
        if (entry.level === 'error') consoleErrors.push(entry.text);
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

    assert(consoleErrors.length === 0, `Console errors: ${JSON.stringify(consoleErrors)}.`);
    const report = { baseUrl, outputDirectory, audits, consoleErrors };
    await writeFile(join(outputDirectory, 'report.json'), `${JSON.stringify(report, null, 2)}\n`);
    process.stdout.write(`${JSON.stringify(report, null, 2)}\n`);
} finally {
    if (client && sessionId) {
        try { await setLocale(client, sessionId, originalLocale); } catch { /* Preserve the original failure. */ }
    }
    client?.close();
    browser.kill('SIGTERM');
    await rm(profileDirectory, { recursive: true, force: true });
}
