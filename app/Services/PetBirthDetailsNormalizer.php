<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetBirthDatePrecision;
use App\Models\PetProfile;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Throwable;

final class PetBirthDetailsNormalizer
{
    public const MAX_AGE_YEARS = 500;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $errorKeys
     * @return array{
     *     birth_date: string|null,
     *     birth_date_precision: PetBirthDatePrecision,
     *     estimated_age_months: int|null,
     *     estimated_age_recorded_at: CarbonImmutable|null,
     *     birthday_celebration_month: int|null,
     *     birthday_celebration_day: int|null
     * }
     */
    public function normalize(
        array $data,
        ?PetProfile $current = null,
        array $errorKeys = [],
    ): array {
        $precision = $this->precision($data, $current, $errorKeys);
        [$celebrationMonth, $celebrationDay] = $precision === PetBirthDatePrecision::Exact
            ? [null, null]
            : $this->celebration($data, $errorKeys);
        $birthDate = null;
        $estimatedAgeMonths = null;
        $estimatedAgeRecordedAt = null;

        if ($precision->usesDate()) {
            $birthDate = $this->date(
                $data['birth_date'] ?? null,
                'Y-m-d',
                'birth_date',
                $errorKeys,
            )->toDateString();
        } elseif ($precision === PetBirthDatePrecision::Month) {
            $value = $data['birth_month'] ?? null;

            if (! is_string($value) || $value === '') {
                $legacyDate = $data['birth_date'] ?? null;
                $value = is_string($legacyDate) ? mb_substr($legacyDate, 0, 7) : null;
            }

            $birthDate = $this->date(
                $value,
                'Y-m',
                'birth_month',
                $errorKeys,
            )->startOfMonth()->toDateString();
        } elseif ($precision === PetBirthDatePrecision::Year) {
            $value = $data['birth_year'] ?? null;

            if (($value === null || $value === '') && is_string($data['birth_date'] ?? null)) {
                $value = mb_substr((string) $data['birth_date'], 0, 4);
            }

            $year = $this->boundedInteger(
                $value,
                now()->year - self::MAX_AGE_YEARS,
                now()->year,
                'birth_year',
                $errorKeys,
            );
            $birthDate = CarbonImmutable::create($year, 1, 1)->toDateString();
        } elseif ($precision === PetBirthDatePrecision::AgeEstimate) {
            $estimatedAgeMonths = $this->estimatedAgeMonths($data, $errorKeys);
            $estimatedAgeRecordedAt = $current?->birth_date_precision === PetBirthDatePrecision::AgeEstimate
                && $current->estimated_age_months === $estimatedAgeMonths
                && $current->estimated_age_recorded_at !== null
                    ? $current->estimated_age_recorded_at
                    : now()->toImmutable();
        }

        return [
            'birth_date' => $birthDate,
            'birth_date_precision' => $precision,
            'estimated_age_months' => $estimatedAgeMonths,
            'estimated_age_recorded_at' => $estimatedAgeRecordedAt,
            'birthday_celebration_month' => $celebrationMonth,
            'birthday_celebration_day' => $celebrationDay,
        ];
    }

    /** @param array<string, mixed> $data @param array<string, string> $errorKeys */
    private function precision(
        array $data,
        ?PetProfile $current,
        array $errorKeys,
    ): PetBirthDatePrecision {
        $currentPrecision = $current instanceof PetProfile
            ? $current->birth_date_precision
            : PetBirthDatePrecision::Unknown;
        $value = $data['birth_date_precision'] ?? $currentPrecision;
        $precision = $value instanceof PetBirthDatePrecision
            ? $value
            : PetBirthDatePrecision::tryFrom((string) $value);

        if (! $precision instanceof PetBirthDatePrecision) {
            $this->fail('birth_date_precision', 'birth_precision_invalid', $errorKeys);
        }

        return $precision;
    }

