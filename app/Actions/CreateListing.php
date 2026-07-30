<?php

namespace App\Actions;

use App\Enums\ListingStatus;
use App\Enums\ListingType;
use App\Models\AuditLog;
use App\Models\Listing;
use App\Services\ForumActor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateListing
{
    public function __construct(private readonly ForumActor $actor) {}

    /** @param array<string, mixed> $data */
    public function handle(array $data): Listing
    {
        return DB::transaction(function () use ($data): Listing {
            $identity = $this->actor->identity();
            $type = ListingType::from((string) $data['type']);
            $status = $data['intent'] === 'publish'
                ? ListingStatus::Published
                : ListingStatus::Draft;
            $isFree = $type === ListingType::Adoption || (bool) ($data['is_free'] ?? false);

            unset($data['intent'], $data['safety_acknowledged']);

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
                'gallery' => [],
                'status' => $status,
                'safety_status' => 'community',
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
                ],
            ]);

            return $listing;
        });
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
