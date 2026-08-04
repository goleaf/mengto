<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetAppearanceColor;
use App\Enums\PetAppearancePattern;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PetAppearanceNormalizer
{
    public const MAX_ADDITIONAL_COLORS = 4;

    public const MAX_TEXT_LENGTH = 1000;

    /** @var list<string> */
    private const STRUCTURED_KEYS = [
        'primary_color',
        'additional_colors',
        'patterns',
        'color_details',
        'feather_color_details',
        'scale_color_details',
        'seasonal_color_changes',
    ];

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function apply(array $data, array $existing): array
    {
        $next = $existing;

        if (array_key_exists('appearance_summary', $data)) {
            $next['appearance_summary'] = $this->text(
                $data['appearance_summary'],
                'appearance_summary',
                1500,
            );
        }

        if (array_key_exists('identifying_marks', $data)) {
            $next['identifying_marks'] = $this->text(
                $data['identifying_marks'],
                'identifying_marks',
                1500,
            );
        }

        if (! $this->containsStructuredInput($data)) {
            return $next;
        }

        $primaryColor = $this->nullableColor($data['primary_color'] ?? null);
        $additionalColors = $this->enumList(
            $data['additional_colors'] ?? [],
            PetAppearanceColor::class,
            self::MAX_ADDITIONAL_COLORS,
            'additional_colors',
        );
        $patterns = $this->enumList(
            $data['patterns'] ?? [],
            PetAppearancePattern::class,
            count(PetAppearancePattern::cases()),
            'patterns',
        );

        if ($primaryColor !== null && in_array($primaryColor->value, $additionalColors, true)) {
            $this->invalid('additional_colors', 'appearance_primary_duplicate');
        }

        $appearance = [
            'schema_version' => 1,
            'primary_color' => $primaryColor?->value,
            'additional_colors' => $additionalColors,
            'patterns' => $patterns,
            'color_details' => $this->text($data['color_details'] ?? null, 'color_details'),
            'feather_color_details' => $this->text(
                $data['feather_color_details'] ?? null,
                'feather_color_details',
            ),
            'scale_color_details' => $this->text(
                $data['scale_color_details'] ?? null,
                'scale_color_details',
            ),
            'seasonal_color_changes' => $this->text(
                $data['seasonal_color_changes'] ?? null,
                'seasonal_color_changes',
            ),
        ];

        if ($this->isEmpty($appearance)) {
            unset($next['appearance']);

            return $next;
        }

        $next['appearance'] = $appearance;

        return $next;
    }

    /** @param array<string, mixed> $data */
    private function containsStructuredInput(array $data): bool
    {
        foreach (self::STRUCTURED_KEYS as $key) {
            if (array_key_exists($key, $data)) {
                return true;
            }
        }

        return false;
    }

    private function nullableColor(mixed $value): ?PetAppearanceColor
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_string($value)) {
            $this->invalid('primary_color', 'appearance_primary_color');
        }

        $color = PetAppearanceColor::tryFrom($value);

        if (! $color instanceof PetAppearanceColor) {
            $this->invalid('primary_color', 'appearance_primary_color');
        }

        return $color;
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return list<string>
     */
    private function enumList(mixed $values, string $enum, int $maximum, string $field): array
    {
        if (! is_array($values) || count($values) > $maximum) {
            $this->invalid($field, 'appearance_selection');
        }

        $normalized = [];

        foreach (array_values($values) as $value) {
            if (! is_string($value) || $enum::tryFrom($value) === null) {
                $this->invalid($field, 'appearance_selection');
            }

            if (in_array($value, $normalized, true)) {
                $this->invalid($field, 'appearance_duplicate');
            }

            $normalized[] = $value;
        }

        return $normalized;
    }

    private function text(mixed $value, string $field, int $maximum = self::MAX_TEXT_LENGTH): string
    {
        if ($value === null) {
            return '';
        }

        if (! is_string($value)) {
            $this->invalid($field, 'appearance_text');
        }

        $normalized = trim($value);

        if (Str::length($normalized) > $maximum) {
            $this->invalid($field, 'appearance_text');
        }

        return $normalized;
    }

    /** @param array<string, mixed> $appearance */
    private function isEmpty(array $appearance): bool
    {
        return $appearance['primary_color'] === null
            && $appearance['additional_colors'] === []
            && $appearance['patterns'] === []
            && $appearance['color_details'] === ''
            && $appearance['feather_color_details'] === ''
            && $appearance['scale_color_details'] === ''
            && $appearance['seasonal_color_changes'] === '';
    }

    private function invalid(string $field, string $message): never
    {
        throw ValidationException::withMessages([
            $field => __("pet_profiles.validation.{$message}"),
        ]);
    }
}
