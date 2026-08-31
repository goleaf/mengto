<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

test('production routes contain no person-specific demo profile contract', function (): void {
    $routes = collect(app('router')->getRoutes()->getRoutes());

    expect($routes->pluck('action.as')->filter()->all())
        ->not->toContain(
            'profile.mia',
            'profile.mia.legacy',
            'pets.scout',
            'pets.nori',
            'neighbors.ari',
            'messages.details',
            'messages.actions',
            'meetups.small_dog_social',
        )
        ->and($routes->pluck('uri')->all())
        ->not->toContain('@mia-carter', 'profile/mia-carter', '@mia-carter/scout', '@mia-carter/nori', 'pets/scout');
});

test('registration and onboarding boundaries cannot create prototype pets or invoke demo seeders', function (): void {
    $paths = [
        app_path('Actions/RegisterUser.php'),
        app_path('Actions/InitializeUserOnboarding.php'),
        app_path('Livewire/Onboarding.php'),
    ];
    $forbidden = '/PetProfile::(?:create|factory)|CreatePetProfile|DatabaseSeeder|SocialIdentitySeeder|DemoDataSeeder/u';

    foreach ($paths as $path) {
        expect(File::get($path))->not->toMatch($forbidden);
    }
});

test('production code and translations contain no prototype person or pet identity hardcodes', function (): void {
    $roots = [
        app_path(),
        base_path('routes'),
        resource_path('views'),
        lang_path(),
        config_path(),
    ];
    $forbidden = '/Mia Carter|mia-carter|profile\.mia|owner-mia-carter|\bScout\b|\bNori\b|demoOwner|ownerPage|ownerProfile|demoPets|member_profiles\.owner/u';
    $matches = [];

    foreach ($roots as $root) {
        foreach (File::allFiles($root) as $file) {
            if (! in_array($file->getExtension(), ['php', 'blade.php'], true)
                && ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            if (preg_match($forbidden, $file->getContents()) === 1) {
                $matches[] = str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname());
            }
        }
    }

    expect($matches)->toBe([]);
});

test('new migrations cannot introduce demo account or pet identity data', function (): void {
    $releasedException = '2026_07_30_155124_add_identity_fields_to_users_table.php';
    $forbidden = '/Mia Carter|mia-carter|test@example\.com|\bScout\b|\bNori\b/u';
    $matches = [];

    foreach (File::files(database_path('migrations')) as $migration) {
        if ($migration->getFilename() === $releasedException) {
            continue;
        }

        if (preg_match($forbidden, $migration->getContents()) === 1) {
            $matches[] = $migration->getFilename();
        }
    }

    expect($matches)->toBe([])
        ->and(File::get(database_path('migrations/'.$releasedException)))
        ->toMatch('/test@example\.com.*mia-carter/s');
});

test('the canonical accessibility journey derives self profile routing from the authenticated header', function (): void {
    $source = File::get(base_path('scripts/accessibility-browser-check.mjs'));

    expect($source)
        ->toContain("document.querySelector('[data-header-link=\"profile\"]')")
        ->toContain('/^\\/members\\/[^/]+$/')
        ->toContain("login(client, sessionId, 'andrej-browser@example.test')")
        ->toContain("canonicalIdentityTarget.name === 'Andrej Browser'")
        ->toContain("new URL(canonicalIdentityTarget.href).pathname === '/members/00000000-0000-4000-8000-000000000001'")
        ->toContain("const canonicalIdentityOnly = process.argv.includes('--canonical-identity-only')")
        ->toContain('selfProfileAudit.smallTargets.length === 0')
        ->toContain('selfProfileAudit.clippedRegions.length === 0')
        ->toContain('selfProfileAudit.keyboardFocusTarget')
        ->toContain('selfProfileAudit.focusVisible')
        ->not->toMatch('/@mia-carter|profile\/mia-carter|data-owner-profile|member_profiles\.owner/u');

    expect(File::get(base_path('scripts/run-browser-check.php')))
        ->toContain("'canonical-identity' => ['scripts/accessibility-browser-check.mjs', '--canonical-identity-only']")
        ->toContain("['page-identity', 'canonical-identity']")
        ->and(File::get(base_path('package.json')))
        ->toContain('"test:browser:canonical-identity": "php scripts/run-browser-check.php canonical-identity"');
});
