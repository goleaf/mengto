<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\SynchronizeAdoptionCase;
use App\Enums\ListingType;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

final class AdoptionCaseSeeder extends Seeder
{
    public function run(SynchronizeAdoptionCase $synchronize): void
    {
        Listing::query()
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
            ->where('type', ListingType::Adoption->value)
            ->chunkById(100, function (Collection $listings) use ($synchronize): void {
                foreach ($listings as $listing) {
                    $synchronize->handle($listing);
                }
            });
    }
}
