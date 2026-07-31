<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Validation\Rule;

final readonly class UpdateProfilePreferences
{
    public function __construct(
        private Gate $gate,
        private ValidationFactory $validator,
    ) {}

    /**
     * @param  array{locale: string, timezone: string}  $data
     */
    public function handle(User $user, array $data): User
    {
        $this->gate->authorize('update', $user);

        /** @var array{locale: string, timezone: string} $validated */
        $validated = $this->validator->make($data, [
            'locale' => [
                'required',
                'string',
                Rule::in(config('platform.supported_locales', ['en'])),
            ],
            'timezone' => ['required', 'string', 'timezone:all'],
        ])->validate();

        $user->forceFill($validated)->save();

        return $user;
    }
}
