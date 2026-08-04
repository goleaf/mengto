<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetCoatLength;
use App\Enums\PetCoatTexture;
use App\Enums\PetFeatherType;
use App\Enums\PetManeType;
use App\Enums\PetSeasonalShedding;
use App\Enums\PetUndercoatType;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class PetBodyCoveringNormalizer
{
    public const MAX_SKIN_CONDITION_LENGTH = 1000;

    /** @var list<string> */
    private const STRUCTURED_KEYS = [
        'coat_length',
        'coat_texture',
        'undercoat',
        'hairless',
        'feather_type',
        'skin_condition',
        'mane_type',
        'seasonal_shedding',
    ];

    public function __construct(private PetBodyCoveringSchema $schema) {}

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function apply(array $data, string $species, array $existing): array
    {
        if (! $this->containsInput($data)) {
            return $existing;
        }

        $fields = $this->schema->for($species);
        $hairless = $fields['coat'] ? $this->boolean($data['hairless'] ?? false) : false;

        if ($hairless && ($this->filled($data['coat_length'] ?? null)
            || $this->filled($data['coat_texture'] ?? null)
            || $this->filled($data['undercoat'] ?? null))) {
            $this->invalid('hairless', 'body_covering_hairless_conflict');
        }

        $bodyCovering = [
            'schema_version' => 1,
            'coat_length' => $fields['coat'] && ! $hairless
                ? $this->nullableEnum($data['coat_length'] ?? null, PetCoatLength::class, 'coat_length')
                : null,
            'coat_texture' => $fields['coat'] && ! $hairless
                ? $this->nullableEnum($data['coat_texture'] ?? null, PetCoatTexture::class, 'coat_texture')
                : null,
            'undercoat' => $fields['coat'] && ! $hairless
                ? $this->nullableEnum($data['undercoat'] ?? null, PetUndercoatType::class, 'undercoat')
                : null,
            'hairless' => $hairless,
            'feather_type' => $fields['feathers']
                ? $this->nullableEnum($data['feather_type'] ?? null, PetFeatherType::class, 'feather_type')
                : null,
            'skin_condition' => $fields['skin']
                ? $this->text($data['skin_condition'] ?? null, 'skin_condition')
                : '',
            'mane_type' => $fields['mane']
                ? $this->nullableEnum($data['mane_type'] ?? null, PetManeType::class, 'mane_type')
                : null,
            'seasonal_shedding' => $fields['shedding']
                ? $this->nullableEnum(
                    $data['seasonal_shedding'] ?? null,
                    PetSeasonalShedding::class,
                    'seasonal_shedding',
                )
                : null,
        ];

        $next = $existing;

        if ($this->isEmpty($bodyCovering)) {
            unset($next['body_covering']);

            return $next;
        }

        $next['body_covering'] = $bodyCovering;

        return $next;
    }

    /** @param array<string, mixed> $data */
    private function containsInput(array $data): bool
    {
        foreach (self::STRUCTURED_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     */
    private function nullableEnum(mixed $value, string $enum, string $field): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value) || $enum::tryFrom($value) === null) {
            $this->invalid($field, 'body_covering_selection');
        }

        return $value;
    }

    private function boolean(mixed $value): bool
    {
        if (! is_bool($value)) {
            $this->invalid('hairless', 'body_covering_hairless');
        }

        return $value;
    }

    private function text(mixed $value, string $field): string
    {
        if ($value === null) {
            return '';
        }

        if (! is_string($value)) {
            $this->invalid($field, 'body_covering_text');
        }

        $normalized = trim($value);

        if (Str::length($normalized) > self::MAX_SKIN_CONDITION_LENGTH) {
            $this->invalid($field, 'body_covering_text');
        }

        return $normalized;
    }

    private function filled(mixed $value): bool
    {
        return $value !== null && $value !== '';
    }

    /** @param array<string, mixed> $bodyCovering */
    private function isEmpty(array $bodyCovering): bool
    {
        return $bodyCovering['coat_length'] === null
            && $bodyCovering['coat_texture'] === null
            && $bodyCovering['undercoat'] === null
            && $bodyCovering['hairless'] === false
            && $bodyCovering['feather_type'] === null
            && $bodyCovering['skin_condition'] === ''
            && $bodyCovering['mane_type'] === null
            && $bodyCovering['seasonal_shedding'] === null;
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => __("pet_profiles.validation.{$message}"),
        ]);
    }
}
