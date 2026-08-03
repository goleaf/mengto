<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\PetManagerRole;
use App\Enums\PetProfileAccessRequestType;
use Illuminate\Validation\Rule;
use Livewire\Form;

final class PetProfileAccessRequestForm extends Form
{
    public string $requestType = 'co-ownership';

    public string $requestedRole = 'family-member';

    public string $evidenceSummary = '';

    public string $temporaryAccessEndsAt = '';

    /** @return list<PetManagerRole> */
    public static function correctionRoles(): array
    {
        return [
            PetManagerRole::CoOwner,
            PetManagerRole::FamilyMember,
            PetManagerRole::FosterCarer,
            PetManagerRole::Sitter,
            PetManagerRole::Caregiver,
            PetManagerRole::ProfileAdministrator,
            PetManagerRole::Specialist,
            PetManagerRole::Finder,
            PetManagerRole::Volunteer,
            PetManagerRole::PreviousOwner,
            PetManagerRole::Other,
        ];
    }

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        return [
            'requestType' => ['required', Rule::enum(PetProfileAccessRequestType::class)],
            'requestedRole' => [
                Rule::requiredIf($this->requestType === PetProfileAccessRequestType::RelationshipCorrection->value),
                Rule::in(array_map(
                    static fn (PetManagerRole $role): string => $role->value,
                    self::correctionRoles(),
                )),
            ],
            'evidenceSummary' => ['required', 'string', 'min:20', 'max:2000'],
            'temporaryAccessEndsAt' => [
                Rule::requiredIf($this->requestType === PetProfileAccessRequestType::TemporaryAccess->value),
                'nullable',
                'date',
                'after:now',
            ],
        ];
    }

    /**
     * @return array{
     *     request_type: PetProfileAccessRequestType,
     *     requested_role: PetManagerRole|null,
     *     evidence_summary: string,
     *     temporary_access_ends_at: string|null
     * }
     */
    public function data(): array
    {
        $validated = $this->validate();
        $type = PetProfileAccessRequestType::from((string) $validated['requestType']);

        return [
            'request_type' => $type,
            'requested_role' => $type === PetProfileAccessRequestType::RelationshipCorrection
                ? PetManagerRole::from((string) $validated['requestedRole'])
                : null,
            'evidence_summary' => trim((string) $validated['evidenceSummary']),
            'temporary_access_ends_at' => filled($validated['temporaryAccessEndsAt'] ?? null)
                ? (string) $validated['temporaryAccessEndsAt']
                : null,
        ];
    }
}
