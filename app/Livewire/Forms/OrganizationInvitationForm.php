<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Data\OrganizationInvitationData;
use App\Enums\OrganizationRole;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class OrganizationInvitationForm extends Form
{
    public string $email = '';

    public string $role = 'member';

    public string $expiresAt = '';

    public string $idempotencyKey = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255', 'exists:users,email'],
            'role' => [
                'required',
                Rule::in(array_map(
                    static fn (OrganizationRole $role): string => $role->value,
                    OrganizationRole::assignableCases(),
                )),
            ],
            'expiresAt' => ['required', 'date', 'after:now', 'before_or_equal:+30 days'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }

    /** @return array{email: string, invitation: OrganizationInvitationData} */
    public function data(): array
    {
        $validated = $this->validate();

        return [
            'email' => mb_strtolower(trim((string) $validated['email'])),
            'invitation' => new OrganizationInvitationData(
                role: OrganizationRole::from((string) $validated['role']),
                expiresAt: CarbonImmutable::parse((string) $validated['expiresAt']),
                idempotencyKey: (string) $validated['idempotencyKey'],
            ),
        ];
    }
}
