<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PetBirthDatePrecision;
use App\Models\PetProfile;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final class PetProfileAgeCalculator
{
    /** @return array{minimum: int, maximum: int}|null */
    public function monthsRange(PetProfile $profile, ?DateTimeInterface $at = null): ?array
    {
        $reference = $at === null
            ? now()->toImmutable()
            : CarbonImmutable::instance($at);

        return match ($profile->birth_date_precision) {
            PetBirthDatePrecision::Exact,
            PetBirthDatePrecision::Estimated => $this->dateRange($profile, $reference),
            PetBirthDatePrecision::Month => $this->periodRange(
                $profile->birth_date?->startOfMonth(),
                $profile->birth_date?->endOfMonth(),
                $reference,
            ),
            PetBirthDatePrecision::Year => $this->periodRange(
                $profile->birth_date?->startOfYear(),
                $profile->birth_date?->endOfYear(),
                $reference,
            ),
            PetBirthDatePrecision::AgeEstimate => $this->estimateRange($profile, $reference),
            PetBirthDatePrecision::Unknown => null,
        };
    }

    /** @return array{minimum: int, maximum: int}|null */
    private function dateRange(PetProfile $profile, CarbonImmutable $reference): ?array
    {
        if ($profile->birth_date === null || $profile->birth_date->isAfter($reference)) {
            return null;
        }

        $months = (int) floor($profile->birth_date->diffInMonths($reference));

        return ['minimum' => $months, 'maximum' => $months];
    }

    /** @return array{minimum: int, maximum: int}|null */
    private function periodRange(
        ?CarbonImmutable $oldestBirth,
        ?CarbonImmutable $youngestBirth,
        CarbonImmutable $reference,
    ): ?array {
        if ($oldestBirth === null
            || $youngestBirth === null
            || $oldestBirth->isAfter($reference)) {
            return null;
        }

        if ($youngestBirth->isAfter($reference)) {
            $youngestBirth = $reference;
        }

        return [
            'minimum' => (int) floor($youngestBirth->diffInMonths($reference)),
            'maximum' => (int) floor($oldestBirth->diffInMonths($reference)),
        ];
    }

    /** @return array{minimum: int, maximum: int}|null */
    private function estimateRange(PetProfile $profile, CarbonImmutable $reference): ?array
    {
        if ($profile->estimated_age_months === null
            || $profile->estimated_age_recorded_at === null) {
            return null;
        }

        $elapsed = $profile->estimated_age_recorded_at->isBefore($reference)
            ? (int) floor($profile->estimated_age_recorded_at->diffInMonths($reference))
            : 0;
        $months = $profile->estimated_age_months + $elapsed;

        return ['minimum' => $months, 'maximum' => $months];
    }
}
