<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Services\EmailVerificationMode;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

final readonly class UpdateCurrentUserProfile
{
    public function __construct(
        private Gate $gate,
        private EmailVerificationMode $emailVerification,
        private ValidationFactory $validator,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(User $user, array $data): User
    {
        $currentUser = User::query()->findOrFail($user->getKey());
        $this->gate->authorize('update', $currentUser);
        abort_unless(
            $currentUser->isActive() && $this->emailVerification->allows($currentUser),
            403,
        );

        $normalized = [
            'name' => trim((string) ($data['title'] ?? '')),
        ];
        /** @var array{name: string} $validated */
        $validated = $this->validator->make($normalized, [
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ])->validate();

        $currentUser->forceFill($validated)->saveOrFail();
        $user->refresh();

        return $currentUser;
    }
}
