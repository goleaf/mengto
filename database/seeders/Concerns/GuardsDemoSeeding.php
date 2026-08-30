<?php

declare(strict_types=1);

namespace Database\Seeders\Concerns;

use LogicException;

trait GuardsDemoSeeding
{
    protected function assertDemoSeedingIsAllowed(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Demo seed data may only be created in an explicitly allowed environment.');
        }
    }
}
