<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetManagerRole;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetManagerInvitationForm extends Form
{
    public string $email = '';

    public string $role = 'family-member';

    public string $endsAt = '';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'max:255', Rule::exists('users', 'email')],
            'role' => [
                'required',
                Rule::in([
                    PetManagerRole::CoOwner->value,
                    PetManagerRole::FamilyMember->value,
                    PetManagerRole::FosterCarer->value,
                    PetManagerRole::Sitter->value,
                    PetManagerRole::Caregiver->value,
                    PetManagerRole::ProfileAdministrator->value,
                    PetManagerRole::Specialist->value,
                    PetManagerRole::Volunteer->value,
                    PetManagerRole::Other->value,
                ]),
            ],
            'endsAt' => ['nullable', 'date', 'after:now'],
        ];
    }

    /** @return array{email: string, role: PetManagerRole, ends_at: string|null} */
    public function data(): array
    {
        $validated = $this->validate();

        return [
            'email' => mb_strtolower(trim((string) $validated['email'])),
            'role' => PetManagerRole::from((string) $validated['role']),
            'ends_at' => filled($validated['endsAt'] ?? null)
                ? (string) $validated['endsAt']
                : null,
        ];
    }
}
