<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ReviewProfessionalCredential;
use App\Enums\AdoptionApplicationStatus;
use App\Enums\AdoptionPlacementType;
use App\Enums\CredentialStatus;
use App\Enums\CredentialType;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\Credential;
use App\Models\ExpertProfile;
use App\Models\User;
use App\Services\SynchronizeAdoptionProviderIdentity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use LogicException;

final class AdoptionDemoSeeder extends Seeder
{
    public function run(
        ReviewProfessionalCredential $reviewCredential,
        SynchronizeAdoptionProviderIdentity $synchronizeProviderIdentity,
    ): void {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException(__('seeding.errors.demo_environment'));
        }

        $case = AdoptionCase::query()
            ->whereHas('listing', fn ($query) => $query->where('slug', 'gentle-adult-cat-meta-is-ready-for-adoption'))
            ->first();
        $applicant = User::query()->where('actor_key', 'mia-carter')->first();
        $administrator = User::query()->where('actor_key', 'demo-administrator')->first();
        $providerOwner = User::query()->where('actor_key', 'demo-marketplace-member')->first();

        if ($case === null || $applicant === null || $administrator === null || $providerOwner === null) {
            return;
        }

        $this->seedProviderVerification(
            $case,
            $administrator,
            $providerOwner,
            $reviewCredential,
            $synchronizeProviderIdentity,
        );

        $application = AdoptionApplication::query()->firstOrCreate(
            [
                'adoption_case_id' => $case->id,
                'applicant_user_id' => $applicant->id,
            ],
            [
                'idempotency_key' => (string) Str::uuid(),
                'placement_type' => AdoptionPlacementType::Adoption,
                'status' => AdoptionApplicationStatus::Screening,
                'identity_status' => 'pending',
                'message' => 'Our household would like to follow the full meeting and screening process.',
                'private_profile' => [
                    'experience' => 'Previous adult cat care experience.',
                    'home_context' => 'Quiet indoor home with landlord permission.',
                    'household' => 'Two adults.',
                    'other_animals' => 'No resident animals.',
                    'care_plan' => 'Indoor enrichment, preventive care, and a gradual routine.',
                    'placement_reason' => 'The described temperament matches our household.',
                    'transport_plan' => 'Secure carrier and local collection.',
                ],
                'terms_accepted' => true,
                'privacy_accepted' => true,
                'reference_contact_consent' => false,
                'submitted_at' => now()->subDay(),
                'reviewed_at' => now(),
            ],
        );

        AdoptionEvent::query()->firstOrCreate(
            [
                'adoption_case_id' => $case->id,
                'adoption_application_id' => $application->id,
                'event_type' => 'demo-application-created',
            ],
            [
                'actor_user_id' => $applicant->id,
                'current_status' => $application->status->value,
                'reason_translation_key' => 'adoption.events.application_submitted',
                'metadata' => ['demo' => true],
            ],
        );
    }

    private function seedProviderVerification(
        AdoptionCase $case,
        User $administrator,
        User $providerOwner,
        ReviewProfessionalCredential $reviewCredential,
        SynchronizeAdoptionProviderIdentity $synchronizeProviderIdentity,
    ): void {
        $profile = ExpertProfile::query()
            ->where('slug', 'vilnius-animal-aid-provider')
            ->first();

        if ($profile === null) {
            $profile = ExpertProfile::factory()
                ->for($providerOwner, 'owner')
                ->unverified()
                ->create([
                    'slug' => 'vilnius-animal-aid-provider',
                    'public_name' => 'Vilnius Animal Aid',
                    'legal_name' => 'Vilnius Animal Aid',
                    'primary_type' => 'rescue-organization',
                    'headline' => 'Local animal rescue and adoption provider',
                    'bio' => 'A demonstration rescue profile used to exercise the complete identity review and adoption placement workflow.',
                    'boundaries' => 'Provider verification confirms organization identity only and does not represent veterinary or legal authority.',
                    'specializations' => ['adoption', 'rescue'],
                    'species' => ['dog', 'cat'],
                    'formats' => ['in-person', 'text'],
                ]);
        }

        $credential = Credential::query()->firstOrCreate(
            [
                'expert_profile_id' => $profile->id,
                'type' => CredentialType::OrganizationRegistration->value,
                'credential_identifier_hash' => hash('sha256', 'demo:vilnius-animal-aid'),
            ],
            [
                'title' => 'Rescue organization registration',
                'issuer' => 'Demo organization registry',
                'jurisdiction' => 'LT',
                'number_last_four' => '0001',
                'issued_at' => now()->subYear(),
                'expires_at' => now()->addYear(),
                'renewal_due_at' => now()->addMonths(10),
                'status' => CredentialStatus::Submitted,
                'scope' => ['adoption-provider'],
                'metadata' => ['demo' => true],
            ],
        );

        if ($credential->status === CredentialStatus::Submitted) {
            $reviewCredential->handle(
                $administrator,
                $credential->id,
                CredentialStatus::InReview,
                'credential_verification.reason.information-required',
                'The demo organization registration entered independent documentary review.',
                'demo-adoption-provider-review-'.$credential->id,
                ['demo' => true],
            );
            $reviewCredential->handle(
                $administrator,
                $credential->id,
                CredentialStatus::Verified,
                'credential_verification.reason.approved',
                'The demo registry independently confirmed the organization registration.',
                'demo-adoption-provider-verified-'.$credential->id,
                ['demo' => true],
            );
        }

        $synchronizeProviderIdentity->handle($case);
    }
}
