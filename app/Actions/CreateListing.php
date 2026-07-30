<?php

namespace App\Actions;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Enums\ModerationStatus;
use App\Enums\SellerType;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Services\ForumActor;
use App\Services\ListingSafety;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateListing
{
    public function __construct(
        private readonly ForumActor $actor,
        private readonly ListingSafety $safety,
    ) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Listing
    {
        return DB::transaction(function () use ($data): Listing {
            $identity = $this->actor->identity();
            $type = ListingType::from((string) $data['type']);
            $sellerType = SellerType::from((string) $data['seller_type']);
            $assessment = $this->safety->assess($data);
            $publishRequested = $data['intent'] === 'publish';
            $moderationStatus = $assessment['manual_review']
                ? ModerationStatus::Pending
                : ModerationStatus::Approved;
            $status = $publishRequested && $moderationStatus === ModerationStatus::Approved
                ? ListingStatus::Published
                : ListingStatus::Draft;
            $isFree = in_array($type, [
                ListingType::Adoption,
                ListingType::Free,
                ListingType::ShelterNeed,
            ], true) || (bool) ($data['is_free'] ?? false);
            $gallery = $this->storePhotos($data['photos'] ?? []);
            $videoUrl = $this->storeVideo($data['video'] ?? null);
            $attributes = $this->attributes($data);

            unset(
                $data['intent'],
                $data['safety_acknowledged'],
                $data['photos'],
                $data['video'],
                $data['length_cm'],
                $data['width_cm'],
                $data['height_cm'],
                $data['max_weight_kg'],
                $data['rental_rate_unit'],
                $data['deposit_amount'],
                $data['available_from'],
                $data['available_until'],
                $data['minimum_days'],
                $data['maximum_days'],
                $data['service_duration_minutes'],
                $data['service_format'],
                $data['urgency'],
                $data['received_quantity'],
                $data['needed_by'],
                $data['animal_name'],
                $data['animal_age'],
                $data['animal_sex'],
                $data['temperament'],
                $data['adoption_conditions'],
            );

            $listing = Listing::query()->create([
                ...$data,
                'owner_key' => $identity['key'],
                'owner_name' => $identity['name'],
                'owner_initials' => $identity['initials'],
                'slug' => $this->uniqueSlug((string) $data['title']),
                'type' => $type,
                'price' => $isFree ? null : ($data['price'] ?? null),
                'is_free' => $isFree,
                'species' => array_values($data['species']),
                'delivery_options' => array_values($data['delivery_options']),
                'attributes' => $attributes,
                'gallery' => $gallery,
                'cover_url' => $data['cover_url'] ?? ($gallery[0] ?? null),
                'video_url' => $videoUrl,
                'status' => $status,
                'safety_status' => $assessment['manual_review'] ? 'review-required' : 'community',
                'moderation_status' => $moderationStatus,
                'risk_flags' => $assessment['flags'],
                'seller_type' => $sellerType,
                'is_verified_seller' => false,
                'is_business' => $sellerType !== SellerType::PrivateSeller,
                'contact_policy' => 'platform-only',
                'published_at' => $status === ListingStatus::Published ? now() : null,
            ]);

            AuditLog::query()->create([
                'actor_key' => $identity['key'],
                'actor_role' => 'listing-owner',
                'action' => 'listing.created',
                'target_type' => Listing::class,
                'target_id' => (string) $listing->id,
                'metadata' => [
                    'status' => $listing->status->value,
                    'type' => $listing->type->value,
                    'moderation_status' => $listing->moderation_status->value,
                    'risk_flags' => $listing->risk_flags,
                ],
            ]);

            return $listing;
        });
    }

    /** @param array<int, UploadedFile> $photos */
    private function storePhotos(array $photos): array
    {
        return collect($photos)
            ->filter(fn (mixed $photo): bool => $photo instanceof UploadedFile)
            ->map(fn (UploadedFile $photo): string => Storage::disk('public')->url(
                $photo->store('marketplace/listings', 'public'),
            ))
            ->values()
            ->all();
    }

    private function storeVideo(mixed $video): ?string
    {
        if (! $video instanceof UploadedFile) {
            return null;
        }

        return Storage::disk('public')->url($video->store('marketplace/listing-videos', 'public'));
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        return collect([
            'length_cm' => $data['length_cm'] ?? null,
            'width_cm' => $data['width_cm'] ?? null,
            'height_cm' => $data['height_cm'] ?? null,
            'max_weight_kg' => $data['max_weight_kg'] ?? null,
            'rate_unit' => $data['rental_rate_unit'] ?? null,
            'deposit_amount' => $data['deposit_amount'] ?? null,
            'available_from' => $data['available_from'] ?? null,
            'available_until' => $data['available_until'] ?? null,
            'minimum_days' => $data['minimum_days'] ?? null,
            'maximum_days' => $data['maximum_days'] ?? null,
            'service_duration_minutes' => $data['service_duration_minutes'] ?? null,
            'service_format' => $data['service_format'] ?? null,
            'urgency' => $data['urgency'] ?? null,
            'received_quantity' => $data['received_quantity'] ?? null,
            'needed_by' => $data['needed_by'] ?? null,
            'animal_name' => $data['animal_name'] ?? null,
            'animal_age' => $data['animal_age'] ?? null,
            'animal_sex' => $data['animal_sex'] ?? null,
            'temperament' => $data['temperament'] ?? null,
            'adoption_conditions' => $data['adoption_conditions'] ?? null,
        ])->reject(fn (mixed $value): bool => $value === null || $value === '')->all();
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'listing';
        $slug = $base;
        $suffix = 2;

        while (Listing::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
