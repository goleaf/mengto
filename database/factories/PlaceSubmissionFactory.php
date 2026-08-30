<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceLocationPrecision;
use App\Enums\PlaceSubmissionResolution;
use App\Enums\PlaceSubmissionSource;
use App\Enums\PlaceSubmissionStatus;
use App\Enums\PlaceType;
use App\Models\PlaceSubmission;
use App\Models\User;
use App\Services\PlaceIdentityNormalizer;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceSubmission> */
final class PlaceSubmissionFactory extends ApplicationFactory
{
    protected $model = PlaceSubmission::class;

    public function definition(): array
    {
        $name = fake()->unique()->company().' Pet Place';
        $address = fake()->streetAddress().', Vilnius';
        $phone = '+370 6'.fake()->numerify('## ## ###');
        $website = 'https://'.fake()->unique()->domainName().'/pets';
        $normalizer = new PlaceIdentityNormalizer;

        return [
            'submitter_user_id' => User::factory(),
            'stable_key' => 'place-submission-'.Str::lower((string) Str::ulid()),
            'idempotency_key' => (string) Str::uuid(),
            'payload_fingerprint' => hash('sha256', $name.'|'.$address),
            'status' => PlaceSubmissionStatus::Submitted,
            'resolution' => PlaceSubmissionResolution::None,
            'source_kind' => PlaceSubmissionSource::PersonalVisit,
            'source_reference' => 'Observed during a recent visit.',
            'relationship_to_place' => 'visitor',
            'location_precision' => PlaceLocationPrecision::PublicPoint,
            'locale' => 'en',
            'name' => $name,
            'normalized_name' => $normalizer->name($name),
            'catalog_category' => 'park',
            'place_type' => PlaceType::Park,
            'summary' => fake()->sentence(),
            'public_region' => 'Vilnius',
            'public_address' => $address,
            'normalized_address' => $normalizer->address($address),
            'public_latitude' => '54.687200',
            'public_longitude' => '25.279700',
            'exact_address' => null,
            'exact_latitude' => null,
            'exact_longitude' => null,
            'public_phone' => $phone,
            'normalized_phone' => $normalizer->phone($phone),
            'public_email' => fake()->unique()->safeEmail(),
            'normalized_email' => fn (array $attributes): string => $normalizer->email((string) $attributes['public_email']) ?? '',
            'public_website' => $website,
            'normalized_website' => $normalizer->website($website),
            'identity_hash' => hash('sha256', implode('|', [
                $normalizer->name($name),
                $normalizer->address($address),
                $normalizer->phone($phone),
            ])),
            'submitted_facts' => [
                'hours' => ['weekdays' => '08:00-20:00'],
                'rules' => 'Leashes are required near shared paths.',
                'features' => ['water', 'lighting'],
            ],
            'consent_version' => 'places-submission-v1',
            'consented_at' => now(),
            'observed_at' => now()->subDay(),
            'audit_context' => ['channel' => 'factory'],
            'continued_as_distinct' => false,
            'lock_version' => 0,
            'submitted_at' => now(),
        ];
    }

    public function duplicateReview(): static
    {
        return $this->state(fn (): array => ['status' => PlaceSubmissionStatus::DuplicateReview]);
    }

    public function rejected(): static
    {
        return $this->state(fn (): array => [
            'status' => PlaceSubmissionStatus::Rejected,
            'reviewed_at' => now(),
            'rejected_at' => now(),
        ]);
    }

    public function privateExact(): static
    {
        return $this->state(fn (): array => [
            'location_precision' => PlaceLocationPrecision::PrivateExact,
            'public_latitude' => null,
            'public_longitude' => null,
            'exact_address' => fake()->streetAddress().', Vilnius',
            'exact_latitude' => '54.687234',
            'exact_longitude' => '25.279734',
        ]);
    }
}
