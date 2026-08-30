<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Services\EmailVerificationMode;
use App\Validation\ProfilePreferenceRules;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;

final readonly class UpdateProfilePreferences
{
    public function __construct(
        private Gate $gate,
        private EmailVerificationMode $emailVerification,
        private ValidationFactory $validator,
    ) {}

    /**
     * @param  array{locale: string, timezone: string}  $data
     */
    public function handle(User $user, array $data): User
    {
        $currentUser = User::query()->findOrFail($user->getKey());
        $this->gate->authorize('update', $currentUser);
        abort_unless(
            $currentUser->isActive()
                && $this->emailVerification->allows($currentUser),
            403,
        );

        /** @var array{locale: string, timezone: string} $validated */
        $validated = $this->validator->make(
            $data,
            ProfilePreferenceRules::rules(),
            ProfilePreferenceRules::messages(),
            ProfilePreferenceRules::attributes(),
        )->validate();

        $currentUser->forceFill($validated)->saveOrFail();

        return $currentUser;
    }
}
