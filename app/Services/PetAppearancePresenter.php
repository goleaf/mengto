<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetAppearanceColor;
use App\Enums\PetAppearancePattern;
use App\Models\PetProfile;

final readonly class PetAppearancePresenter
{
    public function __construct(private LocaleFormatter $formatter) {}

    /**
     * @return array{
     *     primary_color: string|null,
     *     additional_colors: list<string>,
     *     additional_color_list: string|null,
     *     patterns: list<string>,
     *     pattern_list: string|null,
     *     color_details: string,
     *     feather_color_details: string,
     *     scale_color_details: string,
     *     seasonal_color_changes: string
     * }|null
     */
    public function for(PetProfile $profile): ?array
    {
        $appearance = data_get($profile->profile_data, 'appearance');

        if (! is_array($appearance)) {
            return null;
        }

        $primary = $this->color($appearance['primary_color'] ?? null);
        $additional = $this->labels(
            $appearance['additional_colors'] ?? null,
            PetAppearanceColor::class,
        );
        $patterns = $this->labels(
            $appearance['patterns'] ?? null,
            PetAppearancePattern::class,
        );
        $colorDetails = $this->text($appearance['color_details'] ?? null);
        $featherDetails = $this->text($appearance['feather_color_details'] ?? null);
        $scaleDetails = $this->text($appearance['scale_color_details'] ?? null);
        $seasonalChanges = $this->text($appearance['seasonal_color_changes'] ?? null);

        if ($primary === null
            && $additional === []
            && $patterns === []
            && $colorDetails === ''
            && $featherDetails === ''
            && $scaleDetails === ''
            && $seasonalChanges === '') {
            return null;
        }

        return [
            'primary_color' => $primary,
            'additional_colors' => $additional,
            'additional_color_list' => $additional === [] ? null : $this->formatter->list($additional),
            'patterns' => $patterns,
            'pattern_list' => $patterns === [] ? null : $this->formatter->list($patterns),
            'color_details' => $colorDetails,
            'feather_color_details' => $featherDetails,
            'scale_color_details' => $scaleDetails,
            'seasonal_color_changes' => $seasonalChanges,
        ];
    }

    private function color(mixed $value): ?string
    {
        return is_string($value) ? PetAppearanceColor::tryFrom($value)?->label() : null;
    }

    /**
     * @template T of \BackedEnum
     *
     * @param  class-string<T>  $enum
     * @return list<string>
     */
    private function labels(mixed $values, string $enum): array
    {
        if (! is_array($values)) {
            return [];
        }

        $labels = [];

        foreach ($values as $value) {
            $case = is_string($value) ? $enum::tryFrom($value) : null;

            if ($case !== null && method_exists($case, 'label')) {
                $labels[] = $case->label();
            }
        }

        return $labels;
    }

    private function text(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }
}
