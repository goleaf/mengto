<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticatedUser = User::factory()->create([
            'actor_key' => 'mia-carter',
            'name' => 'Mia Carter',
            'email' => 'mia@example.test',
            'email_verified_at' => now(),
            'locale' => 'en',
            'timezone' => 'Europe/Vilnius',
            'status' => UserStatus::Active,
        ]);

        $this->actingAs($this->authenticatedUser);
    }
}
