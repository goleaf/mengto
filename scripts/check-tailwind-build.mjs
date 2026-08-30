import fs from 'node:fs';
import path from 'node:path';

const buildDirectory = path.resolve(process.argv[2] ?? 'public/build');
const manifestPath = path.join(buildDirectory, 'manifest.json');

if (!fs.existsSync(manifestPath)) {
    throw new Error(`Vite manifest is missing: ${manifestPath}`);
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const expectedEntries = [
    'resources/css/app.css',
    'resources/scss/app.scss',
    'resources/js/app.js',
];
const actualEntries = Object.entries(manifest)
    .filter(([, asset]) => asset.isEntry === true)
    .map(([source]) => source)
    .sort();

if (JSON.stringify(actualEntries) !== JSON.stringify([...expectedEntries].sort())) {
    throw new Error(`Unexpected Vite entries: ${actualEntries.join(', ')}`);
}

for (const [source, asset] of Object.entries(manifest)) {
    const assetPath = path.join(buildDirectory, asset.file);

    if (!fs.existsSync(assetPath)) {
        throw new Error(`Manifest asset is missing for ${source}: ${asset.file}`);
    }
}

const tailwindAsset = manifest['resources/css/app.css'];
const tailwindCss = fs.readFileSync(path.join(buildDirectory, tailwindAsset.file), 'utf8');
const requiredSelectors = [
    '.bg-paw-canvas',
    '.bg-paw-surface',
    '.border-border-subtle',
    '.border-border-strong',
    '.border-control-border',
    '.text-text-muted',
    '.text-status-warning-foreground',
    '.max-w-\\[90\\%\\]',
    '.md\\:grid-cols-\\[minmax\\(0\\,1fr\\)_18rem\\]',
    '.ring-gray-300',
    '.ring-blue-300',
];
const missingSelectors = requiredSelectors.filter((selector) => !tailwindCss.includes(selector));

if (missingSelectors.length > 0) {
    throw new Error(`Tailwind production CSS is missing selectors: ${missingSelectors.join(', ')}`);
}

const summary = expectedEntries.map((source) => {
    const asset = manifest[source];
    const bytes = fs.statSync(path.join(buildDirectory, asset.file)).size;

    return `${source}=${asset.file}:${bytes}`;
});

process.stdout.write(`Tailwind build contract passed: ${summary.join(' ')}\n`);
