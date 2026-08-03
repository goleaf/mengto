import { spawn } from 'node:child_process';
import { access, mkdir, mkdtemp, readFile, rm, writeFile } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const baseUrl = (process.env.BROWSER_BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '');
const origin = new URL(baseUrl);
const outputDirectory = process.env.BROWSER_OUTPUT_DIR
    ?? join(tmpdir(), 'mengto-pet-duplicate-access-browser');
const candidateKey = process.env.BROWSER_PET_PROFILE_KEY ?? 'pet-browser-duplicate';
const privateCandidateKey = process.env.BROWSER_PRIVATE_PET_PROFILE_KEY ?? 'pet-browser-private';
const memberPassword = process.env.BROWSER_MEMBER_PASSWORD ?? 'password';
const managerPassword = process.env.BROWSER_MANAGER_PASSWORD ?? 'password';
const chromeCandidates = [
    process.env.CHROME_BIN,
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '/usr/bin/google-chrome',
    '/usr/bin/chromium',
].filter(Boolean);

if (! ['localhost', '127.0.0.1', '::1'].includes(origin.hostname)) {
    throw new Error('The pet duplicate browser check only runs against a loopback URL.');
}

const assert = (condition, message) => {
    if (! condition) throw new Error(message);
};

const chromeExecutable = async () => {
    for (const candidate of chromeCandidates) {
        try {
            await access(candidate);

            return candidate;
        } catch {
            // Continue through the platform-specific candidates.
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
        throw new Error(result.exceptionDetails.exception?.description ?? result.exceptionDetails.text);
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

const login = async (client, sessionId, email, password) => {
    await navigate(client, sessionId, `${baseUrl}/login`);
    await evaluate(client, sessionId, `((email, password) => {
        const setValue = (selector, value) => {
            const input = document.querySelector(selector);
            const setter = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set;
            setter.call(input, value);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        setValue('#login-email', email);
        setValue('#login-password', password);
    })(${JSON.stringify(email)}, ${JSON.stringify(password)})`);
    await delay(300);
    await evaluate(client, sessionId, `(() => {
        document.querySelector('[data-auth-page="login"] .auth-button--primary').click();
    })()`);
    await waitUntil(
        async () => ! (await evaluate(client, sessionId, 'location.pathname')).includes('/login'),
        `Login did not complete for ${email}.`,
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
    await client.send('Emulation.setTouchEmulationEnabled', { enabled: mobile }, sessionId);
};

const auditExpression = (scope) => `(() => {
    const scope = document.querySelector(${JSON.stringify(scope)});
    const visible = (element) => {
        const style = getComputedStyle(element);
        const box = element.getBoundingClientRect();
        return style.display !== 'none' && style.visibility !== 'hidden'
            && box.width > 0 && box.height > 0;
    };
    const controls = [...(scope?.querySelectorAll('a, button, input, select, textarea') ?? [])]
        .filter(visible);
    const ids = [...document.querySelectorAll('[id]')].map((element) => element.id).filter(Boolean);
    const firstButton = scope?.querySelector('button');
    const before = firstButton ? getComputedStyle(firstButton) : null;
    const borderBefore = before?.borderColor ?? null;
    firstButton?.focus();
    const focused = firstButton ? getComputedStyle(firstButton) : null;

    return {
        h1Count: document.querySelectorAll('main h1').length,
        scopeCount: document.querySelectorAll(${JSON.stringify(scope)}).length,
        overflow: document.documentElement.scrollWidth - document.documentElement.clientWidth,
        unnamed: controls.filter((element) => !(
            element.getAttribute('aria-label') || element.getAttribute('aria-labelledby')
            || element.labels?.length || element.textContent.trim() || element.title
        )).length,
        smallTargets: controls.filter((element) => {
            const box = element.getBoundingClientRect();
            return box.width < 44 || box.height < 44;
        }).map((element) => ({
            tag: element.tagName,
            text: element.textContent.trim().slice(0, 60),
            width: Math.round(element.getBoundingClientRect().width),
            height: Math.round(element.getBoundingClientRect().height),
        })),
        duplicateIds: [...new Set(ids.filter((id, index) => ids.indexOf(id) !== index))],
        rawKeys: document.body.innerText.match(/\\bpet_profiles\\.[a-z0-9_.-]+/gi) ?? [],
        focusVisible: ! firstButton || Boolean(
            focused && (
                (focused.outlineStyle !== 'none' && Number.parseFloat(focused.outlineWidth) > 0)
                || focused.boxShadow !== 'none'
                || focused.borderColor !== borderBefore
            )
        ),
    };
})()`;

const assertAudit = (audit, label, mobile) => {
    assert(audit.h1Count === 1, `${label}: expected one main heading.`);
    assert(audit.scopeCount === 1, `${label}: expected one feature scope.`);
    assert(audit.overflow <= 1, `${label}: horizontal overflow is ${audit.overflow}px.`);
    assert(audit.unnamed === 0, `${label}: unnamed controls remain.`);
    assert(audit.duplicateIds.length === 0, `${label}: duplicate IDs remain.`);
    assert(audit.rawKeys.length === 0, `${label}: raw translation keys are visible.`);
    assert(audit.focusVisible, `${label}: keyboard focus is not visible.`);
    if (mobile) assert(audit.smallTargets.length === 0, `${label}: controls below 44px ${JSON.stringify(audit.smallTargets)}.`);
};

await mkdir(outputDirectory, { recursive: true });
const profileDirectory = await mkdtemp(join(tmpdir(), 'mengto-pet-duplicate-chrome-'));
const browser = spawn(await chromeExecutable(), [
    '--headless=new',
    '--disable-background-networking',
    '--disable-default-apps',
    '--disable-extensions',
    '--disable-gpu',
    '--hide-scrollbars',
    '--no-first-run',
    '--remote-debugging-port=0',
    `--user-data-dir=${profileDirectory}`,
    'about:blank',
], { stdio: ['ignore', 'ignore', 'pipe'] });
const browserExited = new Promise((resolve) => browser.once('exit', resolve));

let client;
let sessionId;

try {
    const [port, browserPath] = (await waitForFile(join(profileDirectory, 'DevToolsActivePort')))
        .trim().split(/\r?\n/);
    client = await CdpClient.connect(`ws://127.0.0.1:${port}${browserPath}`);
    const { targetId } = await client.send('Target.createTarget', { url: 'about:blank' });
    ({ sessionId } = await client.send('Target.attachToTarget', { targetId, flatten: true }));
    const consoleErrors = [];
    client.on('Runtime.exceptionThrown', ({ exceptionDetails }) => {
        consoleErrors.push(exceptionDetails.text);
    }, sessionId);
    client.on('Log.entryAdded', ({ entry }) => {
        if (entry.level === 'error') consoleErrors.push(entry.text);
    }, sessionId);
    await Promise.all([
        client.send('Page.enable', {}, sessionId),
        client.send('Runtime.enable', {}, sessionId),
        client.send('Log.enable', {}, sessionId),
    ]);
    await client.send('Emulation.setEmulatedMedia', {
        features: [{ name: 'prefers-reduced-motion', value: 'reduce' }],
    }, sessionId);

    await setViewport(client, sessionId, 1440, 900, false);
    await login(client, sessionId, 'mia@example.test', memberPassword);
    await navigate(client, sessionId, `${baseUrl}/pets/manage/new`);
    await evaluate(client, sessionId, `(() => {
        const setValue = (selector, value, prototype) => {
            const input = document.querySelector(selector);
            const setter = Object.getOwnPropertyDescriptor(prototype, 'value').set;
            setter.call(input, value);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        };
        setValue('#pet-profile-name', 'Browser Match', HTMLInputElement.prototype);
        setValue('#pet-profile-species', 'dog', HTMLSelectElement.prototype);
        setValue('#pet-profile-relationship', 'primary-owner', HTMLSelectElement.prototype);
        setValue('#pet-profile-visibility', 'private', HTMLSelectElement.prototype);
    })()`);
    await waitUntil(
        async () => await evaluate(client, sessionId, `(() => {
            const input = document.querySelector('#pet-profile-species-confidence');

            return input && [...input.options].some((option) => option.value === 'possible');
        })()`),
        'Possible dog identification did not become available.',
    );
    const confidenceBehavior = await evaluate(client, sessionId, `(() => {
        const setValue = (selector, value, prototype) => {
            const field = document.querySelector(selector);
            const fieldSetter = Object.getOwnPropertyDescriptor(prototype, 'value').set;
            fieldSetter.call(field, value);
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        };
        const input = document.querySelector('#pet-profile-species-confidence');
        const values = [...input.options].map((option) => option.value);
        const setter = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, 'value').set;
        setValue('#pet-profile-name', 'Browser Match', HTMLInputElement.prototype);
        setValue('#pet-profile-relationship', 'primary-owner', HTMLSelectElement.prototype);
        setValue('#pet-profile-visibility', 'private', HTMLSelectElement.prototype);
        setter.call(input, 'possible');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));

        return {
            controlCount: document.querySelectorAll('#pet-profile-species-confidence').length,
            values,
            described: input.getAttribute('aria-describedby')
                === 'pet-profile-species-confidence-help pet-profile-species-confidence-error',
        };
    })()`);
    assert(confidenceBehavior.controlCount === 1, 'The species confidence control is missing.');
    assert(confidenceBehavior.values.includes('confirmed'), 'Confirmed species choice is missing.');
    assert(confidenceBehavior.values.includes('possible'), 'Possible species choice is missing.');
    assert(confidenceBehavior.described, 'Species confidence help and errors are not associated.');
    await delay(300);
    await evaluate(client, sessionId, `(() => {
        document.querySelector('[data-section="pet-profile-create"] form').requestSubmit();
    })()`);
    await waitUntil(
        async () => await evaluate(client, sessionId, 'Boolean(document.querySelector("#pet-duplicate-review-heading"))'),
        'The safe duplicate review did not appear.',
    );
    const duplicateBehavior = await evaluate(client, sessionId, `(() => ({
        candidateCount: document.querySelectorAll('[wire\\\\:key^="duplicate-candidate-"]').length,
        candidateVisible: document.body.innerText.includes('Browser Match'),
        thisIsMyPetVisible: Boolean(document.querySelector('button[wire\\\\:click^="startAccessRequest"]')),
        differentAnimalVisible: Boolean(document.querySelector('button[wire\\\\:click="confirmDifferentAnimal"]')),
        privateCandidateLeaked: document.documentElement.innerHTML.includes(${JSON.stringify(privateCandidateKey)}),
        credentialLeak: /administrator@example\\.test|password/i.test(document.querySelector('#pet-duplicate-review-heading').parentElement.parentElement.innerText),
    }))()`);
    assert(duplicateBehavior.candidateCount === 1, 'The duplicate review did not show exactly one safe candidate.');
    assert(duplicateBehavior.candidateVisible, 'The safe candidate identity is missing.');
    assert(duplicateBehavior.thisIsMyPetVisible, 'The existing-profile action is missing.');
    assert(duplicateBehavior.differentAnimalVisible, 'The different-animal action is missing.');
    assert(! duplicateBehavior.privateCandidateLeaked, 'A private candidate key leaked into the page.');
    assert(! duplicateBehavior.credentialLeak, 'Credential-like data leaked into the safe candidate block.');
    const duplicateDesktopAudit = await evaluate(
        client,
        sessionId,
        auditExpression('#pet-duplicate-review-heading'),
    );
    assertAudit(duplicateDesktopAudit, 'desktop duplicate review heading', false);
    const duplicateDesktop = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'pet-duplicate-review-desktop.png'),
        Buffer.from(duplicateDesktop.data, 'base64'),
    );

    await evaluate(client, sessionId, `(() => {
        document.querySelector('button[wire\\\\:click^="startAccessRequest"]').click();
    })()`);
    await waitUntil(
        async () => await evaluate(client, sessionId, 'Boolean(document.querySelector("#pet-access-request-evidence"))'),
        'The access request form did not open.',
    );
    await evaluate(client, sessionId, `(() => {
        const input = document.querySelector('#pet-access-request-evidence');
        const setter = Object.getOwnPropertyDescriptor(HTMLTextAreaElement.prototype, 'value').set;
        setter.call(input, 'I share daily care and can provide the registration privately.');
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    })()`);
    await delay(300);
    await waitUntil(
        async () => await evaluate(client, sessionId, `(() => {
            const button = document.querySelector('button[wire\\\\:click="submitSelectedAccessRequest"]');

            return Boolean(button && ! button.disabled);
        })()`),
        'The access request action did not become available.',
    );
    await evaluate(client, sessionId, `(() => {
        document.querySelector('button[wire\\\\:click="submitSelectedAccessRequest"]').click();
    })()`);
    await delay(1_000);
    const requestOutcome = await evaluate(client, sessionId, `(() => ({
        submitted: document.body.innerText.includes('Your access request was sent for private review.'),
        errors: [...document.querySelectorAll('[role="alert"]')]
            .map((element) => element.textContent.trim())
            .filter(Boolean),
        evidenceValue: document.querySelector('#pet-access-request-evidence')?.value ?? null,
    }))()`);
    assert(
        requestOutcome.submitted,
        `The browser access request was not submitted: ${JSON.stringify(requestOutcome)}.`,
    );

    await setViewport(client, sessionId, 375, 812, true);
    const duplicateMobileAudit = await evaluate(
        client,
        sessionId,
        auditExpression('[data-section="pet-profile-create"]'),
    );
    assertAudit(duplicateMobileAudit, 'mobile duplicate review', true);
    const duplicateMobile = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'pet-duplicate-review-mobile.png'),
        Buffer.from(duplicateMobile.data, 'base64'),
    );

    await client.send('Network.clearBrowserCookies', {}, sessionId);
    await setViewport(client, sessionId, 1440, 900, false);
    await login(client, sessionId, 'administrator@example.test', managerPassword);
    await navigate(client, sessionId, `${baseUrl}/pets/manage/${candidateKey}/access-requests`);
    const managerBehavior = await evaluate(client, sessionId, `(() => ({
        requestCount: document.querySelectorAll('[wire\\\\:key^="pet-access-review-"]').length,
        requesterVisible: document.body.innerText.includes('Mia Carter'),
        evidenceVisible: document.body.innerText.includes('I share daily care'),
        approveVisible: document.body.innerText.includes('Approve and invite'),
        rejectVisible: document.body.innerText.includes('Reject request'),
    }))()`);
    assert(managerBehavior.requestCount === 1, 'The manager review did not show one pending request.');
    assert(managerBehavior.requesterVisible, 'The requester identity is missing from manager review.');
    assert(managerBehavior.evidenceVisible, 'Private evidence is missing from authorized manager review.');
    assert(managerBehavior.approveVisible && managerBehavior.rejectVisible, 'Manager review decisions are incomplete.');
    const managerDesktopAudit = await evaluate(
        client,
        sessionId,
        auditExpression('[data-section="pet-profile-access-requests"]'),
    );
    assertAudit(managerDesktopAudit, 'desktop manager review', false);

    await setViewport(client, sessionId, 320, 900, true);
    const managerMobileAudit = await evaluate(
        client,
        sessionId,
        auditExpression('[data-section="pet-profile-access-requests"]'),
    );
    assertAudit(managerMobileAudit, '320px manager review', true);
    const managerMobile = await client.send('Page.captureScreenshot', {
        format: 'png',
        captureBeyondViewport: true,
    }, sessionId);
    await writeFile(
        join(outputDirectory, 'pet-access-review-mobile.png'),
        Buffer.from(managerMobile.data, 'base64'),
    );

    assert(consoleErrors.length === 0, `Console errors: ${JSON.stringify(consoleErrors)}.`);
    const report = {
        baseUrl,
        outputDirectory,
        duplicateBehavior,
        duplicateDesktopAudit,
        duplicateMobileAudit,
        managerBehavior,
        managerDesktopAudit,
        managerMobileAudit,
        consoleErrors,
    };
    await writeFile(join(outputDirectory, 'report.json'), `${JSON.stringify(report, null, 2)}\n`);
    process.stdout.write(`${JSON.stringify(report, null, 2)}\n`);
} finally {
    client?.close();

    if (browser.exitCode === null && browser.signalCode === null) browser.kill('SIGTERM');
    await Promise.race([browserExited, delay(5_000)]);

    if (browser.exitCode === null && browser.signalCode === null) {
        browser.kill('SIGKILL');
        await browserExited;
    }

    await rm(profileDirectory, { recursive: true, force: true });
}
