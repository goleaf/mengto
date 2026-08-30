<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlaceVisibility;
use App\Models\Place;
use App\Models\PlaceMergeRedirect;
use App\Models\User;
use Illuminate\Support\Str;

/** @extends ApplicationFactory<PlaceMergeRedirect> */
final class PlaceMergeRedirectFactory extends ApplicationFactory
{
    protected $model = PlaceMergeRedirect::class;

    public function definition(): array
    {
        return [
            'source_place_id' => Place::factory(),
            'destination_place_id' => Place::factory(),
            'created_by_user_id' => User::factory(),
            'source_identifier' => 'merged-place-'.Str::lower((string) Str::ulid()),
            'source_visibility' => PlaceVisibility::Public,
            'created_at' => now(),
        ];
    }
}
