<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetCoatLength;
use App\Enums\PetCoatTexture;
use App\Enums\PetFeatherType;
use App\Enums\PetManeType;
use App\Enums\PetSeasonalShedding;
use App\Enums\PetUndercoatType;
use App\Models\PetProfile;

final readonly class PetBodyCoveringPresenter
{
    public function __construct(private PetBodyCoveringSchema $schema) {}

    /**
     * @return array{
     *     coat_length: string|null,
     *     coat_texture: string|null,
     *     undercoat: string|null,
     *     hairless: string|null,
     *     feather_type: string|null,
     *     mane_type: string|null,
     *     seasonal_shedding: string|null
     * }|null
     */
    public function for(PetProfile $profile): ?array
    {
        $bodyCovering = data_get($profile->profile_data, 'body_covering');

        if (! is_array($bodyCovering)) {
            return null;
        }

        $fields = $this->schema->for($profile->species);
        $presented = [
            'coat_length' => $fields['coat']
                ? $this->label($bodyCovering['coat_length'] ?? null, PetCoatLength::class)
                : null,
            'coat_texture' => $fields['coat']
                ? $this->label($bodyCovering['coat_texture'] ?? null, PetCoatTexture::class)
                : null,
            'undercoat' => $fields['coat']
                ? $this->label($bodyCovering['undercoat'] ?? null, PetUndercoatType::class)
                : null,
            'hairless' => $fields['coat'] && ($bodyCovering['hairless'] ?? null) === true
                ? __('pet_profiles.body_covering.hairless_public')
                : null,
            'feather_type' => $fields['feathers']
                ? $this->label($bodyCovering['feather_type'] ?? null, PetFeatherType::class)
                : null,
            'mane_type' => $fields['mane']
                ? $this->label($bodyCovering['mane_type'] ?? null, PetManeType::class)
                : null,
            'seasonal_shedding' => $fields['shedding']
                ? $this->label(
                    $bodyCovering['seasonal_shedding'] ?? null,
                    PetSeasonalShedding::class,
                )
                : null,
        ];

        return array_filter($presented, static fn (?string $value): bool => $value !== null) === []
            ? null
            : $presented;
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     */
    private function label(mixed $value, string $enum): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $case = $enum::tryFrom($value);

        return $case !== null && method_exists($case, 'label')
            ? $case->label()
            : null;
    }
}
