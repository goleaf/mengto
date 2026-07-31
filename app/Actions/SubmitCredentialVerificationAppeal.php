<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Credential;
use App\Models\CredentialVerificationAppeal;
use App\Models\CredentialVerificationEvent;
use App\Models\User;
use App\Policies\CredentialPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SubmitCredentialVerificationAppeal
{
    public function __construct(private readonly CredentialPolicy $policy) {}

    public function handle(User $submitter, int $credentialId, string $statement): CredentialVerificationAppeal
    {
        Validator::make(
            ['statement' => $statement],
            ['statement' => ['required', 'string', 'min:40', 'max:4000']],
        )->validate();

        return DB::transaction(function () use ($submitter, $credentialId, $statement): CredentialVerificationAppeal {
            $credential = Credential::query()
                ->with('expertProfile:id,owner_id,owner_key')
                ->lockForUpdate()
                ->findOrFail($credentialId);

            if (! $this->policy->appeal($submitter, $credential)) {
                if (! $this->policy->view($submitter, $credential)) {
                    throw new AuthorizationException;
                }

                throw ValidationException::withMessages([
                    'credential' => __('credential_verification.validation.appeal_status'),
                ]);
            }

            if ($credential->appeals()->whereIn('status', ['submitted', 'in-review'])->exists()) {
                throw ValidationException::withMessages([
                    'credential' => __('credential_verification.validation.appeal_exists'),
                ]);
            }

            $appeal = $credential->appeals()->create([
                'submitted_by_user_id' => $submitter->id,
                'status' => 'submitted',
                'statement' => trim($statement),
                'metadata' => [],
            ]);
            $credential->forceFill([
                'appeal_status' => 'submitted',
                'lock_version' => $credential->lock_version + 1,
            ])->save();
            CredentialVerificationEvent::query()->create([
                'credential_id' => $credential->id,
                'actor_user_id' => $submitter->id,
                'event_type' => 'appeal-submitted',
                'from_status' => $credential->status->value,
                'to_status' => $credential->status->value,
                'idempotency_key' => (string) Str::uuid(),
                'metadata' => ['appeal_id' => $appeal->id],
            ]);

            return $appeal;
        }, 3);
    }
}
