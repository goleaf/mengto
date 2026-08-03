<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\OrganizationRestrictionCapability;
use App\Models\Organization;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class EventOrganizableOrganization implements ValidationRule
{
    public function __construct(private ?User $actor) {}

    /**
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value) || ! $this->actor instanceof User) {
            $fail(__('forum_events.validation.organization_membership_required'));

            return;
        }

        $allowed = Organization::query()
            ->select(['id'])
            ->eventOrganizableBy($this->actor)
            ->allowingCapability(OrganizationRestrictionCapability::CreateEvents)
            ->whereKey((int) $value)
            ->exists();

        if (! $allowed) {
            $fail(__('forum_events.validation.organization_membership_required'));
        }
    }
}
