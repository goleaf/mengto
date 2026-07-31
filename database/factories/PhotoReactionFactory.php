<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PhotoAsset;
use App\Models\PhotoReaction;
use App\Models\User;

/**
 * @extends ApplicationFactory<PhotoReaction>
 */
final class PhotoReactionFactory extends ApplicationFactory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'photo_asset_id' => PhotoAsset::factory(),
            'user_id' => User::factory(),
            'reaction' => fake()->randomElement(['like', 'love', 'funny', 'support', 'useful']),
        ];
    }
}
