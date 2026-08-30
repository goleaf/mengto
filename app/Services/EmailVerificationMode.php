<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Config\Repository;

final readonly class EmailVerificationMode
{
    public function __construct(private Repository $config) {}

    public function isEnabled(): bool
    {
        return $this->config->get('platform.email_verification_enabled', true) !== false;
    }
}
