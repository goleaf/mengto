<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AdoptionCaseStatus;
use App\Enums\AdoptionProviderType;
use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\ModerationStatus;
use App\Enums\SellerType;
use App\Models\AdoptionCase;
use App\Models\AdoptionEvent;
use App\Models\Listing;
use App\Services\SynchronizeAdoptionProviderIdentity;
use Illuminate\Support\Facades\DB;

final class SynchronizeAdoptionCase
{
    public function __construct(
        private readonly SynchronizeAdoptionProviderIdentity $synchronizeProviderIdentity,
    ) {}

    public function handle(Listing $listing): ?AdoptionCase
    {
        if ($listing->type !== ListingType::Adoption) {
            return null;
        }

        return DB::transaction(function () use ($listing): AdoptionCase {
            $lockedListing = Listing::query()
                ->select([
                    'id',
                    'owner_id',
                    'owner_key',
                    'type',
                    'seller_type',
                    'status',
                    'moderation_status',
                    'attributes',
                    'city',
                    'currency',
                    'delivery_options',
                    'published_at',
                ])
                ->lockForUpdate()
                ->findOrFail($listing->id);

            $existing = AdoptionCase::query()
                ->where('listing_id', $lockedListing->id)
                ->first();

            if ($existing !== null) {
                return $this->synchronizeProviderIdentity->handle($existing);
            }

            $attributes = $lockedListing->attributes ?? [];
            $case = AdoptionCase::query()->create([
                'listing_id' => $lockedListing->id,
                'case_number' => 'ADP-'.str_pad((string) $lockedListing->id, 10, '0', STR_PAD_LEFT),
                'provider_type' => $lockedListing->seller_type === SellerType::PrivateSeller
                    ? AdoptionProviderType::PrivatePerson
                    : AdoptionProviderType::Organization,
                'provider_verified' => false,
                'status' => $this->status($lockedListing),
                'animal_name' => (string) ($attributes['animal_name'] ?? __('adoption.unknown_animal')),
                'age_description' => $attributes['animal_age'] ?? null,
                'sex' => $attributes['animal_sex'] ?? null,
                'sterilization_status' => (string) ($attributes['sterilization_status'] ?? 'unknown'),
                'vaccination_status' => (string) ($attributes['vaccination_status'] ?? 'unknown'),
                'microchip_status' => (string) ($attributes['microchip_status'] ?? 'unknown'),
                'public_location' => $lockedListing->city,
                'behavior_summary' => $attributes['temperament'] ?? null,
                'special_requirements' => $attributes['adoption_conditions'] ?? null,
                'adoption_fee_minor' => 0,
                'currency' => $lockedListing->currency,
                'fee_explanation' => null,
                'transport_options' => $lockedListing->delivery_options,
                'privacy_level' => 'approximate-location',
                'published_at' => $lockedListing->published_at,
            ]);

            AdoptionEvent::query()->create([
                'adoption_case_id' => $case->id,
                'event_type' => 'legacy-listing-synchronized',
                'current_status' => $case->status->value,
                'reason_translation_key' => 'adoption.events.case_created',
                'metadata' => ['listing_id' => $lockedListing->id],
            ]);

            return $this->synchronizeProviderIdentity->handle($case);
        });
    }

    private function status(Listing $listing): AdoptionCaseStatus
    {
        if ($listing->status === ListingStatus::Completed) {
            return AdoptionCaseStatus::Closed;
        }

        if ($listing->moderation_status === ModerationStatus::Pending) {
            return AdoptionCaseStatus::PendingReview;
        }

        return $listing->status === ListingStatus::Published
            ? AdoptionCaseStatus::Published
            : AdoptionCaseStatus::Draft;
    }
}
