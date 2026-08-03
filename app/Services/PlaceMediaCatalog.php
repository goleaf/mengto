<?php

declare(strict_types=1);

namespace App\Services;

final class PlaceMediaCatalog
{
    /** @var array<string, string> */
    private const CATEGORY_ASSETS = [
        'park' => 'park',
        'route' => 'park',
        'dog-park' => 'dog-park',
        'vet' => 'veterinary',
        'emergency-vet' => 'veterinary',
        'grooming' => 'grooming',
        'pet-cafe' => 'community',
        'pet-store' => 'pet-store',
        'shelter' => 'shelter',
    ];

    /**
     * @return array{image: string, image_small: string, image_medium: string}
     */
    public function primary(string $category): array
    {
        return $this->variant($category, 'primary');
    }

    /**
     * @return array<int, array{image: string, image_small: string, image_medium: string}>
     */
    public function gallery(string $category): array
    {
        return [
            $this->variant($category, 'primary'),
            $this->variant($category, 'secondary'),
            $this->variant($category, 'tertiary'),
        ];
    }

    /**
     * @return array{image: string, image_small: string, image_medium: string}
     */
    private function variant(string $category, string $variant): array
    {
        $asset = self::CATEGORY_ASSETS[$category] ?? 'community';
        $prefix = "/images/places/{$asset}-{$variant}";

        return [
            'image' => "{$prefix}-lg.jpg",
            'image_small' => "{$prefix}-sm.jpg",
            'image_medium' => "{$prefix}-md.jpg",
        ];
    }
}
