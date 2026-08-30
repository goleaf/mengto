<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Models\AdoptionApplication;
use App\Models\AdoptionCase;
use App\Models\DomesticClassification;
use App\Models\ExpertProfile;
use App\Models\ForumCategory;
use App\Models\ForumEvent;
use App\Models\ForumEventOccurrence;
use App\Models\ForumMentorProfile;
use App\Models\ForumMentorScope;
use App\Models\KnowledgeArticle;
use App\Models\Listing;
use App\Models\MedicalRecord;
use App\Models\Order;
use App\Models\Organization;
use App\Models\PetProfile;
use App\Models\Reservation;
use App\Models\SearchCase;
use App\Models\Sighting;
use App\Models\SmartDevice;
use App\Models\Taxon;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LogicException;

final class RepresentativeFieldCoverageSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $allowedEnvironments = config('platform.demo_seed_environments');

        if (! is_array($allowedEnvironments) || ! app()->environment($allowedEnvironments)) {
            throw new LogicException('Representative seed data may only be created in an explicitly allowed environment.');
        }

        $user = User::query()->where('email', 'user@example.com')->firstOrFail();
        $classification = $this->canonicalDomesticClassification(
            'taxon.core.canis-lupus-familiaris',
            'domestic.core.canis-lupus-familiaris',
            'Domestic dog',
            ['Dog', 'Canis lupus familiaris'],
        );
        $catClassification = $this->canonicalDomesticClassification(
            'taxon.core.felis-catus',
            'domestic.core.felis-catus',
            'Domestic cat',
            ['Cat', 'Felis catus'],
        );

        $this->completeDeterministicPetAndSearchData($user, $classification);
        $this->completeDeterministicAdoptionData($user, $catClassification);
        $this->completeDeterministicMarketplaceData($user);
        $this->seedDeterministicOrderLifecycleVariants($user);
        $this->completeDeterministicExpertAndEventData($classification);
        $this->completeDeterministicStructuredData();
        $this->completeDeterministicDeviceSafetyData();
    }

    /** @param list<string> $aliases */
    private function canonicalDomesticClassification(
        string $taxonKey,
        string $classificationKey,
        string $name,
        array $aliases,
    ): DomesticClassification {
        $taxon = Taxon::query()
            ->where('stable_key', $taxonKey)
            ->firstOrFail();

        return DomesticClassification::query()->updateOrCreate(
            ['stable_key' => $classificationKey],
            [
                'taxon_id' => $taxon->id,
                'classification_type' => 'species',
                'canonical_name' => $name,
                'is_active' => true,
                'aliases' => $aliases,
                'metadata' => [
                    'source' => 'platform-core-animal-taxonomy',
                    'representative_seed' => true,
                ],
            ],
        );
    }

    private function completeDeterministicAdoptionData(
        User $user,
        DomesticClassification $classification,
    ): void {
        $case = AdoptionCase::query()
            ->with('listing:id,owner_id')
            ->whereHas('listing', static fn ($query) => $query
                ->where('slug', 'gentle-adult-cat-meta-is-ready-for-adoption'))
            ->first();

        if (! $case instanceof AdoptionCase || $case->listing?->owner_id === null) {
            return;
        }

        $pet = PetProfile::query()->updateOrCreate(
            ['profile_key' => 'demo-adoption-meta'],
            [
                'user_id' => $case->listing->owner_id,
                'slug' => 'meta-adoption-demo',
                'name' => 'Mėta',
                'species' => 'Cat',
                'taxon_id' => $classification->taxon_id,
                'breed' => 'Domestic Shorthair',
                'domestic_classification_id' => $classification->id,
                'birth_date' => now()->subYears(4)->toDateString(),
                'birth_date_precision' => 'year',
                'sex' => 'female',
                'reproductive_status' => 'spayed',
                'visibility' => 'public',
                'status' => 'active',
                'creation_key' => 'demo:adoption:meta',
                'creator_relationship' => 'shelter',
                'is_discoverable' => true,
                'published_at' => now(),
                'profile_data' => [
                    'adoption_case' => true,
                    'status' => 'Available for adoption',
                ],
            ],
        );

        $case->forceFill([
            'pet_profile_id' => $pet->id,
            'taxon_id' => $classification->taxon_id,
            'domestic_classification_id' => $classification->id,
        ])->save();

        AdoptionApplication::query()
            ->where('adoption_case_id', $case->id)
            ->where('applicant_user_id', $user->id)
            ->first()?->forceFill([
                'private_references' => [[
                    'name' => 'Demo household reference',
                    'relationship' => 'long-term pet sitter',
                    'consent_recorded' => true,
                ]],
                'contract_metadata' => [
                    'template' => 'representative-adoption-v1',
                    'review_required' => true,
                ],
            ])->save();
    }

    private function completeDeterministicStructuredData(): void
    {
        ForumEventOccurrence::query()
            ->where('stable_key', 'demo-point13-weekly-group-walk-occurrence-1')
            ->first()?->forceFill([
                'metadata' => ['representative_schedule_variant' => true],
            ])->save();

        KnowledgeArticle::query()
            ->where('slug', 'dog-travel-documents-lithuania-to-poland')
            ->first()?->forceFill([
                'protected_sections' => ['official-source-checklist'],
            ])->save();
    }

    private function completeDeterministicDeviceSafetyData(): void
    {
        SmartDevice::query()
            ->where('slug', 'scout-trail-gps')
            ->firstOrFail()
            ->forceFill([
                'safety_state' => [
                    'pet_in_doorway' => false,
                    'obstruction_detected' => false,
                    'pet_present' => false,
                    'leak_detected' => false,
                ],
                'safety_state_recorded_at' => now(),
            ])
            ->save();
    }

    private function completeDeterministicPetAndSearchData(
        User $user,
        ?DomesticClassification $classification,
    ): void {
        $pet = PetProfile::query()
            ->where('user_id', $user->id)
            ->where('profile_key', 'pet-scout')
            ->first();

        if ($pet instanceof PetProfile && $classification instanceof DomesticClassification) {
            $pet->forceFill([
                'taxon_id' => $classification->taxon_id,
                'domestic_classification_id' => $classification->id,
                'creation_key' => 'demo:pet-profile:scout',
                'creator_relationship' => 'guardian',
            ])->save();
        }

        $case = SearchCase::query()
            ->where('slug', 'scout-missing-vingis-park')
            ->where('owner_id', $user->id)
            ->first();

        if ($case instanceof SearchCase) {
            $case->forceFill([
                'pet_profile_id' => $pet?->id,
                'taxon_id' => $classification?->taxon_id,
                'domestic_classification_id' => $classification?->id,
                'photos' => [asset('images/places/park-primary-lg.jpg')],
                'risk_flags' => ['possible-duplicate'],
            ])->save();

            Sighting::query()
                ->where('idempotency_key', '60000000-0000-4000-8000-000000000001')
                ->first()?->forceFill([
                    'reporter_id' => $user->id,
                    'reporter_key' => $user->actor_key,
                    'reporter_name' => $user->name,
                    'photo_url' => asset('images/places/park-primary-lg.jpg'),
                    'risk_flags' => ['location-sensitive'],
                ])->save();
        }
    }

    private function completeDeterministicMarketplaceData(User $user): void
    {
        Reservation::query()
            ->where('idempotency_key', '10000000-0000-4000-8000-000000000001')
            ->first()?->forceFill([
                'requester_id' => $user->id,
                'requester_key' => $user->actor_key,
                'requester_name' => $user->name,
            ])->save();

        $listing = Listing::query()
            ->where('slug', 'rehabilitation-ramp-rental-vilnius')
            ->first();

        if ($listing instanceof Listing) {
            $listing->forceFill([
                'gallery' => [asset('images/places/pet-store-primary-lg.jpg')],
                'risk_flags' => ['verified-rental-handover'],
            ])->save();
        }

        $order = Order::query()
            ->with(['listing:id,owner_id,owner_key,owner_name', 'reservation:id,requester_id,requester_key,requester_name'])
            ->where('reference', 'ORD-DEMO-RAMP')
            ->first();

        if (! $order instanceof Order || $order->reservation === null || $order->listing === null) {
            return;
        }

        $seller = User::query()->where('actor_key', $order->listing->owner_key)->first();
        $order->forceFill([
            'buyer_id' => $order->reservation->requester_id,
            'buyer_key' => $order->reservation->requester_key,
            'buyer_name' => $order->reservation->requester_name,
            'seller_id' => $seller?->id,
            'seller_key' => $order->listing->owner_key,
            'seller_name' => $order->listing->owner_name,
        ])->save();
    }

    private function completeDeterministicExpertAndEventData(
        ?DomesticClassification $classification,
    ): void {
        ExpertProfile::query()
            ->where('slug', 'dr-emilia-vaitke')
            ->first()?->forceFill([
                'cover_url' => asset('images/places/veterinary-primary-lg.jpg'),
            ])->save();

        MedicalRecord::query()
            ->where('slug', 'scout-health')
            ->first()?->forceFill([
                'image_url' => asset('images/places/veterinary-secondary-lg.jpg'),
            ])->save();

        $organization = Organization::query()
            ->where('stable_key', 'demo-organization-vilnius-welfare')
            ->first();

        if ($organization instanceof Organization) {
            $organization->forceFill([
                'metadata' => [
                    ...($organization->metadata ?? []),
                    'representative_navigation' => true,
                ],
            ])->save();

            $event = ForumEvent::query()->where('stable_key', 'small-dog-social')->first();

            if ($event instanceof ForumEvent) {
                $event->forceFill([
                    'responsible_organization_id' => $organization->id,
                    'registration_opens_at' => $event->starts_at->subWeek(),
                    'registration_closes_at' => $event->starts_at->subHour(),
                ])->save();
            }
        }

        if (! $classification instanceof DomesticClassification) {
            return;
        }

        $mentorProfile = ForumMentorProfile::query()
            ->whereHas('user', static fn ($query) => $query->where('actor_key', 'demo-lithuanian'))
            ->first();
        $categoryId = ForumCategory::query()->where('stable_key', 'forum.health')->value('id');

        if ($mentorProfile instanceof ForumMentorProfile && is_numeric($categoryId)) {
            ForumMentorScope::query()
                ->where('forum_mentor_profile_id', $mentorProfile->id)
                ->where('mentorship_type', 'first-time-owner')
                ->first()?->forceFill([
                    'forum_category_id' => (int) $categoryId,
                    'taxon_id' => $classification->taxon_id,
                ])->save();
        }
    }

    private function seedDeterministicOrderLifecycleVariants(User $seller): void
    {
        $listing = Listing::query()
            ->where('slug', 'adjustable-enrichment-puzzle-in-excellent-condition')
            ->where('owner_id', $seller->id)
            ->first();
        $buyer = User::query()->where('actor_key', 'demo-marketplace-member')->first();

        if (! $listing instanceof Listing || ! $buyer instanceof User) {
            return;
        }

        $variants = [
            [
                'reference' => 'ORD-DEMO-COMPLETED',
                'reservation_key' => '30000000-0000-4000-8000-000000000001',
                'order_key' => '40000000-0000-4000-8000-000000000001',
                'status' => OrderStatus::Completed,
                'payment_status' => PaymentStatus::Paid,
                'paid_at' => now()->subDays(2),
                'completed_at' => now()->subDay(),
                'cancelled_at' => null,
            ],
            [
                'reference' => 'ORD-DEMO-CANCELLED',
                'reservation_key' => '30000000-0000-4000-8000-000000000002',
                'order_key' => '40000000-0000-4000-8000-000000000002',
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Cancelled,
                'paid_at' => null,
                'completed_at' => null,
                'cancelled_at' => now()->subDay(),
            ],
        ];

        foreach ($variants as $variant) {
            if (Order::query()->where('reference', $variant['reference'])->exists()) {
                continue;
            }

            $reservation = Reservation::query()
                ->where('idempotency_key', $variant['reservation_key'])
                ->first();

            if (! $reservation instanceof Reservation) {
                $reservation = Reservation::factory()->create([
                    'listing_id' => $listing->id,
                    'requester_id' => $buyer->id,
                    'requester_key' => $buyer->actor_key,
                    'requester_name' => $buyer->name,
                    'idempotency_key' => $variant['reservation_key'],
                    'status' => ReservationStatus::Accepted,
                ]);
            }

            Order::factory()->create([
                'listing_id' => $listing->id,
                'reservation_id' => $reservation->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $seller->id,
                'reference' => $variant['reference'],
                'idempotency_key' => $variant['order_key'],
                'buyer_key' => $buyer->actor_key,
                'buyer_name' => $buyer->name,
                'seller_key' => $seller->actor_key,
                'seller_name' => $seller->name,
                'unit_price' => $listing->price,
                'total_amount' => $listing->price,
                'status' => $variant['status'],
                'payment_status' => $variant['payment_status'],
                'paid_at' => $variant['paid_at'],
                'completed_at' => $variant['completed_at'],
                'cancelled_at' => $variant['cancelled_at'],
            ]);
        }
    }
}
