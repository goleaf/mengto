<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\UserStatus;
use App\Models\User;
use App\Services\SocialActorResolver;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected User $authenticatedUser;

    public function createApplication()
    {
        $storagePath = $_ENV['LARAVEL_STORAGE_PATH'] ?? $_SERVER['LARAVEL_STORAGE_PATH'] ?? null;

        if (is_string($storagePath) && str_starts_with($storagePath, sys_get_temp_dir().DIRECTORY_SEPARATOR.'laravel-test')) {
            foreach ([
                'app/private',
                'app/public',
                'framework/cache/data',
                'framework/sessions',
                'framework/testing',
                'framework/views',
                'logs',
            ] as $directory) {
                $path = $storagePath.DIRECTORY_SEPARATOR.$directory;

                if (! is_dir($path) && ! mkdir($path, 0770, true) && ! is_dir($path)) {
                    throw new \RuntimeException('Unable to create isolated test storage.');
                }
            }
        }

        return parent::createApplication();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticatedUser = User::factory()->create([
            'actor_key' => 'mia-carter',
            'name' => 'Mia Carter',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
        ]);
        app(SocialActorResolver::class)->forUser($this->authenticatedUser);

        $this->actingAs($this->authenticatedUser);
    }
}
