<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ForumJournalType;
use Illuminate\Validation\ValidationException;

final class ForumJournalMetricRegistry
{
    /**
     * @var array<string, array{unit: string, min: float, max: float}>
     */
    private const DEFINITIONS = [
        'weight_kg' => ['unit' => 'kg', 'min' => 0.001, 'max' => 5000.0],
        'weight_g' => ['unit' => 'g', 'min' => 0.001, 'max' => 1000000.0],
        'temperature_c' => ['unit' => 'celsius', 'min' => -5.0, 'max' => 80.0],
        'duration_minutes' => ['unit' => 'minutes', 'min' => 0.0, 'max' => 10080.0],
        'exercise_minutes' => ['unit' => 'minutes', 'min' => 0.0, 'max' => 1440.0],
        'sleep_hours' => ['unit' => 'hours', 'min' => 0.0, 'max' => 24.0],
        'count' => ['unit' => 'count', 'min' => 0.0, 'max' => 100000.0],
        'success_percent' => ['unit' => 'percent', 'min' => 0.0, 'max' => 100.0],
        'humidity_percent' => ['unit' => 'percent', 'min' => 0.0, 'max' => 100.0],
        'intensity_score' => ['unit' => 'score-10', 'min' => 0.0, 'max' => 10.0],
        'pain_score' => ['unit' => 'score-10', 'min' => 0.0, 'max' => 10.0],
        'mobility_score' => ['unit' => 'score-10', 'min' => 0.0, 'max' => 10.0],
        'appetite_score' => ['unit' => 'score-10', 'min' => 0.0, 'max' => 10.0],
        'stress_score' => ['unit' => 'score-10', 'min' => 0.0, 'max' => 10.0],
        'socialization_score' => ['unit' => 'score-10', 'min' => 0.0, 'max' => 10.0],
        'range_of_motion_deg' => ['unit' => 'degrees', 'min' => 0.0, 'max' => 360.0],
        'ph' => ['unit' => 'ph', 'min' => 0.0, 'max' => 14.0],
        'ammonia_mg_l' => ['unit' => 'mg-l', 'min' => 0.0, 'max' => 1000.0],
        'nitrite_mg_l' => ['unit' => 'mg-l', 'min' => 0.0, 'max' => 1000.0],
        'nitrate_mg_l' => ['unit' => 'mg-l', 'min' => 0.0, 'max' => 5000.0],
        'litter_count' => ['unit' => 'count', 'min' => 0.0, 'max' => 1000.0],
    ];

    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     unit: string,
     *     unit_label: string,
     *     min: float,
     *     max: float
     * }>
     */
    public function definitions(ForumJournalType $type): array
    {
        return collect($this->keysFor($type))
            ->map(function (string $key): array {
                $definition = self::DEFINITIONS[$key];

                return [
                    'key' => $key,
                    'label' => __("forum_journals.metrics.{$key}"),
                    'unit' => $definition['unit'],
                    'unit_label' => __("forum_journals.units.{$definition['unit']}"),
                    'min' => $definition['min'],
                    'max' => $definition['max'],
                ];
            })
            ->all();
    }

    /**
     * @param  list<array{key: string, value: int|float|string}>  $measurements
     * @return list<array{
     *     metric_key: string,
     *     numeric_value: string,
     *     unit: string,
     *     position: int
     * }>
     */
    public function normalize(
        ForumJournalType $type,
        array $measurements,
    ): array {
        if (count($measurements) > 8) {
            throw ValidationException::withMessages([
                'entryForm.measurements' => __('forum_journals.validation.measurement_count'),
            ]);
        }

        $allowed = array_flip($this->keysFor($type));
        $seen = [];
        $normalized = [];

        foreach ($measurements as $index => $measurement) {
            $key = trim($measurement['key']);
            $value = $measurement['value'];

            if (! isset($allowed[$key]) || isset($seen[$key]) || ! is_numeric($value)) {
                throw ValidationException::withMessages([
                    "entryForm.measurements.{$index}" => __('forum_journals.validation.measurement'),
                ]);
            }

            $number = (float) $value;
            $definition = self::DEFINITIONS[$key];

            if (! is_finite($number)
                || $number < $definition['min']
                || $number > $definition['max']
            ) {
                throw ValidationException::withMessages([
                    "entryForm.measurements.{$index}" => __('forum_journals.validation.measurement_range', [
                        'min' => $definition['min'],
                        'max' => $definition['max'],
                    ]),
                ]);
            }

            $seen[$key] = true;
            $normalized[] = [
                'metric_key' => $key,
                'numeric_value' => number_format($number, 4, '.', ''),
                'unit' => $definition['unit'],
                'position' => $index + 1,
            ];
        }

        return $normalized;
    }

    /** @return list<string> */
    private function keysFor(ForumJournalType $type): array
    {
        return match ($type) {
            ForumJournalType::General => [
                'weight_kg',
                'duration_minutes',
                'count',
                'intensity_score',
            ],
            ForumJournalType::Training => [
                'duration_minutes',
                'count',
                'success_percent',
            ],
            ForumJournalType::Behavior => [
                'count',
                'duration_minutes',
                'intensity_score',
            ],
            ForumJournalType::Recovery => [
                'weight_kg',
                'temperature_c',
                'pain_score',
                'appetite_score',
            ],
            ForumJournalType::Weight => ['weight_kg', 'weight_g'],
            ForumJournalType::Rehabilitation => [
                'exercise_minutes',
                'range_of_motion_deg',
                'pain_score',
                'mobility_score',
            ],
            ForumJournalType::AdoptionAdaptation => [
                'stress_score',
                'appetite_score',
                'sleep_hours',
            ],
            ForumJournalType::Foster => [
                'weight_kg',
                'weight_g',
                'appetite_score',
                'socialization_score',
            ],
            ForumJournalType::Aquarium => [
                'temperature_c',
                'ph',
                'ammonia_mg_l',
                'nitrite_mg_l',
                'nitrate_mg_l',
            ],
            ForumJournalType::Terrarium => [
                'temperature_c',
                'humidity_percent',
                'weight_g',
            ],
            ForumJournalType::PregnancyNewborn => [
                'weight_kg',
                'weight_g',
                'temperature_c',
                'litter_count',
            ],
            ForumJournalType::SeniorCare => [
                'weight_kg',
                'mobility_score',
                'appetite_score',
                'pain_score',
            ],
        };
    }
}
