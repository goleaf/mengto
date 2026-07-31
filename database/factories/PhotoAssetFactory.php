<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PhotoAsset;
use Illuminate\Support\Str;

/**
 * @extends ApplicationFactory<PhotoAsset>
 */
final class PhotoAssetFactory extends ApplicationFactory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $postKey = 'post-'.Str::lower((string) Str::ulid());

        return [
            'key' => $postKey.'-photo-1',
            'post_key' => $postKey,
            'position' => 1,
        ];
    }
}
