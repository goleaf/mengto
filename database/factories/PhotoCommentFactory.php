<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PhotoAsset;
use App\Models\PhotoComment;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<PhotoComment>
 */
final class PhotoCommentFactory extends ApplicationFactory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_asset_id' => PhotoAsset::factory(),
            'user_id' => User::factory(),
            'body' => fake()->sentence(),
            'idempotency_key' => Str::lower((string) Str::ulid()),
        ];
    }
}
