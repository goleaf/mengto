<?php

namespace App\Actions;

use App\Enums\ExpertProfileStatus;
use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Services\ForumActor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateExpertProfile
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): ExpertProfile
    {
        return DB::transaction(function () use ($data): ExpertProfile {
            $identity = $this->actor->identity();
            $credentialFile = $data['credential_file'] ?? null;
            $credentialType = $data['credential_type'] ?? 'qualification';
            $credentialTitle = $data['credential_title'] ?? __('messages.expert.professional_qualification');
            $credentialIssuer = $data['credential_issuer'] ?? __('messages.expert.issuer_pending_confirmation');
            unset($data['credential_file'], $data['credential_type'], $data['credential_title'], $data['credential_issuer']);

            $profile = ExpertProfile::query()->create([
                ...$data,
                'owner_key' => $identity['key'],
                'slug' => $this->uniqueSlug((string) $data['public_name']),
                'specializations' => array_values($data['specializations']),
                'species' => array_values($data['species']),
                'languages' => array_values($data['languages']),
                'formats' => array_values($data['formats']),
                'age_groups' => $data['age_groups'] ?? [],
                'methods' => array_values(array_filter($data['methods'] ?? [])),
                'accessibility' => $data['accessibility'] ?? [],
                'professional_interests' => [],
                'workplaces' => [],
                'status' => ExpertProfileStatus::Pending,
                'verification_status' => $credentialFile instanceof UploadedFile
                    ? VerificationStatus::Submitted
                    : VerificationStatus::Unsubmitted,
            ]);

            if ($credentialFile instanceof UploadedFile) {
                Credential::query()->create([
                    'expert_profile_id' => $profile->id,
                    'type' => $credentialType,
                    'title' => $credentialTitle,
                    'issuer' => $credentialIssuer,
                    'status' => 'submitted',
                    'file_path' => $credentialFile->store('expert-credentials', 'local'),
                ]);
            }

            AuditLog::query()->create([
                'expert_profile_id' => $profile->id,
                'actor_key' => $identity['key'],
                'actor_role' => 'profile-owner',
                'action' => 'expert-profile.created',
                'target_type' => ExpertProfile::class,
                'target_id' => (string) $profile->id,
                'metadata' => ['verification_status' => $profile->verification_status->value],
            ]);

            return $profile;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'specialist';
        $slug = $base;
        $suffix = 2;

        while (ExpertProfile::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
