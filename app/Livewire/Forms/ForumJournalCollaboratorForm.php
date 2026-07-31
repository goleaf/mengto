<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\ForumJournalCollaboratorRole;
use App\Enums\UserStatus;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class ForumJournalCollaboratorForm extends Form
{
    public string $email = '';

    public string $role = 'viewer';

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc',
                'max:255',
                Rule::exists('users', 'email')->where('status', UserStatus::Active->value),
            ],
            'role' => ['required', Rule::enum(ForumJournalCollaboratorRole::class)],
        ];
    }

    /** @return array{email: string, role: ForumJournalCollaboratorRole} */
    public function data(): array
    {
        $validated = $this->validate();

        return [
            'email' => str((string) $validated['email'])->trim()->lower()->toString(),
            'role' => ForumJournalCollaboratorRole::from((string) $validated['role']),
        ];
    }
}
