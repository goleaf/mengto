<?php

declare(strict_types=1);

use Database\Seeders\AdoptionDemoSeeder;
use Database\Seeders\CollaborativeGuideDemoSeeder;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\seed;

test('adoption demo seeding refuses an environment not explicitly allowed', function (): void {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(AdoptionDemoSeeder::class))
        ->toThrow(LogicException::class);
});

test('collaborative guide demo seeding refuses an environment not explicitly allowed', function (): void {
    Config::set('platform.demo_seed_environments', []);

    expect(fn () => seed(CollaborativeGuideDemoSeeder::class))
        ->toThrow(LogicException::class);
});
