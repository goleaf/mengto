<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

final readonly class ActiveAuthenticatedUser
{
    public function __construct(
        private ForumActor $actor,
        private EmailVerificationMode $emailVerification,
    ) {}

    public function require(User $expected, bool $lockForUpdate = false): User
    {
        $authenticated = $this->actor->requireUser();
        abort_unless($authenticated->is($expected), 403);

        $query = User::query()->whereKey($expected->getKey());

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $persisted = $query->first();

        abort_unless(
            $persisted instanceof User
                && $persisted->isActive()
                && $this->emailVerification->allows($persisted),
            403,
        );

        return $persisted;
    }
}