    /** @param array<string, mixed> $data @param array<string, string> $errorKeys */
    private function estimatedAgeMonths(array $data, array $errorKeys): int
    {
        if (array_key_exists('estimated_age_months', $data)) {
            return $this->boundedInteger(
                $data['estimated_age_months'],
                0,
                self::MAX_AGE_YEARS * 12,
                'estimated_age_months',
                $errorKeys,
            );
        }

        if (array_key_exists('estimated_age_years', $data)) {
            $years = $this->boundedInteger(
                $data['estimated_age_years'],
                0,
                self::MAX_AGE_YEARS,
                'estimated_age_years',
                $errorKeys,
            );
            $months = $this->boundedInteger(
                $data['estimated_age_month_remainder'] ?? 0,
                0,
                11,
                'estimated_age_month_remainder',
                $errorKeys,
            );
            $total = ($years * 12) + $months;

            if ($total <= self::MAX_AGE_YEARS * 12) {
                return $total;
            }

            $this->fail('estimated_age_month_remainder', 'birth_value_invalid', $errorKeys);
        }

        if (is_string($data['birth_date'] ?? null)) {
            $legacyDate = $this->date(
                $data['birth_date'],
                'Y-m-d',
                'birth_date',
                $errorKeys,
            );

            return (int) floor($legacyDate->diffInMonths(now()));
        }

        $this->fail('estimated_age_years', 'estimated_age_required', $errorKeys);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $errorKeys
     * @return array{0: int|null, 1: int|null}
     */
    private function celebration(array $data, array $errorKeys): array
    {
        $monthValue = $data['birthday_celebration_month'] ?? null;
        $dayValue = $data['birthday_celebration_day'] ?? null;

        if (($monthValue === null || $monthValue === '') && ($dayValue === null || $dayValue === '')) {
            return [null, null];
        }

        if ($monthValue === null || $monthValue === '' || $dayValue === null || $dayValue === '') {
            $this->fail('birthday_celebration_day', 'celebration_pair_required', $errorKeys);
        }

        $month = $this->boundedInteger($monthValue, 1, 12, 'birthday_celebration_month', $errorKeys);
        $day = $this->boundedInteger($dayValue, 1, 31, 'birthday_celebration_day', $errorKeys);

        try {
            CarbonImmutable::createSafe(2000, $month, $day);
        } catch (Throwable) {
            $this->fail('birthday_celebration_day', 'celebration_invalid', $errorKeys);
        }

        return [$month, $day];
    }

    /** @param array<string, string> $errorKeys */
    private function date(
        mixed $value,
        string $format,
        string $field,
        array $errorKeys,
    ): CarbonImmutable {
        if (! is_string($value) || trim($value) === '') {
            $this->fail($field, 'birth_value_required', $errorKeys);
        }

        try {
            $date = CarbonImmutable::createFromFormat('!'.$format, trim($value));
        } catch (Throwable) {
            $date = null;
        }

        if (! $date instanceof CarbonImmutable
            || $date->format($format) !== trim($value)
            || $date->isFuture()
            || $date->lt(now()->subYears(self::MAX_AGE_YEARS)->startOfYear())) {
            $this->fail($field, 'birth_value_invalid', $errorKeys);
        }

        return $date;
    }

    /** @param array<string, string> $errorKeys */
    private function boundedInteger(
        mixed $value,
        int $minimum,
        int $maximum,
        string $field,
        array $errorKeys,
    ): int {
        $validated = filter_var($value, FILTER_VALIDATE_INT);

        if ($validated === false || $validated < $minimum || $validated > $maximum) {
            $this->fail($field, 'birth_value_invalid', $errorKeys);
        }

        return $validated;
    }

    /** @param array<string, string> $errorKeys */
    private function fail(string $field, string $message, array $errorKeys): never
    {
        throw ValidationException::withMessages([
            $errorKeys[$field] ?? $field => __("pet_profiles.validation.{$message}"),
        ]);
    }
}
